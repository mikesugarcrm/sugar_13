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

require_once 'soap/SoapHelperFunctions.php';

class SoapHelperFunctionsTest extends TestCase
{
    /**
     * @covers get_report_value()
     * @return void
     */
    public function testGetReportValueHasCorrectOutputFormat()
    {
        $this->expectNotToPerformAssertions();

        $data = get_report_value($this->createBr8874Report());
        if (!isset($data['output_list']) || !is_array($data['output_list']) || count($data['output_list']) === 0) {
            $this->fail('No output_list');
        }
        foreach ($data['output_list'] as $entry) {
            foreach (['id', 'module_name', 'name_value_list'] as $key) {
                if (!isset($entry[$key])) {
                    $this->fail('Entry has no "' . $key . '" property');
                }
            }
        }
    }

    /**
     * @return object
     */
    private function createBr8874Report(): object
    {
        /**
         * Report defs for generating the report
         */
        $reportDef = [
            'display_columns' => [
                0 => [
                    'name' => 'id',
                    'label' => 'ID',
                    'table_key' => 'self',
                ],
                1 => [
                    'name' => 'name',
                    'label' => 'Name',
                    'table_key' => 'self',
                ],
            ],
            'module' => 'Users',
            'group_defs' => [],
            'summary_columns' => [],
            'report_name' => 'BR-8874 test report',
            'chart_type' => 'none',
            'do_round' => 1,
            'numerical_chart_column' => '',
            'numerical_chart_column_type' => '',
            'assigned_user_id' => '1',
            'report_type' => 'tabular',
            'full_table_list' => [
                'self' => [
                    'value' => 'Users',
                    'module' => 'Users',
                    'label' => 'Users',
                    'dependents' => [],
                ],
            ],
            'filters_def' => [],
        ];

        $stub = new stdClass();
        $stub->content = htmlentities(\JSON::encode($reportDef), ENT_COMPAT);
        return $stub;
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $user = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['current_user'] = $user;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }
}
