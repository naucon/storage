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
 * Interface IdentityFlattenerInterface
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
interface IdentityFlattenerInterface
{
    /**
     * @param  int|string|array     $identifier
     * @return string       flattened identifier
     */
    public function flatten($identifier);
}