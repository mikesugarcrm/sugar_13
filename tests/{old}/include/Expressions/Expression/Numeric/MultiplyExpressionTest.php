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

class MultiplyExpressionTest extends TestCase
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
            ['multiply(8, "2")', '16.000000'],
            ['multiply("33.333333", "0.90")', '29.999999'],
        ];
    }
}
