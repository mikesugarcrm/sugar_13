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

class AddExpressionTest extends TestCase
{
    /**
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
            ['add(1, 1, "1")', '3.000000'],
            ['add("33.333333", "33.333333")', '66.666666'],
        ];
    }

    /**
     * @bug 63681
     * @throws Exception
     */
    public function testValueOfEmptyString()
    {
        $expr = 'add(number(""), number("1"))';
        $result = Parser::evaluate($expr)->evaluate();
        $this->assertEquals(1, $result);
    }
}
