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
 * Test aggregate functions if NULL fields are present
 *
 * @author avucinci@sugarcrm.com
 */
class Bug63673Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test if aggregate functions return proper values if NULL fields are present
     *
     * @param $reportDef - Report definition
     * @param $create - array for call_user_func() for generating test data
     * @param $avg - expected AVG result
     * @param $count - expected COUNT result
     * @param $max - expected MAX result
     * @param $sum - expected SUM result
     *
     * @dataProvider aggregateDataProvider
     */
    public function testAggregateFunctions($reportDef, $create, $avg, $count, $max, $sum)
    {
        // FIXME TY-1323: Figure out why this test is failing
        call_user_func($create);

        $report = new Report($reportDef);
        $report->run_summary_query();
        $row = $report->get_summary_next_row();
        $this->assertNotEmpty($row['cells'], 'Empty summary results');
        $this->assertEquals($avg, $row['cells'][0], 'AVG result wrong');
        $this->assertEquals($count, $row['cells'][1], 'COUNT result wrong');
        $this->assertEquals($max, $row['cells'][2], 'MAX result wrong');
        $this->assertEquals($sum, $row['cells'][3], 'SUM result wrong');
    }

    /**
     * Creates 3 opportunities, setting the name for filtering
     * and setting probabilities to 10, 20, NULL so we can test aggregate functions
     */
    private static function setUpProducts()
    {
        $prod = SugarTestProductUtilities::createProduct();
        $prod->name = 'Bug 63673 Test Prod 1';
        $prod->quantity = 9;
        $prod->save();

        $prod = SugarTestProductUtilities::createProduct();
        $prod->name = 'Bug 63673 Test Prod 2';
        $prod->quantity = 20;
        $prod->save();

        $prod = SugarTestProductUtilities::createProduct();
        $prod->name = 'Bug 63673 Test Prod 3';
        $prod->save();
        $GLOBALS['db']->query("UPDATE products SET quantity = NULL WHERE id = '{$prod->id}'");
    }

    public static function aggregateDataProvider()
    {
        return [
            [
                '{
                    "display_columns":[
                        {
                            "name":"name",
                            "label":"Product Name",
                            "table_key":"self"
                        },
                        {
                            "name":"quantity",
                            "label":"Quantity",
                            "table_key":"self"
                        }
                    ],
                    "module":"Products",
                    "group_defs":[],
                    "summary_columns":[
                        {
                            "name":"quantity",
                            "label":"AVG: Quantity (%)",
                            "field_type":"int",
                            "group_function":"avg",
                            "table_key":"self"
                        },
                        {
                            "name":"count",
                            "label":"Count",
                            "field_type":"",
                            "group_function":"count",
                            "table_key":"self"
                        },
                        {
                            "name":"quantity",
                            "label":"MAX: Quantity (%)",
                            "field_type":"int",
                            "group_function":"max",
                            "table_key":"self"
                        },
                        {
                            "name":"quantity",
                            "label":"SUM: Quantity (%)",
                            "field_type":"int",
                            "group_function":"sum",
                            "table_key":"self"
                        }
                    ],
                    "report_name":"Bug 63673",
                    "chart_type":"none",
                    "do_round":1,
                    "chart_description":"",
                    "numerical_chart_column":"self:quantity:avg",
                    "numerical_chart_column_type":"",
                    "assigned_user_id":"1",
                    "report_type":"summary",
                    "full_table_list":{
                        "self":{
                            "value":"Products",
                            "module":"Products",
                            "label":"Products"
                        }
                    },
                    "filters_def":{
                        "Filter_1":{
                            "operator":"AND",
                            "0":{
                                "name":"name",
                                "table_key":"self",
                                "qualifier_name":"starts_with",
                                "input_name0":"Bug 63673",
                                "input_name1":"on"
                            }
                        }
                    }
                }',
                ['Bug63673Test', 'setUpProducts'],
                '14.50',
                '3',
                '20',
                '29',
            ],
        ];
    }
}
