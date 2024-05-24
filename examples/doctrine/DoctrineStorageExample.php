<?php

use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Provider\DoctrineStorage;

require_once 'bootstrap.php';

// prepare storage for example to be not empty
$model1 = new Product();
$model1->setId(1);
$model1->setSku('U123');
$model1->setDescription('Dragon fruit');

$model2 = new Product();
$model2->setId(2);
$model2->setSku('U225');
$model2->setDescription('Mango');

// service definition
$storage = new DoctrineStorage($entityManager, Product::class);

// save entry
$storage->flush(1, $model1);
$storage->flush(2, $model2);

// find entry
if ($storage->has(1)) {
    $product = $storage->find(1);
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';

} else {
    echo 'Missing Product';
    echo '<br/>';
}
echo '<br/>';

// find all entries
echo 'All datasets: ' .  count($products = $storage->findAll());    // 2 datasets
echo '<br/>';
foreach ($products as $product) {
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';
}
echo '<br/>';

// find multiple entries
echo 'Multiple datasets: ' .  count($products = $storage->findMultiple([1, 2]));    // 2 datasets
echo '<br/>';
foreach ($products as $product) {
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';
}
echo '<br/>';

// remove entry
$storage->remove(2, $model2);
echo 'remove dataset';
echo '<br/>';
echo '<br/>';

// find all entries
echo 'All datasets: ' .  count($products = $storage->findAll());    // 1 datasets
echo '<br/>';
foreach ($products as $product) {
    /** @var \Naucon\Storage\Tests\Model\Product $product */
    echo $product->getSku() . ' ' . $product->getDescription();
    echo '<br/>';
}
echo '<br/>';

// remove entry
$storage->removeAll();
echo 'remove all datasets';
echo '<br/>';
echo '<br/>';

// find all entries
echo 'All datasets: ' .  count($storage->findAll());    // 0 datasets
echo '<br/>';
echo '<br/>';
