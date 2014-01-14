<?php

namespace Webforge\Doctrine;

use Webforge\Doctrine\Test\Entities\Author;
use Webforge\Doctrine\Test\Entities\User;
use Webforge\Doctrine\Test\Entities\Post;

class EntityFactoryTest extends \Webforge\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = __NAMESPACE__ . '\\EntityFactory';
    parent::setUp();
  }

  public function testFactoryCanCreateAnEntityWithoutAnyConstructorOnlyWithSetters() {
    $factory = new EntityFactory('Webforge\Doctrine\Test\Entities\User');

    $user = $factory->create(array(
      'email'=>$email = 'p.scheit@ps-webforge.com',
      'special'=>$special = 'internal'
    ));

    $this->assertInstanceOf('Webforge\Doctrine\Test\Entities\User', $user);
    $this->assertEquals($email, $user->getEmail());
    $this->assertEquals($special, $user->getSpecial());
  }

  public function testFactoryCanCreateAnEntityWithConstructorAndSettersCombined() {
    $factory = new EntityFactory('Webforge\Doctrine\Test\Entities\Post');

    $author = new Author('p.scheit@ps-webforge.com');
    $revisor = new Author('y.bobkova@ps-webforge.com');

    $post = $factory->create(array(
      // per constructor
      'author'=>$author,
      'revisor'=>$revisor,

      // per setter
      'active'=>FALSE,
      'created'=>'today'
    ));

    $this->assertInstanceOf('Webforge\Doctrine\Test\Entities\Post', $post);
    $this->assertSame($author, $post->getAuthor(), 'author');
    $this->assertSame($revisor, $post->getRevisor(), 'revisor');
    $this->assertFalse($post->getActive(), 'active');
    $this->assertEquals('today', $post->getCreated(), 'created');
  }
}
