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
use Naucon\Utility\Map;

/**
 * Class FileStorage
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class FileStorage extends AbstractStorage implements IdentityFlattenerAwareInterface
{
    use IdentityFlattenerAwareTrait;

    /**
     * @var     string          directory in the filesystem
     */
    protected $storageDir;

    /**
     * @var     Map             identity map
     */
    protected $identityMap;

    /**
     * @var     string          file prefix
     */
    protected $filePrefix = 'storage-model-';



    /**
     * Constructor
     *
     * @param   string      $storageDir      directory in the filesystem
     * @param   string      $modelClass      model class name
     */
    public function __construct($storageDir, $modelClass = null)
    {
        parent::__construct($modelClass);

        $this->storageDir = $storageDir;
        $this->identityMap = new Map();

        $this->identityFlattener = new IdentityFlattener();
    }

    /**
     * @inheritdoc
     * @throws \Naucon\Utility\Exception\MapException
     */
    public function find($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $identifier = $this->sanitizeIdentifier($identifier);
        if (!$this->identityMap->hasKey($identifier)) {
            $file = $this->storageDir . '/' . $this->filePrefix . $identifier;
            $model = $this->loadFile($file);
            if ($model === null) {
                return null;
            }

            return $this->identityMap->set($model->getId(), $model);
        }

        if (!$this->identityMap->hasKey($identifier)) {
            return null;
        }

        return $this->identityMap->get($identifier);
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

        $identifier = $this->sanitizeIdentifier($identifier);
        if (!$hasId = $this->identityMap->hasKey($identifier)) {
            $file = $this->storageDir . '/' . $this->filePrefix . $identifier;
            if ($this->hasFile($file)) {
                $hasId = true;
            }
        }

        return $hasId;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $models = $this->loadAllFiles();
        return $models;
    }

    /**
     * @inheritdoc
     * @throws \Naucon\Utility\Exception\MapException
     */
    public function removeAll()
    {
        $models = $this->loadAllFiles();
        foreach ($models as $model) {
            $identifier = $model->getId();
            unset($model);
            if ($this->has($identifier)) {
                unlink($this->storageDir . '/' . $this->filePrefix . $identifier);
                $this->identityMap->remove($identifier);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     * @throws \Naucon\Utility\Exception\MapException
     */
    public function flush($identifier, $model)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $identifier = $this->sanitizeIdentifier($identifier);
        $this->identityMap->set($identifier, $model);
        file_put_contents($this->storageDir . '/' . $this->filePrefix . $identifier, serialize($model));
        return true;
    }


    /**
     * @inheritdoc
     * @throws \Naucon\Utility\Exception\MapException
     */
    public function remove($identifier, $model)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $identifier = $this->sanitizeIdentifier($identifier);
        unset($model);

        if ($this->has($identifier)) {
            unlink($this->storageDir . '/' . $this->filePrefix . $identifier);
            $this->identityMap->remove($identifier);
        }

        return true;
    }

    /**
     * @param   string      $file
     * @return  bool        returns true if the given file is present
     */
    protected function hasFile($file)
    {
        $fileExist = false;
        if (file_exists($file)) {
            $fileExist = true;
        }

        return $fileExist;
    }

    /**
     * @param   string      $file
     * @return  object|null      model
     */
    protected function loadFile($file)
    {
        if (!$this->hasFile($file)) {
            return null;
        }

        $model = unserialize(file_get_contents($file));
        return $model;
    }

    /**
     * @return  array      array of models
     */
    protected function loadAllFiles()
    {
        $models = [];
        $files = glob($this->storageDir . '/' . $this->filePrefix . '*');
        foreach ($files as $file) {
            $models[] = $this->loadFile($file);
        }

        return $models;
    }

    /**
     * @param int|string    $identifier     unsanitzed identifier
     * @return string       sanitzed identifier
     */
    protected function sanitizeIdentifier($identifier)
    {
        $char = 'ÁÉÍÓÚÝáéíóúýÂÊÎÔÛâêîôûÀÈÌÒÙàèìòùÄËÏÖÜäëïöüÿÃÕÅÑãõåñÇç@°ºªß';
        $rep  = 'AEIOUYaeiouyAEIOUaeiouAEIOUaeiouAEIOUaeiouyAOANaoanCcaooas';
        $identifier = strtr(utf8_decode($identifier), utf8_decode($char), $rep);
        $identifier = filter_var($identifier, FILTER_SANITIZE_URL);
        $identifier = preg_replace('/[^a-zA-Z0-9._-]/', '_', $identifier);

        return $identifier;
    }
}
