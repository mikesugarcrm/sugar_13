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

/**
 * Bug #56144
 * Scheduler Bug
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket 56144
 */
class Bug56144 extends TestCase
{
    /**
     * @var \SugarJobQueue|mixed
     */
    public $jq;

    protected function setUp(): void
    {
        $this->jq = new SugarJobQueue();
    }

    public function testCleanup()
    {
        $job = new SchedulersJob();
        $job->update_date_modified = false;
        $job->status = SchedulersJob::JOB_STATUS_RUNNING;
        $job->scheduler_id = 'unittest';
        $job->execute_time = $GLOBALS['timedate']->nowDb();
        $job->date_entered = $GLOBALS['timedate']->getNow()->modify('-1 day')->asDb();
        $job->date_modified = $GLOBALS['timedate']->getNow()->modify('-10 minutes')->asDb();
        $job->name = 'Unit Test Job';
        $job->target = 'test::test';
        $job->save();
        $job_id = $job->id;
        $this->jq->cleanup();

        $job = new SchedulersJob();
        $job->retrieve($job_id);

        // Cleanup will always set job resolution to JOB_FAILURE when a job is cleaned
        $this->assertNotEquals(SchedulersJob::JOB_FAILURE, $job->resolution, 'Wrong resolution');
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM job_queue WHERE scheduler_id='unittest'");
    }
}
