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

class Bug50000Test extends TestCase
{
    public $reporter;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['mod_strings'] = return_module_language('en_us', 'Reports');
        require_once 'modules/Reports/templates/templates_reports.php';
        $this->reporter = new Bug50000MockReporter();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['mod_strings']);
        unset($this->reporter);
    }

    /**
     * @dataProvider bug50000DataProvider
     */
    public function testColumnLabelsAreCorrectForMatrixReport($report_def, $header_row, $expected)
    {
        $this->reporter->report_def = $report_def;

        $this->assertSame($expected, getHeaderColumnNamesForMatrix($this->reporter, $header_row, ''));
    }

    /**
     * Data provider for testColumnLabelsAreCorrectForMatrixReport()
     * @return array report_def, header_row, expected
     */
    public function bug50000DataProvider()
    {
        $strings = return_module_language('en_us', 'Reports');
        return [
            [
                [
                    'group_defs' => [
                        [
                            'label' => 'User Name',
                            'name' => 'user_name',
                            'table_key' => 'Opportunities:assigned_user_link',
                            'type' => 'user_name',
                        ],
                        [
                            'label' => 'Name',
                            'name' => 'name',
                            'table_key' => 'Opportunities:accounts',
                            'type' => 'name',
                        ],
                    ],
                ],
                ['User Name', 'Account Name', 'Count'],
                ['User Name', 'Account Name', $strings['LBL_REPORT_GRAND_TOTAL']],
            ],
        ];
    }
}


class Bug50000MockReporter
{
    public $report_def;
    public $group_defs_Info;
}
