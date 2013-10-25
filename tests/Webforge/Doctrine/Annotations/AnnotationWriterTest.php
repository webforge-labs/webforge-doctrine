<?php

namespace Webforge\Doctrine\Annotations;

use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotationWriterTest extends \Webforge\Doctrine\Test\Base {
  
  public function setUp() {
    parent::setUp();

    //$this->createAnnotationReader();

    $this->defaultWriter = new Writer();
    $this->defaultWriter->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping');

    $this->writer = new Writer();
    $this->writer->setAnnotationNamespaceAlias('Doctrine\ORM\Mapping', 'ORM');

    $this->registerAnnotations();
  }
  
  /**
   * Parses some DocBlockText and returns the annotations found
   */
  protected function parseAnnotations($docText, $context) {
    $parser = new DocParser;
    $parser->addNamespace('Doctrine\ORM\Mapping');

    $annotations = $parser->parse($docText, $context);
    $this->assertInternalType('array', $annotations, 'cannot parse the annotation string: '.$docText);

    return $annotations;
  }
  
  /**
   * @dataProvider provideSingleAnnotations
   */
  public function testWritesCorrectlyParsedAnnotationsAsString($docText, $context) {
    $annotations = $this->parseAnnotations($docText, $context);
    $annotation = current($annotations);

    $this->assertEquals(
      $docText, 
      $this->defaultWriter->writeAnnotation($annotation)
    );
  }
  
  public static function provideSingleAnnotations() {
    $tests = array();
    
    // testSinglePlainValue
    $tests[] = '@Column(type="integer")';
    
    // testBoolValue
    $tests[] = '@Column(nullable=true)';
    
    // single flat plain
    $tests[] = '@ChangeTrackingPolicy("NOTIFY")';

    // without values
    $tests[] = '@Column';
    
    // nested annotation
    $tests[] = '@Table(name="test_tags", uniqueConstraints={@UniqueConstraint(name="label", columns={"label"})})';

    // orderBy hier für brauchen wirs dann (mit value)
    $tests[] = '@OrderBy({"someField"="ASC"})';
    
    // custom annotation 
    $tests[] = '@\\'.__NAMESPACE__.'\ComplexAnnotation(root1Key="rootValue2", root2Key="rootValue1")';
    
    return array_map(
      function ($annotationString) { 
        return array($annotationString, __CLASS__.'::provideSingleAnnoations');
      },
      $tests
    );
  }
  
  protected function createAnnotation($className, Array $properties) {
    $fqcn = 'Doctrine\ORM\Mapping\\'.$className;

    return new $fqcn($properties);
  }


  protected function registerAnnotations() {
    AnnotationRegistry::registerFile(
      $this->getPackageDir('vendor/')->getFile('doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php')
    );
  }
}

/**
 * @Annotation
 */
class ComplexAnnotation extends \Doctrine\Common\Annotations\Annotation implements \Doctrine\ORM\Mapping\Annotation {
  public $root1Key;
  public $root2Key;
}
