<?php

namespace Webforge\Doctrine;

class SyncedEntityRepositoryTest extends Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\SyncedEntityRepository';
    parent::setUp();
    $this->userClass = 'Webforge\Doctrine\Test\Entities\User';

    $this->entityFactory = new EntityFactory($this->userClass);
    $this->users = new SyncedEntityRepository($this->userClass, 'email', $this->em, $this->entityFactory);
    //$this->reflector = $this->dcc->getEntityReflector(); // look in assertUserEntity: TODO
  }

  protected function getFixtures() {
    return array(new \Webforge\Doctrine\Fixtures\EmptyFixture());
  }

  public function testAnEntityGetsInsertedWhenNotAvaibleInDB() {
    $this->resetDatabaseOnNextTest();
    $user = $this->users->sync($fields = (object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v1'));

    $this->assertUserEntity($fields, $user);
  }

  public function testAnEntityThatsPreviouslySyncedWillGetUpdatedOnSecondCall() {
    $this->resetDatabaseOnNextTest();
    $this->users->sync((object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v1'));

    $user = $this->users->sync($fields = (object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v2'));
    $this->assertUserEntity($fields, $user);
  }

  public function testAnEntityGetsPersistedWhenSynced() {
    $this->resetDatabaseOnNextTest();
    $user = $this->users->sync((object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v1'));

    $this->em->flush();

    $this->assertGreaterThan(0, $user->getId(), 'user should have now an generated id, because it was persisted');
  }

  public function testAnPersistedEntityGetsUpdatedWhenSynced() {
    $this->resetDatabaseOnNextTest();
    $userpre = $this->users->sync((object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v1'));

    $this->em->flush();
    $this->em->clear();

    $user = $this->users->sync($fields = (object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v2'));

    $this->em->flush();
    $this->em->clear();

    $user = $this->em->find($this->userClass, $userpre->getId());

    $this->assertUserEntity($fields, $user);
  }

  public function testAnPersistedAndThenDeletedEntityGetsUpdatedWhenSynced() {
    $this->resetDatabaseOnNextTest();
    $userpre = $this->users->sync((object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v1'));
    
    $this->em->remove($userpre);
    $this->em->flush();
    $this->em->clear();
    // it is removed but still in the internal memory cache of $users
    $user = $this->users->sync($fields = (object) array('email'=>'p.scheit@ps-webforge.com', 'special'=>'v2'));

    $this->em->flush();
    $this->em->clear();

    $user = $this->em->find($this->userClass, $userpre->getId());

    $this->assertUserEntity($fields, $user);
  }

  protected function assertUserEntity($fields, $user) {
    $this->assertInstanceOf($this->userClass, $user);

    foreach ($fields as $property => $value) {
      $getter = 'get'.ucfirst($property);
      $this->assertEquals($value, $user->$getter(), $property.' of entity has not the right value');
    }
  }
}
