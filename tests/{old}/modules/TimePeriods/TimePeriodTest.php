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

require_once 'modules/TimePeriods/TimePeriod.php';

class TimePeriodTest extends TestCase
{
    private $preTestIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');
    }

    protected function tearDown(): void
    {
        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds)) . "')");

        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * check that the timestamps are generated correctly
     * @group timeperiods
     */
    public function testTimePeriodTimeStamps()
    {
        // create a time period
        $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $timedate = TimeDate::getInstance();

        $start_date_timestamp = $timedate->fromDbDate('2009-01-01');
        $start_date_timestamp->setTime(0, 0, 0);
        $start_date_timestamp = $start_date_timestamp->getTimestamp();

        $end_date_timestamp = $timedate->fromDbDate('2009-03-31');
        $end_date_timestamp->setTime(23, 59, 59);
        $end_date_timestamp = $end_date_timestamp->getTimestamp();

        $this->assertEquals($start_date_timestamp, $tp->start_date_timestamp, 'start time stamps do not match');
        $this->assertEquals($end_date_timestamp, $tp->end_date_timestamp, 'end time stamps do not match');
    }

    /**
     * @group timeperiods
     */
    public function testUpgradeLegacyTimePeriodsUpgradesTimePeriodsWithOutDateStamps()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2009-04-01', '2009-06-30');

        // create a third just to make sure that only two are really updated
        SugarTestTimePeriodUtilities::createTimePeriod('2009-07-01', '2009-09-30');

        $sql = "UPDATE timeperiods
                SET start_date_timestamp = null, end_date_timestamp = null
                WHERE id in ('" . $tp1->id . "','" . $tp2->id . "')";
        $db = DBManagerFactory::getInstance();
        $db->query($sql);

        $updated = $tp1->upgradeLegacyTimePeriods();

        $this->assertEquals(2, $updated);
    }

    /**
     * @dataProvider dataProviderGetGenericStartEndByDuration
     *
     * @param $duration
     * @param $expected_start
     * @param $expected_end
     */
    public function testGetGenericStartEndByDuration($duration, $expected_start, $expected_end)
    {
        $tp = new TimePeriod();

        // set the start date since this is a unit test.
        $dates = $tp->getGenericStartEndByDuration($duration, '2014-08-21');

        $this->assertEquals($expected_start, $dates['start_date']);
        $this->assertEquals($expected_end, $dates['end_date']);
    }

    public function dataProviderGetGenericStartEndByDuration()
    {
        return [
            [0, '2014-07-01', '2014-09-30'],
            [3, '2014-10-01', '2014-12-31'],
            [12, '2014-01-01', '2014-12-31'],
            ['0', '2014-07-01', '2014-09-30'],
            ['3', '2014-10-01', '2014-12-31'],
            ['12', '2014-01-01', '2014-12-31'],
            ['current', '2014-07-01', '2014-09-30'],
            ['next', '2014-10-01', '2014-12-31'],
            ['year', '2014-01-01', '2014-12-31'],
        ];
    }
}
