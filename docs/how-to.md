# How to

## create an entity programatically

Use the `Webforge\Doctrine\EntityFactory` to create an entity just with the fqn and an array of field => value pairs. The EntityFactory will still work if the constructor of the entity changes.