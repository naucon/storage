<?php

require realpath(__DIR__ . '/../') . '/vendor/autoload.php';

use Naucon\Storage\StorageFactory;

use Naucon\Storage\StorageChain;
use Naucon\Storage\StorageManager;
use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\Provider\NativeSessionStorage;
use Naucon\Storage\Tests\Model\Product;

// prepare storage for example to be not empty
$model = new Product();
$model->setId(1);
$model->setSku('U123');
$model->setDescription('Dragon fruit');


$storage1 = new ArrayStorage(Product::class);
$storage1->flush(1, $model);

$storage2 = new NativeSessionStorage(Product::class);
$storage2->flush(1, $model);

// storage chain definition
$storageChain = new StorageChain();
$storageChain->register('product_default', $storage1);
$storageChain->register('product_session', $storage2);

// define storage manager
$storageManager = new StorageManager($storageChain);

// register storage manager
$storageFactory = new StorageFactory();
$storageFactory->register('product', $storageManager);

$storage = $storageFactory->getStorage('product');

if ($storage->has(1)) {
    $product = $storage->find(1);
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';

} else {
    echo 'Missing Product';
}
