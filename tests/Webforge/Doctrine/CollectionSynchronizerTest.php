<?php

namespace Webforge\Doctrine;

use Webforge\Doctrine\Test\Entities\Post;
use Webforge\Doctrine\Test\Entities\Author;
use Webforge\Doctrine\Test\Entities\Tag;
use Webforge\Doctrine\Test\Entities\PostImage;
use Webforge\Common\ArrayUtil as A;

class CollectionSynchronizerTest extends CollectionTestCase {

  public function setUp() {
    parent::setUp();

    $this->synchronizer = CollectionSynchronizer::createFor(
      'Webforge\Doctrine\Test\Entities\Post',
      'tags',
      $this->em
    );
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

  public function testProcessingWithItemsWithAnOrderProperty() {
    $this->resetDatabaseOnNextTest();
    $post = $this->createPost();
    $binaries = $this->createBinaries();

    $path1 = $binaries[1]->getPath();
    $path2 = $binaries[2]->getPath();
    $path3 = $binaries[3]->getPath();

    $toCollection = Array(
      (object) array('id'=>NULL, 'position'=>1, 'binary'=>$binaries[1]), 
      (object) array('id'=>NULL, 'position'=>2, 'binary'=>$binaries[2]), 
      (object) array('id'=>NULL, 'position'=>3, 'binary'=>$binaries[3]), 
    );

    // in this example a PostImage is always unique for one post connected to one image
    // so the only dbimages we have, are in the toCollection
    // this is a special form of an hydrator, which finds the toObject in the toCollection via LINQ-Query
    $dbImages = $post->getImages()->toArray(); 
    $dbImages = A::indexBy($dbImages, function($postImage) {
      return $postImage->getBinary()->getPath();
    });

    $this->synchronizer->setHydrator(function($image) use (&$dbImages) {
      if (array_key_exists($image->binary->getPath(), $dbImages)) {
        return $dbImages[$image->binary->getPath()];
      }

      return NULL;
    });

    $this->synchronizer->setCreater(function($image, $post) {
      return new PostImage($image->binary, $post, $image->position);
    });

    $this->synchronizer->setAdder(function($post, $image) {
      $post->addImage($image);
    });

    $this->synchronizer->process($post, $post->getImages(), $toCollection);

    $post = $this->assertImagesOrdered($post, array($path1, $path2, $path3));
    // note: after this $post is another object as $post before and we have CLEARED the entity-manager

    // switch order by position
    $toCollection[0]->position = 2;
    $toCollection[1]->position = 1;

    // refresh binaries (because we cleared em)
    $binariesByPath = A::indexBy($this->em->getRepository(get_class($binaries[1]))->findAll(), 'path');
    foreach ($toCollection as $key => $image) {
      $image->binary = $binariesByPath[$image->binary->getPath()];
    }

    // refresh db index
    $dbImages = $post->getImages()->toArray(); 
    $dbImages = A::indexBy($dbImages, function($postImage) {
      return $postImage->getBinary()->getPath();
    });

    $this->synchronizer->process($post, $post->getImages(), $toCollection);
    $this->assertImagesOrdered($post, array($path2, $path1, $path3));
  }

  protected function assertImagesOrdered($post, array $paths) {
    $post = $this->refresh($post);

    $postImages = $post->getImages()->toArray();

    $normalizedImages = array_values(
      array_map(
        function($image) {
          return $image->getBinary()->getPath();
        }, 

        $postImages
      )
    );

    foreach ($paths as $key => $path) {
      $this->assertEquals($key+1, $postImages[$key]->getPosition(), 'Position for image: '.$path.' is wrong');
    }

    $this->assertEquals(
      $paths,
      $normalizedImages,
      "the synchronized collection post.images does not match the expected\n".
      implode(', ', $paths)."\n".
      implode(', ', $normalizedImages)."\n"
    );
    return $post;
  }
}
