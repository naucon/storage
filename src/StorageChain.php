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

use Naucon\Storage\Merge\MergeStrategyInterface;

/**
 * Class StorageChain
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
class StorageChain implements StorageInterface
{
    /**
     * @var StorageRegistry
     */
    protected $storageRegistry;

    /**
     * @var StorageLocator
     */
    protected $storageLocator;

    /**
     * @var MergeStrategyInterface
     */
    protected $mergeStrategy;


    /**
     * Constructor
     *
     * @param   StorageRegistry $registry
     */
    public function __construct(StorageRegistry $registry = null)
    {
        if (!is_null($registry)) {
            $this->storageRegistry = $registry;
        } else {
            $this->storageRegistry = new StorageRegistry();
        }

        $this->storageLocator = new StorageLocator($this->storageRegistry);
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
     * define merge strategy
     *
     * @param MergeStrategyInterface $mergeStrategy
     */
    public function setMergeStrategy(MergeStrategyInterface $mergeStrategy)
    {
        $this->mergeStrategy = $mergeStrategy;
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $model    = null;
        $storages = $this->storageRegistry->all();
        foreach ($storages as $storage) {
            if ($storage->has($identifier)) {
                $modelNext = $storage->find($identifier);
                if ($this->mergeStrategy === null) {
                    $model = $modelNext;

                    break;
                } else {
                    $model = $this->mergeStrategy->merge($modelNext, $model);
                }
            }
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function findMultiple(array $identifiers)
    {
        $models = [];
        $storages = $this->storageRegistry->all();
        foreach ($identifiers as $identifier) {
            $model = null;
            foreach ($storages as $storage) {
                if (!$storage->has($identifier)) {
                    continue;
                }

                $modelNext = $storage->find($identifier);
                if ($this->mergeStrategy === null) {
                    $model = $modelNext;

                    break;
                }

                $model = $this->mergeStrategy->merge($modelNext, $model);
            }

            if ($model !== null) {
                $models[] = $model;
            }
        }

        return $models;
    }


    /**
     * @inheritdoc
     */
    public function has($identifier)
    {
        $hasModel = false;
        $storages = $this->storageRegistry->all();
        foreach ($storages as $storage) {
            if ($storage->has($identifier)) {
                $hasModel = true;
                break;
            }
        }

        return $hasModel;
    }


    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $allModels = [];
        $storages  = $this->storageRegistry->all();
        foreach ($storages as $storage) {
            if ($models = $storage->findAll()) {
                $allModels = array_merge($allModels, $models);
                break;
            }
        }

        return $allModels;
    }


    /**
     * @inheritdoc
     * @throws Exception\MissingStorageException
     */
    public function flush($identifier, $model)
    {
        $return   = false;
        $storages = $this->storageLocator->locate($model);
        foreach ($storages as $storage) {
            if ($storage->flush($identifier, $model)) {
                $return = true;
            }
        }

        return $return;
    }


    /**
     * @inheritdoc
     * @throws Exception\MissingStorageException
     */
    public function remove($identifier, $model)
    {
        $return   = false;
        $storages = $this->storageLocator->locate($model);
        foreach ($storages as $storage) {
            if ($storage->remove($identifier, $model)) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $storages = $this->storageRegistry->all();
        $result = true;

        foreach ($storages as $storage) {
            $result = $storage->removeAll() && $result;
        }

        return $result;
    }
}
