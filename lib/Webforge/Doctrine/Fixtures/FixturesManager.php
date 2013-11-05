<?php

namespace Webforge\Doctrine\Fixtures;

use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\FixtureInterface as DCFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManager;

class FixturesManager {
  
  protected $em;
  protected $executor;
  protected $loader;
  protected $purger;

  protected $log;
  
  public function __construct(EntityManager $em) {
    $this->em = $em;
  }
  
  public function add(DCFixture $fixture) {
    $this->getLoader()->addFixture($fixture);
    return $this;
  }
  
  public function execute() {
    return $this->getExecutor()->execute($this->getLoader()->getFixtures());
  }
  
  public function getLoader() {
    if (!isset($this->loader)) {
      $this->loader = new Loader();
    }
    return $this->loader;
  }
  
  public function getExecutor() {
    if (!isset($this->executor)) {
      $this->executor = new ORMExecutor($this->em, $this->getPurger());
      $log = $this->log;
      $this->executor->setLogger(function ($msg) use (&$log) {
        $log .= $msg;
      });
    }
    
    return $this->executor;
  }
  
  public function getPurger() {
    if (!isset($this->purger)) {
      $this->purger = new QNDTruncateORMPurger($this->em);
      $this->purger->setPurgeMode(QNDTruncateORMPurger::PURGE_MODE_TRUNCATE);
    }
    return $this->purger;
  }

  public function getLog() {
    return $this->log;
  }
}
