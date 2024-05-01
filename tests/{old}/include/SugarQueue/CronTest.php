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

class CronTest extends TestCase
{
    /**
     * @var \SugarCronJobs|mixed
     */
    public $jq;
    /**
     * @var mixed
     */
    public $config_cron;
    public static $jobCalled = false;
    public $cron_config;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
    }

    public static function tearDownAdterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function setUp(): void
    {
        $this->jq = $jobq = new SugarCronJobs();
        self::$jobCalled = false;
        if (isset($GLOBALS['sugar_config']['cron'])) {
            $this->config_cron = $GLOBALS['sugar_config']['cron'];
        }
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
        if (isset($GLOBALS['sugar_config']['cron'])) {
            $GLOBALS['sugar_config']['cron'] = $this->config_cron;
        } else {
            unset($GLOBALS['sugar_config']['cron']);
        }
    }

    public function testConfig()
    {
        $GLOBALS['sugar_config']['cron'] = ['max_cron_jobs' => 12, 'max_cron_runtime' => 34, 'min_cron_interval' => 56];
        $jobq = new SugarCronJobs();
        $this->assertEquals(12, $jobq->max_jobs, 'Wrong setting for max_jobs');
        $this->assertEquals(34, $jobq->max_runtime, 'Wrong setting for max_runtime');
        $this->assertEquals(56, $jobq->min_interval, 'Wrong setting for min_interval');
    }

    public function testThrottle()
    {
        //Set the min_interval to 30 if it is set to 0 for this test
        if ($this->jq->min_interval === 0) {
            $this->jq->min_interval = 30;
        }
        $this->jq->throttle();
        $this->assertFalse($this->jq->throttle(), 'Should prohibit second time');
        // wait a bit
        sleep(2);
        $this->jq->min_interval = 1;
        $this->assertTrue($this->jq->throttle(), 'Should allow after delay');
    }

    public static function cronJobFunction()
    {
        self::$jobCalled = true;
        return true;
    }

    public static function cronJobLongFunction()
    {
        self::$jobCalled = true;
        sleep(2);
        return true;
    }

    public function testQueueJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = 'Unit test Job';
        $job->target = 'function::CronTest::cronJobFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();
        $this->assertTrue(self::$jobCalled, 'Job was not called');
        $this->assertTrue($this->jq->runOk(), 'Wrong OK flag');
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEmpty(session_id(), 'Session not destroyed');
    }

    public function testQueueFailJob()
    {
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->name = 'Unit test Job';
        $job->target = 'function::test::test';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk(), 'Wrong OK flag');
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEmpty(session_id(), 'Session not destroyed');
    }

    public function testJobsCount()
    {
        $td = TimeDate::getInstance();
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $td->nowDb();
        $job->name = 'Unit test Job 1';
        $job->target = 'function::CronTest::cronJobFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $td->asDb($td->getNow()->modify('+1 day'));
        $job->name = 'Unit test Job 2';
        $job->target = 'function::CronTest::cronJobFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 1; // only one job per cycle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, 'Job was not called');
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
    }

    public function testJobsTimeCutoff()
    {
        $td = TimeDate::getInstance();
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $td->nowDb();
        $job->name = 'Unit test Job 1';
        $job->target = 'function::CronTest::cronJobLongFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;
        // job 2 - newer, should not be executed
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $td->asDb($td->getNow()->modify('+1 day'));
        $job->name = 'Unit test Job 2';
        $job->target = 'function::CronTest::cronJobLongFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid2 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->max_jobs = 10;
        $this->jq->max_runtime = 1; // only 1 sec runtime
        $this->jq->enforceHardLimit = false;
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue(self::$jobCalled, 'Job was not called');
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        // test that second one wasn't run
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
    }

    public function testJobsCleanup()
    {
        // job 1 - oldest, should be executed
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = TimeDate::getInstance()->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = 'Unit test Job 1';
        $job->target = 'function::CronTest::cronJobFunction';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid1 = $job->id;

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse(self::$jobCalled, 'Job was called');
        $this->assertFalse($this->jq->runOk(), 'Wrong OK flag');
        $job = new SchedulersJob();
        $job->retrieve($jobid1);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEmpty(session_id(), 'Session not destroyed');
    }
}
