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

require_once 'modules/Reports/SavedReport.php';

/**
 * @group Bug45686
 */
class Bug45686Test extends TestCase
{
    /**
     * @var string|mixed
     */
    public $reportDefs;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->reportDefs = <<<DEFS
{"display_columns":[{"name":"account_type","label":"<s>Type</s>","table_key":"self"}],"module":"Accounts",
"group_defs":[{"name":"account_type","label":"<s>Type</s>","table_key":"self","type":"enum"}],
"summary_columns":[{"name":"count","label":"<s>ZZZ</s>","field_type":"","group_function":"count","table_key":"self"},
{"name":"account_type","label":"<s>Type</s>","table_key":"self"}],"report_name":"<s>test</s>","chart_type":"hBarF","do_round":1,
"chart_description":"<s>chart</s>","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1",
"report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"<s>Accounts</s>"}},
"filters_def":{"Filter_1":{"operator":"AND"}}}
DEFS;
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM saved_reports WHERE assigned_user_id='{$GLOBALS['current_user']->id}'");
        SugarTestHelper::tearDown();
    }

    /**
     * Test that report ctor strips HTML from labels
     */
    public function testHtmlInReports()
    {
        $rep = new Report($this->reportDefs);
        $this->assertNotContains('<s>', $rep->report_def_str);
        $this->assertNotContains('</s>', $rep->report_def_str);
    }

    /**
     * Test that SavedReport save strips HTML from labels
     */
    public function testHtmlInSavedReports()
    {
        $rep = new SavedReport();
        $rep->save_report(-1, $GLOBALS['current_user']->id, '<s>' . to_html('<s>TEST</s>') . '</s>', 'Accounts', 'summary', $this->reportDefs, 0, 1);
        $id = $rep->id;
        $rep = new SavedReport();
        $rep->retrieve($id);
        $this->assertNotContains('<s>', $rep->name);
        $this->assertNotContains('</s>', $rep->name);
    }
}
