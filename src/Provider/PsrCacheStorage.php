<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Naucon\Storage\Provider;

use Naucon\Storage\AbstractStorage;
use Naucon\Storage\Exception\UnsupportedException;
use Naucon\Storage\Identity\IdentityFlattener;
use Naucon\Storage\Identity\IdentityFlattenerAwareInterface;
use Naucon\Storage\Identity\IdentityFlattenerAwareTrait;
use Naucon\Storage\Identity\IdentityValidator;
use Naucon\Storage\Identity\IdentityValidatorAwareInterface;
use Naucon\Storage\Identity\IdentityValidatorAwareTrait;
use Naucon\Storage\Identity\NamespaceGenerator;
use Naucon\Storage\Identity\NamespaceGeneratorAwareInterface;
use Naucon\Storage\Identity\NamespaceGeneratorAwareTrait;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class PsrCacheStorage (PSR-6 Cache)
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class PsrCacheStorage extends AbstractStorage implements IdentityFlattenerAwareInterface, IdentityValidatorAwareInterface, NamespaceGeneratorAwareInterface
{
    use IdentityFlattenerAwareTrait;
    use IdentityValidatorAwareTrait;
    use NamespaceGeneratorAwareTrait;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var int     life time
     */
    protected $lifetime;

    /**
     * Constructor
     *
     * @param CacheItemPoolInterface $cache
     * @param string|null $modelClass
     * @param string|null $namespace
     * @param int $lifetime
     */
    public function __construct(CacheItemPoolInterface $cache, $modelClass = null, $namespace = null, $lifetime = 0)
    {
        parent::__construct($modelClass);

        $this->cache     = $cache;
        $this->namespace = $namespace;
        $this->lifetime  = $lifetime;

        $this->identityFlattener  = new IdentityFlattener();
        $this->identityValidator  = new IdentityValidator();
        $this->namespaceGenerator = new NamespaceGenerator();
    }

    /**
     * @inheritdoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function find($identifier)
    {
        $cache = $this->cache;
        $key   = $this->buildKey($identifier);

        $cacheItem = $cache->getItem($key);
        if ($cacheItem->isHit())  {
            $model = $cacheItem->get();
            return $model;
        }

        return null;
    }

    /**
     * @inheritdoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function findMultiple(array $identifiers)
    {
        $keys = [];
        foreach ($identifiers as $identifier) {
            $keys[] = $this->buildKey($identifier);
        }

        /**
         * @var \Psr\Cache\CacheItemInterface[] $cacheItems
         */
        $models = [];
        $cacheItems = $this->cache->getItems($keys);
        foreach ($cacheItems as $cacheItem) {
            if ($cacheItem->isHit())  {
                $models[] = $cacheItem->get();
            }
        }
        return $models;
    }

    /**
     * @inheritdoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function has($identifier)
    {
        $cache = $this->cache;
        $key   = $this->buildKey($identifier);

        if ($cache->hasItem($key)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @throws UnsupportedException
     */
    public function findAll()
    {
        throw new UnsupportedException('findAll is not supported in a PSR-6 cache.');
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $cache = $this->cache;

        if ($cache->clear()) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function flush($identifier, $model)
    {
        $cache    = $this->cache;
        $lifetime = $this->lifetime;
        $key      = $this->buildKey($identifier);

        $cacheItem = $cache->getItem($key);
        $cacheItem->set($model);
        if ($lifetime > 0) {
            $cacheItem->expiresAfter($lifetime);
        }

        if ($cache->save($cacheItem)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function remove($identifier, $model)
    {
        unset($model);

        $cache = $this->cache;
        $key   = $this->buildKey($identifier);

        if ($cache->deleteItem($key)) {
            return true;
        }

        return false;
    }

    /**
     * @param int|string|array  $identifier
     * @return  string          key
     */
    protected function buildKey($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);
        $this->identityValidator->validate($identifier);
        $namespace  = $this->namespaceGenerator->generate($this->modelClass, $this->namespace);

        $key = $namespace . ':' . $identifier;

        return $key;
    }
}
