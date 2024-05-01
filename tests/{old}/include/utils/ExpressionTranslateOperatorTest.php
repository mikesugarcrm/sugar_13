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

require_once 'include/utils/expression_utils.php';

/**
 * Class ExpressionTranslateOperatorTest
 * Check if translate_operator works properly and covers all operators
 */
class ExpressionTranslateOperatorTest extends TestCase
{
    /**
     * @dataProvider operatorDataset
     */
    public function testExpressionTranslateOperator($operator, $type, $expected)
    {
        $operator = translate_operator($operator, $type);

        $this->assertEquals($expected, $operator, 'Operator translated improperly');
    }

    public static function operatorDataset()
    {
        return [
            ['Equals', 'php', '=='],
            ['Is empty', 'php', '=='],
            ['Less Than', 'php', '<'],
            ['More Than', 'php', '>'],
            ['Does not Equal', 'php', '!='],
            ['Is not empty', 'php', '!='],
            ['Equals', 'sql', '='],
            ['Is empty', 'sql', '='],
            ['Less Than', 'sql', '<'],
            ['More Than', 'sql', '>'],
            ['Does not Equal', 'sql', '!='],
            ['Is not empty', 'sql', '!='],
        ];
    }
}
