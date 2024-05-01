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

class SugarForecasting_Filter_TimePeriodFilterTest extends TestCase
{
    private static $currentSettings;

    /**
     * Setup global variables
     */
    public static function setUpBeforeClass(): void
    {
        $admin = BeanFactory::newBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts', 'base');
        $settingsToRestore = ['timeperod_interval', 'timeperiod_leaf_interval', 'timeperiod_start_date', 'timeperiod_shown_forward', 'timeperiod_shown_backward'];
        foreach ($settingsToRestore as $id) {
            if (isset($settings[$id])) {
                self::$currentSettings[$id] = $settings[$id];
            }
        }
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods set deleted = 1');
    }

    /**
     * Call SugarTestHelper to teardown initialization in setUpBeforeClass
     */
    public static function tearDownAfterClass(): void
    {
        self::updateForecastSettings(self::$currentSettings);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $db = DBManagerFactory::getInstance();
        $db->query('DELETE FROM timeperiods WHERE deleted = 0');
        $db->query('UPDATE timeperiods SET deleted = 0');
    }

    public function timePeriodFilterWithTimePeriodsProvider()
    {
        $timedate = TimeDate::getInstance();
        $now = $timedate->getNow(false);
        $year = $now->format('Y');
        return [
            [TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 'current_year', 1, 1, 12],
            [TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 'current_year', 2, 2, 20],
            [TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, $now->setDate($year, 2, 1)->asDbDate(), 'current_year', 1, 1, 12],
            [TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 'current_year', 1, 1, 9],
            [TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 1, 1)->asDbDate(), 'current_year', 2, 2, 15],
            [TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, $now->setDate($year, 2, 1)->asDbDate(), 'current_year', 2, 2, 15],
        ];
    }

    /**
     * This is a test to check that the SugarForecasting_Filter_TimePeriodFilter class returns the appropriate timeperiods based on the settings
     * for the timeperiod type and the shown forward/backward settings.
     *
     * @group forecasts
     * @group timeperiods
     * @dataProvider timePeriodFilterWithTimePeriodsProvider
     */
    public function testTimePeriodFilterWithTimePeriods($parentType, $leafType, $startDate, $fiscalYear, $shownForward, $shownBackward, $expectedLeaves)
    {
        $forecastConfigSettings = [
            'timeperiod_interval' => $parentType,
            'timeperiod_leaf_interval' => $leafType,
            'timeperiod_start_date' => $startDate,
            'timeperiod_fiscal_year' => $fiscalYear,
            'timeperiod_shown_forward' => $shownForward,
            'timeperiod_shown_backward' => $shownBackward,
        ];

        self::updateForecastSettings($forecastConfigSettings);

        $admin = BeanFactory::newBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts', 'base');

        $timePeriod = TimePeriod::getByType($parentType);
        $timePeriod->rebuildForecastingTimePeriods([], $settings);

        $obj = new SugarForecasting_Filter_TimePeriodFilter([]);
        $this->assertEquals($expectedLeaves, count($obj->process()));

        //Now assert that the leaf_cycle is 1 according to the specified start month
        $timedate = TimeDate::getInstance();
        $timePeriodToCheck = TimePeriod::getEarliest($leafType);

        while ($timePeriodToCheck != null) {
            if ($timedate->fromDbDate($timePeriodToCheck->start_date)->format('n') == $timedate->fromDbDate($startDate)->format('n')) {
                $this->assertEquals(1, $timePeriodToCheck->leaf_cycle);
            }
            $timePeriodToCheck = $timePeriodToCheck->getNextTimePeriod();
        }
    }

    private static function updateForecastSettings($settings)
    {
        $admin = BeanFactory::newBean('Administration');
        foreach ($settings as $id => $value) {
            $admin->saveSetting('Forecasts', $id, $value, 'base');
        }
    }
}
