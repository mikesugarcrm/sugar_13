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

/**
 * @coversDefaultClass ProrateValueExpression
 * Class ProrateValueExpressionTest
 */
class ProrateValueExpressionTest extends TestCase
{
    /**
     * @covers ::evaluate
     * @dataProvider dataProviderTestEvaluate
     */
    public function testEvaluate($test, $expected)
    {
        $result = Parser::evaluate($test)->evaluate();
        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestEvaluate()
    {
        return [
            // Days only
            ['prorateValue(10.50, 30, "day", 60, "day")', 5.25],
            // Test that strings of numbers work as well
            ['prorateValue("10.50", "30", "day", "60", "day")', 5.25],
            // Years to months
            ['prorateValue(35, 3, "year", 6, "month")', 210.00],
            // Months to years
            ['prorateValue(600, 36, "month", 2, "year")', 900.00],
            // Years to days
            ['prorateValue(120, 3, "year", 30, "day")', 4380.00],
            // Days to years
            ['prorateValue(100, 60, "day", 1, "year")', 16.438356],
            // Months to days
            ['prorateValue(75, 2, "month", 6, "day")', 760.416666],
            // Days to months
            ['prorateValue(1234, 100, "day", 6, "month")', 676.164383],
        ];
    }
}
