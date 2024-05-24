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

use Naucon\Storage\Identity\IdentityFlattener;
use Naucon\Storage\Identity\IdentityFlattenerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class IdentityFlattenerTest
 *
 * @package Naucon\Storage\Tests\Identity
 * @author Sven Sanzenbacher
 */
class IdentityFlattenerTest extends TestCase
{
    public function testInit()
    {
        $flattener = new IdentityFlattener();

        $this->assertInstanceOf(IdentityFlattenerInterface::class, $flattener);
    }

    /**
     * @dataProvider            identifierProvider
     * @param int|string|array  $identifier
     * @param string            $expectedResult
     */
    public function testFlatten($identifier, $expectedResult)
    {
        $flattener = new IdentityFlattener();
        $actualResult = $flattener->flatten($identifier);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function identifierProvider()
    {
        return [
            ['foo', 'foo'],
            [1, '1'],
            [['product_id' => 4], '4'],
            [['product_id' => 4, 'category_id' => 1], '4_1'],
        ];
    }
}
