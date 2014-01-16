<?php

namespace Webforge\Doctrine;

use Webforge\Doctrine\Test\Entities\CompanyCar;
use Webforge\Doctrine\Test\Entities\Post;
use Webforge\Doctrine\Test\Entities\Author;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Webforge\Doctrine\Fixtures\EmptyFixture;
use Doctrine\DBAL\Types\Type as DBALType;
use Webforge\Common\DateTime\DateTime;

class ContainerEntitiesTest extends \Webforge\Doctrine\Test\DatabaseTestCase {

  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Container';
    parent::setUp();
  }

  protected function getFixtures() {
    return array(new EmptyFixture());
  }

  public function testEntityManagerFIndsTheEntyLoadedFromTestFiles() {
    $this->resetDatabaseOnNextTest();
    $repo = $this->em->getRepository('Webforge\Doctrine\Test\Entities\CompanyCar');

    $car = new CompanyCar('Ford');
    $this->em->persist($car);
    $this->em->flush();
    $this->em->clear();

    $this->assertInstanceOf(
      'Webforge\Doctrine\Test\Entities\CompanyCar', 
      $repo->find($car->getId())
    );
  }

  public function testFixtureCleansTheDBOnSetup() {
    $repo = $this->em->getRepository('Webforge\Doctrine\Test\Entities\CompanyCar');
    $this->assertNull($repo->find(1));

    $this->assertNotEmpty($this->fm->getLog());
  }

  public function testInitDoctrineRegistersTypes() {
    $this->assertTrue(DBALType::hasType('WebforgeDateTime'));
    $this->assertTrue(DBALType::hasType('WebforgeDate'));
  }

  public function testDateTimeConversion() {
    $this->resetDatabaseOnNextTest();
    $post = new Post($author = new Author('p.scheit@ps-webforge.com'));
    $post->setCreated($now = DateTime::now());
    $post->setActive(TRUE);

    $this->em->persist($post);
    $this->em->persist($author);
    $this->em->flush();
    $this->em->clear();

    $post = $this->em->find('Webforge\Doctrine\Test\Entities\Post', $post->getId());
    $this->assertInstanceOf('Webforge\Common\DateTime\DateTime', $post->getCreated());
    $this->assertEquals($now->getTimestamp(), $post->getCreated()->getTimestamp());
  }

  public function testCanCreateASchemaToolForAnEntityManager() {
    $this->assertInstanceOf('Doctrine\ORM\Tools\SchemaTool', $tool1 = $this->dcc->getSchemaTool('default'));
  }
}
