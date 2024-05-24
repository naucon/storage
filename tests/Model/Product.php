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
 * Class Product
 *
 * @package Naucon\Storage\Tests\Model
 */
class Product implements \Serializable
{
    /**
     * @var     string                  product id
     */
    protected $id;

    /**
     * @var     string                  SKU
     */
    protected $sku;

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
     * @return  string                  SKU
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param   string      $sku       SKU
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
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
                $this->sku,
                $this->description
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
            $this->sku,
            $this->description
            ) = unserialize($data);
    }
}
