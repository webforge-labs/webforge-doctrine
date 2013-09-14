<?php

namespace Webforge\Doctrine\Test;

use Webforge\Doctrine\Container;

class Base extends \Webforge\Code\Test\Base {

  /**
   * @var Webforge\Doctrine\Test\Mocker
   */
  protected $mocker;

  /**
   * @var Webforge\Doctrine\Container
   */
  protected $dcc;

  public function setUp() {
    parent::setUp();

    $this->mocker = new Mocker($this);
    $this->dcc = new Container();
  }
}
