<?php

require realpath(__DIR__ . '/../') . '/vendor/autoload.php';

use Naucon\Storage\StorageFactory;
use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\Tests\Model\Product;

// prepare storage for example to be not empty
$model = new Product();
$model->setId(1);
$model->setSku('U123');
$model->setDescription('Dragon fruit');

$storage = new ArrayStorage(Product::class);
$storage->flush(1, $model);


// service definition
$storageFactory = new StorageFactory();
$storageFactory->register('product', $storage);

$storage = $storageFactory->getStorage('product');

if ($storage->has(1)) {
    $product = $storage->find(1);
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';

} else {
    echo 'Missing Product';
}
