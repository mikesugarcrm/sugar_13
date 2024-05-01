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

class SugarWidgetFieldcurrencyTest extends TestCase
{
    protected $reporter;
    protected $layoutManager;
    protected $widgetField;

    protected function setUp(): void
    {
        $this->reporter = new Report();
        $this->layoutManager = new LayoutManager();
    }

    protected function tearDown(): void
    {
        unset($this->reporter);
        unset($this->layoutManager);
        unset($this->widgetField);
    }

    /**
     * Tests if the function returns currency id alias based on associated table
     *
     * @param string $tableAlias
     * @param array $tableColumns ,
     * @param array $cstmTableColumns ,
     * @param string|false $expected
     *
     * @dataProvider getCurrencyIdTableAliasProvider
     * @covers       SugarWidgetFieldCurrency::getCurrencyIdTable
     */
    public function testGetCurrencyIdTableAlias(
        string $tableAlias,
        array  $tableColumns,
        array  $cstmTableColumns,
        $expected
    ) {

        $this->layoutManager->setAttributePtr('reporter', $this->reporter);
        $this->widgetField = new SugarWidgetFieldCurrency($this->layoutManager);

        $tableAlias = $this->widgetField->getCurrencyIdTableAlias(
            $tableAlias,
            $tableColumns,
            $cstmTableColumns
        );
        $this->assertEquals($expected, $tableAlias);
    }

    /**
     * @return array ($tableAlias, $tableColumns, $cstmTableColumns, $expected)
     */
    public static function getCurrencyIdTableAliasProvider()
    {
        return [
            // currency_id is in stock table but not in custom table
            [
                'l1_cstm',
                [
                    'currency_id' => [
                        'name' => 'currency_id',
                        'type' => 'char',
                    ],
                ],
                [
                    'test_currency_c' => [
                        'name' => 'test_currency_c',
                        'type' => 'decimal',
                        'len' => '26, 6',
                    ],
                ],
                'l1',
            ],
            // currency_id is in both stock and custom table (not likely an use case)
            [
                'revenue_line_items_cstm',
                [
                    'currency_id' => [
                        'name' => 'currency_id',
                        'type' => 'char',
                    ],
                ],
                [
                    'rli_currency_c' => [
                        'name' => 'rli_currency_c',
                        'type' => 'decimal',
                        'len' => '26, 6',
                    ],
                    'currency_id' => [
                        'name' => 'currency_id',
                        'type' => 'char',
                    ],
                ],
                'revenue_line_items_cstm',
            ],
            // currency_id is in neither stock or custom table
            [
                'accounts',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'char',
                    ],
                ],
                [
                    'acct_currency_c' => [
                        'name' => 'acct_currency_c',
                        'type' => 'decimal',
                        'len' => '26, 6',
                    ],
                ],
                false,
            ],
        ];
    }
}
