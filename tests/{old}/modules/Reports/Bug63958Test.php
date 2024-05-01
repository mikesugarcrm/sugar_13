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
 * Use subselect team joins instead of inner joins for non-admin users
 */
class Bug63958Test extends TestCase
{
    /**
     * @group 63958
     * @return void
     */
    public function testQueryTeamJoin()
    {
        $reportDefs = <<<DEFS
{"display_columns":[{"name":"full_name","label":"Name","table_key":"Accounts:contacts"}],"module":"Accounts","group_defs":[],"summary_columns":[],"report_name":"test report","do_round":1,"numerical_chart_column":"","numerical_chart_column_type":"","assigned_user_id":"seed_will_id","report_type":"tabular","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"},"Accounts:contacts":{"name":"Accounts > Contacts","parent":"self","link_def":{"name":"contacts","relationship_name":"accounts_contacts","bean_is_lhs":true,"link_type":"many","label":"Contacts","module":"Contacts","table_key":"Accounts:contacts"},"dependents":["display_cols_row_1"],"module":"Contacts","label":"Contacts"}},"filters_def":{"Filter_1":{"operator":"AND"}},"chart_type":"none"}
DEFS;

        $report = new Report($reportDefs);
        $report->run_summary_query();

        $this->assertContains('AND accounts.team_set_id IN', $report->where, 'Verify the team join in the WHERE clause');
        $this->assertNotEmpty($report->summary_result, 'Verify the query was valid by testing for summary results');
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        // Execute this test as a non-admin user
        SugarTestHelper::setUp('current_user', [true, 0]);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }
}
