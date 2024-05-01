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

class TrackerQueriesTest extends TestCase
{
    public $dump_slow_queries;
    public $slow_query_time_msec;
    public $paused;
    public $query_hash = '01968500dc05a7c938343c02f58f833a';

    protected function setUp(): void
    {
        TrackerTestUtility::setUp();

        $this->dump_slow_queries = $GLOBALS['sugar_config']['dump_slow_queries'] ?? false;
        $this->slow_query_time_msec = $GLOBALS['sugar_config']['slow_query_time_msec'] ?? 100;

        $trackerManager = TrackerManager::getInstance();
        $this->paused = $trackerManager->isPaused();

        $trackerManager->pause();
        $query = "DELETE FROM tracker_queries WHERE query_hash = '{$this->query_hash}'";
        $GLOBALS['db']->query($query);
        $trackerManager->unPause();

        $GLOBALS['sugar_config']['dump_slow_queries'] = true;
        $GLOBALS['sugar_config']['slow_query_time_msec'] = 0; //force it to dump
    }

    protected function tearDown(): void
    {
        TrackerTestUtility::tearDown();

        $GLOBALS['sugar_config']['dump_slow_queries'] = $this->dump_slow_queries;
        $GLOBALS['sugar_config']['slow_query_time_msec'] = $this->slow_query_time_msec;
        $trackerManager = TrackerManager::getInstance();
        if ($this->paused) {
            $trackerManager->pause();
        }

        $query = "DELETE FROM tracker_queries WHERE query_hash = '{$this->query_hash}'";
        $GLOBALS['db']->query($query);
    }

    public function test_track_slow_query()
    {
        $trackerManager = TrackerManager::getInstance();
        $disabledMonitors = $trackerManager->getDisabledMonitors();
        $trackerManager->setDisabledMonitors([]);
        $trackerManager->pause();
        $result = $GLOBALS['db']->query('SELECT sum(run_count) as total FROM tracker_queries');
        $total = $GLOBALS['db']->fetchByAssoc($result);
        $preRun = (int)$total['total'];

        $trackerManager->unPause();
        $timedate = TimeDate::getInstance();

        $mon = $trackerManager->getMonitor('tracker_queries');

        $sql = "INSERT INTO FOO VALUES ('Test', 101, 'News Time', 'Our Latest Headlines', 4)";
        $mon->setValue('text', $sql);
        $mon->setValue('query_id', create_guid());
        $mon->setValue('sec_total', 1);
        $mon->setValue('sec_avg', 1);
        $mon->setValue('date_modified', $timedate->nowDb());

        $trackerManager->saveMonitor($mon, true);

        $trackerManager->pause();
        $result = $GLOBALS['db']->query('SELECT sum(run_count) as total FROM tracker_queries');
        $total = $GLOBALS['db']->fetchByAssoc($result);
        $postRun = (int)$total['total'];

        //Check that count increased
        $this->assertGreaterThan($preRun, $postRun);

        $result = $GLOBALS['db']->query("SELECT id, run_count FROM tracker_queries WHERE query_hash = '{$this->query_hash}'");
        $stuff = $GLOBALS['db']->fetchByAssoc($result);

        //Check that this query is in there
        $this->assertEquals(1, $stuff['run_count']);

        $trackerManager->unPause();

        $mon = $trackerManager->getMonitor('tracker_queries');

        $trackerManager->saveMonitor($mon, true);
        $mon->setValue('text', $sql);
        $mon->setValue('query_id', create_guid());
        $mon->setValue('sec_total', 1);
        $mon->setValue('sec_avg', 1);
        $mon->setValue('date_modified', $timedate->nowDb());

        $trackerManager->saveMonitor($mon, true);

        $trackerManager->pause();
        $result = $GLOBALS['db']->query("SELECT id, run_count FROM tracker_queries WHERE query_hash = '{$this->query_hash}'");
        $stuff = $GLOBALS['db']->fetchByAssoc($result);
        //Check that this query is in there
        $this->assertEquals(2, $stuff['run_count']);

        $trackerManager->setDisabledMonitors($disabledMonitors);
    }
}
