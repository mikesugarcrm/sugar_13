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
use Sugarcrm\Sugarcrm\Reports\Types;

/**
 * Test https://sugarcrm.atlassian.net/browse/CA-1429
 * (SI 90360) Field values in reports are not clickable
 */
class BugCA1429Test extends TestCase
{
    private $reportBean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->reportBean = BeanFactory::newBean('Reports');
        $this->reportBean->report_def = [
            'display_columns' => [
                0 => [
                    'name' => 'test_c',
                    'label' => 'test',
                    'table_key' => 'Accounts:opportunities',
                ],
                1 => [
                    'name' => 'name',
                    'label' => 'Opportunity Name',
                    'table_key' => 'Accounts:opportunities',
                ],
            ],
            'module' => 'Accounts',
            'group_defs' => [],
            'summary_columns' => [],
            'report_name' => 'CA-1429',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Accounts',
                    'module' => 'Accounts',
                    'label' => 'Accounts',
                    'dependents' => [],
                ],
                'Accounts:opportunities' => [
                    'name' => 'Accounts  >  Opportunity',
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'opportunities',
                        'relationship_name' => 'accounts_opportunities',
                        'bean_is_lhs' => true,
                        'link_type' => 'many',
                        'label' => 'Opportunity',
                        'module' => 'Opportunities',
                        'table_key' => 'Accounts:opportunities',
                    ],
                    'dependents' => [],
                    'module' => 'Opportunities',
                    'label' => 'Opportunity',
                ],
            ],
            'filters_def' => [],
        ];

        BeanFactory::registerBean($this->reportBean);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        BeanFactory::unregisterBean($this->reportBean);
    }

    /**
     * Test create_select function creates the correct id field
     */
    public function testCreateSelectId()
    {
        $report = new SubpanelFromReports($this->reportBean);
        $report->create_select();

        $this->assertEquals([
            0 => 'accounts.id primaryid
',
            1 => 'accounts.name accounts_name
',
        ], $report->select_fields, 'Should return the id from primary table');
    }
}
