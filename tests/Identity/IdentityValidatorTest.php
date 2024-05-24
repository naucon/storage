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

use Naucon\Storage\Identity\IdentityValidator;
use Naucon\Storage\Identity\IdentityValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class IdentityValidatorTest
 *
 * @package Naucon\Storage\Tests\Identity
 * @author Sven Sanzenbacher
 */
class IdentityValidatorTest extends TestCase
{
    public function testInit()
    {
        $validator = new IdentityValidator();

        $this->assertInstanceOf(IdentityValidatorInterface::class, $validator);
    }

    /**
     * @dataProvider            identifierProvider
     * @param int|string|array  $identifier
     * @param bool              $expectedResult
     */
    public function testValidate($identifier, $expectedResult)
    {
        $validator = new IdentityValidator();
        $actualResult = $validator->validate($identifier);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function identifierProvider()
    {
        return [
            ['123', true],
            ['foo', true],
            ['foo_', true],
            ['bar', true]
        ];
    }

    /**
     * @expectedException \Naucon\Storage\Exception\InvalidArgumentException
     */
    public function testValidateWithEmptyStringShouldThrowException()
    {
        $identifier = '';

        $validator = new IdentityValidator();
        $validator->validate($identifier);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\InvalidArgumentException
     */
    public function testValidateWithNullShouldThrowException()
    {
        $identifier = null;

        $validator = new IdentityValidator();
        $validator->validate($identifier);
    }

    /**
     * @expectedException \Naucon\Storage\Exception\InvalidArgumentException
     */
    public function testValidateWithIllegalCharactersShouldThrowException()
    {
        $identifier = 'foo@';

        $validator = new IdentityValidator();
        $validator->validate($identifier);
    }

    /**
     * @return array
     */
    public function illegalIdentifiersProvider()
    {
        return [
            ['{foo'],
            ['foo}'],
            ['(foo'],
            ['foo)'],
            ['foo\\'],
            ['foo/'],
            ['foo@'],
            ['foo:'],
            ['{}()/\@:']
        ];
    }
}
