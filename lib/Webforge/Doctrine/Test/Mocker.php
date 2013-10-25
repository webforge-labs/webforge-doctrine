<?php

namespace Webforge\Doctrine\Test;

use Doctrine\ORM\EntityManager;

class Mocker {

  protected $test;

  public function __construct(\PHPUnit_Framework_TestCase $testCase) {
    $this->test = $testCase;
  }

  /**
   * Creates an EntityManager for testing purposes.
   *
   * NOTE: The created EntityManager will have its dependant DBAL parts completely
   * mocked out using a DriverMock, ConnectionMock, etc. These mocks can then
   * be configured in the tests to simulate the DBAL behavior that is desired
   * for a particular test,
   *
   * @return Doctrine\ORM\EntityManager
   */
  public function createEntityManager($eventManager = NULL) {
    $classLoader = new \Doctrine\Common\ClassLoader(
      'Doctrine\Tests',
      (string) $GLOBALS['env']['root']->sub('vendor/doctrine/orm/tests/')
    );
    $classLoader->register();

    $metadataCache = new \Doctrine\Common\Cache\ArrayCache;

    $config = new \Doctrine\ORM\Configuration();
    $config->setMetadataCacheImpl($metadataCache);
    $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(array((string) $this->test->getPackageDir('tests/files/Entities/')->create()), true));
    $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
    $config->setProxyDir($this->test->getTempDirectory('Proxies/'));
    $config->setProxyNamespace('Doctrine\Tests\Proxies');

    $conn = array(
      'driverClass'  => 'Doctrine\Tests\Mocks\DriverMock',
      'wrapperClass' => 'Doctrine\Tests\Mocks\ConnectionMock',
      'user'         => 'john',
      'password'     => 'wayne'
    );

    $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, $eventManager);

    return \Doctrine\Tests\Mocks\EntityManagerMock::create($conn, $config, $eventManager);
  }
  

  public function createSchemaTool(EntityManager $em) {
    return $this->test->getMockBuilder('Doctrine\ORM\Tools\SchemaTool')->disableOriginalConstructor()->getMock();
  }
}
