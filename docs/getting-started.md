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
  )
);

$em = $dcc->getEntityManager('default');
```

If your framework provides you with a method to get a Doctrine Container use this, because it will nicely integrate with your other configuration.