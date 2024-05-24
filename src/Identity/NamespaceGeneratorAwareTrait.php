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
 * Trait NamespaceGeneratorAwareTrait
 *
 * @package Naucon\Storage\Identity
 * @author Sven Sanzenbacher
 */
trait NamespaceGeneratorAwareTrait
{
    /**
     * @var NamespaceGeneratorInterface
     */
    protected $namespaceGenerator;

    /**
     * @param NamespaceGeneratorInterface $namespaceGenerator
     */
    public function setNamespaceGenerator(NamespaceGeneratorInterface $namespaceGenerator)
    {
        $this->namespaceGenerator = $namespaceGenerator;
    }
}