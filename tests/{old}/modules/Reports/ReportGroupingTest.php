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
 * @covers Report
 */
class ReportGroupingTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);

        // create account before custom field is created
        SugarTestAccountUtilities::createAccount();

        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('custom_field', [
            'Accounts',
            [
                'name' => 'checkbox',
                'type' => 'bool',
            ],
        ]);

        // create account after custom field is created
        SugarTestAccountUtilities::createAccount();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testAllTasksAreDisplayed()
    {
        global $current_user;

        $definition = [
            'display_columns' => [],
            'module' => 'Accounts',
            'group_defs' => [
                [
                    'name' => 'checkbox_c',
                    'table_key' => 'self',
                ],
            ],
            'summary_columns' => [],
            'report_type' => 'summary',
            'full_table_list' => [
                'self' => [
                    'module' => 'Accounts',
                ],
                'Accounts:created_by_link' => [
                    'parent' => 'self',
                    'link_def' => [
                        'name' => 'created_by_link',
                        'relationship_name' => 'accounts_created_by',
                        'bean_is_lhs' => false,
                        'link_type' => 'one',
                        'module' => 'Users',
                        'table_key' => 'Accounts:created_by_link',
                    ],
                    'module' => 'Users',
                ],
            ],
            'filters_def' => [
                [
                    'operator' => 'AND',
                    [
                        'name' => 'id',
                        'table_key' => 'Accounts:created_by_link',
                        'qualifier_name' => 'is',
                        'input_name0' => $current_user->id,
                    ],
                ],
            ],
        ];

        $report = new Report(json_encode($definition));
        $report->run_summary_query();

        $row1 = $report->get_summary_next_row();
        $this->assertIsArray($row1);
        $this->assertEquals(2, $row1['count'], 'Summary row should contain 2 records');

        $row2 = $report->get_summary_next_row();
        $this->assertEmpty($row2, 'There should not be second row');
    }
}
