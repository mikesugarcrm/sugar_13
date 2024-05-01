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

namespace Sugarcrm\SugarcrmTestsUnit\inc\Expressions\Expression\Parser;

use PHPUnit\Framework\TestCase;

/**
 * Class ParserTest
 *
 * @coversDefaultClass \Parser
 */
class ParserTest extends TestCase
{
    /**
     * @dataProvider dataProviderIsRelatedExpression
     *
     * @covers ::isRelatedExpression
     * @param string $formula
     * @param array $function_map
     * @param boolean $expected
     */
    public function testIsRelatedExpression($formula, $function_map, $expected)
    {
        \Parser::$function_cache = $function_map;
        $expr = \Parser::evaluate($formula);
        $this->assertEquals($expected, \Parser::isRelatedExpression($expr));
    }

    public static function dataProviderIsRelatedExpression()
    {
        return [
            [
                'count($revenuelineitems)',
                [
                    'count' => [
                        'class' => 'CountRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountRelatedExpression.php',
                    ],
                ],
                true,
            ],
            [
                'ceil(5.53)',
                [
                    'ceil' => [
                        'class' => 'CeilingExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CeilingExpression.php',
                    ],
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::getFormulaRelateFields
     * @dataProvider dataProviderGetFormulaRelatedFields
     */
    public function testGetFormulaRelateFields($formula, $linkName, $function_map, $expected)
    {
        \Parser::$function_cache = $function_map;
        $expr = \Parser::evaluate($formula);
        $this->assertSame($expected, \Parser::getFormulaRelateFields($expr, $linkName));
    }

    public static function dataProviderGetFormulaRelatedFields()
    {
        return [
            [
                'count($revenuelineitems)',
                '',
                [
                    'count' => [
                        'class' => 'CountRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountRelatedExpression.php',
                    ],
                ],
                [],
            ],
            [
                'countConditional($revenuelineitems,"sales_stage",createList("Closed Won","Closed Lost"))',
                '',
                [
                    'countConditional' => [
                        'class' => 'CountConditionalRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/CountConditionalRelatedExpression.php',
                    ],
                    'createList' => [
                        'class' => 'DefineEnumExpression',
                        'src' => 'include/Expressions/Expression/Enum/DefineEnumExpression.php',
                    ],
                ],
                ['sales_stage'],
            ],
            // will return all rollup fields since we don't have a linkname specified
            [
                'rollupSum($revenuelineitems, "likely_case")',
                '',
                [
                    'rollupSum' => [
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    ],
                ],
                ['likely_case'],
            ],
            // will return all rollup fields since we don't have a linkname specified
            [
                'add(rollupSum($revenuelineitems, "likely_case"),rollupSum($opportunities, "amount"))',
                '',
                [
                    'add' => [
                        'class' => 'AddExpression',
                        'src' => 'include/Expressions/Expression/Numeric/AddExpression.php',
                    ],
                    'rollupSum' => [
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    ],
                ],
                ['likely_case', 'amount'],
            ],
            // will return only amount since linkName is opportunities
            [
                'add(rollupSum($revenuelineitems, "likely_case"),rollupSum($opportunities, "amount"))',
                'opportunities',
                [
                    'add' => [
                        'class' => 'AddExpression',
                        'src' => 'include/Expressions/Expression/Numeric/AddExpression.php',
                    ],
                    'rollupSum' => [
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    ],
                ],
                ['amount'],
            ],
            // this should return an empty array since we are looking for opportunities, but we have revenuelineitems
            // in the formula
            [
                'rollupSum($revenuelineitems, "amount")',
                'opportunities',
                [
                    'rollupSum' => [
                        'class' => 'SumRelatedExpression',
                        'src' => 'include/Expressions/Expression/Numeric/SumRelatedExpression.php',
                    ],
                ],
                [],
            ],
        ];
    }
}
