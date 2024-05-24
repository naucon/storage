<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Tests\Model;

/**
 * Class User
 *
 * @package Naucon\Storage\Tests\Model
 */
class User implements \Serializable
{
    /**
     * @var     string                  product id
     */
    protected $id;

    /**
     * @var     string                 username
     */
    protected $username;


    /**
     * @return  string                  product id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param   string                  product id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return  string                  username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param   string  $username       username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->username
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        list(
            $this->id,
            $this->username
            ) = unserialize($data);
    }
}
