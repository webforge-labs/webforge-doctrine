<?php

namespace Webforge\Doctrine;

use Mockery as m;

class DatabaseTestCaseResetTest extends Test\DatabaseTestCase {

  protected $setUpWasCalled = FALSE;
  
  protected function setUpDatabase() {
    $this->setUpWasCalled = TRUE;
    //$this->fm->shouldReceive('execute')->once();
  }

  public function testFMIsExecutedWhileSetup() {
    $this->assertTrue($this->setUpWasCalled);
  }

  public function testFMIsNotExecutedForTheNextTest() {
    $this->assertFalse($this->setUpWasCalled);

    $this->resetDatabaseOnNextTest();
  }

  public function testFMIsOnlyExecutedForTheNextTest_When_ResetDatabaseOnNextTestWasCalledBefore() {
    $this->assertTrue($this->setUpWasCalled);
  }

}
