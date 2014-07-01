<?php

namespace Webforge\Doctrine;

use Webforge\Doctrine\Fixtures\EmptyFixture;
use Webforge\Doctrine\Test\Entities\Post;
use Webforge\Doctrine\Test\Entities\Author;
use Webforge\Doctrine\Test\Entities\Tag;
use Webforge\Common\DateTime\DateTime;

class CollectionSynchronizerManyToOneTest extends \Webforge\Doctrine\Test\DatabaseTestCase {

  public function setUp() {
    parent::setUp();

    $this->synchronizer = CollectionSynchronizer::createFor(
      'Webforge\Doctrine\Test\Entities\Author',
      'revisionedPosts',
      $this->em
    );

    $this->author = new Author('p.scheit@ps-webforge.net');
  }
  
  protected function getFixtures() {
    return array(new EmptyFixture());
  }

  public function testProcessingForAssociationOnNotOwningSideInManyToOne() {
    $revisor = $this->createRevisor();

    $post1 = $this->createPost($revisor);
    $post2 = $this->createPost($revisor);

    $this->em->flush();

    $toCollection = Array(
      array('id'=>$post1->getId())
      // delete post2
    );

    $this->synchronizer->process($revisor, $revisor->getRevisionedPosts(), $toCollection);

    $this->assertCount(1, $revisor->getRevisionedPosts());
  }

  protected function createRevisor() {
    $author = new Author('ik@ps-webforge.net');
    $this->em->persist($author);

    return $author;
  }

  protected function createPost($revisor) {
    $post = new Post($this->author, $revisor);
    $post->setActive(true);
    $post->setCreated(DateTime::now());

    $this->em->persist($post);
    $this->em->persist($this->author);
    return $post;
  }
}
