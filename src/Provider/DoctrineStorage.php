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
use Doctrine\Persistence\ObjectManager;

/**
 * Class DoctrineStorage
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class DoctrineStorage extends AbstractStorage
{
    /**
     * @var \Doctrine\Persistence\ObjectManager
     */
    protected $objectManager;



    /**
     * Constructor
     *
     * @param   ObjectManager      $objectManager
     * @param   string      $modelClass      model class name
     */
    public function __construct(ObjectManager $objectManager, $modelClass)
    {
        parent::__construct($modelClass);

        $this->objectManager = $objectManager;
    }


    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $repository = $this->objectManager->getRepository($this->modelClass);

        if (is_array($identifier)) {
            $criteria = $identifier;
            $model = $repository->findOneBy($criteria);
        } else {
            $model = $repository->find($identifier);
        }

        if ($model == false) {
            return null;
        }

        return $model;
    }

    /**
     * @inheritdoc
     */
    public function findMultiple(array $identifiers)
    {
        $repository = $this->objectManager->getRepository($this->modelClass);

        $criteria = [];
        foreach ($identifiers as $identifier) {
            if (is_array($identifier)) {
                foreach ($identifier as $column => $value) {
                    $criteria[$column][] = $value;
                }
            } else {
                $criteria['id'][] = $identifier;
            }
        }

        $models = $repository->findBy($criteria);

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function has($identifier)
    {
        $model = $this->objectManager->getRepository($this->modelClass)->find($identifier);

        if (!$model) {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return $this->objectManager->getRepository($this->modelClass)->findAll();
    }

    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        $existingModel = $this->objectManager->getRepository($this->modelClass)->find($identifier);
        if ($existingModel === null) {
            $this->objectManager->persist($model);
        }

        $this->objectManager->flush();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        if ($model = $this->find($identifier)) {
            $this->objectManager->remove($model);
            $this->objectManager->flush();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $models = $this->objectManager->getRepository($this->modelClass)->findAll();
        foreach ($models as $model) {
            $this->objectManager->remove($model);
        }
        $this->objectManager->flush();
        return true;
    }
}
