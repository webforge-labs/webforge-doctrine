<?php

namespace Webforge\Doctrine\Test\Entities;

use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Author
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Author class.
 * @ORM\MappedSuperClass
 */
abstract class CompiledAuthor extends User {
  
  /**
   * writtenPosts
   * @ORM\OneToMany(mappedBy="author", targetEntity="Webforge\Doctrine\Test\Entities\Post")
   */
  protected $writtenPosts;
  
  /**
   * revisionedPosts
   * @ORM\OneToMany(mappedBy="revisor", targetEntity="Webforge\Doctrine\Test\Entities\Post")
   */
  protected $revisionedPosts;
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $writtenPosts
   */
  public function setWrittenPosts(ArrayCollection $writtenPosts) {
    $this->writtenPosts = $writtenPosts;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
   */
  public function getWrittenPosts() {
    return $this->writtenPosts;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $revisionedPosts
   */
  public function setRevisionedPosts(ArrayCollection $revisionedPosts) {
    $this->revisionedPosts = $revisionedPosts;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
   */
  public function getRevisionedPosts() {
    return $this->revisionedPosts;
  }
  
  public function addWrittenPost(Post $writtenPost) {
    if (!$this->writtenPosts->contains($writtenPost)) {
        $this->writtenPosts->add($writtenPost);
    }
    return $this;
  }
  
  public function removeWrittenPost(Post $writtenPost) {
    if ($this->writtenPosts->contains($writtenPost)) {
        $this->writtenPosts->remove($writtenPost);
    }
    return $this;
  }
  
  public function hasWrittenPost(Post $writtenPost) {
    return $this->writtenPosts->contains($writtenPost);
  }
  
  public function addRevisionedPost(Post $revisionedPost) {
    if (!$this->revisionedPosts->contains($revisionedPost)) {
        $this->revisionedPosts->add($revisionedPost);
    }
    return $this;
  }
  
  public function removeRevisionedPost(Post $revisionedPost) {
    if ($this->revisionedPosts->contains($revisionedPost)) {
        $this->revisionedPosts->remove($revisionedPost);
    }
    return $this;
  }
  
  public function hasRevisionedPost(Post $revisionedPost) {
    return $this->revisionedPosts->contains($revisionedPost);
  }
  
  public function __construct() {
    $this->writtenPosts = new ArrayCollection();
    $this->revisionedPosts = new ArrayCollection();
  }
}
