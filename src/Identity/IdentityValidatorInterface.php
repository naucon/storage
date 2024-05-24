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
 * Interface IdentityValidatorInterface
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
interface IdentityValidatorInterface
{
    /**
     * validate the given identifier
     *
     * @param string    $identifier
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate($identifier);
}