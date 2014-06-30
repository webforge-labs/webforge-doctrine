<?php

namespace Webforge\Doctrine\Test\Entities;

use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Category
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Category class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledCategory {
  
  /**
   * id
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * posts
   * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Post", mappedBy="categories")
   * @ORM\JoinTable(name="posts2categories", joinColumns={@ORM\JoinColumn(name="posts_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="categories_id", onDelete="cascade")})
   */
  protected $posts;
  
  /**
   * @param integer $id
   */
  public function setId($id) {
    $this->id = $id;
    return $this;
  }
  
  /**
   * @return integer
   */
  public function getId() {
    return $this->id;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post> $posts
   */
  public function setPosts(ArrayCollection $posts) {
    $this->posts = $posts;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Post>
   */
  public function getPosts() {
    return $this->posts;
  }
  
  public function addPost(Post $post) {
    if (!$this->posts->contains($post)) {
        $this->posts->add($post);
    }
    return $this;
  }
  
  public function removePost(Post $post) {
    if ($this->posts->contains($post)) {
        $this->posts->removeElement($post);
    }
    return $this;
  }
  
  public function hasPost(Post $post) {
    return $this->posts->contains($post);
  }
  
  public function __construct() {
    $this->posts = new ArrayCollection();
  }
}
