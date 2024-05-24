<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Naucon\Storage\Tests\Identity;

use Naucon\Storage\Identity\NamespaceGenerator;
use Naucon\Storage\Identity\NamespaceGeneratorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class NamespaceGeneratorTest
 *
 * @package Naucon\Storage\Tests\Identity
 * @author Sven Sanzenbacher
 */
class NamespaceGeneratorTest extends TestCase
{
    public function testInit()
    {
        $generator = new NamespaceGenerator();

        $this->assertInstanceOf(NamespaceGeneratorInterface::class, $generator);
    }

    /**
     * @dataProvider            classNameProvider
     * @param string            $className
     * @param string            $prefix
     * @param string            $separator
     * @param string            $expectedResult
     */
    public function testGenerate($className, $prefix, $separator, $expectedResult)
    {
        $generator = new NamespaceGenerator($separator);
        $actualResult = $generator->generate($className, $prefix);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function classNameProvider()
    {
        return [
            ['foo', null, null, 'acbd18db4cc2f85cedef654fccc4a4d8'],
            ['foo', 'ns', null, 'ns_acbd18db4cc2f85cedef654fccc4a4d8'],
            ['foo', 'ns', '-', 'ns-acbd18db4cc2f85cedef654fccc4a4d8'],
            ['foo_bar', null, null, '5c7d96a3dd7a87850a2ef34087565a6e'],
            ['Foo/Bar/', null, null, 'ee2861b3af11e8ca31b378ebf1479539'],
            ['/Foo/Bar/', null, null, 'a03582bb52fe22c28158ea01ec5b36cd']
        ];
    }
}
