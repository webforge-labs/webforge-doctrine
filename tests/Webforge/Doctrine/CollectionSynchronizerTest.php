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

    $this->synchronizer = new CollectionSynchronizer(
      $this->em,
      $this->em->getRepository('Webforge\Doctrine\Test\Entities\Tag'),
      new EntityFactory('Webforge\Doctrine\Test\Entities\Tag')
    );
  }
  
  protected function getFixtures() {
    return array(new EmptyFixture());
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

  protected function createTags() {
    $tags = array();
    foreach (array('usa', 'whitehouse', 'germany', 'nsa') as $key => $label) {
      $id = $key+1;
      $tags[$label] = $tag = new Tag($label);
      $tag->setId($id);

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
