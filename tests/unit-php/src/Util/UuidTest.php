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

namespace Sugarcrm\SugarcrmTestsUnit\Util;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Uuid
 */
class UuidTest extends TestCase
{
    /**
     * @coversNothing
     * @dataProvider providerMethods
     */
    public function testUniqueness($method)
    {
        $uuid1 = Uuid::$method();
        $uuid2 = Uuid::$method();
        $this->assertNotSame($uuid1, $uuid2, 'Random numbers are not unique');
    }

    /**
     * @covers ::uuid1
     * @covers ::uuid4
     * @dataProvider providerMethods
     */
    public function testFormat($method, $format)
    {
        $this->assertMatchesRegularExpression($format, Uuid::$method());
    }

    public function providerMethods()
    {
        return [
            [
                'uuid1',
                '/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/i',
            ],
            [
                'uuid4',
                '/^[a-z0-9]{8}-[a-z0-9]{4}-4[a-z0-9]{3}-[89ab][a-z0-9]{3}-[a-z0-9]{12}$/i',
            ],
        ];
    }

    /**
     * Provides data for testIsValid
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            'validUUid' => [
                'uuid' => '956fc0c6-eb25-491c-aa19-411bde06e238',
                'expectedResult' => true,
            ],
            'invalidUUid' => [
                'uuid' => md5('test'),
                'expectedResult' => false,
            ],
        ];
    }

    /**
     * @param string $uuid
     * @param bool $expectedResult
     *
     * @covers ::isValid
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(string $uuid, bool $expectedResult)
    {
        $this->assertEquals($expectedResult, Uuid::isValid($uuid));
    }
}
