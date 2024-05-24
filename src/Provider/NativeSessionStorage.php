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
 * Class NativeSessionStorage
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class NativeSessionStorage extends AbstractStorage implements IdentityFlattenerAwareInterface
{
    use IdentityFlattenerAwareTrait;

    /**
     * @var     string      namespace to store values in the session
     */
    const SESSION_NAMESPACE = 'storage';

    /**
     * @var     bool        is session started
     */
    protected $sessionStarted = false;

    /**
     * @var     string      namespace to store values in the session
     */
    protected $namespace;



    /**
     * Constructor
     *
     * @param   string      $namespace      namespace to store values in the session
     * @param   string      $modelClass      model class name
     */
    public function __construct($namespace = self::SESSION_NAMESPACE, $modelClass = null)
    {
        parent::__construct($modelClass);

        $this->namespace = $namespace;

        $this->identityFlattener = new IdentityFlattener();
    }



    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        if (!$this->sessionStarted) {
            $this->startSession();
        }

        if (!isset($_SESSION[$this->namespace][$identifier])) {
            return null;
        }

        return $_SESSION[$this->namespace][$identifier];
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

        if (!$this->sessionStarted) {
            $this->startSession();
        }

        if (array_key_exists($identifier, $_SESSION[$this->namespace])) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        if (!$this->sessionStarted) {
            $this->startSession();
        }

        $models = array_values($_SESSION[$this->namespace]);

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        if (!$this->sessionStarted) {
            $this->startSession();
        }

        $_SESSION[$this->namespace] = [];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        if (!$this->sessionStarted) {
            $this->startSession();
        }

        $_SESSION[$this->namespace][$identifier] = $model;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        unset($model);
        $identifier = $this->identityFlattener->flatten($identifier);

        if (!$this->sessionStarted) {
            $this->startSession();
        }

        if (array_key_exists($identifier, $_SESSION[$this->namespace])) {
            unset($_SESSION[$this->namespace][$identifier]);
        }

        return true;
    }

    protected function startSession()
    {
        if (!headers_sent()) {
            if (PHP_VERSION_ID >= 50400) {
                if (PHP_SESSION_NONE === session_status()) {
                    session_start();
                }
            } elseif (!session_id()) {
                session_start();
            }
        }

        if (!isset($_SESSION)) {
            $_SESSION = [];
        }

        if (!array_key_exists($this->namespace, $_SESSION)) {
            $_SESSION[$this->namespace] = [];
        }

        $this->sessionStarted = true;
    }
}
