<?php

namespace Webforge\Doctrine;

use Doctrine\Tests\Models\Company\CompanyCar;

class ContainerEntitiesTest extends \Webforge\Doctrine\Test\SchemaTestCase {

  protected $container;
  
  public function setUp() {
    $this->chainClass = 'Webforge\\Doctrine\\Container';
    parent::setUp();

    $this->em = $this->dcc->getEntityManager('tests');
  }

  public function testEntityManagerFIndsTheEntyLoadedFromTestFiles() {
    $this->assertNull($this->em->find('Doctrine\Tests\Models\Company\CompanyCar', 1));

    $car = new CompanyCar('Ford');
    $this->em->persist($car);
    $this->em->flush();
    $this->em->clear();

    $this->assertInstanceOf(
      'Doctrine\Tests\Models\Company\CompanyCar', 
      $this->em->find('Doctrine\Tests\Models\Company\CompanyCar', 1)
    );
  }
}
