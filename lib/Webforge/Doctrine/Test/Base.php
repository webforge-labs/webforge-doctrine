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

  /**
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;

  /**
   * @var array
   */
  protected $entitiesPaths;

  /**
   * @var string
   */
  protected $con = 'tests';
  
  public function setUp() {
    parent::setUp();

    $this->mocker = new Mocker($this);
    $this->dcc = new Container();
  }

  protected function initDoctrineContainer() {
    $this->dcc->initDoctrine(
      $this->frameworkHelper->getBootContainer()->getProject()->getConfiguration()->get(array('db')),
      $this->entitiesPaths = array($this->getTestDirectory('Entities/'))
    );
  }

  protected function setUpEntityManager() {
    $this->em = $this->dcc->getEntityManager($this->con, $reset = TRUE, $resetConnection = TRUE);
  }
}
