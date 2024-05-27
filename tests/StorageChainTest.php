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
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\StorageInterface;
use Naucon\Storage\StorageChain;
use PHPUnit\Framework\TestCase;

class StorageChainTest extends TestCase
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


    public function setUp(): void
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

        $this->storage1 = new ArrayStorage(Product::class);
        $this->storage1->flush(2, $this->model2);
        $this->storage2 = new ArrayStorage(Product::class);
        $this->storage2->flush(2, $this->model2);
    }

    public function testFind()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $model = $manager->find($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithMissingEntity()
    {
        $identifier = 1;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $model = $manager->find($identifier);

        $this->assertNull($model);
    }

    public function testFindWithoutStorage()
    {
        $identifier = 1;

        $manager = new StorageChain();
        $model = $manager->find($identifier);

        $this->assertNull($model);
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

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);
        $manager->flush($identifier1, $this->model1);

        $models = $manager->findMultiple($identifiers);

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

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);
        $manager->flush($criteria1, $this->model1);

        $models = $manager->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 3;
        $identifier2 = 4;
        $identifiers = [$identifier1, $identifier2];

        $expectedResult = [];

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $models = $manager->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithoutStorage()
    {
        $identifier1 = 3;
        $identifier2 = 4;
        $identifiers = [$identifier1, $identifier2];

        $expectedResult = [];

        $manager = new StorageChain();
        $models = $manager->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testHas()
    {
        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $identifier = 2;
        $this->assertTrue($manager->has($identifier));

        $identifier = 1;
        $this->assertFalse($manager->has($identifier));
    }

    public function testFindAll()
    {
        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);
        $models = $manager->findAll();

        $this->assertEquals([$this->model2], $models);
    }

    public function testFlush()
    {
        $identifier = 1;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $this->assertFalse($this->storage1->has($identifier));
        $this->assertFalse($this->storage2->has($identifier));

        $this->assertTrue($manager->flush($identifier, $this->model1));

        $this->assertTrue($this->storage1->has($identifier));
        $this->assertTrue($this->storage2->has($identifier));
        $this->assertEquals($this->model1, $this->storage1->find($identifier));
        $this->assertEquals($this->model1, $this->storage2->find($identifier));
    }

    public function testFlushWithExistingId()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $this->assertTrue($manager->flush($identifier, $this->model2));
    }

    public function testFlushNewModel()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $model = $manager->find($identifier);
        $this->assertEquals($this->model2, $model);

        $newModel = new Product();
        $newModel->setId(3);
        $newModel->setSku('foobar');
        $newModel->setDescription('Orange');

        $this->assertTrue($manager->flush(3, $newModel));
        $this->assertTrue($manager->has(3));
    }

    public function testFlushAndReplaceModel()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $model = $manager->find($identifier);
        $this->assertEquals($this->model2, $model);

        $newModel = new Product();
        $newModel->setId(2);
        $newModel->setSku('bar');
        $newModel->setDescription('Orange');

        $this->assertTrue($manager->flush(2, $newModel));

        $model = $manager->find($identifier);

        $this->assertNotEquals($this->model2, $model);
    }

    public function testRemove()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $model = $manager->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($manager->remove($identifier, $this->model2));

        $this->assertFalse($manager->has($identifier));
    }

    public function testRemoveWithMissingId()
    {
        $identifier = 1;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $return = $manager->remove($identifier, $this->model1);

        $this->assertTrue($return);
    }

    public function testWithoutSupport()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', new ArrayStorage());
        $manager->register('product2', new ArrayStorage());

        $manager->flush($identifier, $this->model2);

        $model = $manager->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($manager->remove($identifier, $this->model2));

        $this->assertFalse($manager->has($identifier));
    }

    public function testRemoveAll()
    {
        $identifier = 2;

        $manager = new StorageChain();
        $manager->register('product1', $this->storage1);
        $manager->register('product2', $this->storage2);

        $manager->flush($identifier, $this->model2);

        $model = $manager->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($manager->removeAll());

        $this->assertFalse($manager->has($identifier));
    }
}
