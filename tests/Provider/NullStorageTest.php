<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Tests\Provider;

use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Provider\NullStorage;
use PHPUnit\Framework\TestCase;

class NullStorageTest extends TestCase
{
    /**
     * @var \Naucon\Storage\Tests\Model\Product
     */
    protected $model1;

    /**
     * @var \Naucon\Storage\Tests\Model\Product
     */
    protected $model2;



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
    }

    public function testCreate()
    {
        $storage = new NullStorage(Product::class);
        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFindOrCreateWithFoundEntity()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf(Product::class, $model);
        $this->assertNotEquals($this->model2, $model);
    }

    public function testFindOrCreateWithMissingEntity()
    {
        $identifier = 1;

        $storage = new NullStorage(Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFind()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    public function testFindWithCompositeIdentifier()
    {
        $criteria = ['product_id' => 2];

        $storage = new NullStorage(Product::class);
        $storage->flush($criteria, $this->model2);

        $model = $storage->find($criteria);

        $this->assertNull($model);
    }

    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier = 1;

        $storage = new NullStorage(Product::class);
        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    public function testFindMultiple()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];

        $expectedResult = [];

        $storage = new NullStorage(Product::class);
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

        $storage = new NullStorage(Product::class);
        $storage->flush($criteria1, $this->model1);
        $storage->flush($criteria2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals([], $models);
    }

    public function testHas()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $this->assertFalse($storage->has($identifier));
    }

    public function testFindAll()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $models = $storage->findAll();

        $this->assertEquals([], $models);
    }

    public function testFlush()
    {
        $identifier = 1;

        $storage = new NullStorage(Product::class);

        $this->assertFalse($storage->has($identifier));
        $this->assertTrue($storage->flush($identifier, $this->model1));

        $this->assertFalse($storage->has($identifier));
        $this->assertNull($storage->find($identifier));
    }

    public function testFlushWithExistingId()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $this->assertTrue($storage->flush($identifier, $this->model2));
    }

    public function testRemove()
    {
        $identifier = 2;

        $storage = new NullStorage(Product::class);
        $storage->flush($identifier, $this->model2);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveWithMissingId()
    {
        $identifier = 1;

        $storage = new NullStorage(Product::class);

        $result = $storage->remove($identifier, $this->model1);

        $this->assertTrue($result);
    }

    public function testWithoutSupport()
    {
        $identifier = 2;

        $storage = new NullStorage();
        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);
        $this->assertNull($model);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveAll()
    {
        $identifier2 = 2;
        $identifier1 = 1;

        $storage = new NullStorage();
        $storage->flush($identifier2, $this->model2);
        $storage->flush($identifier1, $this->model1);

        $this->assertEquals([], $storage->findAll());

        $this->assertTrue($storage->removeAll());
        $this->assertEquals([], $storage->findAll());
    }
}
