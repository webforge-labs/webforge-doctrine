<?php

namespace Webforge\Doctrine;


use Webforge\Doctrine\Test\Entities\Post;
use Webforge\Doctrine\Test\Entities\Author;
use Webforge\Doctrine\Test\Entities\Tag;
use Webforge\Doctrine\Test\Entities\Binary;
use Webforge\Doctrine\Test\Entities\PostImage;
use Webforge\Common\DateTime\DateTime;
use Webforge\Doctrine\Fixtures\EmptyFixture;

abstract class CollectionTestCase extends \Webforge\Doctrine\Test\DatabaseTestCase {

  protected function getFixtures() {
    return array(new EmptyFixture());
  }

  protected function createTags() {
    $tags = array();
    foreach (array('usa', 'whitehouse', 'germany', 'nsa') as $key => $label) {
      $tags[$label] = $tag = new Tag($label);

      $this->em->persist($tag);
    }

    return (object) $tags;
  }

  protected function createBinaries() {
    $bins = array();
    $key = 1;
    foreach (array('gallery1/DSC0001.jpg', 'gallery1/DSC0002.jpg', 'gallery1/DSC0003.jpg') as $path) {
      $bins[$key++] = $binary = new Binary($path);
      $this->em->persist($binary);
    }

    return $bins;
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
    $post = $this->refresh($post);

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

  protected function refresh($post) {
    $this->em->flush();
    $this->em->clear();
    return $this->em->getRepository(get_class($post))->findOneBy(array('id'=>$post->getId()));
  }
}
