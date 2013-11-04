<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use Webforge\Common\ArrayUtil AS A;
use Psc\Doctrine\Helper as DoctrineHelper;

/**
 * Basisklasse für Datenbanktests
 *
 * die Klasse ruft bei Default vor dem ersten Test die Mainfixture auf (und das nur einmal)
 * soll während des Tests die Datenbank neu geladen werden kann der test einfach self::$setupDatabase = TRUE; setzen
 * setUpDatabase() kann überschrieben werden für weitere fixtures
 */
abstract class DatabaseTestCase extends \Psc\Code\Test\HTMLTestCase {
  
  /**
   * Ist dies während der setUp()-Method  == true dann wird die gesetzte Fixture geladen
   * 
   * @var bool
   */
  protected static $setupDatabase = TRUE;
  
  /**
   * @var bool
   */
  protected $backupGlobals = FALSE;
  
  /**
   * @var string
   */
  protected $con = 'tests';
  
  /**
   * @var Psc\Doctrine\DCPackage
   */
  protected $dc;
  
  /**
   * @var Psc\Doctrine\Module
   */
  protected $module;
  
  /**
   * @var Doctrine\ORM\EntityManager
   */
  protected $em;

  /**
   * @var Doctrine\DBAL\Schema\AbstractSchemaManager
   */
  protected $sm;
  
  /**
   * @var Psc\Doctrine\FixturesManager
   */
  protected $dcFixtures;
  
  /**
   * Set this to add to dcFixtures at setup
   * @var array
   */
  protected $fixtures = NULL;
  
  /**
   *
   * Diese Methode brauchen wir trotzdem. Denn Die Klassenvariable für DoctrineDatabaseTestCase  verhält sich wie eine globale und wird nicht zwischen den
   * Test-Klassen zurückgesetzt. Diese Funktion wird immer vor jeder TestKlasse ausgeführt.
   */
  public static function setUpBeforeClass() {
    self::$setupDatabase = TRUE;
  }
  
  public function setUp() {
    parent::setUp();
    
    $this->module = $this->getModule('Doctrine');
    $this->setUpModule($this->module);
    $this->setUpEntityManager();
    $this->dc = $this->getDoctrinePackage();

    $this->setUpFixturesManager();

    if (self::$setupDatabase) {
      $this->setUpDatabase();
      self::$setupDatabase = FALSE;
    }
  }

  protected function setUpModule($module) {}

  protected function setUpEntityManager() {
    $this->em = $this->module->getEntityManager($this->con, $reset = TRUE, $resetConnection = TRUE);
  }

  /**
   * Wird nur einmal Pro TestKlasse aufgerufen
    *
   * code in setup der sachen aus der Datenbank braucht muss nach parent::setUp() stehen
   *
   * setUpDatabase()
   *
   * setUp()
   * firstTest()
   * tearDown()
   *
   * setUp()
   * secondTest()
   * tearDown()
   *
   * setUp()
   * thirdTest()
   * tearDown()
   */
  protected function setUpDatabase() {
    $this->dcFixtures->execute();
  }

  protected function setUpFixturesManager() {
    $this->dcFixtures = new FixturesManager($this->em);
    $this->setUpFixtures();
  }

  public function setUpFixtures() {
    if (Code::isTraversable($this->fixtures)) {
      foreach ($this->fixtures as $fixture) {
        $this->dcFixtures->add($fixture);
      }
    }
  }
  
  protected function resetDatabaseOnNextTest() {
    self::$setupDatabase = TRUE;
  }
  
  /**
   * @return Psc\Doctrine\DCPackage
   */
  public function getDoctrinePackage() {
    if (!isset($this->dc)) {
      $this->dc = new DCPackage($this->module, $this->em);
    }
    return $this->dc;
  }

  /**
   * @return ClassMetadata
   */
  public function getEntityMetadata($entityName) {
    return $this->em->getMetaDataFactory()->getMetadataFor($this->getEntityName($entityName));
  }
  
  public function getSchemaManager() {
    if (!isset($this->sm)) {
      $this->sm = $this->em->getConnection()->getSchemaManager();
    }
    
    return $this->sm;
  }
  
  /**
   * @return Psc\Doctrine\Mocks\EntityManager
   */
  public function getEntityManagerMock() {
    return $this->doublesManager->createEntityManagerMock($this->module, $this->con);
  }

  protected function onNotSuccessfulTest(\Exception $e) {
    if (isset($this->em) && $this->em->getConnection()->isTransactionActive()) {
      $this->em->getConnection()->rollback();
    }
    
    parent::onNotSuccessfulTest($e);
  }
  
  
  /* HELPERS */
  /**
   * @return EntitiyRepository
   */
  public function getRepository($name) {
    return $this->em->getRepository($this->getEntityName($name));
  }

  /**
   * @return string
   */
  public function getEntityName($shortName) {
    return $this->module->getEntityName($shortName);
  }

  /**
   * @chainable
   */
  public function startDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(new \Psc\Doctrine\FlushSQLLogger);
    return $this;
  }
  
  /**
   * @chainable
   */
  public function stopDebug() {
    $this->em->getConnection()->getConfiguration()->setSQLLogger(NULL);
    return $this;
  }
  
  public function dump($var, $depth = 3) {
    \Psc\Doctrine\Helper::dump($var, $depth);
  }
  
  /**
   * Hydrates one entity by criterias or by identifier
   * 
   * @param int|string|array an identifier or an array
   * @return object<$entity>
   */
  public function hydrate($entity, $data) {
    if (is_array($data) && !A::isNumeric($data)) {// numeric bedeutet composite key (z.b. OID)
      return $this->getRepository($entity)->hydrateBy($data);
    } else {
      return $this->getRepository($entity)->hydrate($data);
    }
  }

  /* ASSERTIONS */
  
  /**
   * Asserted 2 Collections mit einem Feld als vergleicher
   */
  public function assertCollection($expected, $actual, $compareFieldGetter = 'identifier') {
    $this->assertEquals(DoctrineHelper::map($expected, $compareFieldGetter),
                        DoctrineHelper::map($actual, $compareFieldGetter),
                        'Collections sind nicht gleich'
                       );
  }
}
