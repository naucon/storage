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

use Naucon\Registry\RegistryInterface;
use Naucon\Registry\Registry;

/**
 * Class StorageRegistry
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
class StorageRegistry implements \Countable
{
    /**
     * @var RegistryInterface
     */
    protected $internalRegistry;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->internalRegistry = new Registry();
    }


    /**
     * @param   string          $name       storage name
     * @param   StorageInterface $storage   storage meta data
     * @return  $this
     */
    public function register($name, StorageInterface $storage)
    {
        $this->internalRegistry->register($name, $storage);
        return $this;
    }

    /**
     * @param   string          $name       storage name
     * @return  $this
     */
    public function unregister($name)
    {
        $this->internalRegistry->unregister($name);
        return $this;
    }

    /**
     * @return  array|StorageInterface[]           returns all registered storages
     */
    public function all()
    {
        return $this->internalRegistry->all();
    }

    /**
     * @param  string           $name       storage name
     * @return StorageInterface|null
     */
    public function get($name)
    {
        return $this->internalRegistry->get($name);
    }

    /**
     * @param   string          $name       storage name
     * @return  bool            true if storage is registered
     */
    public function has($name)
    {
        return $this->internalRegistry->has($name);
    }

    /**
     * @return  int             how many storages are registered
     */
    public function count()
    {
        return $this->internalRegistry->count();
    }
}