<?php

namespace Webforge\Doctrine\Test;

use Doctrine\ORM\EntityManager;
use Mockery as m;

class Mocker {

  protected $test;

  public function __construct(\PHPUnit_Framework_TestCase $testCase) {
    $this->test = $testCase;
  }

  /**
   * Creates an dumb EntityManager for testing purposes.
   *
   * @return Doctrine\ORM\EntityManager
   */
  public function createEntityManager($eventManager = NULL) {
    $em = m::mock('Doctrine\ORM\EntityManager');
    $connection = m::mock('Doctrine\DBAL\Connection');

    $em->shouldReceive('getConnection')->byDefault()->andReturn($connection);

    return $em;
  }

  public function createSchemaTool(EntityManager $em) {
    return $this->test->getMockBuilder('Doctrine\ORM\Tools\SchemaTool')->disableOriginalConstructor()->getMock();
  }
}
