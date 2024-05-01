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
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarDateTimeTest
 *
 * @coversDefaultClass \SugarDateTime
 */
class SugarDateTimeTest extends TestCase
{
    public $germanWeekdayMap = [
        'dom_cal_weekdays_long' => [
            'Sonntag',
            'Montag',
            'Dienstag',
            'Mitwoch',
            'Donnerstag',
            'Freitag',
            'Samstag',
        ],
        'dom_cal_weekdays' => [
            'So',
            'Mo',
            'Di',
            'Mi',
            'Do',
            'Fr',
            'Sa',
        ],
    ];

    /**
     * @dataProvider weekdayProvider
     * @covers ::__get
     * @param $date
     * @param $englishDay
     * @param $germanLong
     * @param $germanShort
     * @throws Exception
     */
    public function testGetWeekdays($date, $englishDay, $germanLong, $germanShort)
    {
        $sdt = new SugarDateTime($date, new DateTimeZone('UTC'));
        TestReflection::setProtectedValue($sdt, '_strings', $this->germanWeekdayMap);
        $this->assertEquals($sdt->day_of_week_long, $germanLong);
        $this->assertEquals($sdt->day_of_week_english, $englishDay);
        $this->assertEquals($sdt->day_of_week_short, $germanShort);
    }

    public function weekdayProvider()
    {
        return [
            [
                'date' => '2020-06-15',
                'englishDay' => 'Monday',
                'germanDay' => 'Montag',
                'germanShort' => 'Mo',
            ],
            [
                'date' => '2020-06-16',
                'englishDay' => 'Tuesday',
                'germanDay' => 'Dienstag',
                'germanShort' => 'Di',

            ],
            [
                'date' => '2020-06-17',
                'englishDay' => 'Wednesday',
                'germanDay' => 'Mitwoch',
                'germanShort' => 'Mi',
            ],
            [
                'date' => '2020-06-18',
                'englishDay' => 'Thursday',
                'germanDay' => 'Donnerstag',
                'germanShort' => 'Do',
            ],
            [
                'date' => '2020-06-19',
                'englishDay' => 'Friday',
                'germanDay' => 'Freitag',
                'germanShort' => 'Fr',
            ],
            [
                'date' => '2020-06-20',
                'englishDay' => 'Saturday',
                'germanDay' => 'Samstag',
                'germanShort' => 'Sa',
            ],
            [
                'date' => '2020-06-21',
                'englishDay' => 'Sunday',
                'germanDay' => 'Sonntag',
                'germanShort' => 'So',
            ],
        ];
    }
}
