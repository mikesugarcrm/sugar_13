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

class SchedulerTest extends TestCase
{
    /**
     * @var \TimeDate|mixed
     */
    public $timedate;
    /**
     * @var \SugarDateTime|mixed
     */
    public $now;
    protected static $old_timedate;

    /**
     * @var TestScheduler
     */
    protected $scheduler;

    /**
     * @var array
     */
    protected $preferencesOld;

    public static function setUpBeforeClass(): void
    {
        self::$old_timedate = $GLOBALS['timedate'];
        unset($GLOBALS['disable_date_format']);
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y');
        $GLOBALS['current_user']->setPreference('timef', 'h:ia');
        $GLOBALS['current_user']->setPreference('timezone', 'America/Los_Angeles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $GLOBALS['timedate'] = self::$old_timedate;
    }

    protected function setUp(): void
    {
        $this->scheduler = new TestScheduler(false);
        $GLOBALS['timedate'] = $this->timedate = TimeDate::getInstance();
        $this->timedate->allow_cache = true;
        $this->now = $this->timedate->getNow();

        SugarTestReflection::callProtectedMethod($this->scheduler, 'getUser');
        $this->scheduler->user->getPreference('timezone');
        $cacheDb = SugarCache::instance();
        $cacheKey = $this->scheduler->user->id . '_PREFERENCES';
        $prefs = $cacheDb->$cacheKey;
        $this->preferencesOld = $prefs;
        $prefs['global']['timezone'] = 'America/Los_Angeles';
        $prefs['global']['timef'] = 'h:ia';
        $prefs['global']['datef'] = 'm/d/Y';
        SugarTestReflection::callProtectedMethod(
            $this->scheduler->user->_userPreferenceFocus,
            'storeToCache',
            [$prefs['global'], 'global']
        );
    }

    protected function tearDown(): void
    {
        $this->timedate->setNow($this->now);
        $GLOBALS['db']->query("DELETE FROM schedulers WHERE id='{$this->scheduler->id}'");
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='{$this->scheduler->id}'");

        SugarTestReflection::callProtectedMethod(
            $this->scheduler->user->_userPreferenceFocus,
            'storeToCache',
            [$this->preferencesOld['global'], 'global']
        );
    }

    /**
     * Test catch-up functionality
     */
    public function testCatchUp()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->catch_up = true;
        $this->assertTrue($this->scheduler->fireQualified());
        // we were late to the job
        $this->timedate->setNow($this->timedate->fromDb('2011-02-01 14:45:00'));
        $this->scheduler->job_interval = '30::3::*::*::*'; // 10:30 or 11:30 in UTC
        $this->scheduler->last_run = null;
        $this->scheduler->catch_up = 0;
        $this->assertFalse($this->scheduler->fireQualified());
        // but we can still catch up
        $this->scheduler->catch_up = 1;
        $this->assertTrue($this->scheduler->fireQualified());
        // if already did it, don't catch up
        $this->scheduler->last_run = $this->timedate->getNow(true)->setDate(2011, 2, 1)->setTime(3, 30)->asDb();
        $this->assertFalse($this->scheduler->fireQualified());
        // but if did it yesterday, do
        $this->scheduler->last_run = $this->timedate->getNow(true)->setDate(2011, 1, 31)->setTime(3, 30)->asDb();
        $this->assertTrue($this->scheduler->fireQualified());
    }

    /**
     * Test date start/finish
     */
    public function testDateFromTo()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->catch_up = 0;
        $this->timedate->setNow($this->timedate->fromDb('2011-04-17 20:00:00'));

        // no limits
        $this->assertTrue($this->scheduler->fireQualified());
        // limit start, inclusive
        $this->scheduler->date_time_start = '2011-01-01 20:00:00';
        $this->assertTrue($this->scheduler->fireQualified(), 'Inclusive start test failed');
        // exact time ok
        $this->scheduler->date_time_start = '2011-04-17 20:00:00';
        $this->assertTrue($this->scheduler->fireQualified(), 'Start now test failed');
        // limit start, exclusive
        $this->scheduler->date_time_start = '2011-05-01 20:00:00';
        $this->assertFalse($this->scheduler->fireQualified(), 'Exclusive start test failed');

