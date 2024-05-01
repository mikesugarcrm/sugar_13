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

class TrackerManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $user = new User();
        $user->retrieve('1');
        $GLOBALS['current_user'] = $user;
    }

    protected function tearDown(): void
    {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();

        $user = new User();
        $user->retrieve('1');
        $GLOBALS['current_user'] = $user;
    }

    public function testPausing()
    {
        $trackerManager = TrackerManager::getInstance();
        $trackerManager->unPause();
        $this->assertFalse($trackerManager->isPaused());
        $trackerManager->pause();
        $this->assertTrue($trackerManager->isPaused());
    }

    public function testPausing2()
    {
        $query = 'select count(id) as total from tracker';
        $result = $GLOBALS['db']->query($query);
        $count1 = 0;
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count1 = $row['total'];
        }

        $trackerManager = TrackerManager::getInstance();
        $trackerManager->pause();

        $monitor = $trackerManager->getMonitor('tracker');
        $monitor->setValue('module_name', 'Contacts');
        $monitor->setValue('item_id', '10909d69-2b55-094d-ba89-47b23d3121dd');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', TimeDate::getInstance()->nowDb(), strtotime('-1 day') + 5000);
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', 1);

        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        $trackerManager->save();

        $count2 = 0;
        $query = 'select count(id) as total from tracker';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count2 = $row['total'];
        }
        $this->assertEquals($count1, $count2);
    }


    public function testPausing3()
    {
        $query = 'select count(id) as total from tracker_queries';
        $result = $GLOBALS['db']->query($query);
        $count1 = 0;
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count1 = $row['total'];
        }

        $dumpSlowQuery = $GLOBALS['sugar_config']['dump_slow_queries'];
        $slowQueryTime = $GLOBALS['sugar_config']['slow_query_time_msec'];
        $GLOBALS['sugar_config']['dump_slow_queries'] = true;
        $GLOBALS['sugar_config']['slow_query_time_msec'] = 0;

        $trackerManager = TrackerManager::getInstance();
        $trackerManager->pause();

        $count2 = 0;
        $query = 'select count(id) as total from tracker_queries';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $count2 = $row['total'];
        }
        $this->assertEquals($count1, $count2);
        $GLOBALS['sugar_config']['dump_slow_queries'] = $dumpSlowQuery;
        $GLOBALS['sugar_config']['slow_query_time_msec'] = $slowQueryTime;
    }

    public function testModuleFilteringWorks()
    {
        $trackerManager = TrackerManager::getInstance();
        SugarTestReflection::setProtectedValue($trackerManager, 'moduleConfig', null);
        $c1 = $this->getMonitorCount();
        $this->saveTestTrackerForModule($trackerManager, 'Contacts');
        $this->assertEquals($c1 + 1, $this->getMonitorCount());
    }

    public function testModuleEnableOnly()
    {
        $trackerManager = TrackerManager::getInstance();
        SugarTestReflection::setProtectedValue($trackerManager, 'moduleConfig', [
            'enable_only' => [
                'Contacts',
            ],
        ]);
        $c1 = $this->getMonitorCount();
        $this->saveTestTrackerForModule($trackerManager, 'Contacts');
        $this->saveTestTrackerForModule($trackerManager, 'Users');
        $this->assertEquals($c1 + 1, $this->getMonitorCount());
    }

    public function testModuleDisable()
    {
        $trackerManager = TrackerManager::getInstance();
        SugarTestReflection::setProtectedValue($trackerManager, 'moduleConfig', [
            'disable' => [
                'Contacts',
            ],
        ]);
        $c1 = $this->getMonitorCount();
        $this->saveTestTrackerForModule($trackerManager, 'Contacts');
        $this->saveTestTrackerForModule($trackerManager, 'Users');
        $this->assertEquals($c1 + 1, $this->getMonitorCount());
    }

    private function saveTestTrackerForModule(TrackerManager $trackerManager, string $module)
    {
        $monitor = $trackerManager->getMonitor('tracker');
        $monitor->setValue('module_name', $module);
        $monitor->setValue('item_id', '-test-');
        $monitor->setValue('item_summary', 'Foo');
        $monitor->setValue('date_modified', TimeDate::getInstance()->nowDb());
        $monitor->setValue('action', 'index');
        $monitor->setValue('session_id', 'test_session');
        $monitor->setValue('user_id', 1);
        $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
        $trackerManager->saveMonitor($monitor, true, true);
    }

    private function getMonitorCount(): int
    {
        $query = 'select count(id) as total from tracker';
        return intval($GLOBALS['db']->getOne($query));
    }
}
