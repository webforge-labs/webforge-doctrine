<?php

namespace Webforge\Doctrine\Test\Entities;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Compiled Entity for Webforge\Doctrine\Test\Entities\User
 * 
 * To change table name or entity repository edit the Webforge\Doctrine\Test\Entities\User class.
 * @ORM\MappedSuperClass
 */
abstract class CompiledUser {
  
  /**
   * id
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue
   */
  protected $id;
  
  /**
   * email
   * @ORM\Column(length=210)
   */
  protected $email;
  
  /**
   * special
   * @ORM\Column(nullable=true)
   */
  protected $special;
  
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
   * @param string $email
   */
  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }
  
  /**
   * @param string $special
   */
  public function setSpecial($special = NULL) {
    $this->special = $special;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getSpecial() {
    return $this->special;
  }
  
  public function __construct($email) {
    $this->email = $email;
  }
}
