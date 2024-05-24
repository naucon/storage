<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Identity;

use Naucon\Storage\Exception\InvalidArgumentException;

/**
 * Class IdentityValidator
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
class IdentityValidator implements IdentityValidatorInterface
{
    /**
     * @var string
     */
    protected $blacklist;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blacklist = '{}()/\@:';
    }


    /**
     * @inheritdoc
     */
    public function validate($identifier)
    {
        if (!is_string($identifier)) {
            throw new InvalidArgumentException(
                sprintf('Identifier must be string, "%s" given', is_object($identifier) ? get_class($identifier) : gettype($identifier))
            );
        }

        if (!isset($identifier[0])) {
            throw new InvalidArgumentException('Identifier can not be empty');
        }

        if (false !== strpbrk($identifier, $this->blacklist)) {
            throw new InvalidArgumentException(
                sprintf('Identifier "%s" contains illegal characters "%s"', $identifier, $this->blacklist)
            );
        }

        return true;
    }
}