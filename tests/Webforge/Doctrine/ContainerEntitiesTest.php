<?php

namespace Webforge\Doctrine;

use Doctrine\Tests\Models\Company\CompanyCar;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

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
    $repo = $this->em->getRepository('Doctrine\Tests\Models\Company\CompanyCar');

    $car = new CompanyCar('Ford');
    $this->em->persist($car);
    $this->em->flush();
    $this->em->clear();

    $this->assertInstanceOf(
      'Doctrine\Tests\Models\Company\CompanyCar', 
      $repo->find($car->getId())
    );
  }

  public function testFixtureCleansTheDBOnSetup() {
    $repo = $this->em->getRepository('Doctrine\Tests\Models\Company\CompanyCar');
    $this->assertNull($repo->find(1));
  }
}

class EmptyFixture extends AbstractFixture {

  public function load(ObjectManager $em) {
    // do nothing, leave empty
  }
}
