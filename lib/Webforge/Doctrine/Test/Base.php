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
  protected $container;

  public function setUp() {
    parent::setUp();

    $this->mocker = new Mocker($this);

    $this->container = new Container();
  }
}
