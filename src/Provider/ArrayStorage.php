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
use Naucon\Storage\Identity\IdentityFlattener;
use Naucon\Storage\Identity\IdentityFlattenerAwareInterface;
use Naucon\Storage\Identity\IdentityFlattenerAwareTrait;

/**
 * Class ArrayStorage
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class ArrayStorage extends AbstractStorage implements IdentityFlattenerAwareInterface
{
    use IdentityFlattenerAwareTrait;

    /**
     * @var     array       storage
     */
    protected $storage = [];


    /**
     * Constructor
     *
     * @param null $modelClass
     */
    public function __construct($modelClass = null)
    {
        parent::__construct($modelClass);

        $this->identityFlattener = new IdentityFlattener();
    }


    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        if (!isset($this->storage[$identifier])) {
            return null;
        }

        return $this->storage[$identifier];
    }

    /**
     * @inheritdoc
     */
    public function findMultiple(array $identifiers)
    {
        $models = [];
        foreach ($identifiers as $identifier) {
            $model = $this->find($identifier);
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
        $identifier = $this->identityFlattener->flatten($identifier);

        if (array_key_exists($identifier, $this->storage)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $models = array_values($this->storage);

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $this->storage[$identifier] = $model;

        return true;
    }


    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        unset($model);
        $identifier = $this->identityFlattener->flatten($identifier);

        if ($this->has($identifier)) {
            unset($this->storage[$identifier]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $this->storage = [];

        return true;
    }
}
