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
use Naucon\Storage\Exception\StorageException;
use Naucon\Storage\Identity\IdentityFlattener;
use Naucon\Storage\Identity\IdentityFlattenerAwareInterface;
use Naucon\Storage\Identity\IdentityFlattenerAwareTrait;
use Naucon\Storage\Identity\IdentityValidator;
use Naucon\Storage\Identity\IdentityValidatorAwareInterface;
use Naucon\Storage\Identity\IdentityValidatorAwareTrait;
use Naucon\Storage\Identity\NamespaceGenerator;
use Naucon\Storage\Identity\NamespaceGeneratorAwareInterface;
use Naucon\Storage\Identity\NamespaceGeneratorAwareTrait;
use Predis\ClientInterface;
use Predis\Collection\Iterator;
use Predis\PredisException;

/**
 * Class RedisStorage
 *
 * requires Redis 2.8 or higher (because of scan command)
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class RedisStorage extends AbstractStorage implements IdentityFlattenerAwareInterface, IdentityValidatorAwareInterface, NamespaceGeneratorAwareInterface
{
    use IdentityFlattenerAwareTrait;
    use IdentityValidatorAwareTrait;
    use NamespaceGeneratorAwareTrait;

    /**
     * @var ClientInterface
     */
    protected $client;

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
     * @param ClientInterface $client
     * @param string|null $modelClass
     * @param string|null $namespace
     * @param int $lifetime
     */
    public function __construct(ClientInterface $client, $modelClass = null, $namespace = null, $lifetime = 0)
    {
        parent::__construct($modelClass);

        $this->client    = $client;
        $this->namespace = $namespace;
        $this->lifetime  = $lifetime;

        $this->identityFlattener  = new IdentityFlattener();
        $this->identityValidator  = new IdentityValidator();
        $this->namespaceGenerator = new NamespaceGenerator();
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        try {
            $this->client->flushdb();

            return true;
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $key   = $this->buildKey($identifier);
        $value = $this->fetch($key);

        if ($value === null) {
            return null;
        }

        $model = unserialize($value);

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function findMultiple(array $identifiers)
    {
        $keys = [];
        foreach ($identifiers as $identifier) {
            $keys[] = $this->buildKey($identifier);
        }

        $values = $this->fetchMultiple($keys);
        $models = $this->decodeModels($values);
        return $models;
    }

    /**
     * @inheritdoc
     */
    public function has($identifier)
    {
        $client = $this->client;
        $key    = $this->buildKey($identifier);

        try {
            if ($client->exists($key)) {
                return true;
            }
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $namespace = $this->buildNamespace();
        $pattern   = $namespace . '*';

        $values = $this->fetchAll($pattern);
        $models = $this->decodeModels($values);

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        $client   = $this->client;
        $lifetime = $this->lifetime;
        $key      = $this->buildKey($identifier);


        $value = serialize($model);

        try {
            if ($lifetime > 0) {
                $client->setex($key, $lifetime, $value);
            } else {
                $client->set($key, $value);
            }
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        unset($model);

        $client = $this->client;
        $key    = $this->buildKey($identifier);

        try {
            $client->del([$key]);
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return true;
    }

    /**
     * @param string $key redis key
     * @return  string|null
     * @throws StorageException
     */
    protected function fetch($key)
    {
        $client = $this->client;

        try {
            $value = $client->get($key);
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return $value;
    }

    /**
     * @param string[] $keys redis keys
     * @return  array
     * @throws StorageException
     */
    protected function fetchMultiple(array $keys)
    {
        $client = $this->client;

        try {
            $values = $client->mget($keys);
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return $values;
    }

    /**
     * @param string $pattern matching pattern
     * @return array    values
     * @throws StorageException
     */
    protected function fetchAll($pattern)
    {
        $client = $this->client;
        $values = [];

        try {
            $keys = [];
            /*
             * in unit test a lot of internal predis code have to be mocked.
             * if feature versions of predis will break the code we have to add an setter
             * to overwrite Iterator in test with a mock.
             */
            foreach (new Iterator\Keyspace($client, $pattern) as $key) {
                $keys[] = $key;
            }

            if (count($keys) > 0) {
                $values = $client->mget($keys);
            }
        } catch (PredisException $exception) {
            throw new StorageException('Redis not accessible', 0, $exception);
        }

        return $values;
    }

    /**
     * build redis key with namespace
     *
     * @param   string    $identifier
     * @return  string    redis key
     */
    protected function buildKey($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);
        $this->identityValidator->validate($identifier);
        $namespace  = $this->buildNamespace();

        $key = $namespace . ':' . $identifier;

        return $key;
    }

    /**
     * build namespace
     *
     * @return  string    namespace
     */
    protected function buildNamespace()
    {
        $namespace  = $this->namespaceGenerator->generate($this->modelClass, $this->namespace);

        return $namespace;
    }

    /**
     * @param   object      $model
     * @return string
     */
    protected function encodeModel($model)
    {
        return serialize($model);
    }

    /**
     * @param   string    $encodedModel
     * @return  object    model
     */
    protected function decodeModel($encodedModel)
    {
        return unserialize($encodedModel);
    }

    /**
     * @param   string[]    $encodedModels
     * @return  object[]    models
     */
    protected function decodeModels(array $encodedModels)
    {
        $models = [];
        foreach ($encodedModels as $encodedModel) {
            if ($encodedModel === null) {
                continue;
            }

            $models[] = $this->decodeModel($encodedModel);
        }

        return $models;
    }
}
