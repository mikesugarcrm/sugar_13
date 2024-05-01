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

class SchedulersJobsTest extends TestCase
{
    /**
     * @var \DBManager|mixed
     */
    public $db;
    public $jobs = [];
    public $scheduler;

    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();
    }

    protected function tearDown(): void
    {
        if (!empty($this->jobs)) {
            $jobs = implode("','", $this->jobs);
            $this->db->query("DELETE FROM job_queue WHERE id IN ('$jobs')");
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $ids = SugarTestAccountUtilities::getCreatedAccountIds();
        if (!empty($ids)) {
            SugarTestAccountUtilities::removeAllCreatedAccounts();
        }
        if (!empty($this->scheduler)) {
            $this->db->query("DELETE FROM schedulers where id = '{$this->scheduler->id}'");
        }
    }

    protected function createJob(array $data)
    {
        $job = new TestSchedulersJob();
        $this->prepareJob($job, $data);
        return $job;
    }

    protected function createJobMock(array $data, array $methodsToBeMocked = [])
    {
        $jobMock = $this->getMockBuilder('TestSchedulersJob')->setMethods($methodsToBeMocked)->getMock();
        $this->prepareJob($jobMock, $data);
        return $jobMock;
    }

    protected function prepareJob(SchedulersJob $job, array $data)
    {
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        foreach ($data as $key => $val) {
            $job->$key = $val;
        }
        $job->execute_time = empty($job->execute_time) ? TimeDate::getInstance()->getNow()->asDb() : $job->execute_time;
        $job->save();
        $this->jobs[] = $job->id;
    }

    public function testJobCreate()
    {
        $job = $this->createJob(['name' => 'TestCreate']);
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->save();
        $this->assertNotEmpty($job->id);
        $job->retrieve($job->id);
        $this->assertEquals('TestCreate', $job->name, 'Wrong name');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
    }

    public function testJobSuccess()
    {
        $job = $this->createJob(['name' => 'Test Success']);
        $job->succeedJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');

        $job = $this->createJob(['name' => 'Test Success 2']);
        $job->succeedJob('very good!');
        $job->db->commit();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('very good!', $job->message);
        $this->assertEmpty($job->failure_count, 'Wrong failure count');
    }

    public function testJobFailure()
    {
        $job = $this->createJob(['name' => 'Test Fail']);
        $job->failJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(1, $job->failure_count, 'Wrong failure count');


        $job = $this->createJob(['name' => 'Test Fail 2']);
        $job->failJob('very bad!');
        $job->db->commit();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('very bad!', $job->message);
    }

    public function testJobPartial()
    {
        global $timedate;
        $now = $timedate->getNow();
        $job = $this->createJob(['name' => 'Test Later', 'job_delay' => 57]);
        $job->postponeJob();

        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_PARTIAL, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
        $date = $timedate->fromDb($job->execute_time_db);
        $this->assertEquals($now->ts + 57, $date->ts);

        $job = $this->createJob(['name' => 'Test Later 2', 'job_delay' => 42]);
        $job->postponeJob('who knows?');
        $job->db->commit();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_PARTIAL, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
        $this->assertStringContainsString('who knows?', $job->message);
        // then succeed
        $job->succeedJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
    }

    public static function staticJobFunction1($job, $data = null)
    {
        global $testJobFunction1Args;
        $testJobFunction1Args = func_get_args();
        return $data != 'failme';
    }

    private static function staticJobFunctionPrivate($job, $data = null)
    {
        global $testJobFunction1Args;
        $testJobFunction1Args = func_get_args();
        return $data != 'failme';
    }

    public static function staticJobFunctionErrors($job, $data = null)
    {
        trigger_error('User Warning', E_USER_WARNING);
        $fp = fopen('/nosuchfile', 'r'); // generate warning
        return $data != 'failme';
    }

    public static function staticJobFunctionInternal($job, $data = null)
    {
        if ($data == 'errors') {
            trigger_error('User Warning', E_USER_WARNING);
        }
        if ($data == 'failme') {
            $job->failJob('Job Failed');
            return true;
        } else {
            $job->succeedJob('Job OK');
            return false;
        }
    }

    public static function staticJobFunctionAccount($job, $data = null)
    {
        SugarTestAccountUtilities::createAccount($data);
        return $data != 'failme';
    }

    public function testJobRunFunc()
    {
        global $testJobFunction1Args;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $testJobFunction1Args = [];
        $job = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(1, count($testJobFunction1Args), 'Wrong number of args to function');
        $this->assertInstanceOf(get_class($job), $testJobFunction1Args[0], 'Wrong type of arg 1');
        $this->assertEquals($testJobFunction1Args[0]->id, $job->id, "Argument 1 ID doesn't match");
        // function with args
        $job = $this->createJob([
            'name' => 'Test Func 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
            'data' => 'function data',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(2, count($testJobFunction1Args), 'Wrong number of args to function');
        $this->assertEquals($testJobFunction1Args[1], 'function data', "Argument 2 doesn't match");
        // function returns failure
        $job = $this->createJob([
            'name' => 'Test Func 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
            'data' => 'failme',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(2, count($testJobFunction1Args), 'Wrong number of args to function');
        // static function
        $testJobFunction1Args = [];
        $job = $this->createJob([
            'name' => 'Test Func 3',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(1, count($testJobFunction1Args), 'Wrong number of args to function');
    }

    public function testJobRunBadFunc()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $testJobFunction1Args = [];
        // unknown function
        $job = $this->createJob([
            'name' => 'Test Bad Func',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::nosuchfunctionblahblah',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('nosuchfun', $job->message);
        // No user
        $job = $this->createJob([
            'name' => 'Test Bad Func 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('No User ID', $job->message);
        // Bad user ID
        $job = $this->createJob([
            'name' => 'Test Bad Func 3',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => 'Unexisting User',
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('Unexisting User', $job->message);
        // Private function
        $testJobFunction1Args = [];
        $job = $this->createJob([
            'name' => 'Test Bad Func 4',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionPrivate',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('staticJobFunctionPrivate', $job->message);
        // Bad target type
        $testJobFunction1Args = [];
        $job = $this->createJob([
            'name' => 'Test Bad Func 5',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'whatever::SchedulersJobsTest::staticJobFunctionPrivate',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('whatever', $job->message);
    }

    public function testJobErrors()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob([
            'name' => 'Test Func Errors',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionErrors',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('User Warning', $job->message);
        $this->assertStringContainsString('nosuchfile', $job->message);
        // failing
        $job = $this->createJob([
            'name' => 'Test Func Errors',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionErrors',
            'data' => 'failme', 'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('User Warning', $job->message);
        $this->assertStringContainsString('nosuchfile', $job->message);
    }

    public function testJobResolution()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob([
            'name' => 'Test Func Errors',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionInternal',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('Job OK', $job->message);
        // failing
        $job = $this->createJob([
            'name' => 'Test Func Errors 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionInternal',
            'data' => 'failme', 'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('Job Failed', $job->message);
        // errors
        $job = $this->createJob([
            'name' => 'Test Func Errors',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionInternal',
            'data' => 'errors',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('Job OK', $job->message);
        $this->assertStringContainsString('User Warning', $job->message);
    }

    public function testJobClients()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJob([
            'name' => 'Test Func Clients',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionInternal',
            'client' => 'UnitTests',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $res = SchedulersJob::runJobId($job->id, 'UnitTests');
        $job->retrieve($job->id);
        $this->assertTrue($res, 'Bad result from runJobId');
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('Job OK', $job->message);
        // wrong client
        $job = $this->createJob([
            'name' => 'Test Func Clients 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::SchedulersJobsTest::staticJobFunctionInternal',
            'client' => 'UnitTests',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $res = SchedulersJob::runJobId($job->id, 'UnitTests2');
        $this->assertFalse($res === true, 'Bad result from runJobId');
        // wrong ID
        $res = SchedulersJob::runJobId("where's waldo?", 'UnitTests2');
        $this->assertFalse($res === true, 'Bad result from runJobId');
    }

    public function testJobURLSuccess()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJobMock([
            'name' => 'Test Url',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'url::www.example.com',
        ], ['fireUrl']);
        $job->expects($this->any())->method('fireUrl')->will($this->returnValue(true));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
    }

    public function testJobURLFailure()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = $this->createJobMock([
            'name' => 'Test Url 2',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'url::www.example.com',
        ], ['fireUrl']);
        $job->expects($this->any())->method('fireUrl')->will($this->returnValue(false));
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
    }

    public function testJobRetries()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        global $timedate;
        $now = $timedate->getNow();

        $job = $this->createJob([
            'name' => 'Test User 1',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'assigned_user_id' => $user1->id,
            'target' => 'function::nosuchfunction',
            'requeue' => true,
            'retry_count' => 2,
            'job_delay' => 1,
            'min_interval' => 242,
        ]);
        $job->runJob();
        $this->assertTrue($job->onFailureCalled, "onFailure wasn't called");
        $this->assertEmpty($job->onFinalFailureCalled, 'onFinalFailure was called prematurely');
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
        $this->assertEquals(1, $job->failure_count, 'Wrong failure count');
        $date = $timedate->fromDb($job->execute_time_db);
        $this->assertEquals($now->ts + 242, $date->ts);
        // try again
        $job->onFailureCalled = null;
        $job->onFinalFailureCalled = null;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->save();
        $job->runJob();
        $this->assertTrue($job->onFailureCalled, "onFailure wasn't called");
        $this->assertEmpty($job->onFinalFailureCalled, 'onFinalFailure was called prematurely');
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
        $this->assertEquals(2, $job->failure_count, 'Wrong failure count');
        // and try again
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->onFailureCalled = null;
        $job->onFinalFailureCalled = null;
        $job->save();
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(3, $job->failure_count, 'Wrong failure count');
        $this->assertTrue($job->onFinalFailureCalled, "onFinalFailure wasn't called");
        $this->assertEmpty($job->onFailureCalled, 'onFailure was called');
    }

    public function testJobDelete()
    {
        $job = $this->createJob(['name' => 'TestCreate']);
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->save();
        $this->assertNotEmpty($job->id);
        $id = $job->id;
        $job->retrieve($id);
        $this->assertNotEmpty($job->id);
        $job->mark_deleted($id);
        $job = new SchedulersJob();
        $job->retrieve($job->id, true, false);
        $this->assertEmpty($job->id);
    }

    /**
     * @ticket 52705
     */
    public function testJobLastRun()
    {
        $this->scheduler = new Scheduler(false);
        $this->scheduler->job_interval = '*::*::*::*::*';
        $this->scheduler->job = 'function::testJobFunction1';
        $this->scheduler->status = 'Active';
        $this->scheduler->last_run = null;
        $this->scheduler->save();
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $testJobFunction1Args = [];
        $job = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'scheduler_id' => $this->scheduler->id,
        ]);
        $job->runJob();
        $job->retrieve($job->id);
        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->scheduler->retrieve($this->scheduler->id);
        $this->assertNotEmpty($this->scheduler->last_run, 'Last run was not updated');
    }

    public function testCleanupJobs()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->scheduler = new Scheduler(false);
        $this->scheduler->id = create_guid();
        $newjob = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_DONE,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'scheduler_id' => $this->scheduler->id,
        ]);
        $oldjob = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_DONE,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'scheduler_id' => $this->scheduler->id,
            'update_date_modified' => false,
            'date_modified' => TimeDate::getInstance()->getNow()->modify('-10 days')->asDB(),
        ]);
        $oldestjob = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_DONE,
            'target' => 'function::testJobFunction1',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'scheduler_id' => $this->scheduler->id,
            'update_date_modified' => false,
            'date_modified' => TimeDate::getInstance()->getNow()->modify('-100 days')->asDb(),
        ]);

        $this->assertNotEmpty($newjob->id);
        $this->assertNotEmpty($oldjob->id);
        $this->assertNotEmpty($oldestjob->id);

        $cleanjob = $this->createJob([
            'name' => 'Test Func',
            'status' => SchedulersJob::JOB_STATUS_RUNNING,
            'target' => 'function::cleanJobQueue',
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'scheduler_id' => $this->scheduler->id,
        ]);
        $cleanjob->runJob();
        // new job should be still there
        $job = new SchedulersJob();
        $job->retrieve($newjob->id);
        $this->assertEquals($newjob->id, $job->id);
        // old job should be deleted
        $job = new SchedulersJob();
        $job->retrieve($oldjob->id);
        $this->assertEmpty($job->id);
        $job->retrieve($oldjob->id, true, false);
        $this->assertEquals($oldjob->id, $job->id);
        // oldest job should be purged
        $count = $this->db->getOne("SELECT count(*) from {$job->table_name} WHERE id='{$oldestjob->id}'");
        $this->assertEquals(0, $count);
    }
}

class TestSchedulersJob extends SchedulersJob
{
    public $onFailureCalled;
    public $onFinalFailureCalled;

    public function onFailureRetry()
    {
        $this->onFailureCalled = true;
    }

    public function onFinalFailure()
    {
        $this->onFinalFailureCalled = true;
    }
}

function testJobFunction1($job, $data = null)
{
    global $testJobFunction1Args;
    $testJobFunction1Args = func_get_args();
    return $data != 'failme';
}
