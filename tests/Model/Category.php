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
 * Class Category
 *
 * @package Naucon\Storage\Tests\Model
 */
class Category implements \Serializable
{
    /**
     * @var     string                  product id
     */
    protected $id;

    /**
     * @var     string                 description
     */
    protected $description;


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
     * @return  string                          description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param    string      $description       description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(
            [
                $this->id,
                $this->description
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        list (
                $this->id,
                $this->description
            )
            = unserialize($data);
    }
}
