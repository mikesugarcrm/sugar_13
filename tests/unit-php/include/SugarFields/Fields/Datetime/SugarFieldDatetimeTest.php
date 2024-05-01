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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Datetime;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarFieldDatetime
 */
class SugarFieldDatetimeTest extends TestCase
{
    /**
     * Data provider for testParseVariable.
     */
    public function providerTestParseVariable()
    {
        // mock evaluateVariable()
        $callback = function (&$ref) {
            $dataMap = [
                '$nowTime' => '05/05/2019T13:03:03',
                '$tomorrowTime' => '05/06/2019T13:03:03',
            ];
            if (isset($dataMap[$ref])) {
                $ref = $dataMap[$ref];
                return true;
            }
            return false;
        };
        return [
            // should be converted
            ['$nowTime', '05/05/2019T13:03:03', $callback],
            [['$nowTime', '$tomorrowTime'], ['05/05/2019T13:03:03', '05/06/2019T13:03:03'], $callback],
            ['$tomorrowTime', '05/06/2019T13:03:03', $callback],
            // shouldn't change
            ['2019-05-05', '2019-05-05', $callback],
            [['2019-05-05', '2019-05-06'], ['2019-05-05', '2019-05-06'], $callback],
        ];
    }

    /**
     * @dataProvider providerTestParseVariable
     * @covers ::parseVariable
     * @param $value
     * @param $expected
     * @param $callback
     */
    public function testParseVariable($value, $expected, $callback)
    {
        $fieldMock = $this->createPartialMock('\SugarFieldDatetime', ['evaluateVariable']);
        $fieldMock->method('evaluateVariable')->will($this->returnCallback($callback));
        TestReflection::callProtectedMethod($fieldMock, 'parseVariable', [&$value]);
        $this->assertEquals($expected, $value, 'Variables should have been converted to datetime strings');
    }
}
