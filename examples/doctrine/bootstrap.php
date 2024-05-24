<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

require realpath(__DIR__ . '/../../') . '/vendor/autoload.php';

$config = ORMSetup::createXMLMetadataConfiguration(
    array(__DIR__."/../../tests/Resources/mapping"),
    true
);

$connection = DriverManager::getConnection([
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/tmp/db.sqlite',
], $config);

$entityManager = new EntityManager($connection, $config);

