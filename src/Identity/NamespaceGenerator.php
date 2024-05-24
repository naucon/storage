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
 * Class NamespaceGenerator
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
class NamespaceGenerator implements NamespaceGeneratorInterface
{
    /**
     * @var string
     */
    protected $separator;

    /**
     * Constructor
     *
     * @param string $separator
     */
    public function __construct($separator = null)
    {
        if ($separator === null) {
            $separator = '_';
        }

        $this->separator = $separator;
    }

    /**
     * @param string $className
     * @param string|null $prefix
     * @return string     generated namespace
     */
    public function generate($className, $prefix = null)
    {
        $namespace = $prefix;
        if ($namespace !== null) {
            $namespace.= $this->separator;
        }
        $namespace.= md5($className);

        return $namespace;
    }
}