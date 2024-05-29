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

use Naucon\Storage\Exception\MissingStorageException;
use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\StorageRegistry;
use Naucon\Storage\Tests\Model\Category;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\StorageInterface;
use Naucon\Storage\StorageFactory;
use PHPUnit\Framework\TestCase;

class StorageFactoryTest extends TestCase
{
    /**
     * @var Product
     */
    protected $model1;

    /**
     * @var Product
     */
    protected $model2;

    /**
     * @var StorageInterface
     */
    protected $storage1;

    /**
     * @var StorageInterface
     */
    protected $storage2;

    /**
     * @var StorageRegistry
     */
    protected $registry;



    public function setUp(): void
    {
        parent::setUp();

        $this->model1 = new Product();
        $this->model1->setId(1);
        $this->model1->setSku('foo');
        $this->model1->setDescription('Apple');

        $this->model2 = new Category();
        $this->model2->setId(2);
        $this->model2->setDescription('Fruit');

        $this->storage1 = new ArrayStorage(Product::class);
        $this->storage1->flush($this->model1->getId(), $this->model1);

        $this->storage2 = new ArrayStorage(Category::class);
        $this->storage2->flush($this->model2->getId(), $this->model2);

        $this->registry = new StorageRegistry();
        $this->registry->register('product', $this->storage1);
        $this->registry->register('category', $this->storage2);
    }

    public function testInit()
    {
        $manager = new StorageFactory();
        $manager->register('product', $this->storage1);
        $manager->register('category', $this->storage2);

        $this->assertInstanceOf(StorageFactory::class, $manager);
    }

    public function testGetStorage()
    {
        $manager = new StorageFactory();
        $manager->register('product', $this->storage1);
        $manager->register('category', $this->storage2);

        $storage = $manager->getStorage('product');

        $this->assertInstanceOf(StorageInterface::class, $storage);
    }

    public function testGetStorageMissingStorage()
    {
        $this->expectException(MissingStorageException::class);
        $manager = new StorageFactory();
        $manager->register('product', $this->storage1);
        $manager->register('category', $this->storage2);

        $manager->getStorage('foo');
    }
}