        // limit end, inclusive
        $this->scheduler->date_time_start = '2011-01-01 20:00:00';
        $this->scheduler->date_time_end = '2011-05-01 20:00:00';
        $this->assertTrue($this->scheduler->fireQualified(), 'Inclusive start test failed');
        // exact time ok
        $this->scheduler->date_time_end = '2011-04-17 20:00:00';
        $this->assertTrue($this->scheduler->fireQualified(), 'Start now test failed');
        // limit start, exclusive
        $this->scheduler->date_time_end = '2011-02-01 20:00:00';
        $this->assertFalse($this->scheduler->fireQualified(), 'Exclusive start test failed');
    }

    /**
     * Test date start/finish
     */
    public function testActiveFromTo()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->catch_up = 0;
        $this->scheduler->time_from = '02:00:00';
        $this->scheduler->time_to = '21:00:00';

        $this->timedate->setNow($this->timedate->fromUser('1/17/2011 01:20am'));
        $this->assertFalse($this->scheduler->fireQualified(), 'Before start test failed');
        $this->timedate->setNow($this->timedate->fromUser('2/17/2011 02:00am'));
        $this->assertTrue($this->scheduler->fireQualified(), 'Start test failed');
        $this->timedate->setNow($this->timedate->fromUser('5/17/2011 10:00am'));
        $this->assertTrue($this->scheduler->fireQualified(), 'After start test failed');
        $this->timedate->setNow($this->timedate->fromUser('7/17/2011 9:00pm'));
        $this->assertTrue($this->scheduler->fireQualified(), 'End test failed');
        $this->timedate->setNow($this->timedate->fromUser('11/17/2011 11:30pm'));
        $this->assertFalse($this->scheduler->fireQualified(), 'After end test failed');
    }

    public function getSchedules()
    {
        return [
            // schedule - now point - last run - should be run?
            ['*::*::*::*::*', '5/17/2011 1:00am', null, true],
            ['*::*::*::*::*', '5/17/2011 11:20pm', null, true],
            ['*::*::*::*::*', '5/17/2011 5:40pm', null, true],
            ['*::*::*::*::*', '5/17/2011 7:43pm', null, true],
            // at X:25
            ['25::*::*::*::*', '5/17/2011 5:25pm', null, true],
            ['25::*::*::*::*', '5/17/2011 7:27pm', null, false],
            ['25::*::*::*::*', '5/17/2011 7:25pm', '5/17/2011 7:25pm', false],
            ['25::*::*::*::*', '5/17/2011 8:25pm', '5/17/2011 7:25pm', true],
            // at 6:00
            ['0::6::*::*::*', '5/17/2011 6:00pm', null, false],
            ['0::6::*::*::*', '5/17/2011 6:00am', null, true],
            ['0::6::*::*::*', '5/17/2011 1:00pm', null, false],
            ['0::6::*::*::*', '5/17/2011 2:00pm', null, false],
            // 2am on 1st
            ['0::2::1::*::*', '2/1/2011 2:00pm', null, false],
            ['0::2::1::*::*', '2/1/2011 2:00am', null, true],
            ['0::2::1::*::*', '2/17/2011 2:00am', null, false],
            ['0::2::1::*::*', '1/31/2011 2:00am', null, false],
            ['0::2::1::*::*', '2/2/2011 2:00am', null, false],
            // Every 15 mins on Mon, Tue
            ['*/15::*::*::*::1,2', '5/16/2011 2:00pm', null, true],
            ['*/15::*::*::*::1,2', '5/17/2011 2:00pm', null, true],
            ['*/15::*::*::*::1,2', '5/18/2011 2:00pm', null, false],
            ['*/15::*::*::*::1,2', '5/17/2011 2:10pm', '5/17/2011 2:00pm', false],
            ['*/15::*::*::*::1,2', '5/17/2011 2:15pm', '5/17/2011 2:00pm', true],
        ];
    }

    /**
     * @dataProvider getSchedules
     * Test deriveDBDateTimes()
     */
    public function testDbTimes($sched, $time, $last, $run)
    {
        $time = $this->timedate->fromUser($time);
        $time->setTime($time->hour, $time->min, random_int(0, 59));
        $this->timedate->setNow($time);
        $this->scheduler->job_interval = $sched;
        $this->scheduler->catch_up = false;
        if ($last) {
            $this->scheduler->last_run = $this->timedate->fromUser($last)->asDb();
        } else {
            $this->scheduler->last_run = null;
        }
        if ($run) {
            $this->assertTrue($this->scheduler->fireQualified());
        } else {
            $this->assertFalse($this->scheduler->fireQualified());
        }
    }

    public function testScheduleJob()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->new_with_id = true;
        $this->scheduler->status = 'Active';
        $this->scheduler->job = 'test::test';
        $this->scheduler->save();
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $this->assertNotEmpty($queue->jobs, 'Job was not submitted');
        $ourjob = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob = $job;
                break;
            }
        }
        $this->assertNotEmpty($ourjob, 'Could not find our job in the queue');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $ourjob->status, 'Wrong status');
    }

    public function testScheduleJobRepeat()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->job = 'test::test';
        $this->scheduler->status = 'Active';
        $this->scheduler->new_with_id = true;
        $this->scheduler->save();
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $this->assertNotEmpty($queue->jobs, 'Job was not submitted');
        $ourjob = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob = $job;
                break;
            }
        }
        $this->assertNotEmpty($ourjob, 'Could not find our job in the queue');
        // Do that again
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $ourjob2 = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob2 = $job;
                break;
            }
        }
        $this->assertEmpty($ourjob2, 'Copy job submitted');
        // set job to running
        $ourjob->status = SchedulersJob::JOB_STATUS_RUNNING;
        $ourjob->save();
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $ourjob2 = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob2 = $job;
                break;
            }
        }
        $this->assertEmpty($ourjob2, 'Copy job submitted');
        // set job to done
        $ourjob->status = SchedulersJob::JOB_STATUS_DONE;
        $ourjob->save();
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $ourjob2 = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob2 = $job;
                break;
            }
        }
        $this->assertNotEmpty($ourjob, 'Could not find our job in the queue');
    }

    public function testJobsCleanupReschedule()
    {
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->job = 'test::test';
        $this->scheduler->status = 'Active';
        $this->scheduler->new_with_id = true;
        $this->scheduler->save();

        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = $this->scheduler->id;
        $job->execute_time = $GLOBALS['timedate']->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = 'Unit test Job 1';
        $job->target = 'test::test';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $job->save();
        $jobid = $job->id;
        // try queue run with old job stuck
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $ourjob = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob = $job;
                break;
            }
        }
        $this->assertEmpty($ourjob, 'Duplicate job found');
        // now cleanup the job
        $queue->cleanup();
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        // now try again - should schedule now
        $queue = new MockSchedulerQueue();
        $this->scheduler->checkPendingJobs($queue);
        $ourjob = null;
        foreach ($queue->jobs as $job) {
            if ($job->scheduler_id == $this->scheduler->id) {
                $ourjob = $job;
                break;
            }
        }
        $this->assertNotEmpty($ourjob, 'Could not find our job in the queue');
    }
}

class MockSchedulerQueue extends SugarJobQueue
{
    public $jobs = [];

    public function submitJob($job)
    {
        $this->jobs[] = $job;
        parent::submitJob($job);
    }
}

class TestScheduler extends Scheduler
{
    public $fired = false;
    public $id = 'test';
    public $name = 'testJob';
    public $date_time_start = '2005-01-01 19:00:00';

    public function fire()
    {
        $this->fired = true;
    }
}
