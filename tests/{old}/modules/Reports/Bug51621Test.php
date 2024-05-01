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
 * @group Bug51621
 */
class Bug51621Test extends TestCase
{
    /**
     * @dataProvider savedReportContentTestData
     * @param $dirtyContent test json string of dirty content going in
     * @param $cleanedContent expected json string of clean content coming out
     */
    public function testCleanBeanForSavedReportDoesNotCorruptReportContents($dirtyContent, $cleanedContent)
    {
        $report = new SavedReport();
        $report->content = $dirtyContent;
        $report->cleanBean();
        $this->assertSame($cleanedContent, $report->content);
    }

    /**
     * @return array
     */
    public function savedReportContentTestData()
    {
        return [
            [
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"asdf","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<img alt=\"<script>\" src=\" http:\/\/www.symbolset.org\/images\/peace-sign-2.jpg\"; width=\"1\" height=\"1\"\/>","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"asdf","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<img alt=\"\" src=\" http:\/\/www.symbolset.org\/images\/peace-sign-2.jpg\"; width=\"1\" height=\"1\"\/>","input_name1":"on"}}},"chart_type":"none"}',
            ],
            [
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"goodReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"Santa Fe","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"goodReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"Santa Fe","input_name1":"on"}}},"chart_type":"none"}',
            ],
            [
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"badReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"<script>alert(\'stuff\');</script>","input_name1":"on"}}},"chart_type":"none"}',
                '{"display_columns":[{"name":"billing_address_city","label":"Billing City","table_key":"self"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"badReport","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"billing_address_city","table_key":"self","qualifier_name":"equals","input_name0":"alert(\'stuff\');","input_name1":"on"}}},"chart_type":"none"}',
            ],
        ];
    }
}
