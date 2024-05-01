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

class SavedReportTest extends TestCase
{
    protected $dbMock = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        global $moduleList, $modListHeader, $app_list_strings;
        require 'config.php';
        require 'include/modules.php';
        require_once 'modules/Reports/config.php';
        $GLOBALS['report_modules'] = getAllowedReportModules($modListHeader);
        $this->dbMock = SugarTestHelper::setUp('mock_db');
    }

    protected function tearDown(): void
    {
        $id = static::$reportId;
        if ($id) {
            $GLOBALS['db']->query("DELETE FROM saved_reports WHERE id='{$id}'");
        }

        unset($GLOBALS['report_modules']);
        SugarTestHelper::tearDown();
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    /**
     * Test of SavedReport's getLastRunDate
     */
    public function testGetLastRunDate()
    {
        $timedate = TimeDate::getInstance();
        $now = db_convert("'" . $timedate->nowDb() . "'", 'datetime');

        $report = new SavedReport();

        $mock = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('beansAreLoaded')->willReturn(true);
        $relBean = new SugarBean();
        $relBean->date_modified = $now;
        $mock->method('getBeans')->willReturn([$relBean]);
        $report->last_run_date_link = $mock;
        //Set values to other relate fields to ensure nothing tries to populate them
        $report->report_cache_id = '-1';

        $report->fill_in_relationship_fields();

        $this->assertEquals($now, $report->last_run_date, 'incorrect last_run_date');
    }

    /**
     * Make sure that the array returned is a subset of `GLOBALS['report_modules']`
     * and contain values from `$app_list_strings['moduleList']`
     */
    public function test_getModulesDropdown()
    {
        global $app_list_strings;
        $allowed_modules = getModulesDropdown();
        foreach ($allowed_modules as $key => $val) {
            $this->assertArrayHasKey($key, $GLOBALS['report_modules']);
            $this->assertEquals($val, $app_list_strings['moduleList'][$key]);
        }
    }

    /**
     * Remove ACL restricted modules from dropdown
     */
    public function testGetModulesDropdownACLs()
    {
        $role = SugarTestACLUtilities::createRole('OnlyContacts' . create_guid(), ['Contacts'], ['access']);
        SugarTestACLUtilities::setupUser($role);
        $aclData = [
            'module' => [
                'access' => [
                    'aclaccess' => ACL_ALLOW_DISABLED,
                ],
            ],
        ];
        ACLAction::setACLData($GLOBALS['current_user']->id, 'Accounts', $aclData);

        $allowed_modules = getModulesDropdown();
        $this->assertArrayNotHasKey('Accounts', $allowed_modules);
    }

    /**
     * Checks if last run date is on the bean
     */
    public function testLastRunDate()
    {
        $bean = BeanFactory::newBean('Reports');
        $this->assertArrayHasKey('last_run_date', $bean->field_defs);
    }

    /**
     * to test and ensure the report object is an instance of basic
     */
    public function testReportBeanType()
    {
        $report = new SavedReport();
        $this->assertInstanceOf('Basic', $report, 'report should be a Basic instance');
    }

    /**
     * Data provider for testReportBeanVardefs()
     * @return array reset, type, name
     */
    public function reportBeanVardefsProvider()
    {
        return [
            // basic
            ['fields', 'description'],
            ['indices', 'id'],
            ['relationships', 'reports_modified_user'],

            // assignable
            ['fields', 'assigned_user_id'],
            ['indices', 'assigned_user_id'],
            ['relationships', 'reports_assigned_user'],

            // reports
            ['fields', 'module'],
            ['indices', 'idx_savedreport_module'],
            ['relationships', 'reports_last_run_date'],

            // team_security
            ['fields', 'team_id'],
            ['indices', 'team_set_saved_reports'],
            ['relationships', 'reports_team_count_relationship'],
        ];
    }

    /**
     * to test that the definitions from various templates exist in the report dictionary
     * @dataProvider reportBeanVardefsProvider
     */
    public function testReportBeanVardefs($type, $name)
    {
        $def = $this->getReportVardef();
        $this->assertArrayHasKey($name, $def[$type], 'Missing ' . $type . ':' . $name);
    }

    /**
     * To get vardefs of Reports
     * @return array
     */
    protected function getReportVardef()
    {
        static $def = null;
        if (empty($def)) {
            global $dictionary;
            unset($dictionary['SavedReport']);
            require 'modules/Reports/vardefs.php';
            $def = $dictionary['SavedReport'];
        }
        return $def;
    }

    /**
     * dataProvider for ::testSavedReports
     * @return array
     */
    public function getSavedReports(): array
    {
        return [
            [
                'report' => [
                    'id' => -1,
                    'created_by' => 1,
                    'name' => 'Test',
                    'module' => 'Accounts',
                    'report_type' => 'summary',
                    'content' => '{}',
                    'deleted' => 0,
                    'team_id' => 1,
                    'chart_type' => 'vBarF',
                    'assigned_user_id' => 1,
                    'description' => 'testDescription',
                ],
            ],
        ];
    }

    /**
     * Tests out if description is overwritten if does not exist
     * @dataProvider getSavedReports
     * @param array $report definitions.
     */
    public function testSavedReports($report)
    {
        $rep = new SavedReport();
        $rep->save_report(
            $report['id'],
            $report['created_by'],
            $report['name'],
            $report['module'],
            $report['report_type'],
            $report['content'],
            $report['deleted'],
            $report['team_id'],
            $report['chart_type'],
            $report['assigned_user_id']
        );
        $id = $rep->id;
        static::$reportId = $id;
        $rep->retrieve($id);
        $this->assertEmpty($rep->description);

        $rep->save_report(
            $report['id'],
            $report['created_by'],
            $report['name'],
            $report['module'],
            $report['report_type'],
            $report['content'],
            $report['deleted'],
            $report['team_id'],
            $report['chart_type'],
            $report['assigned_user_id'],
            $report['description']
        );
        $rep->retrieve($id);
        $this->assertEquals('testDescription', $rep->description);

        $rep->save_report(
            $report['id'],
            $report['created_by'],
            $report['name'],
            $report['module'],
            $report['report_type'],
            $report['content'],
            $report['deleted'],
            $report['team_id'],
            $report['chart_type'],
            $report['assigned_user_id']
        );
        $rep->retrieve($id);
        $this->assertEquals('testDescription', $rep->description);
    }


    /**
     * Tests that out of the box reports, by id, exist in the system
     */
    public function testStockReportsExistById()
    {
        $stockGuids = [
            'sugar-serve' => [
                'c2908254-7606-11e9-a121-f218983a1c3e' => 'New Cases by Business Center by Week',
                'c2908fc4-7606-11e9-a83a-f218983a1c3e' => 'Recently Created Cases',
                'c290929e-7606-11e9-a555-f218983a1c3e' => 'New Cases by Customer Tier by Week',
                'c290953c-7606-11e9-b083-f218983a1c3e' => 'Open Cases by Customer Tier and Priority',
                'c29097d0-7606-11e9-ac35-f218983a1c3e' => 'Total Cases Resolved this Month by Business Center',
                'c2909a50-7606-11e9-914a-f218983a1c3e' => 'Total Cases Resolved this Month by Agent',
                'c2909cd0-7606-11e9-9955-f218983a1c3e' => 'List of Recently Resolved Cases',
                'c2909f50-7606-11e9-b00e-f218983a1c3e' => 'My Cases Resolved this Month by Week',
                'c290a1da-7606-11e9-80e5-f218983a1c3e' => 'My Cases Due Today and Overdue',
                'c290a45a-7606-11e9-9663-f218983a1c3e' => 'All Cases Due Today and Overdue',
                'c290a6da-7606-11e9-a76d-f218983a1c3e' => 'My Open Cases by Followup Date',
                'c290a950-7606-11e9-a526-f218983a1c3e' => 'All Open Cases by Followup Date',
                'c290abda-7606-11e9-9f3e-f218983a1c3e' => 'My Open Cases by Status',
                'c290ae50-7606-11e9-9cb2-f218983a1c3e' => 'My Cases in the Last Week by Status',
                'c290b0da-7606-11e9-81f9-f218983a1c3e' => 'Status of Open Tasks Assigned by Me',
            ],
            'ootb-sample' => [
                'efc0ea32-7905-11e9-8941-f218983a1c3e' => 'Accounts by Assigned to User',
                'efc0fedc-7905-11e9-a594-f218983a1c3e' => 'Accounts By Type By Industry',
                'efc101c0-7905-11e9-8a2f-f218983a1c3e' => 'Accounts Created by User by Month',
                'efc1045e-7905-11e9-ad68-f218983a1c3e' => 'My Customers by Area',
                'efc106fc-7905-11e9-980e-f218983a1c3e' => 'My New Customer Accounts',
                'efc10986-7905-11e9-9f44-f218983a1c3e' => 'Open Calls',
                'efc10c10-7905-11e9-a54a-f218983a1c3e' => 'Upcoming Calls Scheduled by Account',
                'efc10e90-7905-11e9-89f5-f218983a1c3e' => 'Weekly Calls Held this Quarter by User',
                'efc11110-7905-11e9-a9d4-f218983a1c3e' => 'Weekly Calls Scheduled this Quarter by User',
                'efc1139a-7905-11e9-b30a-f218983a1c3e' => 'Contacts Created by User by Month',
                'efc11610-7905-11e9-917c-f218983a1c3e' => 'Leads By Lead Source',
                'efc1189a-7905-11e9-921b-f218983a1c3e' => 'Leads Converted - Lost by Month',
            ],
            'ent-only-sample' => [
                'efc14fd6-7905-11e9-949f-f218983a1c3e' => 'Opportunities Created by Lead Source by Month',
                'efc15260-7905-11e9-82f9-f218983a1c3e' => 'Opportunities with No Calls, Meetings, Tasks, or Emails',
                'efc154e0-7905-11e9-b436-f218983a1c3e' => 'Pipeline By Team By User',
                'efc15774-7905-11e9-8553-f218983a1c3e' => 'Pipeline By Type By Team',
                'efc159f4-7905-11e9-bc5f-f218983a1c3e' => 'Sales per Quarter by Lead Source',
                'efc15c7e-7905-11e9-a206-f218983a1c3e' => 'Top Sales Reps This Quarter',
                'efc15efe-7905-11e9-931e-f218983a1c3e' => 'Total Sales by Quarter',
                'efc1617e-7905-11e9-8280-f218983a1c3e' => 'Total Sales by Quarter by User',
                'efc16412-7905-11e9-8174-f218983a1c3e' => 'Wins and Losses by Users this Quarter',
            ],
            'data-privacy' => [
                '61f5e80a-7b40-11e9-ad44-f218983a1c3e' => 'Count of Leads (unconverted) by Country',
                '61f5f584-7b40-11e9-9acf-f218983a1c3e' => 'List of Leads with no Consent',
                '61f5f8fe-7b40-11e9-96c8-f218983a1c3e' => 'Count of Targets by Country',
            ],
            'old-service' => [
                '5d675032-7b52-11e9-990d-f218983a1c3e' => 'Open Bugs by User by Status',
                '5d6752b2-7b52-11e9-acfc-f218983a1c3e' => 'Summary of Bugs by Priority by Week Created',
                '5d67553c-7b52-11e9-bcf2-f218983a1c3e' => 'Summary of Bugs by Source by Week Created',
                '5d6757c6-7b52-11e9-9ead-f218983a1c3e' => 'Summary of Bugs by Status by Week Created',
                '5d675a50-7b52-11e9-83cb-f218983a1c3e' => 'Cases with No Calls, Meetings, Tasks, or Emails',
                '5d675cda-7b52-11e9-9155-f218983a1c3e' => 'My Open Cases by Priority',
                '5d675f5a-7b52-11e9-9420-f218983a1c3e' => 'New Cases By Month',
                '5d6761ee-7b52-11e9-aa07-f218983a1c3e' => 'Open Cases By Month By User',
                '5d676464-7b52-11e9-90b4-f218983a1c3e' => 'Open Cases by User by Priority',
            ],
        ];

        foreach ($stockGuids as $type => $list) {
            $sql = sprintf(
                'SELECT id, name FROM saved_reports WHERE id in (%s)',
                "'" . implode("','", array_keys($list)) . "'"
            );

            $conn = DBManagerFactory::getConnection();
            $data = $conn->executeQuery($sql)->fetchAllAssociative();

            $this->assertCount(15, $data);

            foreach ($data as $row) {
                $this->assertArrayHasKey($row['id'], $list[$type]);
                $this->assertSame($row['name'], $list[$row['id']]);
            }
        }
    }
}
