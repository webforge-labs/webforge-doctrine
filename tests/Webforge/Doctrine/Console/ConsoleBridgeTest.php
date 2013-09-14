<?php

namespace Webforge\Doctrine\Console;

class ConsoleBridgeTest extends \Webforge\Doctrine\Test\Base {

  protected $consoleBridge, $application, $em;
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Console\\ConsoleBridge';
    parent::setUp();

    $this->dcc->injectEntityManager($this->mocker->createEntityManager());
    $this->consoleBridge = new ConsoleBridge($this->dcc);

    $this->application = new \Symfony\Component\Console\Application();
  }

  public function testAugmentShouldAddTheEMHelperToTheHelperSet() {
    $this->consoleBridge->augment($this->application);
    $this->assertTrue($this->application->getHelperSet()->has('em'), 'application has not helper em registered');
  }

  public function testAugmentShouldAddSomeCommandsFromDoctrineToTheConsole() {
    $this->consoleBridge->augment($this->application);

    $this->assertInstanceOf(
      'Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand',
      $this->application->get('orm:validate-schema'), 
      'application has not validate-schema command registered'
    );

    $this->assertInstanceOf('Webforge\Doctrine\Console\AbstractDoctrineCommand', $this->application->get('orm:update-schema'));
  }
}
