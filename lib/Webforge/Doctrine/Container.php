<?php

namespace Webforge\Doctrine;

use Doctrine\ORM\EntityManager;

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

  protected $entityManagers;

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

  public function injectEntityManager(EntityManager $em, $con = NULL) {
    if (!isset($con)) $con = 'default';

    $this->entityManagers[$con] = $em;
    return $this;
  }
}
