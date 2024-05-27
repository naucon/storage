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

use Naucon\Storage\Exception\InvalidArgumentException;
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

    public function testValidateWithEmptyStringShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $identifier = '';

        $validator = new IdentityValidator();
        $validator->validate($identifier);
    }

    public function testValidateWithNullShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        $identifier = null;

        $validator = new IdentityValidator();
        $validator->validate($identifier);
    }

    public function testValidateWithIllegalCharactersShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
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
