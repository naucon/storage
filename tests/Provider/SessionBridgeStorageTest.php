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
use Naucon\Storage\Provider\SessionBag;
use Naucon\Storage\Provider\SessionBridgeStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * @preserveGlobalState disabled
 */
class SessionBridgeStorageTest extends TestCase
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
     * @var SessionStorageInterface
     */
    protected $storage;
    /**
     * @var SessionInterface
     */
    protected $session;



    public function setUp(): void
    {
        parent::setUp();

        $this->storage = new MockArraySessionStorage();
        $this->session = new Session($this->storage, new AttributeBag(), new FlashBag());
        $this->session->registerBag(new SessionBag());

        $this->model1 = new Product();
        $this->model1->setId(1);
        $this->model1->setSku('foo');
        $this->model1->setDescription('Apple');

        $this->model2 = new Product();
        $this->model2->setId(2);
        $this->model2->setSku('bar');
        $this->model2->setDescription('Pear');
    }

    public function tearDown(): void
    {
        $this->storage = null;
        $this->session = null;

        parent::tearDown();
    }

    public function testCreate()
    {
        $storage = new SessionBridgeStorage($this->session, Product::class);
        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }


    public function testFindOrCreateWithFoundEntity()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

        $model = $storage->findOrCreate($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindOrCreateWithMissingEntity()
    {
        $identifier = 1;

        $storage = new SessionBridgeStorage($this->session, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFind()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithCompositeIdentifier()
    {
        $criteria = ['product_id' => 2];

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($criteria, $this->model2);

        $model = $storage->find($criteria);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier = 1;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $model = $storage->find($identifier);

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

        $storage = new SessionBridgeStorage($this->session, Product::class);
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

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($criteria1, $this->model1);
        $storage->flush($criteria2, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedResult, $models);
    }

    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $identifiers = [$identifier1, $identifier2];


        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush(3, $this->model1);
        $storage->flush(4, $this->model2);

        $models = $storage->findMultiple($identifiers);

        $this->assertEquals([], $models);
    }

    public function testHas()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);
        $this->assertTrue($storage->has($identifier));

        $identifier = 1;
        $this->assertFalse($storage->has($identifier));
    }

    public function testFindAll()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

        $models = $storage->findAll();

        $this->assertEquals([$this->model2], $models);
    }

    public function testFlush()
    {
        $identifier = 1;

        $storage = new SessionBridgeStorage($this->session, Product::class);

        $this->assertFalse($storage->has($identifier));
        $this->assertTrue($storage->flush($identifier, $this->model1));

        $this->assertTrue($storage->has($identifier));
        $this->assertEquals($this->model1, $storage->find($identifier));
    }

    public function testFlushWithExistingId()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

        $this->assertTrue($storage->flush($identifier, $this->model2));
    }

    public function testFlushNewModel()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

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

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

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

        $storage = new SessionBridgeStorage($this->session, Product::class);
        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);
        $this->assertInstanceOf(Product::class, $model);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveWithMissingId()
    {
        $identifier = 1;

        $storage = new SessionBridgeStorage($this->session, Product::class);

        $result = $storage->remove($identifier, $this->model1);

        $this->assertTrue($result);
    }

    public function testWithoutSupport()
    {
        $identifier = 2;

        $storage = new SessionBridgeStorage($this->session);
        $storage->flush($identifier, $this->model2);

        $model = $storage->find($identifier);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($storage->remove($identifier, $this->model2));

        $this->assertFalse($storage->has($identifier));
    }

    public function testRemoveAll()
    {
        $identifier1 = 1;
        $identifier2 = 2;

        $storage = new SessionBridgeStorage($this->session);
        $storage->flush($identifier2, $this->model2);
        $storage->flush($identifier1, $this->model1);

        $model = $storage->find($identifier2);
        $this->assertEquals($this->model2, $model);

        $this->assertTrue($storage->removeAll());

        $this->assertFalse($storage->has($identifier1));
        $this->assertFalse($storage->has($identifier2));
    }
}
