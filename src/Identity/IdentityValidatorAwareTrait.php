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
 * Trait IdentityValidatorAwareTrait
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
trait IdentityValidatorAwareTrait
{
    /**
     * @var IdentityValidatorInterface
     */
    protected $identityValidator;

    /**
     * @param IdentityValidatorInterface    $identityValidator
     */
    public function setIdentityValidator(IdentityValidatorInterface $identityValidator)
    {
        $this->identityValidator = $identityValidator;
    }
}