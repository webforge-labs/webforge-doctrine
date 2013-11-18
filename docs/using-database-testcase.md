# Using the DatabaseTestCase

Sometimes, when you want to acceptance test your whole application (including the database layer), you will persist remove and use fixtures for your entities.
Gladly doctrine comes with a doctrine fixtures extension.

You can start using webforge doctrine with extending the databasetestcase:

```php
class PushNotificationAcceptanceTest extends \Webforge\Doctrine\Test\DatabaseTestCase {
}
```

## env of the database test case

`$this->dcc` points to a `Webforge\Doctrine\Container`.
`$this->em` points to a `Doctrine\ORM\EntityManager` (by default with the con: tests)
`$this->fm` points to a `Webforge\Doctrine\Fixtures\FixturesManager`
`$this->getSchemaManager()` returns a `Doctrine\DBAL\Schema\AbstractSchemaManager` for your current database platform

## convenience Entities shortnames

if you implement `protected function getEntityName($shortName)` to return the full FQN of your entities for the `$shortName` you can get repositories very quickly:

```php
use Webforge\Common\ClassUtil;

class PushNotificationAcceptanceTest extends \Webforge\Doctrine\Test\DatabaseTestCase {

  protected function getEntityName($shortName) {
    return ClassUtil::expandNamespace($shortName, 'ACME\SuperBlog\Entities');
  }

  public function testWithUserRepository() {
    $userRepository = $this->getRepository('user');

    // ...
  }
}
```

## overwrite the used con for your tests

Normally the testcase uses the con `tests` for it. If you want to change it, overwrite the property $con:
```php
class PushNotificationAcceptanceTest extends \Webforge\Doctrine\Test\DatabaseTestCase {
  
  protected $con = 'mycon';

}
```
or make sure `tests` is defined as con in your config:

```php
$conf['db']['tests']['host'] = '127.0.0.1';
$conf['db']['tests']['user'] = 'blog';
$conf['db']['tests']['password'] = 'secrect';
$conf['db']['tests']['database'] = 'blog_tests'
$conf['db']['tests']['port'] = NULL;
$conf['db']['tests']['charset'] = 'utf8';
```

## using fixtures

To use the doctrine fixtures you need to install (with composer) `"doctrine/data-fixtures": "1.0.*@dev"`
You can then create a new fixture like this:

```php
namespace ACME\SuperBlog;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class PushNotificationFixture extends AbstractFixture {

  public function load(ObjectManager $em) {
    // do nothing, leave empty
  }
}
```

and then tell the databasetest to use this fixture:

```php
class PushNotificationAcceptanceTest extends \Webforge\Doctrine\Test\DatabaseTestCase {

  protected function getFixtures() {
    return array(new \ACME\SuperBlog\PushNotificationFixture());
  }

}
```

If you leave the fixture empty your database will be purged (in mysql this truncate is used). You can persist new Entities in the `load()`-method of the fixture. Dont forget to call `$em->flush()` on the end of the load operation.

You can now start writing your tests. Everytime you call `$this->resetDatabaseOnNextTest()` your database will be truncated and then called load() from the fixture again.  
**NOTICE**: If you don't call `resetDatabaseOnNextTest()` your fixture will only executed once for every test file!

## configuring special entities paths

If you are using special entities paths for your tests (you shouldn't) you can overwrite them with

```php
  protected function initEntitiesPaths() {
    if (!isset($this->entitiesPaths)) {
      $this->entitiesPaths = array(new Dir('myspecial/path/to/Entities/'));
    }
  }
```
This should be a root PSR-0 classes directory. Per default the databasetestcase uses the entities paths defined in `$project->dir('doctrine-entities')`. You can configure your directory locations in the composer.json of your project like this:

```json
{
    "name": 

    (....)

    "extra": {
      "directory-locations": {
        "doctrine-entities": "tests/files/Entities/"
      }
    }
}
```

## special database setup

If you dont want to use doctrine fixtures you can still setUp your database on your own:

```php
  protected function setUpDatabase() {
    // $this->fm->execute(); // this is the default implementation for setUpDatabase

    // do something to setup your database:
    $entity = new Post(...);
    $this->em->persist($entity);
    $this->em->flush();
  }
```
