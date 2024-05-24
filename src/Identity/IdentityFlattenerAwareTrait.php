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
 * Trait IdentityFlattenerAwareTrait
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
trait IdentityFlattenerAwareTrait
{
    /**
     * @var IdentityFlattenerInterface
     */
    protected $identityFlattener;

    /**
     * @param IdentityFlattenerInterface $identityFlattener
     */
    public function setIdentityFlattener(IdentityFlattenerInterface $identityFlattener)
    {
        $this->identityFlattener = $identityFlattener;
    }
}