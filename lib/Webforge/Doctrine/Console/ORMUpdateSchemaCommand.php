<?php

namespace Webforge\Doctrine\Console;

use Webforge\Doctrine\Util;
use Webforge\Console\CommandInput;
use Webforge\Console\CommandOutput;
use Webforge\Console\CommandInteraction;
use Webforge\Common\System\System;

class ORMUpdateSchemaCommand extends AbstractDoctrineCommand {

  protected $name = 'orm:update-schema';

  protected function configure() {
    $this
      ->setName('orm:update-schema')
      ->setDescription(
        'Updates the database schema to match the current mapping metadata.'
      )
      ->setHelp(
        $this->getName()." --dry-run\n".
        "Shows the changes that would be made.\n".
        "\n".
        $this->getName()."\n".
        'Updates the database schema to match the current mapping metadata.'
    );

    parent::configure();
  }
  
  public function doExecute(CommandInput $input, CommandOutput $output, CommandInteraction $interact, System $system) {
    $force = !$input->getFlag('dry-run') ? Util::FORCE : NULL;
    $con = $input->getValue('con');

    $util = $this->dcc->getUtil();

    if ($force == Util::FORCE) {
      $output->writeln('Updating schema (forced) for connection: '.$con);
    } else {
      $output->writeln('Printing update schema SQL for Connection: '.$con);
    }
    
    $output->writeln($log = $util->updateSchema($con, $force, "\n"));
    
    if ($force !== Util::FORCE && empty($log)) {
      $output->writeln('nothing to do');
    }
    
    return 0;
  }
}