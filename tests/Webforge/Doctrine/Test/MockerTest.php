<?php

namespace Webforge\Doctrine\Test;

class MockerTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Test\\Mocker';
    parent::setUp();

    $this->mocker = new Mocker($this);
  }

  public function testCanCreateAnEntityManager() {
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->mocker->createEntityManager());
  }
}
