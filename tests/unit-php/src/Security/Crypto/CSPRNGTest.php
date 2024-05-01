<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestsUnit\Security\Crypto;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG
 */
class CSPRNGTest extends TestCase
{
    /**
     * @covers ::generate
     * @dataProvider providerTestGenerate
     */
    public function testGenerate($size, $encode)
    {
        $csprng = new CSPRNG();
        $random = $csprng->generate($size, $encode);
        $actual = TestReflection::callProtectedMethod($csprng, 'binaryStrLen', [$random]);
        $this->assertEquals($size, $actual);
    }

    public function providerTestGenerate()
    {
        return [
            [10, false],
            [256, false],
            [512, false],
            [1024, false],
            [10, true],
            [256, true],
            [512, true],
            [1024, true],
        ];
    }

    /**
     * @covers ::binaryEncode
     * @dataProvider providerTestBinaryEncode
     */
    public function testBinaryEncode($binary, $size, $expected)
    {
        $csprng = new CSPRNG();
        $encoded = TestReflection::callProtectedMethod($csprng, 'binaryEncode', [$binary, $size]);
        $this->assertMatchesRegularExpression('#^[A-Za-z0-9+/]+$#D', $encoded);
        $this->assertEquals($expected, $encoded);
    }

    public function providerTestBinaryEncode()
    {
        return [
            // already encoded format
            [
                'abcdefghij',
                5,
                'YWJjZ',
            ],
            // real binary string
            [
                0xCC . 0xA9 . 0xF2 . 0x42 . 0x8C . 0x39 . 0xBA . 0xDD,
                4,
                'MjA0',
            ],
        ];
    }

    /**
     * @covers ::binaryStrLen
     * @covers ::binarySubstr
     * @dataProvider providerTestBinaryStr
     */
    public function testBinaryStr($string, $strLen, $start, $length, $subStr)
    {
        $csprng = new CSPRNG();
        $actualStrLen = TestReflection::callProtectedMethod($csprng, 'binaryStrLen', [$string]);
        $actualSubStr = TestReflection::callProtectedMethod($csprng, 'binarySubStr', [$string, $start, $length]);
        $this->assertEquals($strLen, $actualStrLen);
        $this->assertEquals($subStr, $actualSubStr);
    }

    public function providerTestBinaryStr()
    {
        return [
            ['xyz', 3, 0, 1, 'x'],
            ['我abc', 6, 3, 2, 'ab'],
        ];
    }
}
