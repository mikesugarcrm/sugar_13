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
 * @ticket 64675
 */
class Bug64675Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerShouldQualify()
     */
    public function testShouldQualify($schedule, $now)
    {
        // server timezone is ahead UTC
        $result = $this->qualify($schedule, $now, 'Europe/Helsinki');
        $this->assertTrue($result, 'The job has not been qualified to run');
    }

    /**
     * @dataProvider providerShouldNotQualify()
     */
    public function testShouldNotQualify($schedule, $now)
    {
        // server timezone is behind UTC
        $result = $this->qualify($schedule, $now, 'America/Los_Angeles');
        $this->assertFalse($result, 'The job has been qualified to run');
    }

    public static function providerShouldQualify()
    {
        return [
            [
                // schedule is "Every minute on Tuesday"
                '*::*::*::*::2',
                // now is "Tuesday, 23:30"
                '2013-01-01 23:30:00',
            ],
            [
                // schedule is "Every minute in January"
                '*::*::*::1::*',
                // now is "January 31st, 23:30"
                '2013-01-31 23:30:00',
            ],
            [
                // schedule is "Every minute of the 1st day of month"
                '*::*::1::*::*',
                // now is "January 1st, 23:30"
                '2013-01-01 23:30:00',
            ],
        ];
    }

    public static function providerShouldNotQualify()
    {
        return [
            [
                // schedule is "Every minute on Tuesday"
                '*::*::*::*::2',
                // now is "Wednesday, 00:30"
                '2013-01-02 00:30:00',
            ],
            [
                // schedule is "Every minute in January"
                '*::*::*::1::*',
                // now is "February 1st, 00:30"
                '2013-02-01 00:30:00',
            ],
            [
                // schedule is "Every minute of the 1st day of month"
                '*::*::1::*::*',
                // now is "January 2nd, 00:30"
                '2013-01-02 00:30:00',
            ],
        ];
    }

    protected function qualify($schedule, $time, $serverTimezone)
    {
        global $timedate;
        global $current_user;

        $this->iniSet('date.timezone', $serverTimezone);

        $time = $timedate->fromString($time);
        $timedate->setNow($time);

        $scheduler = new Scheduler();
        $scheduler->id = 'test';
        $scheduler->date_time_start = '2013-01-01 00:00:00';
        $scheduler->job_interval = $schedule;
        $scheduler->user = $current_user;

        return $scheduler->fireQualified();
    }
}
