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
 * Interface NamespaceGeneratorInterface
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
interface NamespaceGeneratorInterface
{
    /**
     * @param  string       $className
     * @param  string|null  $prefix
     * @return string       generated namespace
     */
    public function generate($className, $prefix = null);
}