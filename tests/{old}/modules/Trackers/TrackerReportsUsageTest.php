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

class TrackerReportsUsageTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestTrackerUtility::setup();

        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //$tracker_sessions_monitor = $trackerManager->getMonitor('tracker_sessions');
        $monitor = $trackerManager->getMonitor('tracker');
        $monitor->setEnabled(true);
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', TimeDate::getInstance()->nowDb(), strtotime('-1 day') + 5000);
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        $trackerManager->save();

        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('-1 week') + 5000));
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        $trackerManager->save();

        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('-1 month') + 5000));
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', $GLOBALS['current_user']->id);
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        $trackerManager->save();
    }

    protected function tearDown(): void
    {
        $query = "DELETE FROM tracker WHERE session_id = 'test_session'";
        $GLOBALS['db']->query($query);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTrackerUtility::restore();
    }

    public function testUsageMetricsDay()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'" . gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('-1 day')) . "'", 'datetime');
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count++;
        }
        $this->assertEquals($count, 1);
    }

    public function testUsageMetricsWeek()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'" . gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('-1 week')) . "'", 'datetime');
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count++;
        }
        $this->assertEquals($count, 2);
    }

    public function testUsageMetricsMonth()
    {
        $query = "SELECT module_name, item_id, item_summary, date_modified from tracker where session_id = 'test_session' and user_id = '{$GLOBALS['current_user']->id}' and date_modified > ";
        $query .= db_convert("'" . gmdate($GLOBALS['timedate']->get_db_date_time_format(), strtotime('-1 month')) . "'", 'datetime');
        $result = $GLOBALS['db']->query($query);
        $count = 0;
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count++;
        }
        $this->assertEquals($count, 3);
    }
}
