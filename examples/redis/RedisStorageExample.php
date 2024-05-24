<?php

require realpath(__DIR__ . '/../../') . '/vendor/autoload.php';

$client = new Predis\Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
    'password' => 'password',
    'database' => 0
]);


use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Provider\RedisStorage;


// prepare storage for example to be not empty
$model = new Product();
$model->setId(1);
$model->setSku('U123');
$model->setDescription('Dragon fruit');

$model2 = new Product();
$model2->setId(2);
$model2->setSku('U112');
$model2->setDescription('Orange');

// service definition
$storage = new RedisStorage($client, Product::class);
// $storage = new RedisStorage($client, Product::class, 'product');  // with extra namespace
// $storage = new RedisStorage($client, Product::class, null, 60);  // with lift time of 60 seconds

// save entry
$storage->flush(1, $model);
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

// find all entries
echo 'Datasets: ' .  count($storage->findAll());    // 1 datasets
echo '<br/>';

// find multiple entries
echo 'Multiple Datasets: ' .  count($products = $storage->findMultiple([1, 2]));    // 1 datasets
var_dump($products);
echo '<br/>';

// remove one entry
$storage->remove(1, $model);
echo 'remove dataset';
echo '<br/>';

// find all entries
echo 'Datasets: ' .  count($storage->findAll());    // 0 datasets
echo '<br/>';

$storage->removeAll();
echo 'remove all datasets';
echo '<br/>';

// find all entries
echo 'Datasets: ' .  count($storage->findAll());    // 0 datasets
echo '<br/>';
