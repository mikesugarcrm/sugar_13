<?php

//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
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

class SugarJobCreateNextTimePeriodTest extends TestCase
{
    private $preTestIds = [];

    //These are the default forecast configuration settings we will use to test
    protected $forecastConfigSettings = [
        'timeperiod_type' => 'chronological',
        'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
        'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
        'timeperiod_start_date' => '2012-01-01',
        'timeperiod_shown_forward' => '2',
        'timeperiod_shown_backward' => '2',
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $this->postSetUp();
    }

    protected function postSetUp($timePeriodType = TimePeriod::ANNUAL_TYPE)
    {
        SugarTestForecastUtilities::setUpForecastConfig($this->forecastConfigSettings);

        //Run rebuildForecastingTimePeriods which takes care of creating the TimePeriods based on the configuration data
        $timePeriod = TimePeriod::getByType($timePeriodType);
        $admin = BeanFactory::newBean('Administration');
        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');

        $timePeriod->rebuildForecastingTimePeriods([], $currentForecastSettings);
    }

    protected function tearDown(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();

        $db = DBManagerFactory::getInstance();

        //Remove any job_queue entries
        $db->query('DELETE FROM job_queue where name = ' . $db->quoted('SugarJobCreateNextTimePeriod'));

        $db->query('UPDATE timeperiods set deleted = 1');

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds)) . "')");

        $db->query('DELETE FROM timeperiods WHERE deleted = 1');
    }

    /**
     * @group timeperiods
     * @group forecasts
     * @outputBuffering disabled
     */
    public function testSugarJobCreateNextTimePeriodJobForAnnualParent()
    {
        global $current_user;
        $admin = BeanFactory::newBean('Administration');
        $config = $admin->getConfigForModule('Forecasts', 'base');

        $timeperiodInterval = $config['timeperiod_interval'];
        $timeperiodLeafInterval = $config['timeperiod_leaf_interval'];

        $parentTimePeriod = TimePeriod::getLatest($timeperiodInterval);
        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $currentTimePeriod = TimePeriod::getCurrentTimePeriod($timeperiodLeafInterval);

        $timedate = TimeDate::getInstance();

        //We run the rebuild command if the latest TimePeriod is less than the specified configuration interval from the current TimePeriod
        $correctStartDate = $timedate->fromDbDate($currentTimePeriod->start_date);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        $shownForward = $config['timeperiod_shown_forward'];

        //Move the current start date forward by the leaf period amounts
        for ($x = 0; $x < $shownForward; $x++) {
            $correctStartDate->modify($parentTimePeriod->next_date_modifier);
        }

        $this->assertGreaterThanOrEqual($correctStartDate, $latestStartDate);

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');

        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        //After the job runs, the $correctStartDate should be set and this should no longer be greater than $latestStartDate
        $this->assertFalse($correctStartDate > $latestStartDate);

        //Now if we run the queue again, retrieving the latest TimePeriod a second time should return the newly created leaf timeperiod
        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');

        $latestTimePeriod2 = TimePeriod::getLatest($timeperiodLeafInterval);
        $this->assertEquals($latestTimePeriod->id, $latestTimePeriod2->id);
    }


    /**
     * @group timeperiods
     * @group forecasts
     */
    public function testSugarJobCreateNextTimePeriodJobForQuarterParent()
    {
        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $this->forecastConfigSettings = [
            'timeperiod_type' => 'chronological',
            'timeperiod_interval' => TimePeriod::QUARTER_TYPE,
            'timeperiod_leaf_interval' => TimePeriod::MONTH_TYPE,
            'timeperiod_start_month' => '1',
            'timeperiod_start_day' => '1',
            'timeperiod_shown_forward' => '8',
            'timeperiod_shown_backward' => '8',
        ];

        $this->postSetUp(TimePeriod::QUARTER_TYPE);
        global $current_user;
        $timeperiodLeafInterval = TimePeriod::MONTH_TYPE;

        $parentTimePeriod = TimePeriod::getLatest(TimePeriod::QUARTER_TYPE);
        $latestTimePeriod = TimePeriod::getLatest(TimePeriod::MONTH_TYPE);
        $currentTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::MONTH_TYPE);

        $timedate = TimeDate::getInstance();

        //We run the rebuild command if the latest TimePeriod is less than the specified configuration interval from the current TimePeriod
        $correctStartDate = $timedate->fromDbDate($currentTimePeriod->start_date);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);


        $shownForward = 8;
        //Move the current start date forward by the leaf period amounts
        for ($x = 0; $x < $shownForward; $x++) {
            $correctStartDate->modify($parentTimePeriod->next_date_modifier);
        }

        $this->assertGreaterThanOrEqual($correctStartDate, $latestStartDate);

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');

        $latestTimePeriod = TimePeriod::getLatest($timeperiodLeafInterval);
        $latestStartDate = $timedate->fromDbDate($latestTimePeriod->start_date);

        //After the job runs, the $correctStartDate should be set and this should no longer be greater than $latestStartDate
        $this->assertFalse($correctStartDate > $latestStartDate);

        //Now if we run the queue again, retrieving the latest TimePeriod a second time should return the newly created leaf timeperiod
        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'SugarJobCreateNextTimePeriod',
            'class::SugarJobCreateNextTimePeriod',
            '',
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution, 'Wrong resolution');
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status, 'Wrong status');

        $latestTimePeriod2 = TimePeriod::getLatest($timeperiodLeafInterval);
        $this->assertEquals($latestTimePeriod->id, $latestTimePeriod2->id);
    }
}
