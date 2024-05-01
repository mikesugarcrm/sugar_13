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

class CronRemoteTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\SugarHttpClient|mixed
     */
    public $client;
    /**
     * @var SugarCronRemoteJobs
     */
    private $jq;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        // clean up queue
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE status='queued'");
        $GLOBALS['sugar_config']['job_server'] = 'http://test.job.server/';
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['sugar_config']['job_server']);
    }

    protected function setUp(): void
    {
        $this->jq = $jobq = new SugarCronRemoteJobs();
        $this->client = $this->getMockBuilder('SugarHttpClient')
            ->setMethods(['callRest'])
            ->getMock();
        $this->jq->setClient($this->client);
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
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

        $jq = $this->jq;
        $this->client->expects($this->once())
            ->method('callRest')
            ->with(
                $this->equalTo('http://test.job.server/submitJob'),
                $this->callback(function ($value) use ($jq, $jobid) {
                    parse_str($value, $qdata);
                    $data = json_decode($qdata['data'], true);
                    return ($jobid == $data['job'])
                        && ($jq->getMyId() == $data['client'])
                        && ($GLOBALS['sugar_config']['site_url'] == $data['instance']);
                })
            )
            ->will($this->returnValue(json_encode(['ok' => $job->id])));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertTrue($this->jq->runOk());

        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_STATUS_RUNNING, $job->status, 'Wrong status');
        $this->assertEquals($this->jq->getMyId(), $job->client, 'Wrong client');
    }

    public function testServerFailure()
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

        $this->client->expects($this->once())
            ->method('callRest')
            ->will($this->returnValue(''));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
    }

    public function testServerFailureWithError()
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

        $this->client->expects($this->once())
            ->method('callRest')
            ->will($this->returnValue('This is not the server you are looking for'));

        $this->jq->min_interval = 0; // disable throttle
        $this->jq->disable_schedulers = true;
        $this->jq->runCycle();

        $this->assertFalse($this->jq->runOk());
        $job = new SchedulersJob();
        $job->retrieve($jobid);
        $this->assertEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');
        $this->assertStringContainsString('This is not the server you are looking for', $job->message);
    }
}
