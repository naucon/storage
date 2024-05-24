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

/**
 * Class IdentityFlattener
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
class IdentityFlattener implements IdentityFlattenerInterface
{
    /**
     * @inheritdoc
     */
    public function flatten($identifier)
    {
        if (is_array($identifier)) {
            $identifier = implode('_', $identifier);
        }

        return (string)$identifier;
    }
}