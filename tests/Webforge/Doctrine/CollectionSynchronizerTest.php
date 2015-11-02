<?php

namespace Webforge\Doctrine;

use Webforge\Doctrine\Fixtures\EmptyFixture;
use Webforge\Doctrine\Test\Entities\Post;
use Webforge\Doctrine\Test\Entities\Author;
use Webforge\Doctrine\Test\Entities\Tag;
use Webforge\Common\DateTime\DateTime;

class CollectionSynchronizerTest extends \Webforge\Doctrine\Test\DatabaseTestCase {

  public function setUp() {
    parent::setUp();

    $this->synchronizer = CollectionSynchronizer::createFor(
      'Webforge\Doctrine\Test\Entities\Post',
      'tags',
      $this->em
    );
  }
  
  protected function getFixtures() {
    return array(new EmptyFixture());
  }

  public function testCreateForWarnsForNonOwningSideCollections() {
    $this->setExpectedException('LogicException', 'The collection Webforge\Doctrine\Test\Entities\Category::posts is not the owningSide');

    CollectionSynchronizer::createFor(
      'Webforge\Doctrine\Test\Entities\Category',
      'posts',
      $this->em
    );
  }

  public function testProcessingWithMissingToCollectionFieldsForUniqueHydration() {
    $post = $this->createPost();
    $this->synchronizer->addUniqueConstraint(array('label'));

    $toCollection = Array(
      array('label'=>'election'), // insert
      array('id'=>7), // label is missing
    );

    $this->setExpectedException('RuntimeException', 'The number of parameters needed for a unique constraint query, does not match for');
    $this->synchronizer->process($post, $post->getTags(), $toCollection);
  }

  public function testProcessingWithRemovingUpdatingAndInsertingIntoFromCollection() {
    $this->resetDatabaseOnNextTest();
    list($post, $tags) = $this->createPostWithTags(array('usa', 'germany', 'nsa'));

    $toCollection = Array(
      array('id'=>$tags->usa->getId(), 'label'=>'usa'), // update (no change)
      //delete: germany
      array('id'=>NULL, 'label'=>'election'), // insert
      array('id'=>$tags->whitehouse->getId(), 'label'=>'white-house'), // update (change label)
      //delete: nsa
    );

    $this->synchronizer->addUniqueConstraint(array('label'));
    $this->synchronizer->process($post, $post->getTags(), $toCollection);

    $this->assertSynchronizedTags($post, array('usa', 'election', 'white-house'));
  }

  public function testProcessingWithOwnHydration() {
    $this->resetDatabaseOnNextTest();

    list($post, $tags) = $this->createPostWithTags(array());

    $toCollection = Array(
      array('id'=>$tags->usa->getId(), 'label'=>'usa'), // update (no change)
    );

    $this->synchronizer->addUniqueConstraint(array('label'));

    $test = $this;
    $wasCalled = FALSE;
    $this->synchronizer->setHydrator(function($toObject, \Doctrine\ORM\EntityRepository $repository, $entity) use ($test, $toCollection, $post, &$wasCalled) {
      $wasCalled = TRUE;
      $test->assertEquals($toCollection[0], $toObject, 'toObject is passed to hydrator');
      $test->assertSame($post, $entity, 'post is passed as entity');

      // remember that $tags is detached in test here
      return $repository->findOneBy(array('label'=>$toObject['label']));
    });

    $this->synchronizer->process($post, $post->getTags(), $toCollection);

    $this->assertTrue($wasCalled, 'the hydrator set to synchronizer with setHydrator should be used while processing a non empty toCollection');
    $this->assertSynchronizedTags($post, array('usa'));
  }

  public function testOverwritingRemoverAndAdderAndMerger() {
    $this->resetDatabaseOnNextTest();
    $this->synchronizer->addUniqueConstraint(array('label'));

    list($post, $tags) = $this->createPostWithTags(array('nsa', 'usa', 'whitehouse', 'germany'));

    $toCollection = Array(
      array('id'=>NULL, 'label'=>'scottland'), // new
      array('id'=>$tags->usa->getId(), 'label'=>'usa'), // update (no change)
    );

    $adderCalled = $removerCalled = $mergerCalled = 0;
    $that = $this;
    $this->synchronizer->setAdder(function ($entity, $collectionEntity, $toObject) use (&$adderCalled, $that, $post) {
      $that->assertSame($post, $entity);
      $that->assertInstanceOf('Webforge\Doctrine\Test\Entities\Tag', $collectionEntity);
      $adderCalled++;
    });

    $this->synchronizer->setRemover(function ($entity, $collectionEntity) use (&$removerCalled, $that, $post) {
      $that->assertSame($post, $entity);
      $that->assertInstanceOf('Webforge\Doctrine\Test\Entities\Tag', $collectionEntity);
      $removerCalled++;
    });

    $this->synchronizer->setMerger(function ($entity, $collectionEntity, $toObject) use (&$mergerCalled, $that, $post) {
      $that->assertSame($post, $entity);
      $that->assertInstanceOf('Webforge\Doctrine\Test\Entities\Tag', $collectionEntity);
      $that->assertEquals('usa', $collectionEntity->getLabel());
      $that->assertEquals('usa', $toObject['label']);
      $mergerCalled++;
    });

    $this->synchronizer->process($post, $post->getTags(), $toCollection);

    $this->assertEquals(2, $adderCalled, 'adder calltimes'); // one time: for the merger, second time for the adder
    $this->assertEquals(3, $removerCalled, 'remover calltimes');
    $this->assertEquals(1, $mergerCalled, 'merger calltimes');
  }

  protected function createTags() {
    $tags = array();
    foreach (array('usa', 'whitehouse', 'germany', 'nsa') as $key => $label) {
      $tags[$label] = $tag = new Tag($label);

      $this->em->persist($tag);
    }

    return (object) $tags;
  }

  protected function createPost() {
    $post = new Post($author = new Author('p.scheit@ps-webforge.net'));
    $post->setActive(true);
    $post->setCreated(DateTime::now());

    $this->em->persist($post);
    $this->em->persist($author);
    return $post;
  }

  // note: $tags are detached after createPostWithTags
  protected function createPostWithTags(array $labels) {
    $post = $this->createPost();
    $tags = $this->createTags();

    foreach ($labels as $label) {
      $post->addTag($tags->$label);
    }

    $this->em->flush();
    $this->em->clear();

    $post = $this->em->getRepository(get_class($post))->findOneBy(array('id'=>$post->getId()));

    return array($post, $tags);
  }

  protected function assertSynchronizedTags($post, array $tags) {
    $this->em->flush();
    $this->em->clear();
    $post = $this->em->getRepository(get_class($post))->findOneBy(array('id'=>$post->getId()));

    $normalizedTags = array_values(
      array_map(
        function($tag) {
          return $tag->getLabel();
        }, 

        $post->getTags()->toArray()
      )
    );

    $this->assertArrayEquals(
      $tags,
      $normalizedTags,
      "the synchronized collection post.tags does not match the expected\n".
      implode(', ', $tags)."\n".
      implode(', ', $normalizedTags)."\n"
    );
  }
}
