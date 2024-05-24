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

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Provider\DoctrineStorage;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class DoctrineStorageTest extends TestCase
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
     * @var ObjectManager|PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;



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

        $this->objectManagerMock = $this->createMock(ObjectManager::class);
    }

    public function testCreate()
    {
        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $model = $storage->create();

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFindOrCreateWithFoundEntity()
    {
        $identifier = 2;
        $expectedModelClass = Product::class;
        $expectedModel = $this->model2;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertEquals($this->model2, $model);
    }

    public function testFindOrCreateWithMissingEntity()
    {
        $identifier = 1;
        $expectedModelClass = Product::class;
        $expectedModel = null;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);

        $model = $storage->findOrCreate($identifier);

        $this->assertInstanceOf(Product::class, $model);
    }

    public function testFind()
    {
        $identifier = 2;
        $expectedModelClass = Product::class;
        $expectedModel = $this->model2;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $model = $storage->find($identifier);

        $this->assertEquals($expectedModel, $model);
    }

    public function testFindWithCompositeIdentifier()
    {
        $criteria = ['product_id' => 2];
        $expectedModelClass = Product::class;
        $expectedModel = $this->model2;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $model = $storage->find($criteria);

        $this->assertEquals($expectedModel, $model);
    }

    public function testFindWithMissingEntityShouldReturnNull()
    {
        $identifier = 1;
        $expectedModelClass = Product::class;
        $expectedModel = false;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $model = $storage->find($identifier);

        $this->assertNull($model);
    }

    public function testFindMultiple()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $expectedModelClass = Product::class;
        $identifiers = [$identifier1, $identifier2];
        $expectedCriteria = ['id' => $identifiers];

        $expectedModels = [
            $this->model1,
            $this->model2
        ];

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with($expectedCriteria)
            ->willReturn($expectedModels);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedModels, $models);
    }

    public function testFindMultipleWithCompositeIdentifier()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $criteria1 = ['product_id' => $identifier1];
        $criteria2 = ['product_id' => $identifier2];
        $expectedModelClass = Product::class;

        $identifiers = [
            $criteria1,
            $criteria2
        ];

        $expectedCriteria = ['product_id' => [$identifier1, $identifier2]];

        $expectedModels = [
            $this->model1,
            $this->model2
        ];

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with($expectedCriteria)
            ->willReturn($expectedModels);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $models = $storage->findMultiple($identifiers);

        $this->assertEquals($expectedModels, $models);
    }

    public function testFindMultipleWithMissingEntityShouldReturnEmpty()
    {
        $identifier1 = 1;
        $identifier2 = 2;
        $expectedModelClass = Product::class;
        $identifiers = [$identifier1, $identifier2];
        $expectedCriteria = ['id' => $identifiers];

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with($expectedCriteria)
            ->willReturn([]);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $models = $storage->findMultiple($identifiers);

        $this->assertEquals([], $models);
    }

    public function testHas()
    {
        $identifier = 2;
        $expectedModelClass = Product::class;
        $expectedModel = $this->model2;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);

        $this->assertTrue($storage->has($identifier));
    }

    public function testHasNot()
    {
        $identifier = 1;
        $expectedModelClass = Product::class;
        $expectedModel = false;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);

        $this->assertFalse($storage->has($identifier));
    }

    public function testFindAll()
    {
        $expectedModelClass = Product::class;
        $expectedModels = [$this->model2];

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedModels);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $models = $storage->findAll();

        $this->assertEquals([$this->model2], $models);
    }

    public function testFlush()
    {
        $identifier = 1;
        $expectedModel = $this->model1;
        $expectedModelClass = Product::class;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn(null);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $this->objectManagerMock->expects($this->once())
            ->method('persist')
            ->with($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('flush');

        $storage = new DoctrineStorage($this->objectManagerMock, $expectedModelClass);
        $this->assertTrue($storage->flush($identifier, $this->model1));
    }

    public function testFlushWithExistingId()
    {
        $identifier = 2;
        $expectedModel = $this->model2;
        $expectedModelClass = Product::class;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $this->objectManagerMock->expects($this->never())
            ->method('persist');

        $this->objectManagerMock->expects($this->once())
            ->method('flush');

        $storage = new DoctrineStorage($this->objectManagerMock, $expectedModelClass);
        $this->assertTrue($storage->flush($identifier, $this->model2));
    }

    public function testRemove()
    {
        $identifier = 2;
        $expectedModelClass = Product::class;
        $expectedModel = $this->model2;

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
            ->method('find')
            ->with($identifier)
            ->willReturn($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($expectedModelClass)
            ->willReturn($objectRepositoryMock);

        $this->objectManagerMock->expects($this->once())
            ->method('remove')
            ->with($expectedModel);

        $this->objectManagerMock->expects($this->once())
            ->method('flush');

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $this->assertTrue($storage->remove($identifier, $this->model2));
    }

    public function testRemoveAll()
    {
        $expectedModelClass = Product::class;
        $expectedModels = [$this->model2, $this->model1];

        $objectRepositoryMock = $this->createMock(ObjectRepository::class);
        $objectRepositoryMock->expects($this->once())
                             ->method('findAll')
                             ->willReturn($expectedModels);

        $this->objectManagerMock->expects($this->once())
                                ->method('getRepository')
                                ->with($expectedModelClass)
                                ->willReturn($objectRepositoryMock);

        $this->objectManagerMock->expects($this->exactly(2))
                                ->method('remove');

        $this->objectManagerMock->expects($this->once())
                                ->method('flush');

        $storage = new DoctrineStorage($this->objectManagerMock, Product::class);
        $this->assertTrue($storage->removeAll());
    }
}
