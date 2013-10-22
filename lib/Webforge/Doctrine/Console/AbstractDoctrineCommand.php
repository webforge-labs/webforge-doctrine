<?php

namespace Webforge\Doctrine\Console;

use Webforge\Doctrine\Container as DoctrineContainer;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractDoctrineCommand extends SymfonyCommand {

  /**
   * Webforge\Doctrine\Container
   */
  protected $dcc;

  public function __construct(DoctrineContainer $dcc) {
    $this->dcc = $dcc;
    parent::__construct();
  }

  protected function configure() {
    $this->addOption(
      'con', '', InputOption::VALUE_REQUIRED,
      'Shortname of the connection (configuration)',
      'default'
    );
    
    $this->addOption(
      'dry-run', '', InputOption::VALUE_NONE,
      'When set not database actions are processed, only output is given'
    );
  }

  protected function getEntityManager($con) {
    return $this->dcc->getEntityManager($con);
  }
}
