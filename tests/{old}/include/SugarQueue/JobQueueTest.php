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

class JobQueueTest extends TestCase
{
    /**
     * @var \TestSugarJobQueue|mixed
     */
    public $jq;

    protected function setUp(): void
    {
        $this->jq = new TestSugarJobQueue();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testSubmitJob()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = 'Unit test Job 1';
        $job->target = 'test::test';
        $job->assigned_user_id = $GLOBALS['current_user']->id;
        $id = $this->jq->submitJob($job);

        $this->assertNotEmpty($id, 'Bad job ID');
        $job = new SchedulersJob();
        $job->retrieve($id);
        $this->assertEquals(SchedulersJob::JOB_PENDING, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_QUEUED, $job->status, 'Wrong status');
        $this->assertEquals($now, $job->execute_time_db, 'Wrong execute time');
    }

    public function testJobDefaultUser()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $now = $GLOBALS['timedate']->nowDb();
        $job->name = 'Unit test Job 1';
        $job->target = 'test::test';
        $id = $this->jq->submitJob($job);
        $this->assertNotEmpty($id, 'Bad job ID');
        $job = new SchedulersJob();
        $job->retrieve($id);
        $this->assertEquals($GLOBALS['current_user']->id, $job->assigned_user_id);
    }

    public function testCleanup()
    {
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->nowDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->date_modified = '2010-01-01 12:00:00';
        $job->name = 'Unit test Job 1';
        $job->target = 'test::test';
        $job->save();
        $jobid = $job->id;
        $this->jq->cleanup();

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
    }

    public function testDelete()
    {
        $timedate = TimeDate::getInstance();
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $timedate->nowDb();
        $job->name = 'Unit test Job 1';
        $job->target = 'test::test';
        $job->save();
        $jobid = $job->id;
        $this->jq->deleteJob($jobid);

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEmpty($job->id, 'Job not deleted');
    }

    public function testGetNextJob()
    {
        // should get only jobs with status QUEUED, in execute_time order, and mark them as running
        // expected execution: job1 -> job2 -> job3 (nlt triggered though, in future)

        // Clean up the queue
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE status='" . SchedulersJob::JOB_STATUS_QUEUED . "'");
        $job = $this->jq->nextJob('unit test');
        $this->assertNull($job, 'Extra job found');

        // older job, execution time newer then job below
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = 'job1';
        $job->target = 'test::test';
        $job->execute_time = '2012-01-01 12:00:00';
        $job->save();
        $jobid1 = $job->id;

        // newer job, same execution time
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2011-01-01 12:00:00';
        $job->name = 'job2';
        $job->target = 'test::test';
        $job->execute_time = '2011-01-01 12:00:00';
        $job->save();
        $jobid2 = $job->id;

        // job with execute date in the future
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_QUEUED;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->getNow()->modify('+3 days')->asDb();
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = 'job3';
        $job->target = 'test::test';
        $job->save();
        $jobid3 = $job->id;

        //running job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = 'Running Job';
        $job->target = 'test::test';
        $job->execute_time = TimeDate::getInstance()->getNow()->asDb();
        $job->save();
        $jobid4 = $job->id;

        // done job
        $job = new SchedulersJob();
        $job->status = SchedulersJob::JOB_STATUS_DONE;
        $job->scheduler_id = 'unittest';
        $job->date_entered = '2010-01-01 12:00:00';
        $job->name = 'Done Job';
        $job->target = 'test::test';
        $job->execute_time = TimeDate::getInstance()->getNow()->asDb();
        $job->save();
        $jobid5 = $job->id;

        // get the first one
        $job = $this->jq->nextJob('unit test');
        $this->assertEquals($jobid2, $job->id, 'Wrong job fetched');
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, 'Wrong status');
        $this->assertEquals('unit test', $job->client, 'Wrong client');

        // check that DB record matches
        $job = new SchedulersJob();
        $job->retrieve($jobid2);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, 'Wrong status');
        $this->assertEquals('unit test', $job->client, 'Wrong client');

        // get the second one
        $job = $this->jq->nextJob('unit test');
        $this->assertEquals($jobid1, $job->id, 'Wrong job fetched');
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, 'Wrong status');
        $this->assertEquals('unit test', $job->client, 'Wrong client');

        // try to get the third one, should get null
        $job = $this->jq->nextJob('unit test');
        $this->assertNull($job, 'Extra job found');
    }

    public function testResolveJob()
    {
        $jobId = '1234';
        $msg = 'test msg';
        $delay = 95;

        $job = $this->getClassMock('SchedulersJob');
        $job->expects($this->once())
            ->method('postponeJob')
            ->with($this->equalTo($msg), $this->equalTo($delay));

        $sut = $this->getClassMock('SugarJobQueue', ['getJob']);
        $sut->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($job));

        $sut->postponeJob($jobId, $msg, $delay);
    }

    public function testPostponeJob()
    {
        $jobId = '1234';
        $msg = 'test msg';
        $delay = 95;

        $job = $this->getClassMock('SchedulersJob');
        $job->expects($this->once())
            ->method('resolveJob')
            ->with($this->equalTo($msg), $this->equalTo($delay));

        $sut = $this->getClassMock('SugarJobQueue', ['getJob']);
        $sut->expects($this->any())
            ->method('getJob')
            ->will($this->returnValue($job));

        $sut->resolveJob($jobId, $msg, $delay);
    }

    protected function getClassMock($class, $methods = [])
    {
        return $this->getMockbuilder($class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}

class TestSugarJobQueue extends SugarJobQueue
{
    public function getJob($jobId)
    {
        return parent::getJob($jobId);
    }
}
