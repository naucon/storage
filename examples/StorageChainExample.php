<?php

require realpath(__DIR__ . '/../') . '/vendor/autoload.php';

use Naucon\Storage\StorageChain;
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
if (!$storage2->has(1)) {
    echo 'no product in session';
    echo '<br/>';
}

// service definition
$storageChain = new StorageChain();
$storageChain->register('product_default', $storage1);
$storageChain->register('product_session', $storage2);

if ($storageChain->has(1)) {
    $product = $storageChain->find(1);
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';

} else {
    echo 'Missing Product';
}

// save entry (in all registered storages)
$storageChain->flush(1, $model);

if ($storage2->has(1)) {
    echo 'product is in session';
    echo '<br/>';
}

echo 'Datasets: ' .  count($storageChain->findAll());    // 1 datasets
echo '<br/>';

// remove entry (in all registered storages)
$storageChain->remove(1, $product);
echo 'remove dataset';
echo '<br/>';


// find all entries
echo 'Datasets: ' .  count($storageChain->findAll());    // 0 datasets
echo '<br/>';
