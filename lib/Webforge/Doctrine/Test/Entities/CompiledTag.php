<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\Tag
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\Tag class.
 * @ORM\MappedSuperclass
 */
abstract class CompiledTag {
  
  /**
   * id
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * label
   * @ORM\Column
   */
  protected $label;
  
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
   * @param string $label
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }
  
  public function __construct($label) {
    $this->label = $label;
  }
}
