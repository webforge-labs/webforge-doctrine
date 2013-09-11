<?php

namespace Webforge\Doctrine\Console;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand;
use Webforge\Doctrine\Container as DoctrineContainer;

class ConsoleBridge {

  /**
   * Webforge\Doctrine\Container
   */
  protected $dcc;

  public function __construct(DoctrineContainer $dcc) {
    $this->dcc = $dcc;
  }

  public function augment($application) {
    $application->getHelperSet()->set(
      new EntityManagerHelper($this->dcc->getEntityManager()),
      'em'
    );

    $application->addCommands(array(
      new ValidateSchemaCommand()
    ));
  }
}
