<?php

namespace Webforge\Doctrine\Test\Entities;

use Webforge\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Webforge\Common\DateTime\DateTime;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Post
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Post class.
 * @ORM\MappedSuperClass
 */
abstract class CompiledPost {
  
  /**
   * id
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * author
   * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Author", inversedBy="writtenPosts")
   */
  protected $author;
  
  /**
   * revisor
   * @ORM\ManyToOne(targetEntity="Webforge\Doctrine\Test\Entities\Author", inversedBy="revisionedPosts")
   */
  protected $revisor;
  
  /**
   * categories
   * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Category", inversedBy="posts")
   * @ORM\JoinTable(name="posts2categories", joinColumns={@ORM\JoinColumn(name="posts_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="categories_id", onDelete="cascade")})
   */
  protected $categories;
  
  /**
   * tags
   * @ORM\ManyToMany(targetEntity="Webforge\Doctrine\Test\Entities\Tag")
   * @ORM\JoinTable(name="posts2tags", joinColumns={@ORM\JoinColumn(name="posts_id", onDelete="cascade")}, inverseJoinColumns={@ORM\JoinColumn(name="tags_id", onDelete="cascade")})
   */
  protected $tags;
  
  /**
   * active
   * @ORM\Column(type="boolean")
   */
  protected $active;
  
  /**
   * created
   * @ORM\Column(type="WebforgeDateTime")
   */
  protected $created;
  
  /**
   * modified
   * @ORM\Column(type="WebforgeDateTime", nullable=true)
   */
  protected $modified;
  
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
   * @param Webforge\Doctrine\Test\Entities\Author $author
   */
  public function setAuthor(Author $author) {
    $this->author = $author;
    $author->addWrittenPost($this);
    return $this;
  }
  
  /**
   * @return Webforge\Doctrine\Test\Entities\Author
   */
  public function getAuthor() {
    return $this->author;
  }
  
  /**
   * @param Webforge\Doctrine\Test\Entities\Author $revisor
   */
  public function setRevisor(Author $revisor = NULL) {
    $this->revisor = $revisor;
    $revisor->addRevisionedPost($this);
    return $this;
  }
  
  /**
   * @return Webforge\Doctrine\Test\Entities\Author
   */
  public function getRevisor() {
    return $this->revisor;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Category> $categories
   */
  public function setCategories(ArrayCollection $categories) {
    $this->categories = $categories;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Category>
   */
  public function getCategories() {
    return $this->categories;
  }
  
  /**
   * @param Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Tag> $tags
   */
  public function setTags(ArrayCollection $tags) {
    $this->tags = $tags;
    return $this;
  }
  
  /**
   * @return Doctrine\Common\Collections\Collection<Webforge\Doctrine\Test\Entities\Tag>
   */
  public function getTags() {
    return $this->tags;
  }
  
  /**
   * @param bool $active
   */
  public function setActive($active) {
    $this->active = $active;
    return $this;
  }
  
  /**
   * @return bool
   */
  public function getActive() {
    return $this->active;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $created
   */
  public function setCreated(DateTime $created) {
    $this->created = $created;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getCreated() {
    return $this->created;
  }
  
  /**
   * @param Webforge\Common\DateTime\DateTime $modified
   */
  public function setModified(DateTime $modified = NULL) {
    $this->modified = $modified;
    return $this;
  }
  
  /**
   * @return Webforge\Common\DateTime\DateTime
   */
  public function getModified() {
    return $this->modified;
  }
  
  public function addCategory(Category $category) {
    if (!$this->categories->contains($category)) {
        $this->categories->add($category);
        $category->addPost($this);
    }
    return $this;
  }
  
  public function removeCategory(Category $category) {
    if ($this->categories->contains($category)) {
        $this->categories->remove($category);
        $category->removePost($this);
    }
    return $this;
  }
  
  public function hasCategory(Category $category) {
    return $this->categories->contains($category);
  }
  
  public function addTag(Tag $tag) {
    if (!$this->tags->contains($tag)) {
        $this->tags->add($tag);
    }
    return $this;
  }
  
  public function removeTag(Tag $tag) {
    if ($this->tags->contains($tag)) {
        $this->tags->remove($tag);
    }
    return $this;
  }
  
  public function hasTag(Tag $tag) {
    return $this->tags->contains($tag);
  }
  
  public function __construct(Author $author, Author $revisor = NULL) {
    if (isset($author)) {
        $this->setAuthor($author);
    }
    if (isset($revisor)) {
        $this->setRevisor($revisor);
    }
    $this->categories = new ArrayCollection();
    $this->tags = new ArrayCollection();
  }
}
