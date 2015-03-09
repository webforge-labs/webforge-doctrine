<?php

namespace Webforge\Doctrine\Console;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;
use Mockery as m;
use Webforge\Doctrine\Util;

class ORMUpdateSchemaCommandTest extends \Webforge\Doctrine\Test\Base {

  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Console\\ORMUpdateSchemaCommand';
    parent::setUp();

    $this->dcc->injectUtil(
      $this->util = m::mock('Webforge\Doctrine\Util', array($this->dcc))
    );

    $this->dcc->injectEntityManager($this->mocker->createEntityManager(array('database'=>'db_tests')), 'tests');
    $this->dcc->injectEntityManager($this->mocker->createEntityManager(array('database'=>'db_default')), 'default');

    $this->application = new Application();
    $this->application->addCommands(array(
      new ORMUpdateSchemaCommand($this->dcc, $this->frameworkHelper->getSystem())
    ));
  }

  public function testWithADryRunSubmitsTheCorrectConnection() {
    $this->util
      ->shouldReceive('updateSchema')
      ->once()
      ->with('tests', NULL, m::any());

    $output = $this->execute('orm:update-schema', array('--con'=>'tests', '--dry-run'=>TRUE));

    $this->assertContains('Printing update schema SQL for con: tests connected with: db_tests', $output);
  }

  public function testWithDefaultsRunSubmitsForceAnUsesTheNormalConnection() {
    $this->util
      ->shouldReceive('updateSchema')
      ->once()
      ->with('default', Util::FORCE, m::any());

    $this->execute('orm:update-schema', array());
  }

  protected function execute($name, Array $args) {
    $command = $this->application->find($name);

    $tester = new CommandTester($command);
    $tester->execute(array_merge(
      array('command'=>$name), $args
    ));

    return $tester->getDisplay();
  }
}
