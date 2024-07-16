<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Provider;


use Naucon\Storage\Session\AttributeBag\LegacyNamespacedAttributeBag;

/**
 * Class SessionBag
 *
 * @package Naucon\Storage\Provider
 * @author Sven Sanzenbacher
 */
class SessionBag extends LegacyNamespacedAttributeBag
{
    /**
     * @var     string      default session storage key
     */
    const DEFAULT_STORAGE_KEY = '_naucon_storage';

    /**
     * @var     string      default session bag name
     */
    const DEFAULT_NAME = 'naucon_storage';

    /**
     * Constructor
     *
     * @inheritdoc
     */
    public function __construct($storageKey = self::DEFAULT_STORAGE_KEY, $namespaceCharacter = '/')
    {
        parent::__construct($storageKey, $namespaceCharacter);

        $this->setName(self::DEFAULT_NAME);
    }
}