<?php

require realpath(__DIR__ . '/../') . '/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Session\Session;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Provider\SessionBridgeStorage;
use Naucon\Storage\Provider\SessionBag;


// prepare storage for example to be not empty
$model = new Product();
$model->setId(1);
$model->setSku('U123');
$model->setDescription('Dragon fruit');

// service definition
$session = new Session();
$session->registerBag(new SessionBag());

$storage = new SessionBridgeStorage($session, Product::class);

// save entry
$storage->flush(1, $model);

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

echo 'Datasets: ' .  count($storage->findAll());    // 1 datasets
echo '<br/>';

// remove entry
$storage->remove(1, $product);
echo 'remove dataset';
echo '<br/>';

// find all entries
echo 'Datasets: ' .  count($storage->findAll());    // 0 datasets
echo '<br/>';
