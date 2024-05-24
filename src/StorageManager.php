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
use Naucon\Storage\Exception\UnsupportedException;

/**
 * Class StorageManager
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
class StorageManager implements StorageInterface, CreateAwareInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;



    /**
     * Constructor
     *
     * @param   StorageInterface $storage
     */
    public function __construct(StorageInterface $storage = null)
    {
        if (!is_null($storage)) {
            $this->setStorage($storage);
        }
    }


    /**
     * define storage
     *
     * @param   StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return  bool        true if storage is set, else false
     */
    public function hasStorage()
    {
        if ($this->storage !== null) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     * @throws UnsupportedException
     */
    public function create()
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        if (!$this->storage instanceof CreateAwareInterface) {
            throw new UnsupportedException(
                sprintf('Storage %s do not support "create".', get_class($this->storage))
            );
        }

        $model = $this->storage->create();

        return $model;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     * @throws UnsupportedException
     */
    public function findOrCreate($identifier)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        if (!$this->storage instanceof CreateAwareInterface) {
            throw new UnsupportedException(
                sprintf('Storage %s do not support "create".', get_class($this->storage))
            );
        }

        $model = $this->storage->findOrCreate($identifier);

        return $model;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function find($identifier)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $model = $this->storage->find($identifier);

        return $model;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function findMultiple(array $identifiers)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $models = $this->storage->findMultiple($identifiers);

        return $models;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function removeAll()
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $this->storage->removeAll();

        return true;
    }

    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function has($identifier)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $result = $this->storage->has($identifier);

        return $result;
    }


    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function findAll()
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $models = $this->storage->findAll();

        return $models;
    }


    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function flush($identifier, $model)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $result = $this->storage->flush($identifier, $model);

        return $result;
    }


    /**
     * @inheritdoc
     * @throws MissingStorageException
     */
    public function remove($identifier, $model)
    {
        if (!$this->hasStorage()) {
            throw new MissingStorageException('Storage has no storage.');
        }

        $result = $this->storage->remove($identifier, $model);

        return $result;
    }
}
