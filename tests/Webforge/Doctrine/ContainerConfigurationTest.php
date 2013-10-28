<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityManager;
use Webforge\Common\System\Dir;

class ContainerConfigurationTest extends \Webforge\Doctrine\Test\Base {

  protected $container;
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Container';
    parent::setUp();

    // interface DoctrineConfigurationProvider? getDatabaseConfiguration => array('name' ... 'password' ... 'charset' ...)
    $this->dbConfigArray = $this->frameworkHelper->getBootContainer()->getProject()->getConfiguration()->get(array('db'));

    $this->entitiesPaths = array($this->getTestDirectory('fake-entities/'));

    $this->container = new Container();
  }

  public function testPreConditions() {
    $this->assertArrayHasKey('tests', $this->dbConfigArray);
    $this->assertArrayHasKey('default', $this->dbConfigArray);

    foreach ($this->dbConfigArray as $con=>$config) {
      $this->assertArrayHasKey('user', $config);
      $this->assertNotEmpty($config['user']);
      $this->assertArrayHasKey('password', $config);
      $this->assertNotEmpty($config['password']);
      $this->assertArrayHasKey('database', $config);
      $this->assertNotEmpty($config['database']);
    }
  }

  public function testGetEntityManagerWithoutInitDoctrineFirstThrowsLogicException() {
    $this->setExpectedException('LogicException');
    $this->container->getEntityManager();
  }

  /**
   * @dataProvider invalidConfigurationArrays
   */
  public function testWithInvalidConfigurationArrayThrowsInvalidArgumentException($invalid) {
    $this->setExpectedException('InvalidArgumentException');
    $this->container->initDoctrine($invalid, $this->entitiesPaths);
  }


  public static function invalidConfigurationArrays() {
    $tests = array();

    $valid = array(
      'dbname'=>'acme-blog',
      'user'=>'xxx',
      'password'=>'yyy'
    );
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };
  
    $test(
      array()
    );

    $test(
      array('default'=>NULL)
    );

    $test(
      array(
        'default'=>array(
          'db'=>'shortparam is wrong',
          'user'=>'xxx',
          'password'=>'yyy'
        )
      )
    );

    $test(
      array(
        'default'=>array(
          'databse'=>'acme-blog',
          'user'=>'acme',
          'password'=>'r0adrunn3r',
          'driver'=>'pdo_sqlite'
        )
      )
    );

    $test(
      array(
        'tests'=>$valid,
        'staging'=>$valid
        // default is missing
      )
    );
  
    return $tests;
  }

  /**
   * @eg\documents Webforge\Doctrine\Container::initDoctrine()
   */
  public function testDocumentationConfigurationExample() {
    $dcc = new Container();

    $dcc->initDoctrine(
      array(
        'default'=>array(
          'database'=>'acme-blog',
          'user'=>'acme',
          'password'=>'r0adrunn3r',
          'driver'=>'pdo_mysql',
        ),
        'tests'=>array(
          'database'=>'acme-blog_tests',
          'user'=>'acme',
          'password'=>'r0adrunn3r',
          'driver'=>'pdo_sqlite', // default: pdo_mysql
        )
      ),
      array(
        Dir::factory(__DIR__.DIRECTORY_SEPARATOR)->sub('../lib/ACME/SuperBlog/Entities/')->resolvePath()
      )
    );

    $em = $dcc->getEntityManager('default');

    /*
     the defaults for the configuration are: host=>127.0.0.1, port=>NULL, unix_socket=>NULL, charset="utf8"
     defaults from doctrine DBAL are used see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
    */
  }

  protected function initDefault() {
    $this->container->initDoctrine($this->dbConfigArray, $this->entitiesPaths);
  }

  public function testCanBeConfiguredWithDatabaseConfigurationFromOurConfigFileToReturnAnEntityManager() {
    $this->initDefault();

    foreach (array('tests', 'default') as $con) {
      $em = $this->container->getEntityManager($con);
      $config = $this->dbConfigArray[$con];

      $this->assertEntityManagerHasConfig($em, $config, $con);
    }
  }

  public function testConfigurationCacheIsArrayByDefault() {
    $this->initDefault();

    $this->assertInstanceOf(
      'Doctrine\Common\Cache\ArrayCache',
      $this->container->getEntityManager()->getConfiguration()->getMetadataCacheImpl()
    );
  }

  protected function assertEntityManagerHasConfig(EntityManager $em, Array $config, $con = 'default') {
    $connectionParams = array(
      'database'=>'getDatabase',
      'host'=>'getHost',
      'port'=>'getPort',
      'user'=>'getUsername',
      'password'=>'getPassword'
    );

    $dbalConnection = $em->getConnection();
    $dbalConfig = $dbalConnection->getParams();
      
    $this->assertEquals($config['charset'], $dbalConfig['charset']);

    foreach ($connectionParams as $param => $getter) {
      $this->assertEquals(
        $config[$param],
        $dbalConnection->$getter(),
        $param.' is not set correctly from config for '.$con
      );
    }
  }
}
