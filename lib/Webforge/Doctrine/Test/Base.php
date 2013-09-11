<?php

namespace Webforge\Doctrine\Test;

class Base extends \Webforge\Code\Test\Base {

  /**
   * @var Webforge\Doctrine\Test\Mocker
   */
  protected $mocker;

  public function setUp() {
    parent::setUp();

    $this->mocker = new Mocker($this);
  }
}
