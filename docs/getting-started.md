# Getting started

Get a new instance of the `Webforge\Doctrine\Container`.  
Then put in your database configuration:

```php
use Webforge\Doctrine\Container;

$dcc = new Container();

$dcc->initDoctrine(
  array(
    'default'=>array(
      'database'=>'acme-blog',
      'user'=>'acme',
      'password'=>'r0adrunn3r',
      'driver'=>'pdo_mysql',
    ),
    'tests'=>array(
      'database'=>'acme-blog_tests',
      'user'=>'acme',
      'password'=>'r0adrunn3r',
      'driver'=>'pdo_sqlite', // default: pdo_mysql
    )
  ),
  array(
    Dir::factory(__DIR__.DIRECTORY_SEPARATOR)->sub('../lib/ACME/SuperBlog/Entities/')->resolvePath()
  )
);

$em = $dcc->getEntityManager('default');

/*
 the defaults for the configuration are: host=>127.0.0.1, port=>NULL, unix_socket=>NULL, charset="utf8"
 defaults from doctrine DBAL are used see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
*/
```

If your framework provides you with a method to get a Doctrine Container use this, because it will nicely integrate with your other configuration.