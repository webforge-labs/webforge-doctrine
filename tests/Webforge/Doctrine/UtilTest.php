<?php

namespace Webforge\Doctrine;

class UtilTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Util';
    parent::setUp();
  }

  public function testUpdateSchemaCallsTheSchemaTool() {
    $this->markTestIncomplete('This is inbelievable hard todo with mocks. Thats why doctrine is testing this behaviour only with acceptance tests. And we should do the same here. Needs a better (functional) container');
  }
}
