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

use Naucon\Storage\Exception\InvalidArgumentException;
use Naucon\Storage\Exception\MissingStorageException;

/**
 * Class StorageLocator
 *
 * to locate registered storage for a specified model
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
class StorageLocator
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
    public function __construct(StorageRegistry $registry)
    {
        $this->storageRegistry = $registry;
    }

    /**
     * @param   object      $model          model to locate storage
     * @return  StorageInterface[]
     * @throws  MissingStorageException
     * @throws  InvalidArgumentException
     */
    public function locate($model)
    {
        $storages = $this->storageRegistry->all();

        $locatedStorages = [];
        foreach ($storages as $storage) {
            if (!$storage instanceof StorageInterface) {
                throw new InvalidArgumentException(
                    sprintf('StorageLocate requires a storage of type "%s". Storage of type "%s" was given.',
                        StorageInterface::class,
                        get_class($storage)
                    )
                );
            }

            if ($storage instanceof SupportAwareInterface) {
                if (false == $storage->support($model)) {
                    continue;
                }
            }

            $locatedStorages[] = $storage;
        }

        if (count($locatedStorages) == 0) {
            throw new MissingStorageException(
                sprintf('StorageLocate could not locate any storage for model of type %s.',
                    get_class($model)
                )
            );
        }

        return $locatedStorages;
    }
}
