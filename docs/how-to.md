# How to

## create an entity programatically

Use the `Webforge\Doctrine\EntityFactory` to create an entity just with the fqn and an array of field => value pairs. The EntityFactory will still work if the constructor of the entity changes.

## synchronize a collection

```php
$entities = new \Webforge\Doctrine\Entities($entityManager, 'ACME\Blog\Entities');
$synchronizer = $entities->getCollectionSynchronizer('Post', 'tags');

$tags = array(
  ['id'=>NULL, 'label'=>'USA'],
  ['id'=>4, 'label'=>'Germany'],
  ['id'=>7, 'label'=>'France']
);

// synchronizes the tags in the $post with the toCollection $tags
// if a tag isn't persisted yet (USA) then it's created and persisted and then added
$synchronizer->process($post, $post->getTags(), $tags);

// $post->getTags() contains now 3 entities: Tag:USA, Tag:Germany, Tag:France
// tags that were previously in the collection will be deleted
```

## synchronize an ordered collection

PostImage is an entity, that wraps a binary (an image) and represents the position in the collection of images in the post. So PostImage has `binary` and `position` as properties.  
We hydrated or created a collection of images `$toCollection`, that we want to synchronize with the images-collection in `$post`.

```php
$toCollection = [
  (object) ['id'=>NULL, 'position'=>1, 'binary'=>$binary1], 
  (object) ['id'=>NULL, 'position'=>2, 'binary'=>$binary2], 
  (object) ['id'=>NULL, 'position'=>3, 'binary'=>$binary3], 
];

$synchronizer = $entities->getCollectionSynchronizer('Post', 'tags');

// we have a special constructor, so we override the default method from synchronizer here
$synchronizer->setCreater(function($image, $post) {
  return new PostImage($image->binary, $post, $image->position);
});

// index the images by its binary path
$dbImages = $post->getImages()->toArray();
$dbImages = A::indexBy($dbImages, function($image) {
  return $image->getBinary()->getPath();
});

// override the hydrator, so that we save a lot of single database requests to the db
$synchronizer->setHydrator(function (\stdClass $image) use ($dbImages) {
  if (array_key_exists($image->binary->getPath(), $dbImages)) {
    return $dbImages[$image->binary->getPath()];
  }
  return NULL;
});

$synchronizer->process($post, $post->getImages(), $toCollection);
```

If we would not override the hydrator with the lambda that searches in `$dbImages` it would work as well, but it would be much slower. Try to select all entities you need before synchronization. Because the `PostImage` is ManyToOne to `Post` it's possible to find all postImages in the collection of post.
