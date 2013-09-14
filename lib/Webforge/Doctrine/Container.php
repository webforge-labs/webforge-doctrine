<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * A Container for the full configuratino and business objects from the doctrine module
 * 
 * Language:
 * 
 *   $con always describes a single string that identifies the connection (for example default, or tests)
 *   a connection has a database, a user and a password and an encoding
 *   for every $con one entityManager is in the container (if requested)
 *   
 * 
 */
class Container {

  /**
   * @var Doctrine\ORM\EntityManager[]
   */
  protected $entityManagers;

  /**
   * @var Webforge\Doctrine\Util
   */
  protected $util;

  /**
   * @var Doctrine\ORM\Tools\SchemaTool[]
   */
  protected $schemaTools;

  /**
   * @param string $con default is 'default'
   */
  public function getEntityManager($con = NULL) {
    if (!isset($con)) $con = 'default';

    if (isset($this->entityManagers[$con])) {
      return $this->entityManagers[$con];
    }

    throw new \LogicException('Im not so intelligent right now: EntityManager for '.$con.' was not injected.');
  }

  /**
   * @return Doctrine\ORM\Tools\SchemaTool
   */
  public function getSchemaTool($con) {
    if (!isset($this->schemaTools[$con])) {
      return $this->schemaTools[$con] = new SchemaTool($this->getEntityManager($con));
    }

    return $this->schemaTools[$con];
  }

  /**
   * @return Webforge\Doctrine\Util
   */
  public function getUtil() {
    if (!isset($this->util)) {
      $this->util = new Util($this);
    }

    return $this->util;
  }

  public function injectEntityManager(EntityManager $em, $con = NULL) {
    if (!isset($con)) $con = 'default';

    $this->entityManagers[$con] = $em;
    return $this;
  }

  public function injectSchemaTool(SchemaTool $schemaTool, $con) {
    $this->schemaTools[$con] = $schemaTool;
    return $this;
  }

  public function injectUtil(Util $util) {
    $this->util = $util;
    return $this;
  }
}
