<?php

namespace Webforge\Doctrine\Console;

class ConsoleBridgeTest extends \Webforge\Doctrine\Test\Base {

  protected $consoleBridge, $application, $em;
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Console\\ConsoleBridge';
    parent::setUp();

    $this->container->injectEntityManager($this->mocker->createEntityManager());
    $this->consoleBridge = new ConsoleBridge($this->container);

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
  }
}
