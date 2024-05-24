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
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class SessionBridgeStorage
 *
 * integrate Symfony Session as storage
 * but you have to take care for starting and saving session your self.
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class SessionBridgeStorage extends AbstractStorage implements IdentityFlattenerAwareInterface
{
    use IdentityFlattenerAwareTrait;

    /**
     * @var     string      default session key prefix
     */
    const DEFAULT_SESSION_KEY_PREFIX = 'storage-';

    /**
     * @var     SessionInterface
     */
    protected $session;

    /**
     * @var     string      session key prefix
     */
    protected $sessionKeyPrefix;

    /**
     * @var     string      session bag name
     */
    protected $sessionBagName;


    /**
     * Constructor
     *
     * @param   SessionInterface        $session
     * @param   string                  $modelClass      model class name
     */
    public function __construct(SessionInterface $session, $modelClass = null)
    {
        parent::__construct($modelClass);

        $this->session = $session;
        $this->sessionKeyPrefix = self::DEFAULT_SESSION_KEY_PREFIX;
        $this->sessionBagName = SessionBag::DEFAULT_NAME;

        $this->identityFlattener = new IdentityFlattener();
    }


    /**
     * @param   string      $name         session bag name
     */
    public function setSessionBagName($name)
    {
        $this->sessionBagName = $name;
    }

    /**
     * @param   string      $prefix             session key prefix
     */
    public function setSessionKeyPrefix($prefix)
    {
        $this->sessionKeyPrefix = $prefix;
    }

    /**
     * @return  SessionBag|SessionBagInterface
     */
    protected function getSessionBag()
    {
        $sessionBag = $this->session->getBag($this->sessionBagName);

        return $sessionBag;
    }

    /**
     * @inheritdoc
     */
    public function find($identifier)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $sessionBag = $this->getSessionBag();
        if (!$sessionBag->has($this->sessionKeyPrefix . $identifier)) {
            return null;
        }

        return $sessionBag->get($this->sessionKeyPrefix . $identifier);
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

        $sessionBag = $this->getSessionBag();
        if ($sessionBag->has($this->sessionKeyPrefix . $identifier)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        $sessionBag = $this->getSessionBag();
        $models = array_values($sessionBag->all());

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function flush($identifier, $model)
    {
        $identifier = $this->identityFlattener->flatten($identifier);

        $sessionBag = $this->getSessionBag();
        $sessionBag->set($this->sessionKeyPrefix . $identifier, $model);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAll()
    {
        $sessionBag = $this->getSessionBag();
        $sessionBag->clear();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function remove($identifier, $model)
    {
        unset($model);
        $identifier = $this->identityFlattener->flatten($identifier);

        $sessionBag = $this->getSessionBag();
        if ($sessionBag->has($this->sessionKeyPrefix . $identifier)) {
            $sessionBag->remove($this->sessionKeyPrefix . $identifier);
        }

        return true;
    }
}
