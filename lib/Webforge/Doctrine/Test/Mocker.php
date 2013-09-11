<?php

namespace Webforge\Doctrine\Test;

class Mocker {

  protected $test;

  public function __construct(\PHPUnit_Framework_TestCase $testCase) {
    $this->test = $testCase;
  }

  public function createEntityManager() {
    return $this->test->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
  }
}
