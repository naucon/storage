<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Naucon\Storage;

use Naucon\Storage\Exception\MissingStorageException;

/**
 * Class StorageFactory
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
class StorageFactory
{
    /**
     * @var StorageRegistry
     */
    protected $storageRegistry;



    /**
     * Constructor
     *
     * @param   StorageRegistry     $registry
     */
    public function __construct(StorageRegistry $registry = null)
    {
        if (!is_null($registry)) {
            $this->storageRegistry = $registry;
        } else {
            $this->storageRegistry = new StorageRegistry();
        }
    }



    /**
     * register storage
     *
     * @param   string              $name
     * @param   StorageInterface $storage
     */
    public function register($name, StorageInterface $storage)
    {
        $this->storageRegistry->register($name, $storage);
    }

    /**
     * return storage
     *
     * @param   string      $name       name of storage
     * @return  StorageInterface
     * @throws  MissingStorageException
     */
    public function getStorage($name)
    {
        $storage = $this->storageRegistry->get($name);

        if ($storage === null) {
            throw new MissingStorageException(
                sprintf('StorageManager have no registered storage for "%s" ', $name)
            );
        }

        return $storage;
    }
}