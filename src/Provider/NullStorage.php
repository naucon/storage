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

/**
 * Class NullStorage
 * should store nothing - especially for tests
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class NullStorage extends AbstractStorage
{
    /**
     * Constructor
     *
     * @param null $modelClass
     */
    public function __construct($modelClass = null)
    {
        parent::__construct($modelClass);
    }


    /**
     * @inheritdoc
     */
    public function has($identifier)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findMultiple(array $identifiers)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return [];
    }


    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        return true;
    }
}
