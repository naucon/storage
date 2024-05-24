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

use Naucon\Storage\CreateAwareInterface;
use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\StorageInterface;
use Naucon\Storage\StorageManager;
use PHPUnit\Framework\TestCase;

class StorageManagerTest extends TestCase
{
    /**
     * @var \Naucon\Storage\Tests\Model\Product
     */
    protected $model1;

    /**
     * @var \Naucon\Storage\Tests\Model\Product
     */
    protected $model2;

    /**
     * @var StorageInterface
     */
    protected $storage;



    public function setUp()
    {
        parent::setUp();

        $this->model1 = new Product();
        $this->model1->setId(1);
        $this->model1->setSku('foo');
        $this->model1->setDescription('Apple');

        $this->model2 = new Product();
        $this->model2->setId(2);
        $this->model2->setSku('bar');
        $this->model2->setDescription('Pear');

        $this->storage = new ArrayStorage(Product::class);
        $this->storage->flush(2, $this->model2);
    }

    public function testInit()
    {
        $storage = new StorageManager();

        $this->assertInstanceOf(StorageInterface::class, $storage);
        $this->assertInstanceOf(CreateAwareInterface::class, $storage);
    }

    public function testHasStorage()
    {
        $storage = new StorageManager();
        $this->assertFalse($storage->hasStorage());

        $storage = new StorageManager($this->storage);
        $this->assertTrue($storage->hasStorage());
    }

    public function testCreate()
    {
        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\UnsupportedException
     */
    public function testCreateWithSupportingSubStorage()
    {
        /**
         * @var StorageInterface $subStorage
         */
        $subStorage = $this->createMock(StorageInterface::class);

        $storage = new StorageManager();
        $storage->setStorage($subStorage);
        $storage->create();
    }

    public function testFindOrCreateWithFoundEntity()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->findOrCreate($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindOrCreateWithMissingEntity()
    {
        $identifier = 1;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFind()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->find($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier = 1;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\MissingStorageException
     */
    public function testFindWithoutStorage()
    {
        $identifier = 1;

        $storage = new StorageManager();
        $storage->find($identifier);
    }

    public function testFindMultiple()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];

        $expectedResult = [
            $this->model1,
            $this->model2
        ];

        $storage = new StorageManager();
        $storage->setStorage(new ArrayStorage());
        $storage->flush($identifier1, $this->model1);
        $storage->flush($identifier2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithCompositeIdentifier()
    {
        $criteria1 = ['product_id' => 1];
        $criteria2 = ['product_id' => 2];

        $identifiers = [
            $criteria1,
            $criteria2
        ];

        $expectedResult = [
            $this->model1,
            $this->model2
        ];

        $storage = new StorageManager();
        $storage->setStorage(new ArrayStorage());
        $storage->flush($criteria1, $this->model1);
        $storage->flush($criteria2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 3;
        $identifier2 = 4;
        $identifiers = [$identifier1, $identifier2];

        $expectedResult = [];

        $storage = new StorageManager();
        $storage->setStorage(new ArrayStorage());
        $storage->flush(1, $this->model1);
        $storage->flush(2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testHas()
    {
        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $identifier = 2;
        $this->assertTrue($storage->has($identifier));

        $identifier = 1;
        $this->assertFalse($storage->has($identifier));
    }

    public function testFindAll()
    {
        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $models = $storage->findAll();

        $this->assertEquals([$this->model2], $models);
    }

    public function testFlush()
    {
        $identifier = 1;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $this->assertFalse($this->storage->has($identifier));

        $this->assertTrue($storage->flush($identifier, $this->model1));

        $this->assertTrue($this->storage->has($identifier));

        $this->assertEquals($this->model1, $this->storage->find($identifier));
    }

    public function testFlushWithExistingId()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $this->assertTrue($storage->flush($identifier, $this->model2));
    }

    public function testFlushNewModel()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $newModel = new Product();
        $newModel->setId(3);
        $newModel->setSku('foobar');
        $newModel->setDescription('Orange');

        $this->assertTrue($storage->flush(3, $newModel));
        $this->assertTrue($storage->has(3));
    }

    public function testFlushAndReplaceModel()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $newModel = new Product();
        $newModel->setId(2);
        $newModel->setSku('bar');
        $newModel->setDescription('Orange');

        $this->assertTrue($storage->flush(2, $newModel));

        $model = $storage->find($identifier);

        $this->assertNotEquals($this->model2, $model);
    }

    public function testRemove()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveWithMissingId()
    {
        $identifier = 1;

        $storage = new StorageManager();
        $storage->setStorage($this->storage);

        $result = $storage->remove($identifier, $this->model1);

        $this->assertTrue($result);
    }

    public function testWithoutSupport()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage(new ArrayStorage());

        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveAll()
    {
        $identifier = 2;

        $storage = new StorageManager();
        $storage->setStorage(new ArrayStorage());

        $storage->flush( $this->model1->getId(), $this->model1);
        $storage->flush( $this->model2->getId(), $this->model2);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($storage->removeAll());

        $this->assertFalse($storage->has($identifier));
    }
}
