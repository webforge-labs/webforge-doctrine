<?php

namespace Webforge\Doctrine\Console;

use Webforge\Doctrine\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ORMUpdateSchemaCommand extends AbstractDoctrineCommand {

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
  
  protected function execute(InputInterface $input, OutputInterface $output) {
    $force = ($input->getOption('dry-run') !== TRUE) ? Util::FORCE : NULL;
    $con = $input->getOption('con');

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