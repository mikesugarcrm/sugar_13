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

require_once 'modules/Reports/templates/templates_list_view.php';

/**
 * @ticket 53357
 */
class Bug53357Test extends TestCase
{
    protected $opportunity_id;
    protected $amount = 123456;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        global $beanList, $beanFiles;
        require 'include/modules.php';

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);

        // create an opportunity which will be used to create a report
        $opportunity = new Opportunity();
        $opportunity->assigned_user_id = $GLOBALS['current_user']->id;
        $opportunity->amount_usdollar = $this->amount;

        $this->opportunity_id = $opportunity->save();

        /* amount_usdollar gets smashed during save due to forecasting logic
         * we just want it to have a value, so update database directly */
        $opportunity->db->query('update opportunities set amount_usdollar=' . $this->amount . " where id='" . $this->opportunity_id . "'");
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $opportunity = new Opportunity();
        $opportunity->mark_deleted($this->opportunity_id);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset(
            $GLOBALS['current_user'],
            $GLOBALS['beanFiles'],
            $GLOBALS['beanList'],
            $GLOBALS['app_list_strings']
        );
    }

    /**
     * Ensure report Grand Totals are correctly calculated and displayed
     */
    public function testGrandTotalsAreCalculated()
    {
        // create matrix report with the following spec:
        // Module: Opportunities
        // Display columns: Opportunity amount
        // Group By columns: Sales Stage and Assigned User Name
        // Filter: the only opportunity which is created in setUp() method
        $report_def = [
            'module' => 'Opportunities',
            'group_defs' => [
                [
                    'name' => 'user_name',
                    'label' => 'User Name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ],
                [
                    'name' => 'sales_stage',
                    'label' => 'Sales Stage',
                    'table_key' => 'self',
                ],
            ],
            'display_columns' => [],
            'summary_columns' => [
                [
                    'name' => 'user_name',
                    'label' => 'User Name',
                    'table_key' => 'Opportunities:assigned_user_link',
                ],
                [
                    'name' => 'sales_stage',
                    'label' => 'Sales Stage',
                    'table_key' => 'self',
                ],
                [
                    'name' => 'amount_usdollar',
                    'label' => 'AVG: Amount',
                    'group_function' => 'avg',
                    'table_key' => 'self',
                ],
            ],
            'report_type' => 'summary',
            'layout_options' => '2x2',
            'full_table_list' => [
                'self' => [
                    'value' => 'Opportunities',
                    'module' => 'Opportunities',
                ],
                'Opportunities:assigned_user_link' => [
                    'name' => 'Opportunities  >  Assigned to User',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'assigned_user_link',
                        'relationship_name' => 'opportunities_assigned_user',
                        'link_type' => 'one',
                        'module' => 'Users',
                        'table_key' => 'Opportunities:assigned_user_link',
                    ],
                    'module' => 'Users',
                ],
            ],
            'filters_def' => [
                'Filter_1' => [
                    'operator' => 'AND',
                    0 => [
                        'name' => 'id',
                        'table_key' => 'self',
                        'qualifier_name' => 'is',
                        'input_name0' => $this->opportunity_id,
                    ],
                ],
            ],
        ];

        $json = getJSONobj();
        $report = new Report($json->encode($report_def));

        $args = [];

        ob_start();
        template_summary_list_view($report, $args);
        $output = ob_get_contents();
        ob_end_clean();


        global $locale;

        // prepare expected substring (the formatted value of opportunity amount)
        $substring = currency_format_number($this->amount, [
            'currency_id' => $locale->getPrecedentPreference('currency'),
            'convert' => true,
            'currency_symbol' => $locale->getPrecedentPreference('default_currency_symbol'),
        ]);

        // Opportunity amount must appear 4 times in report output:
        // 1. The amount of opportunity itself
        // 2. Grand Total by Sales Stage
        // 3. Grand Total by User
        // 4. The very Grand Total
        $this->assertEquals(4, substr_count($output, $substring));
    }
}
