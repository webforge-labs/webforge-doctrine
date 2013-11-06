<?php

namespace Webforge\Doctrine\Test;

use Webforge\Doctrine\Util;

/**
 * A testcase that requires that the schema for the entities is used
 */
class SchemaTestCase extends Base {

  public static $schemaCreated = FALSE;

  public function setUp() {
    parent::setUp();

    $this->initDoctrineContainer();
    $this->setUpEntityManager();

    if (!self::$schemaCreated) {
      $this->dcc->getUtil()->updateSchema('tests', Util::FORCE, $eol = "\n");

      self::$schemaCreated = TRUE;
    }
  }
}
