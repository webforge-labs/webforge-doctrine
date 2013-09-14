<?php

namespace Webforge\Doctrine;

class ContainerTest extends \Webforge\Doctrine\Test\Base {

  protected $em, $em2;
  protected $container;
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Container';
    parent::setUp();

    $this->em = $this->mocker->createEntityManager();
    $this->em2 = $this->mocker->createEntityManager();
    $this->container = new Container();
  }

  public function testOneEntityManagerCanBeInjected() {
    $this->container->injectEntityManager($this->em);

    $this->assertSame($this->em, $this->container->getEntityManager(), 'calling without con parameter');
    $this->assertSame($this->em, $this->container->getEntityManager('default'), 'calling with default con parameter');
  }

  public function testEntityManagersCanbeInjected() {
    $this->container->injectEntityManager($this->em);
    $this->container->injectEntityManager($this->em2, 'tests');

    $this->assertSame($this->em2, $this->container->getEntityManager('tests'));

    $this->assertNotSame(
      $this->container->getEntityManager('default'),
      $this->container->getEntityManager('tests')
    );
  }

  public function testCanInstantiateTheUtil() {
    $this->assertInstanceOf('Webforge\Doctrine\Util', $this->dcc->getUtil());
  }

  public function testCanCreateASchemaToolForAnEntityManager() {
    $this->container->injectEntityManager($this->em);
    $this->assertInstanceOf('Doctrine\ORM\Tools\SchemaTool', $tool1 = $this->container->getSchemaTool('default'));
  }

  public function testSchemaToolCanBeInjected() {
    $this->container->injectEntityManager($this->em);

    $schemaTool = $this->mocker->createSchemaTool($this->em);
    $this->container->injectSchemaTool($schemaTool, 'default');

    $this->assertSame($schemaTool, $this->container->getSchemaTool('default'));
  }

  public function testUtilCanBeInjected() {
    $util = $this->getMockBuilder('Webforge\Doctrine\Util')->disableOriginalConstructor()->getMock();

    $this->container->injectUtil($util);
    
    $this->assertSame($util, $this->container->getUtil());
  }
}
