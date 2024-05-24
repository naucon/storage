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

/**
 * Class AbstractStorage
 *
 * @package Naucon\Storage
 * @author Sven Sanzenbacher
 */
abstract class AbstractStorage implements StorageInterface, CreateAwareInterface, SupportAwareInterface
{
    use CreateAwareTrait;

    /**
     * @var     string          model class name
     */
    protected $modelClass;


    /**
     * Constructor
     *
     * @param   string      $modelClass      model class name
     */
    public function __construct($modelClass = null)
    {
        $this->modelClass = $modelClass;
    }


    /**
     * @inheritdoc
     */
    public function support($model)
    {
        if ($this->modelClass === null) {
            return true;
        }

        return $model instanceof $this->modelClass;
    }
}