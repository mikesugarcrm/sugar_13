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

namespace Sugarcrm\SugarcrmTestsUnit\Util\Arrays\ArrayFunctions;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Util\Arrays\ArrayFunctions\ArrayFunctions
 */
class ArrayFunctionsTest extends TestCase
{
    /**
     * @covers ::powerSet
     *
     * @param array $data
     * @param array $expected
     *
     * @dataProvider powerSetProvider
     */
    public function testPowerSet(array $data, array $expected)
    {
        $this->assertSame($expected, ArrayFunctions::powerSet($data));
    }

    public function powerSetProvider()
    {
        return [
            'empty array' => [
                [],
                [[]],
            ],
            'single entry array' => [
                ['a'],
                [[], ['a']],
            ],
            'multiple entries array' => [
                ['a', 'b', 'c'],
                [[], ['a'], ['b'], ['b', 'a'], ['c'], ['c', 'a'], ['c', 'b'], ['c', 'b', 'a']],
            ],

        ];
    }
}
