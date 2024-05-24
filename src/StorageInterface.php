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
 * Interface StorageInterface
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
interface StorageInterface
{
    /**
     * find one entry in storage
     *
     * @param   int|string|array  $identifier     identifier of entry
     * @return  object|null             model
     * @throws  StorageException
     */
    public function find($identifier);

    /**
     * find one entry in storage
     *
     * @param   int[]|string[]|array[]  $identifiers     identifiers of entries
     * @return  array|object[]             models
     * @throws  StorageException
     */
    public function findMultiple(array $identifiers);

    /**
     * has entry in storage
     *
     * @param   int|string|array  $identifier     identifier of entry
     * @return  bool        returns true if storage has a registered entry, else false
     * @throws  StorageException
     */
    public function has($identifier);

    /**
     * find all entries in storage
     *
     * @return  object[]       models
     * @throws  StorageException
     */
    public function findAll();

    /**
     * create or update entry in storage
     *
     * @param   int|string|array  $identifier     identifier of entry
     * @param   object      $model          model to update
     * @return  bool
     * @throws  StorageException
     */
    public function flush($identifier, $model);

    /**
     * remove entry in storage
     *
     * @param   int|string|array  $identifier     identifier of entry
     * @param   object      $model          model to update
     * @return  bool
     * @throws  StorageException
     */
    public function remove($identifier, $model);

    /**
     * remove all entries in storage
     *
     * @return  bool
     * @throws  StorageException
     */
    public function removeAll();
}
