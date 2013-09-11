# Doctrine bridge for webforge

Well actually it is for every library that is hand built and wants to use doctrine. It sits on top on [doctrine ORM](http://www.doctrine-project.org/) and makes your life easier

## the container (dcc)

When I first wrote a doctrine bridge for psc-cms everything was passed around as an entity manager. After a short period I noticed, that this was just to few informations for some business objects. So this time I'm going to start with a dependency injection container. `Webforge\Doctrine\Container` is the point into this library. It will get passed around everywhere. So this is your first point where to start.
Because `dc` was used as DoctrinePackage in psc-cms and `container` is used so often for framework containers we often call the instance of the `Webforge\Doctrine\Container` just `dcc`. `dc` for doctrine and `c` for container: `dcc`.

## instantiate a container

(this needs to be specified)

  - configuration for the `con`s
  - paths to store entities


## goals

even if this is the webforge bridge, always try to provide an api that is low coupled as possible.