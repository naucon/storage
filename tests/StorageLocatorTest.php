<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Tests;

use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\StorageRegistry;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Tests\Model\Category;
use Naucon\Storage\StorageInterface;
use Naucon\Storage\StorageLocator;
use Naucon\Storage\Tests\Model\User;
use PHPUnit\Framework\TestCase;

class StorageLocatorTest extends TestCase
{
    /**
     * @var \Naucon\Storage\Tests\Model\Product
     */
    protected $model1;

    /**
     * @var \Naucon\Storage\Tests\Model\Category
     */
    protected $model2;

    /**
     * @var \Naucon\Storage\Tests\Model\User
     */
    protected $model3;

    /**
     * @var StorageRegistry
     */
    protected $storageRegistry;


    public function setUp()
    {
        parent::setUp();

        $this->model1 = new Product();
        $this->model1->setId(1);
        $this->model1->setSku('foo');
        $this->model1->setDescription('Apple');

        $this->model2 = new Category();
        $this->model2->setId(1);
        $this->model2->setDescription('Fruit');

        $this->model3 = new User();
        $this->model3->setId(1);
        $this->model3->setUsername('Tom');

        $this->storageRegistry = new StorageRegistry();
        $this->storageRegistry->register('product1', new ArrayStorage(Product::class));
        $this->storageRegistry->register('product2', new ArrayStorage(Product::class));
    }

    public function testLocate()
    {
        $storageLocator = new StorageLocator($this->storageRegistry);
        $actualStorages = $storageLocator->locate($this->model1);

        $expectedStorages = array_values($this->storageRegistry->all());

        $this->assertEquals($expectedStorages, $actualStorages);
    }

    public function testLocateWithDifferentModels()
    {
        $expectedStoragesForModel1 = array_values($this->storageRegistry->all());

        $this->storageRegistry->register('category', new ArrayStorage(Category::class));
        $this->storageRegistry->register('user', new ArrayStorage(User::class));


        $storageLocator = new StorageLocator($this->storageRegistry);
        $actualStorages = $storageLocator->locate($this->model1);
        $this->assertCount(2, $actualStorages);
        $this->assertEquals($expectedStoragesForModel1, $actualStorages);

        $actualStorages = $storageLocator->locate($this->model2);
        $this->assertCount(1, $actualStorages);

        $actualStorages = $storageLocator->locate($this->model3);
        $this->assertCount(1, $actualStorages);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\MissingStorageException
     */
    public function testLocateWithNoStorage()
    {
        $storageLocator = new StorageLocator(new StorageRegistry());
        $storageLocator->locate($this->model1);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\MissingStorageException
     */
    public function testLocateWithUnknownModel()
    {
        $storageLocator = new StorageLocator($this->storageRegistry);
        $storageLocator->locate($this->model2);
    }

    public function getStorageMock()
    {
        $mock = $this->createMock(StorageInterface::class);

        return $mock;
    }
}
