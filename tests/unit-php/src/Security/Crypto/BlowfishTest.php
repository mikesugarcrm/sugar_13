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
use Sugarcrm\Sugarcrm\Security\Crypto\Blowfish;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Crypto\Blowfish
 */
class BlowfishTest extends TestCase
{
    public function encodeDataProvider()
    {
        return [
            //short keys are cycled over
            ['XBhHTAk8pHkNbLUL5EXJKg==', '1234', 'secret text'],
            ['XBhHTAk8pHkNbLUL5EXJKg==', '12341234', 'secret text'],
            ['XBhHTAk8pHkNbLUL5EXJKg==', '123412341234', 'secret text'],
            ['XBhHTAk8pHkNbLUL5EXJKg==', '1234123412341234', 'secret text'],
            //zero-padded key is different
            ['YV1oNybENeZKaQ8tuYEFWA==', "123412341234\0\0\0\0", 'secret text'],
            //short text is zero-padded
            ['XBhHTAk8pHkNbLUL5EXJKg==', '1234123412341234', "secret text\0\0\0"],
        ];
    }

    /**
     * @covers ::encode
     * @covers ::padKey
     * @dataProvider encodeDataProvider
     */
    public function testEncode($expected, $key, $secret)
    {
        $this->assertEquals($expected, Blowfish::encode($key, $secret));
    }

    public function decodeDataProvider()
    {
        return [
            //short keys are cycled over
            ['secret text', '1234', 'XBhHTAk8pHkNbLUL5EXJKg=='],
            ['secret text', '12341234', 'XBhHTAk8pHkNbLUL5EXJKg=='],
            ['secret text', '123412341234', 'XBhHTAk8pHkNbLUL5EXJKg=='],
            ['secret text', '1234123412341234', 'XBhHTAk8pHkNbLUL5EXJKg=='],
            //zero-padded key is different
            ['secret text', "123412341234\0\0\0\0", 'YV1oNybENeZKaQ8tuYEFWA=='],
        ];
    }

    /**
     * @covers ::decode
     * @covers ::padKey
     * @dataProvider decodeDataProvider
     */
    public function testDecode($expected, $key, $encrypted)
    {
        $this->assertEquals($expected, Blowfish::decode($key, $encrypted));
    }
}
