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

use PHPUnit\Framework\TestCase;

class EqualExpressionTest extends TestCase
{
    public static function dataProviderTestEqual()
    {
        return [
            ['equal(true, 1)', 'true'],
            ['equal(0, "")', 'true'],
            ['equal(1, "true")', 'false'],
            ['equal(true, "true")', 'true'],
            ['equal("true", 1)', 'false'],
            ['equal(false, equal(0, ""))', 'false'],
            ['equal(false, 0)', 'true'],
            ['equal(false, "")', 'true'],
            ['equal(false, "false")', 'true'],
        ];
    }

    /**
     * @dataProvider dataProviderTestEqual
     *
     * @param $expr
     * @param $expected
     * @throws Exception
     */
    public function testIsForecastClosedEvaluate($expr, $expected)
    {
        $context = $this->getMockBuilder('SugarBean')->getMock();

        $result = Parser::evaluate($expr, $context)->evaluate();

        $this->assertSame($expected, strtolower($result));
    }
}
