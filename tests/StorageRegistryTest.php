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

use Naucon\Storage\StorageRegistry;
use Naucon\Storage\Provider\ArrayStorage;
use Naucon\Storage\Tests\Model\Product;
use Naucon\Storage\Tests\Model\Category;
use Naucon\Storage\Tests\Model\User;
use PHPUnit\Framework\TestCase;

class StorageRegistryTest extends TestCase
{
    /**
     * @dataProvider registerProvider
     */
    public function testRegister($expectedStorages)
    {
        $registry = new StorageRegistry();

        foreach ($expectedStorages as $name => $storage) {
            $registry->register($name, $storage);
        }

        $this->assertEquals($expectedStorages, $registry->all());
    }

    /**
     * @dataProvider registerProvider
     */
    public function testHas($expectedStorages)
    {
        $registry = new StorageRegistry();

        foreach ($expectedStorages as $name => $storage) {
            $registry->register($name, $storage);
        }

        $this->assertFalse($registry->has('missingStorage'));

        foreach ($expectedStorages as $name => $storage) {
            $this->assertTrue($registry->has($name));
        }
    }

    /**
     * @dataProvider registerProvider
     */
    public function testGet($expectedStorages)
    {
        $registry = new StorageRegistry();

        foreach ($expectedStorages as $name => $storage) {
            $registry->register($name, $storage);
        }

        $this->assertNull($registry->get('missingStorage'));

        foreach ($expectedStorages as $name => $storage) {
            $this->assertEquals($storage, $registry->get($name));
        }
    }

    /**
     * @dataProvider unregisterProvider
     */
    public function testUnregister($expectedStorages, $expectedUnregister)
    {
        $registry = new StorageRegistry();

        foreach ($expectedStorages as $name => $storage) {
            $registry->register($name, $storage);
        }

        // check that entry is present before removing
        foreach ($expectedUnregister as $name) {
            $this->assertTrue($registry->has($name));
        }

        foreach ($expectedUnregister as $name) {
            $registry->unregister($name);
        }

        foreach ($expectedUnregister as $name) {
            $this->assertFalse($registry->has($name));
        }
    }

    public function registerProvider()
    {
        return [
            [
                [
                    'product' => new ArrayStorage(Product::class),
                    'category' => new ArrayStorage(Category::class),
                    'user' => new ArrayStorage(User::class),
                ]
            ]
        ];
    }

    public function unregisterProvider()
    {
        return [
            [
                [
                    'product' => new ArrayStorage(Product::class),
                    'category' => new ArrayStorage(Category::class),
                    'user' => new ArrayStorage(User::class),
                ],
                [
                    'category',
                ]
            ]
        ];
    }
}
