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
 * Bug52796Test.php
 * This unit test attempts to simulate a row/column report against the Opportunities module to select
 * the amount and amount_usdollar fields, and check if amount_usdollar returned value is calculated
 * using the latest conversion rate for the users currency.
 *
 * @author avucinic
 */
class Bug52796Test extends TestCase
{
    private $reportInstance;
    private $saved = [];

    protected function setUp(): void
    {
        // Change default currency to check conversion
        global $sugar_config;
        SugarTestHelper::setUp('beanList');
        $user = SugarTestHelper::setUp('current_user');
        $user->setPreference('currency_show_preferred', true);
        $currency = SugarTestCurrencyUtilities::createCurrency('CC', 'C', 'CCC', 0.732);
        $this->saved['currency'] = $sugar_config['currency'];
        $sugar_config['currency'] = $currency->id;
    }

    protected function tearDown(): void
    {
        // Set back the default currency value
        global $sugar_config;
        $sugar_config['currency'] = $this->saved['currency'];
        $this->reportInstance = null;
        $this->saved = null;
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestHelper::tearDown();
    }

    /**
     * testReportCurrencyConversion
     * This method tests if conversion from dollar to another currency (Euro for tests) works after change of base_rate
     */
    public function testReportCurrencyConversion()
    {
        // FIXME TY-1320: investigate why this test fails
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $rli->opportunity_id = $id;
        $rli->save();

        // Initialize an opportunities report with 3 columns
        $this->reportInstance = new Report();
        $this->reportInstance->clear_results();
        $this->reportInstance->from = "\n FROM opportunities ";
        $this->reportInstance->table_name = 'opportunities';
        $this->reportInstance->focus = new Opportunity();

        // Report defs
        $this->reportInstance->report_def['display_columns'] = [
            0 => [
                'name' => 'name',
                'label' => 'Opportunity Name',
                'table_key' => 'self',
                'group_function' => '',
            ],
            1 => [
                'name' => 'amount',
                'label' => 'Opportunity Amount',
                'table_key' => 'self',
                'group_function' => '',
            ],
            2 => [
                'name' => 'amount_usdollar',
                'label' => 'Amount',
                'table_key' => 'self',
                'group_function' => '',
            ],
        ];

        // All fields
        $this->reportInstance->all_fields = [
            'self:name' => [
                'name' => 'name',
                'vname' => 'LBL_OPPORTUNITY_NAME',
                'type' => 'name',
                'dbType' => 'varchar',
                'len' => '50',
                'unified_search' => true,
                'comment' => 'Name of the opportunity',
                'merge_filter' => 'selected',
                'importable' => 'required',
                'required' => true,
                'module' => 'Opportunities',
                'real_table' => 'opportunities',
                'rep_rel_name' => 'name_0',
            ],
            'self:amount' => [
                'name' => 'amount',
                'vname' => 'LBL_AMOUNT',
                'type' => 'currency',
                'dbType' => 'double',
                'comment' => 'Unconverted amount of the opportunity',
                'importable' => 'required',
                'duplicate_merge' => '1',
                'required' => true,
                'options' => 'numeric_range_search_dom',
                'enable_range_search' => true,
                'module' => 'Opportunities',
                'real_table' => 'opportunities',
                'rep_rel_name' => 'amount_0',
            ],
            'self:amount_usdollar' => [
                'name' => 'amount_usdollar',
                'vname' => 'LBL_AMOUNT_USDOLLAR',
                'type' => 'currency',
                'group' => 'amount',
                'dbType' => 'double',
                'disable_num_format' => true,
                'duplicate_merge' => '0',
                'audited' => true,
                'comment' => 'Formatted amount of the opportunity',
                'studio' => [
                    'wirelesseditview' => false,
                    'wirelessdetailview' => false,
                    'editview' => false,
                    'detailview' => false,
                    'quickcreate' => false,
                ],
                'module' => 'Opportunities',
                'real_table' => 'opportunities',
                'rep_rel_name' => 'amount_usdollar_0',
            ],
        ];

        $db = DBManagerFactory::getInstance();

        // Report select fields
        $this->reportInstance->select_fields = [
            0 => $db->convert('opportunities.id', 'ifnull') . ' primaryid',
            1 => $db->convert('opportunities.name', 'ifnull') . ' opportunities_name',
            2 => 'opportunities.amount opportunities_amount ',
            3 => $db->convert('opportunities.currency_id', 'ifnull') . ' OPPORTUNITIES_AMOUNT_C9AC638',
            4 => 'opportunities.amount_usdollar OPPORTUNITIES_AMOUNT_UBC8F31',
        ];
        // Create and execute report query
        $this->reportInstance->create_query('query', 'select_fields');
        $this->reportInstance->execute_query('query');

        // Change the Euro currency conversion_rate
        $currency = new Currency();
        $currency->retrieve($currency->retrieveIDBySymbol('C'));
        $oldConversionRate = $currency->conversion_rate;
        $currency->conversion_rate = 0.0123;
        $currency->save();

        // Loop through all results, and check if after conversion_rate change, the amounts are calculated properly
        while ($row = $this->reportInstance->get_next_row('result', 'display_columns')) {
            // Extract the amount in dollars from the first row and strip commas
            preg_match('/([0-9]+,)*[0-9]+\.[0-9]+/', $row['cells'][1], $matches);
            $dollars = str_replace(',', '', $matches[0]);

            // Extract the calculated amount in euros from the second row and strip commas
            preg_match('/([0-9]+,)*[0-9]+\.[0-9]+/', $row['cells'][2], $matches);
            $euros = str_replace(',', '', $matches[0]);

            $actual = $euros;

            $this->assertEquals($dollars, $actual, 'Reports are not processing the amount_usdollar field using latest conversion_rates.' . var_export($row, true));
        }

        // Rollback the old conversion_rate after the test
        $currency->conversion_rate = $oldConversionRate;
        $currency->save();
    }
}
