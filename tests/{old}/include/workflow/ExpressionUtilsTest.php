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

require_once 'include/workflow/expression_utils.php';

/*
 * @group ExpressionUtilsTest
 */

class ExpressionUtilsTest extends TestCase
{
    public function testGetExpression()
    {
        $express_type = '+';
        $first = 8329;
        $second = 2374;
        $output = get_expression($express_type, $first, $second);
        $this->assertEquals(10703, $output);

        $express_type = '-';
        $first = 8329;
        $second = 2374;
        $output = get_expression($express_type, $first, $second);
        $this->assertEquals(5955, $output);

        $express_type = '*';
        $first = 8329;
        $second = 2374;
        $output = get_expression($express_type, $first, $second);
        $this->assertEquals(19773046, $output);

        $express_type = '/';
        $first = 7122;
        $second = 2374;
        $output = get_expression($express_type, $first, $second);
        $this->assertEquals(3, $output);
    }

    public function testExpressAdd()
    {
        $first = 8329;
        $second = 2374;
        $output = express_add($first, $second);
        $this->assertEquals(10703, $output);
    }

    public function testExpressSubtract()
    {
        $first = 8329;
        $second = 2374;
        $output = express_subtract($first, $second);
        $this->assertEquals(5955, $output);
    }

    public function testExpressMultiple()
    {
        $first = 8329;
        $second = 2374;
        $output = express_multiple($first, $second);
        $this->assertEquals(19773046, $output);
    }

    public function testExpressDivide()
    {
        $first = 7122;
        $second = 2374;
        $output = express_divide($first, $second);
        $this->assertEquals(3, $output);
    }
}
