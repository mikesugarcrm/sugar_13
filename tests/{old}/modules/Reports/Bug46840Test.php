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

class Bug46840Test extends TestCase
{
    private $reportInstance;

    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
    }

    public function testQuarter()
    {
        // Summary report for Opportunities grouped by quarter
        $report = new Report('{"display_columns":[],"module":"Opportunities","group_defs":[{"name":"date_closed","label":"Quarter: Expected Close Date","column_function":"quarter","qualifier":"quarter","table_key":"self","type":"date"}],"summary_columns":[{"name":"date_closed","label":"Quarter: Expected Close Date","column_function":"quarter","qualifier":"quarter","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"test report","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","full_table_list":{"self":{"value":"Opportunities","module":"Opportunities","label":"Opportunities"}},"filters_def":{"Filter_1":{"operator":"AND"}}}');
        $report->run_summary_query();
        $this->assertNotEmpty($report->summary_result);
    }
}
