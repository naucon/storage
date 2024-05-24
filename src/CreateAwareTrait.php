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

use Naucon\Storage\Exception\StorageException;

/**
 * Trait CreateAwareTrait
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
trait CreateAwareTrait
{
    /**
     * create a model (entity)
     * but not a entry in storage.
     *
     * @return object           model
     */
    public function create()
    {
        $modelClass = $this->modelClass;

        return new $modelClass();
    }

    /**
     * find one entry in storage or create model (entity)
     *
     * @param   int|string  $identifier     identifier of entry
     * @return  object             model
     * @throws  StorageException
     */
    public function findOrCreate($identifier)
    {
        $model = $this->find($identifier);

        if ($model === null) {
            $model = $this->create();
        }

        return $model;
    }
}
