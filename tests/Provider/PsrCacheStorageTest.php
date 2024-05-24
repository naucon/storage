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

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Naucon\Storage\Provider\PsrCacheStorage;
use Naucon\Storage\Tests\Model\Product;

/**
 * Class PsrCacheStorageTest
 *
 * @package Naucon\Storage\Tests\Provider
 * @author Sven Sanzenbacher
 */
class PsrCacheStorageTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var CacheItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheItem;

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

        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(CacheItemInterface::class);

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
        $cache = $this->cache;

        $storage = new PsrCacheStorage($cache, Product::class);
        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFindOrCreateWithFoundEntity()
    {
        $identifier         = 2;
        $expectedModel      = $this->model2;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:2';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(true);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn($expectedModel);

        $storage = new PsrCacheStorage($cache, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindOrCreateWithMissingEntity()
    {
        $identifier         = 1;
        $expectedModelClass = Product::class;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(false);

        $storage = new PsrCacheStorage($cache, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf($expectedModelClass, $model);
    }

    public function testFind()
    {
        $identifier         = 2;
        $expectedModel      = $this->model2;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:2';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(true);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn($expectedModel);

        $storage = new PsrCacheStorage($cache, Product::class);

        $model = $storage->find($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithCompositeIdentifier()
    {
        $criteria           = ['product_id' => 2];
        $expectedModel      = $this->model2;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:2';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(true);

        $cacheItem->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn($expectedModel);

        $storage = new PsrCacheStorage($cache, Product::class);

        $model = $storage->find($criteria);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier         = 1;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('isHit')
            ->with()
            ->willReturn(false);

        $storage = new PsrCacheStorage($cache, Product::class);

        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    public function testFindMultiple()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];
        $expectedKey1 = '52e848a7eeaa022db017f9b25087fc45:1';
        $expectedKey2 = '52e848a7eeaa022db017f9b25087fc45:2';
        $expectedKeys = [$expectedKey1, $expectedKey2];

        $expectedResult = [
            $this->model1,
            $this->model2
        ];

        $this->cache->expects($this->once())
            ->method('getItems')
            ->with($expectedKeys)
            ->willReturn([$this->cacheItem, $this->cacheItem])
        ;

        $this->cacheItem->expects($this->exactly(2))
            ->method('isHit')
            ->with()
            ->willReturn(true)
        ;

        $this->cacheItem->expects($this->exactly(2))
            ->method('get')
            ->with()
            ->willReturnOnConsecutiveCalls(
                $this->model1,
                $this->model2
            )
        ;

        $storage = new PsrCacheStorage($this->cache, Product::class);
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

        $expectedKey1 = '52e848a7eeaa022db017f9b25087fc45:1';
        $expectedKey2 = '52e848a7eeaa022db017f9b25087fc45:2';
        $expectedKeys = [$expectedKey1, $expectedKey2];

        $expectedResult = [
            $this->model1,
            $this->model2
        ];

        $this->cache->expects($this->once())
            ->method('getItems')
            ->with($expectedKeys)
            ->willReturn([$this->cacheItem, $this->cacheItem])
        ;

        $this->cacheItem->expects($this->exactly(2))
            ->method('isHit')
            ->with()
            ->willReturn(true)
        ;

        $this->cacheItem->expects($this->exactly(2))
            ->method('get')
            ->with()
            ->willReturnOnConsecutiveCalls(
                $this->model1,
                $this->model2
            )
        ;

        $storage = new PsrCacheStorage($this->cache, Product::class);
        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];
        $expectedKey1 = '52e848a7eeaa022db017f9b25087fc45:1';
        $expectedKey2 = '52e848a7eeaa022db017f9b25087fc45:2';
        $expectedKeys = [$expectedKey1, $expectedKey2];

        $this->cache->expects($this->once())
            ->method('getItems')
            ->with($expectedKeys)
            ->willReturn([$this->cacheItem, $this->cacheItem])
        ;

        $this->cacheItem->expects($this->exactly(2))
            ->method('isHit')
            ->with()
            ->willReturn(false)
        ;

        $this->cacheItem->expects($this->never())
            ->method('get')
        ;

        $storage = new PsrCacheStorage($this->cache, Product::class);
        $models = $storage->findMultiple($identifiers);

        $this->assertEquals([], $models);
    }

    public function testHas()
    {
        $goodIdentifier      = 2;
        $badIdentifier       = 1;
        $cache              = $this->cache;

        $returnMap = [
            ['52e848a7eeaa022db017f9b25087fc45:2', true],
            ['52e848a7eeaa022db017f9b25087fc45:1', false]
        ];

        $cache->expects($this->any())
            ->method('hasItem')
            ->willReturnMap($returnMap);

        $storage = new PsrCacheStorage($cache, Product::class);

        $this->assertTrue($storage->has($goodIdentifier));
        $this->assertFalse($storage->has($badIdentifier));
    }

    /**
     * @expectedException  \Naucon\Storage\Exception\UnsupportedException
     */
    public function testFindAll()
    {
        $cache  = $this->cache;

        $storage = new PsrCacheStorage($cache, Product::class);
        $storage->findAll();
    }

    public function testFlush()
    {
        $identifier         = 1;
        $expectedModel      = $this->model1;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('save')
            ->with($cacheItem)
            ->willReturn(true);

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('set')
            ->with($expectedModel);

        $storage = new PsrCacheStorage($cache, Product::class);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    public function testFlushWithNamespace()
    {
        $identifier         = 1;
        $expectedModel      = $this->model1;
        $expectedNamespace  = 'product';
        $expectedKey        = 'product_52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('save')
            ->with($cacheItem)
            ->willReturn(true);

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('set')
            ->with($expectedModel);

        $storage = new PsrCacheStorage($cache, Product::class, $expectedNamespace);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    public function testFlushWithLifeTime()
    {
        $identifier         = 1;
        $lifetime           = 60;
        $expectedModel      = $this->model1;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;
        $cacheItem          = $this->cacheItem;

        $cache->expects($this->once())
            ->method('save')
            ->with($cacheItem)
            ->willReturn(true);

        $cache->expects($this->once())
            ->method('getItem')
            ->with($expectedKey)
            ->willReturn($cacheItem);

        $cacheItem->expects($this->once())
            ->method('set')
            ->with($expectedModel);

        $cacheItem->expects($this->once())
            ->method('expiresAfter')
            ->with($lifetime);

        $storage = new PsrCacheStorage($cache, Product::class, null, $lifetime);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    public function testRemove()
    {
        $identifier         = 2;
        $expectedModel      = $this->model2;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:2';
        $cache              = $this->cache;

        $cache->expects($this->once())
            ->method('deleteItem')
            ->with($expectedKey)
            ->willReturn(true);

        $storage = new PsrCacheStorage($cache, Product::class);

        $this->assertTrue($storage->remove($identifier, $expectedModel));
    }

    public function testRemoveWithMissingIdShouldReturnFalse()
    {
        $identifier = 1;
        $expectedModel      = $this->model1;
        $expectedKey        = '52e848a7eeaa022db017f9b25087fc45:1';
        $cache              = $this->cache;

        $cache->expects($this->once())
            ->method('deleteItem')
            ->with($expectedKey)
            ->willReturn(false);

        $storage = new PsrCacheStorage($cache, Product::class);

        $result = $storage->remove($identifier, $expectedModel);

        $this->assertFalse($result);
    }

    public function testRemoveAll()
    {
        $cache = $this->cache;

        $cache->expects($this->once())
            ->method('clear')
            ->with()
            ->willReturn(true);

        $storage = new PsrCacheStorage($cache, Product::class);

        $this->assertTrue($storage->removeAll());
    }

    public function testRemoveAllFailedShouldReturnFalse()
    {
        $cache = $this->cache;

        $cache->expects($this->once())
            ->method('clear')
            ->with()
            ->willReturn(false);

        $storage = new PsrCacheStorage($cache, Product::class);

        $result = $storage->removeAll();

        $this->assertFalse($result);
    }
}
