<?php

namespace Webforge\Doctrine\Exceptions;

class PDOExceptionHandlerTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\PDOExceptionHandler';
    parent::setUp();

    $this->handler = new PDOExceptionHandler();
  }

  /**
   * @dataProvider provideExceptionConversionFromNativePDOExceptions
   */
  public function testExceptionConversionFromNativePDOExceptions(\PDOException $pdoException, $expectedFQN) {
    $exception = $this->handler->convert($pdoException);

    $this->assertInstanceOf('Webforge\Doctrine\Exceptions\Exception', $exception, 'should extend the base class');
    $this->assertInstanceOf($expectedFQN, $exception, 'converted exception not from correct type');
  }
  
  public static function provideExceptionConversionFromNativePDOExceptions() {
    $tests = array();
  
    $test = function() use (&$tests) {
      $tests[] = func_get_args();
    };

    $e = new \PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'evangelina.karge@ps-webforge.com' for key 'UNIQ_1483A5E9A0D96FBF'");
/*    $e->errorInfo = array(
      0=>'23000',
      1=>1062, 
      2=>"Duplicate entry 'evangelina.karge@ps-webforge.com' for key 'UNIQ_1483A5E9A0D96FBF'"
    );
*/  
    $test($e,'Webforge\Doctrine\Exceptions\UniqueConstraintException');

    $e = new \PDOException("SQLSTATE[08004] [1040] Too many connections");

    $test($e,'Webforge\Doctrine\Exceptions\TooManyConnectionsException');

    $e = new \PDOException("SQLSTATE[42S22]: Column not found: 1054 Unknown column 'field_data_field_noderef_frontpage_image.nid' in 'on clause'
in views_plugin_query_default->execute()'");
    $test($e, 'Webforge\Doctrine\Exceptions\UnknownColumnException');


    $e = new \PDOException("SQLSTATE[HY000]: General error: 1030 Got error -1 from storage engine: UPDATE {locales_source} SET version=:db_update_placeholder_0 WHERE (lid = :db_condition_placeholder_0) ; Array ( [:db_update_placeholder_0] =&gt; 7.12 [:db_condition_placeholder_0] =&gt; 3964 ) in locale()");
    $test($e, 'Webforge\Doctrine\Exceptions\ForeignKeyConstraintException');

    return $tests;
  }
}
