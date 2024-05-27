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

use Naucon\Storage\Exception\StorageException;
use Naucon\Storage\Provider\RedisStorage;
use Naucon\Storage\Tests\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\ClientException;
use Predis\Profile\ProfileInterface;

/**
 * Class RedisStorageTest
 *
 * @package Naucon\Storage\Tests\Provider
 * @author Sven Sanzenbacher
 */
class RedisStorageTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @var Product
     */
    protected $model1;

    /**
     * @var Product
     */
    protected $model2;


    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->getMockBuilder(Client::class)
            ->addMethods(['get', 'mget', 'set', 'setex', 'del', 'exists', 'keys', 'scan', 'flushdb'])
            ->onlyMethods(['getProfile'])
            ->getMock();

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
        $client = $this->client;

        $storage = new RedisStorage($client, Product::class);
        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }

    /**
     * @throws StorageException
     */
    public function testFindOrCreateWithFoundEntity()
    {
        $identifier = 2;
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertEquals($this->model2, $model);
    }

    /**
     * @throws StorageException
     */
    public function testFindOrCreateWithMissingEntity()
    {
        $identifier = 1;
        $expectedModelClass = Product::class;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(null);

        $storage = new RedisStorage($client, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf($expectedModelClass, $model);
    }

    /**
     * @throws StorageException
     */
    public function testFindOrCreateOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier = 2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->findOrCreate($identifier);
    }

    /**
     * @throws StorageException
     */
    public function testFind()
    {
        $identifier = 2;
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class);

        $model = $storage->find($identifier);

        $this->assertEquals($this->model2, $model);
    }

    /**
     * @throws StorageException
     */
    public function testFindWithCompositeIdentifier()
    {
        $criteria = ['product_id' => 2];
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class);

        $model = $storage->find($criteria);

        $this->assertEquals($this->model2, $model);
    }

    /**
     * @throws StorageException
     */
    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier = 1;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willReturn(null);

        $storage = new RedisStorage($client, Product::class);

        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    /**
     * @throws StorageException
     */
    public function testFindOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier = 2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('get')
            ->with($expectedKey)
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->find($identifier);
    }

    /**
     * @throws StorageException
     */
    public function testFindMultiple()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];
        $expectedKeys = [
            '52e848a7eeaa022db017f9b25087fc45:1',
            '52e848a7eeaa022db017f9b25087fc45:2'
        ];

        $expectedSerializedResults = [
            serialize($this->model1),
            serialize($this->model2)
        ];

        $this->client->expects($this->once())
            ->method('mget')
            ->with($expectedKeys)
            ->willReturn($expectedSerializedResults);

        $expectedResult = [];
        foreach ($expectedSerializedResults as $expectedSerializedResult) {
            $expectedResult[]= unserialize($expectedSerializedResult);
        }

        $storage = new RedisStorage($this->client, Product::class);
        $storage->flush($identifier1, $this->model1);
        $storage->flush($identifier2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    /**
     * @throws StorageException
     */
    public function testFindMultipleWithCompositeIdentifier()
    {
        $criteria1 = ['product_id' => 1];
        $criteria2 = ['product_id' => 2];
        $expectedKeys = [
            '52e848a7eeaa022db017f9b25087fc45:1',
            '52e848a7eeaa022db017f9b25087fc45:2'
        ];

        $identifiers = [
            $criteria1,
            $criteria2
        ];

        $expectedResult = [
            $this->model1,
            $this->model2
        ];

        $this->client->expects($this->once())
            ->method('mget')
            ->with($expectedKeys)
            ->willReturn(
                [
                    serialize($this->model1),
                    serialize($this->model2)
                ]
            );

        $storage = new RedisStorage($this->client, Product::class);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    /**
     * @throws StorageException
     */
    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];
        $expectedKeys = [
            '52e848a7eeaa022db017f9b25087fc45:1',
            '52e848a7eeaa022db017f9b25087fc45:2'
        ];

        $this->client->expects($this->once())
            ->method('mget')
            ->with($expectedKeys)
            ->willReturn([]);

        $storage = new RedisStorage($this->client, Product::class);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals([], $models);
    }

    /**
     * @throws StorageException
     */
    public function testFindMultipleOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];
        $expectedKeys = [
            '52e848a7eeaa022db017f9b25087fc45:1',
            '52e848a7eeaa022db017f9b25087fc45:2'
        ];

        $this->client->expects($this->once())
            ->method('mget')
            ->with($expectedKeys)
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($this->client, Product::class);

        $storage->findMultiple($identifiers);
    }

    /**
     * @throws StorageException
     */
    public function testHas()
    {
        $goodIdentifier = 2;
        $badIdentifier = 1;
        $client = $this->client;

        $returnMap = [
            ['52e848a7eeaa022db017f9b25087fc45:2', true],
            ['52e848a7eeaa022db017f9b25087fc45:1', false]
        ];

        $client->expects($this->any())
            ->method('exists')
            ->willReturnMap($returnMap);

        $storage = new RedisStorage($client, Product::class);

        $this->assertTrue($storage->has($goodIdentifier));
        $this->assertFalse($storage->has($badIdentifier));
    }


    /**
     * @throws StorageException
     */
    public function testHasOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier = 2;
        $client = $this->client;

        $client->expects($this->any())
            ->method('exists')
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->has($identifier);
    }

    /**
     * @throws StorageException
     */
    public function testFindAll()
    {
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $expectedPattern = '52e848a7eeaa022db017f9b25087fc45*';
        $client = $this->client;

        $profileInterface = $this->createMock(ProfileInterface::class);

        $profileInterface->expects($this->any())
            ->method('supportsCommand')
            ->with()
            ->willReturn(true);

        $client->expects($this->any())
            ->method('keys')
            ->with($expectedPattern)
            ->willReturn([$expectedKey]);

        $client->expects($this->any())
            ->method('scan')
            ->willReturn([0, [$expectedKey]]);

        $client->expects($this->any())
            ->method('getProfile')
            ->with()
            ->willReturn($profileInterface);

        $client->expects($this->once())
            ->method('mget')
            ->with([$expectedKey])
            ->willReturn([serialize($expectedModel)]);

        $storage = new RedisStorage($client, Product::class);

        $models = $storage->findAll();

        $this->assertEquals([$expectedModel], $models);
    }

    /**
     * @throws StorageException
     */
    public function testFindAllOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $expectedPattern = '52e848a7eeaa022db017f9b25087fc45*';
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $profileInterface = $this->getMockBuilder(ProfileInterface::class)->getMock();

        $profileInterface->expects($this->any())
            ->method('supportsCommand')
            ->with()
            ->willReturn(true);

        $client->expects($this->any())
            ->method('keys')
            ->with($expectedPattern)
            ->willReturn([$expectedKey]);

        $client->expects($this->any())
            ->method('scan')
            ->willReturn([0, [$expectedKey]]);

        $client->expects($this->any())
            ->method('getProfile')
            ->with()
            ->willReturn($profileInterface);

        $client->expects($this->once())
            ->method('mget')
            ->with([$expectedKey])
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->findAll();
    }

    /**
     * @throws StorageException
     */
    public function testFlush()
    {
        $identifier = 1;
        $expectedModel = $this->model1;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('set')
            ->with($expectedKey, serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    /**
     * @throws StorageException
     */
    public function testFlushWithNamespace()
    {
        $identifier = 1;
        $expectedModel = $this->model1;
        $expectedNamespace = 'product';
        $expectedKey = 'product_52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('set')
            ->with($expectedKey, serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class, $expectedNamespace);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    /**
     * @throws StorageException
     */
    public function testFlushWithLifeTime()
    {
        $identifier = 1;
        $lifetime = 60;
        $expectedModel = $this->model1;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('setex')
            ->with($expectedKey, $lifetime, serialize($expectedModel));

        $storage = new RedisStorage($client, Product::class, null, $lifetime);

        $this->assertTrue($storage->flush($identifier, $expectedModel));
    }

    /**
     * @throws StorageException
     */
    public function testFlushOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier = 1;
        $expectedModel = $this->model1;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('set')
            ->with($expectedKey, serialize($expectedModel))
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->flush($identifier, $expectedModel);
    }

    /**
     * @throws StorageException
     */
    public function testRemove()
    {
        $identifier = 2;
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('del')
            ->with([$expectedKey])
            ->willReturn(1);

        $storage = new RedisStorage($client, Product::class);

        $this->assertTrue($storage->remove($identifier, $expectedModel));
    }

    /**
     * @throws StorageException
     */
    public function testRemoveWithMissingIdShouldReturnTrue()
    {
        $identifier = 1;
        $expectedModel = $this->model1;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:1';
        $client = $this->client;

        $client->expects($this->once())
            ->method('del')
            ->with([$expectedKey])
            ->willReturn(0);

        $storage = new RedisStorage($client, Product::class);

        $result = $storage->remove($identifier, $expectedModel);

        $this->assertTrue($result);
    }

    /**
     * @throws StorageException
     */
    public function testRemoveOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $identifier = 2;
        $expectedModel = $this->model2;
        $expectedKey = '52e848a7eeaa022db017f9b25087fc45:2';
        $client = $this->client;

        $client->expects($this->once())
            ->method('del')
            ->with([$expectedKey])
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->remove($identifier, $expectedModel);
    }

    /**
     * @throws StorageException
     */
    public function testRemoveAll()
    {
        $client = $this->client;

        $client->expects($this->once())
               ->method('flushdb')
            ->with();

        $storage = new RedisStorage($client, Product::class);

        $result = $storage->removeAll();

        $this->assertTrue($result);
    }

    /**
     * @throws StorageException
     */
    public function testRemoveAllOnInaccessibleRedis()
    {
        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis not accessible');
        $client = $this->client;

        $client->expects($this->once())
            ->method('flushdb')
            ->willThrowException(new ClientException());

        $storage = new RedisStorage($client, Product::class);

        $storage->removeAll();
    }
}
