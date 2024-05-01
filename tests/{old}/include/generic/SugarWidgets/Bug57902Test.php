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

require_once 'include/generic/SugarWidgets/SugarWidgetReportField.php';

/**
 * Bug #57902
 * click Count on Calls report see message: Database failure. Please refer to sugarcrm.log for details.
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57902
 */
class Bug57902Test extends TestCase
{
    public static function dataProvider()
    {
        return [
            [
                [
                    'column_key' => 'self',
                    'group_function' => 'count',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                ],
                'calls__count ASC',
            ],
            [
                [
                    'column_function' => 'avg',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'avg',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int',
                ],
                'calls_avg_duration_hours ASC',
            ],
            [
                [
                    'column_function' => 'max',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'max',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int',
                ],
                'calls_max_duration_hours ASC',
            ],
            [
                [
                    'column_function' => 'min',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'min',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int',
                ],
                'calls_min_duration_hours ASC',
            ],
            [
                [
                    'column_function' => 'sum',
                    'column_key' => 'self:duration_hours',
                    'group_function' => 'sum',
                    'name' => 'duration_hours',
                    'sort_dir' => 'a',
                    'table_alias' => 'calls',
                    'table_key' => 'self',
                    'type' => 'int',
                ],
                'calls_sum_duration_hours ASC',
            ],
        ];
    }

    /**
     * Test asserts that for group functions order by is alias instead of table.field
     *
     * @dataProvider dataProvider
     * @group 57902
     * @return void
     */
    public function testQueryOrderBy($layout_def, $expected)
    {
        $layoutManager = new LayoutManager();
        $sugarWidgetReportField = new SugarWidgetReportField($layoutManager);

        $actual = $sugarWidgetReportField->queryOrderBy($layout_def);

        $this->assertEquals($expected, $actual, 'ORDER BY string is incorrect');
    }
}
