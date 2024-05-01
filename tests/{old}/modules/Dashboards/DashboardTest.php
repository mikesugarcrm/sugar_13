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

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestDashboardUtilities::removeAllCreatedDashboards();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    public function testGetDashboards()
    {
        $dashboard = SugarTestDashboardUtilities::createDashboard(
            '',
            [
                'dashboard_module' => 'test_module',
                'view_name' => 'test_view',
            ]
        );

        $dashboards = $dashboard->getDashboardsForUser(
            $GLOBALS['current_user'],
            [
                'dashboard_module' => 'test_module',
                'view_name' => 'test_view',
            ]
        );

        $this->assertEquals(1, safeCount($dashboards['records']));
    }

    /**
     * Checking legacy behavior of 'view'
     *
     * 1. Creating dashboard with 'view_name'
     * 2. Retrieving dashboard for user with 'view'
     * 3. Asserting that 'view' and 'view_name' equal to original 'view_name'
     */
    public function testGetDashboardsForUser()
    {
        $expected = SugarTestDashboardUtilities::createDashboard(
            '',
            [
                'assigned_user_id' => $GLOBALS['current_user']->id,
                'dashboard_module' => 'test_module',
                'view_name' => 'test_view',
            ]
        );

        $actual = $expected->getDashboardsForUser(
            $GLOBALS['current_user'],
            [
                'dashboard_module' => 'test_module',
                'view' => 'test_view',
            ]
        );

        $this->assertNotEmpty($actual);
        $actual = reset($actual['records']);
        $this->assertEquals($expected->id, $actual->id);
        $this->assertEquals($expected->view_name, $actual->view);
        $this->assertEquals($expected->view_name, $actual->view_name);
    }

    public function testSaveDashboardDefaults()
    {
        $dashboard = SugarTestDashboardUtilities::createDashboard(
            '',
            [
                'name' => 'Test',
                'dashboard_module' => 'test_module',
                'view_name' => 'test_list',
            ]
        );
        $this->assertEquals($GLOBALS['current_user']->id, $dashboard->assigned_user_id);
        $this->assertEquals($GLOBALS['current_user']->getPrivateTeamID(), $dashboard->team_id);
    }


    /**
     * Tests that the Agent Workbench overview dashboard exists and
     * contains what we want it to contain
     */
    public function testAgentWorkbenchDashboardExists()
    {
        // Test that our dashboard metadata has the id we expect
        $dashboard = require 'modules/Home/dashboards/agent-dashboard/agent-dashboard.php';
        $this->assertArrayHasKey('id', $dashboard);
        $this->assertSame('c108bb4a-775a-11e9-b570-f218983a1c3e', $dashboard['id']);

        // Now get the data from the database
        $id = $dashboard['id'];
        $sql = "SELECT name, dashboard_module, metadata FROM dashboards WHERE id = '$id'";
        $conn = DBManagerFactory::getConnection();
        $data = $conn->executeQuery($sql)->fetchAllAssociative();
        $this->assertCount(1, $data);

        // Get the first row of data
        $row = $data[0];


        // Verify our name and module
        $this->assertSame('LBL_AGENT_WORKBENCH', $row['name']);
        $this->assertSame('Home', $row['dashboard_module']);

        // Now work the metadata for this dashboard
        $meta = json_decode($row['metadata'], true);
        $this->assertArrayHasKey('tabs', $meta);
        $this->assertCount(2, $meta['tabs']);

        $overview = $meta['tabs'][0];

        // Focus on the overview tab for now
        $this->assertArrayHasKey('name', $overview);
        $this->assertSame('LBL_AGENT_WORKBENCH_OVERVIEW', $overview['name']);

        $this->assertArrayHasKey('components', $overview);
        $components = $overview['components'];
        $this->assertCount(1, $components);

        $component = $components[0];
        $this->assertArrayHasKey('rows', $component);

        // These are the three rows of three dashlets
        $rows = $component['rows'];
        $this->assertCount(3, $rows);
        $this->assertCount(3, $rows[0]);
        $this->assertCount(3, $rows[1]);
        $this->assertCount(3, $rows[2]);

        // Now test one of the dashlets in each row
        $this->assertSame('c290a6da-7606-11e9-a76d-f218983a1c3e', $rows[0][0]['view']['reportId']);
        $this->assertSame('My Open Cases by Followup Date', $rows[0][0]['view']['saved_report']);

        $this->assertSame('c290abda-7606-11e9-9f3e-f218983a1c3e', $rows[1][0]['view']['reportId']);
        $this->assertSame('My Open Cases by Status', $rows[1][0]['view']['saved_report']);

        $this->assertSame('c290b0da-7606-11e9-81f9-f218983a1c3e', $rows[2][2]['view']['reportId']);
        $this->assertSame('Status of Open Tasks Assigned by Me', $rows[2][2]['view']['saved_report']);
    }

    /**
     * @param string $id ID of the OOTB Default Dashboard.
     * @param string $name Name (actually translatable label) of the dashboard.
     * @dataProvider providerOOTBDefaultDashboards
     */
    public function testOOTBDefaultDashboardsExist(string $id, string $name)
    {
        $sql = "SELECT name FROM dashboards WHERE id = '$id'";
        $conn = DBManagerFactory::getConnection();
        $data = $conn->executeQuery($sql)->fetchAllAssociative();
        $this->assertCount(1, $data);
        $actualName = $data[0]['name'];
        $this->assertSame($name, $actualName);
    }

    public function providerOOTBDefaultDashboards(): array
    {
        return [
            ['5d673e80-7b52-11e9-833f-f218983a1c3e', 'LBL_BUGS_LIST_DASHBOARD'],
            ['5d6724f4-7b52-11e9-a725-f218983a1c3e', 'LBL_BUGS_RECORD_DASHBOARD'],
            ['5d673c00-7b52-11e9-871e-f218983a1c3e', 'LBL_CASES_LIST_DASHBOARD'],
            ['5d672a1c-7b52-11e9-8ddb-f218983a1c3e', 'LBL_LEADS_LIST_DASHBOARD'],
            ['5d670ec4-7b52-11e9-b9e0-f218983a1c3e', 'LBL_LEADS_RECORD_DASHBOARD'],
            ['5d672ca6-7b52-11e9-a6f5-f218983a1c3e', 'LBL_OPPORTUNITIES_LIST_DASHBOARD'],
            ['5d671a22-7b52-11e9-b2bc-f218983a1c3e', 'LBL_OPPORTUNITIES_RECORD_DASHBOARD'],
            ['5d672f44-7b52-11e9-8c60-f218983a1c3e', 'LBL_TARGETS_LIST_DASHBOARD'],
            ['5d671d06-7b52-11e9-83cf-f218983a1c3e', 'LBL_TARGETS_RECORD_DASHBOARD'],
            ['5d6731c4-7b52-11e9-ab12-f218983a1c3e', 'LBL_TARGET_LISTS_LIST_DASHBOARD'],
            ['5d6736ec-7b52-11e9-a00e-f218983a1c3e', 'LBL_QUOTED_LINE_ITEMS_LIST_DASHBOARD'],
            ['5d673462-7b52-11e9-8929-f218983a1c3e', 'LBL_QUOTES_LIST_DASHBOARD'],
            ['5d671fae-7b52-11e9-92e0-f218983a1c3e', 'LBL_QUOTES_RECORD_DASHBOARD'],
            ['5d67396c-7b52-11e9-8826-f218983a1c3e', 'LBL_FORECASTS_DASHBOARD'],
            // we already test agent workbench above. No need for it here

            ['5d672260-7b52-11e9-93ba-f218983a1c3e', 'LBL_CASES_RECORD_DASHBOARD'],
            ['5d67410a-7b52-11e9-afc1-f218983a1c3e', 'LBL_REVENUE_LINE_ITEMS_LIST_DASHBOARD'],
            ['5d672788-7b52-11e9-8440-f218983a1c3e', 'LBL_REVENUE_LINE_ITEMS_RECORD_DASHBOARD'],
        ];
    }
}
