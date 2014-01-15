<?php

namespace Webforge\Doctrine;

class DatabaseTestCaseTest extends Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\DatabaseTestCase';
    parent::setUp();
  }

  public function testMemberVariablesAreInitAfterSetup() {
    $this->assertInstanceOf('Webforge\Doctrine\Container', $this->dcc);
    $this->assertInstanceOf('Doctrine\ORM\EntityManager', $this->em);
    $this->assertInstanceOf('Webforge\Doctrine\Test\Mocker', $this->mocker);
    $this->assertInstanceOf('Webforge\Doctrine\Fixtures\FixturesManager', $this->fm);
  }

  public function testemHasTestsAsDefaultConnection() {
    $this->assertEquals('doctrine_tests', $this->em->getConnection()->getDatabase());
  }

  protected function getEntityName($shortName) {
    if ($shortName === 'car') {
      return 'Webforge\Doctrine\Test\Entities\CompanyCar';
    }

    return parent::getEntityName($shortName);
  }

  public function testGetRepositoryCanReturnARepositoryForAnEntity() {
    $repo = $this->getRepository('car');
    $this->assertInstanceOf('Doctrine\ORM\EntityRepository', $repo);
  }

  public function testGetSchemaManager() {
    $this->assertInstanceOf('Doctrine\DBAL\Schema\AbstractSchemaManager', $this->getSchemaManager());
  }

  public function testSmokeStartAndStopDebug() {
    $this->startDebug();
    $this->stopDebug();
  }

  public function testGetMetadataReturnsMetadataForEntity() {
    $this->assertInstanceOf('Doctrine\ORM\Mapping\ClassMetadata', $meta = $this->getEntityMetadata('Webforge\Doctrine\Test\Entities\CompanyCar'));

  }
}
