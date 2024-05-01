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

use RRule\RRule;

class CalendarEventsUtilsTest extends TestCase
{
    private $old_assigned_user_id = null;
    protected $meetingIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->meetingIds = [];

        // Reset the instance to null before each test
        $reflectedProperty = new ReflectionProperty(CalendarEventsUtils::class, 'instance');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue(null);
    }

    protected function tearDown(): void
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id IN ('" . $ids . "')");
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = [];
        }

        SugarTestHelper::tearDown();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(
            CalendarEventsUtils::class,
            CalendarEventsUtils::getInstance()
        );
    }

    /**
     * @covers ::getOldAssignedUser
     *
     * @param string $module
     * @param string $id
     *
     * @dataProvider dataProvider
     */
    public function testGetOldAssignedUser_oldAssignedUserNotSet($module, $id)
    {
        global $current_user;

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = '1';
        $meeting->name = 'Test Meeting';
        $meeting->assigned_user_id = $current_user->id;
        $meeting->save();

        $this->meetingIds[] = $meeting->id;

        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $this->old_assigned_user_id = $calendarEventsUtilsMock->getOldAssignedUser($module, $id);

        $this->assertEquals($this->old_assigned_user_id, $current_user->id);
    }

    public static function dataProvider()
    {
        return [
            [
                'module' => 'Meetings',
                'id' => 1,

            ],
        ];
    }

    public function testgetOldAssignedUser_oldAssignedUserIsSet()
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();
        $calendarEventsUtilsMock->setOldAssignedUserValue('1');

        $this->old_assigned_user_id = $calendarEventsUtilsMock->getOldAssignedUser('Meetings');

        $this->assertEquals($this->old_assigned_user_id, '1');
    }

    /**
     * @covers ::formatDateTime
     */
    public function testFormatDateTime()
    {
        global $current_user, $timedate;

        $timezone = new DateTimeZone('America/Los_Angeles');
        $current_user->setPreference('timezone', $timezone->getName());

        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->formatDateTime('datetime', '2023-01-01 10:00:00', 'iso');

        $this->assertEquals('2023-01-01T02:00:00-08:00', $result);

        $result = $calendarEventsUtilsMock->formatDateTime('datetime', '2023-04-04 12:00:00', 'user', $current_user);

        $datetimeFormat = $timedate->get_date_time_format();
        $expectedDate = SugarDateTime::createFromFormat('Y-m-d H:i', '2023-04-04 05:00');
        $expectedDate = $expectedDate->format($datetimeFormat);

        $this->assertEquals($expectedDate, $result);
    }

    public function dataProviderForBuildRecurringSequenceTests()
    {
        return [
            [
                '2015-12-15',
                [
                    'type' => 'Daily',
                    'count' => 3,
                ],
                3,
                '2015-12-15 00:00',
                '2015-12-17 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Daily',
                    'count' => 3,
                    'interval' => 3,
                ],
                3,
                '2015-12-15 00:00',
                '2015-12-21 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Daily',
                    'until' => '2015-12-30',
                    'interval' => 2,
                ],
                8,
                '2015-12-15 00:00',
                '2015-12-29 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Weekly',
                    'dow' => '35',
                    'count' => 4,
                ],
                4,
                '2015-12-16 00:00',
                '2015-12-25 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Weekly',
                    'dow' => '246',
                    'count' => 5,
                    'interval' => 4,
                ],
                5,
                '2015-12-15 00:00',
                '2016-01-14 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Weekly',
                    'dow' => '15',
                    'until' => '2016-01-06',
                    'interval' => 3,
                ],
                2,
                '2015-12-18 00:00',
                '2016-01-04 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 2,
                ],
                2,
                '2015-12-15 00:00',
                '2016-01-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 3,
                    'interval' => 5,
                ],
                3,
                '2015-12-15 00:00',
                '2016-10-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'until' => '2018-06-30',
                    'interval' => 4,
                ],
                8,
                '2015-12-15 00:00',
                '2018-04-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 5,
                    'selector' => 'Each',
                    'interval' => 2,
                    'days' => '8,17,26',
                ],
                5,
                '2015-12-17 00:00',
                '2016-02-26 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 3,
                    'selector' => 'Each',
                    'interval' => 7,
                    'days' => '31',
                ],
                3,
                '2015-12-31 00:00',
                '2020-01-31 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'until' => '2033-08-14',
                    'selector' => 'Each',
                    'interval' => 5,
                    'days' => '31',
                ],
                27,
                '2015-12-31 00:00',
                '2033-01-31 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 5,
                    'selector' => 'On',
                    'interval' => 2,
                    'ordinal' => 'first',
                    'unit' => 'Day',
                ],
                5,
                '2016-02-01 00:00',
                '2016-10-01 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'count' => 9,
                    'selector' => 'On',
                    'interval' => 2,
                    'ordinal' => 'last',
                    'unit' => 'WD',
                ],
                9,
                '2015-12-31 00:00',
                '2017-04-28 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Monthly',
                    'until' => '2025-06-11',
                    'selector' => 'On',
                    'interval' => 4,
                    'ordinal' => 'fifth',
                    'unit' => 'WE',
                ],
                29,
                '2015-12-19 00:00',
                '2025-04-19 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'count' => 4,
                ],
                4,
                '2015-12-15 00:00',
                '2018-12-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'count' => 2,
                    'interval' => 5,
                ],
                2,
                '2015-12-15 00:00',
                '2020-12-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'until' => '2025-03-14',
                    'interval' => 3,
                ],
                4,
                '2015-12-15 00:00',
                '2024-12-15 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'count' => 5,
                    'selector' => 'On',
                    'interval' => 2,
                    'ordinal' => 'fifth',
                    'unit' => 'Wed',
                ],
                5,
                '2015-12-30 00:00',
                '2024-01-03 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'count' => 9,
                    'selector' => 'On',
                    'interval' => 2,
                    'ordinal' => 'last',
                    'unit' => 'Mon',
                ],
                9,
                '2016-12-26 00:00',
                '2032-12-27 00:00',
            ],
            [
                '2015-12-15',
                [
                    'type' => 'Yearly',
                    'until' => '2025-06-11',
                    'selector' => 'On',
                    'interval' => 4,
                    'ordinal' => 'second',
                    'unit' => 'WE',
                ],
                2,
                '2019-01-06 00:00',
                '2023-01-07 00:00',
            ],
        ];
    }

    public function dataProviderForRecurringEventsTests()
    {
        global $timedate;

        $occurrencesFirstDataSet = [
            '2023-09-27 06:30',
            '2023-10-01 06:30',
            '2023-10-10 06:30',
            '2023-10-27 06:30',
            '2023-11-01 07:30',
            '2023-11-10 07:30',
            '2023-11-27 07:30',
            '2023-12-01 07:30',
            '2023-12-10 07:30',
            '2023-12-27 07:30',
            '2024-01-01 07:30',
            '2024-01-10 07:30',
            '2024-01-27 07:30',
            '2024-02-01 07:30',
        ];

        foreach ($occurrencesFirstDataSet as $key => $occurenceDatetime) {
            $datetimeFormat = $timedate->get_date_time_format();
            $expectedDate = SugarDateTime::createFromFormat('Y-m-d H:i', $occurenceDatetime);
            $expectedDate = $expectedDate->format($datetimeFormat);

            $occurrencesFirstDataSet[$key] = $expectedDate;
        }

        $occurrencesSecondDataSet = [
            '2023-09-26 07:00',
            '2023-10-24 07:00',
            '2023-11-28 08:00',
            '2023-12-26 08:00',
            '2024-01-23 08:00',
            '2024-02-27 08:00',
            '2024-03-26 08:00',
            '2024-04-23 07:00',
            '2024-05-28 07:00',
            '2024-06-25 07:00',
        ];

        foreach ($occurrencesSecondDataSet as $key => $occurenceDatetime) {
            $datetimeFormat = $timedate->get_date_time_format();
            $expectedDate = SugarDateTime::createFromFormat('Y-m-d H:i', $occurenceDatetime);
            $expectedDate = $expectedDate->format($datetimeFormat);

            $occurrencesSecondDataSet[$key] = $expectedDate;
        }

        $occurrencesThirdDataSet = [
            '2023-10-02 07:00',
            '2023-11-01 08:00',
            '2023-12-01 08:00',
            '2024-01-01 08:00',
            '2024-02-01 08:00',
        ];

        foreach ($occurrencesThirdDataSet as $key => $occurenceDatetime) {
            $datetimeFormat = $timedate->get_date_time_format();
            $expectedDate = SugarDateTime::createFromFormat('Y-m-d H:i', $occurenceDatetime);
            $expectedDate = $expectedDate->format($datetimeFormat);

            $occurrencesThirdDataSet[$key] = $expectedDate;
        }

        return [
                [
                    'params' => [
                        "duration_hours" => 0,
                        "duration_minutes" => 30,
                        "repeat_interval" => 1,
                        "date_start" => "2023-09-22T09:30:00",
                        "date_end" => "2023-09-22T10:00:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "Each",
                        "repeat_ordinal" => "first",
                        "repeat_unit" => "Sun",
                        "repeat_dow" => 5,
                        "repeat_days" => "1,10,27",
                        "repeat_until" => "2024-02-07",
                        "name" => "MonthEach1,10,27Until",
                    ],
                    'expectedRrule' => [
                        'RRULE' => [
                            'FREQ' => 'MONTHLY',
                            'INTERVAL' => 1,
                            'UNTIL' => '20240207T235959Z',
                            'BYMONTHDAY' => '1,10,27',
                        ],
                        'DTSTART' => '20230922T093000',
                    ],
                    'expectedOccurrences' => $occurrencesFirstDataSet,
                    'RRuleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T093000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=20240207T235959Z;BYMONTHDAY=1,10,27',
                ],
                [
                    'params' => [
                        "duration_hours" => 0,
                        "duration_minutes" => 30,
                        "repeat_interval" => 1,
                        "assigned_user_id" => "1",
                        "date_start" => "2023-09-22T10:00:00",
                        "date_end" => "2023-09-22T10:30:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "On",
                        "repeat_ordinal" => "fourth",
                        "repeat_unit" => "Tue",
                        "repeat_dow" => 5,
                        "repeat_count" => 10,
                        "name" => "MonthOn4TuOc10",
                    ],
                    'expectedRrule' => [
                        'RRULE' => [
                            'FREQ' => 'MONTHLY',
                            'INTERVAL' => 1,
                            'COUNT' => '10',
                            'BYDAY' => '4TU',
                        ],
                        'DTSTART' => '20230922T100000',
                    ],
                    'expectedOccurrences' => $occurrencesSecondDataSet,
                    'RruleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T100000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;COUNT=10;BYDAY=4TU',
                ],
                [
                    'params' => [
                        "duration_hours" => 0,
                        "duration_minutes" => 30,
                        "repeat_interval" => 1,
                        "date_start" => "2023-09-22T10:00:00",
                        "date_end" => "2023-09-22T10:30:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "On",
                        "repeat_ordinal" => "first",
                        "repeat_unit" => "WD",
                        "repeat_dow" => 5,
                        "repeat_until" => "2024-02-13",
                        "name" => "MonthOn1WDUntill",
                    ],
                    'expectedRrule' => [
                        'RRULE' => [
                            'FREQ' => 'MONTHLY',
                            'INTERVAL' => 1,
                            'UNTIL' => '20240213T235959Z',
                            'BYDAY' => 'MO,TU,WE,TH,FR',
                            'BYSETPOS' => '1',
                        ],
                        'DTSTART' => '20230922T100000',
                    ],
                    'expectedOccurrences' => $occurrencesThirdDataSet,
                    'RruleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T100000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=20240213T235959Z;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=1',
                ],
        ];
    }

    public function dataProviderSetOnRRule()
    {
        return [
            [
                'repeatType' => 'Monthly',
                'repeatOrdinal' => 'first',
                'repeatUnit' => 'Wed',
                'dateStartFormatted' => '20230922T093000',
                'expected' => [
                    'RRULE' => [
                        'FREQ' => 'MONTHLY',
                        'INTERVAL' => '1',
                        'UNTIL' => '20240207T235959Z',
                        'BYDAY' => '1WE',
                    ],
                    'DTSTART' => '20230922T093000',
                ],
            ],
            [
                'repeatType' => 'Yearly',
                'repeatOrdinal' => 'last',
                'repeatUnit' => 'Fri',
                'dateStartFormatted' => '20230922T093000',
                'expected' => [
                    'RRULE' => [
                        'FREQ' => 'MONTHLY',
                        'INTERVAL' => '1',
                        'UNTIL' => '20240207T235959Z',
                        'BYDAY' => '-1FR',
                        'BYMONTH' => '9',
                    ],
                    'DTSTART' => '20230922T093000',
                ],
            ],
            [
                'repeatType' => 'Yearly',
                'repeatOrdinal' => 'third',
                'repeatUnit' => 'WD',
                'dateStartFormatted' => '20230922T093000',
                'expected' => [
                    'RRULE' => [
                        'FREQ' => 'MONTHLY',
                        'INTERVAL' => '1',
                        'UNTIL' => '20240207T235959Z',
                        'BYDAY' => 'MO,TU,WE,TH,FR',
                        'BYSETPOS' => '3',
                    ],
                    'DTSTART' => '20230922T093000',
                ],
            ],
            [
                'repeatType' => 'Yearly',
                'repeatOrdinal' => 'fourth',
                'repeatUnit' => 'Day',
                'dateStartFormatted' => '20230922T093000',
                'expected' => [
                    'RRULE' => [
                        'FREQ' => 'MONTHLY',
                        'INTERVAL' => '1',
                        'UNTIL' => '20240207T235959Z',
                        'BYMONTHDAY' => '4',
                        'BYMONTH' => '1',
                    ],
                    'DTSTART' => '20230922T093000',
                ],
            ],
        ];
    }

    public function dataProviderConvertRepeatDowToByDay()
    {
        return [
            [
                'repeatDow' => '0123456',
                'expectedBYDAY' => 'SU,MO,TU,WE,TH,FR,SA',
            ],
            [
                'repeatDow' => '234',
                'expectedBYDAY' => 'TU,WE,TH',
            ],
            [
                'repeatDow' => '36',
                'expectedBYDAY' => 'WE,SA',
            ],
            [
                'repeatDow' => '7',
                'expectedBYDAY' => '',
            ],
            [
                'repeatDow' => '74',
                'expectedBYDAY' => 'TH',
            ],
        ];
    }

    public function dataProviderConvertByDayToRepeatDow()
    {
        return [
            [
                'expectedRepeatDOW' => '0123456',
                'BYDAY' => 'SU,MO,TU,WE,TH,FR,SA',
            ],
            [
                'expectedRepeatDOW' => '234',
                'BYDAY' => 'TU,WE,TH',
            ],
            [
                'expectedRepeatDOW' => '36',
                'BYDAY' => 'WE,SA',
            ],
        ];
    }

    public function dataProviderConvertByDayToRepeatUnitAndOrdinal()
    {
        return [
            [
                'BYSETPOS' => null,
                'BYDAY' => '4SU',
                'expectedRepeat' => [
                    'repeat_unit' => 'Sun',
                    'repeat_ordinal' => 'fourth',
                ],
            ],
            [
                'BYSETPOS' => null,
                'BYDAY' => '2MO',
                'expectedRepeat' => [
                    'repeat_unit' => 'Mon',
                    'repeat_ordinal' => 'second',
                ],
            ],
            [
                'BYSETPOS' => '1',
                'BYDAY' => 'SA,SU',
                'expectedRepeat' => [
                    'repeat_unit' => 'WE',
                    'repeat_ordinal' => 'first',
                ],
            ],
            [
                'BYSETPOS' => '2',
                'BYDAY' => 'MO,TU,WE,TH,FR',
                'expectedRepeat' => [
                    'repeat_unit' => 'WD',
                    'repeat_ordinal' => 'second',
                ],
            ],
        ];
    }

    public function dataProviderTranslateRRuleToSugarRecurrence()
    {
        return [
                [
                    'params' => [
                        "sugarSupportedRrule" => true,
                        "repeat_interval" => '1',
                        "date_start" => "2023-09-22T06:30:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "Each",
                        "repeat_days" => "1,10,27",
                        "repeat_until" => "2024-02-07T23:59:59",
                    ],
                    'RRuleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T093000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=20240207T235959Z;BYMONTHDAY=1,10,27',
                    'humanReadableString' => 'Monthly on the 1st, the 10th and the 27th of the month,'
                     . ' starting from 9/22/23, until 2/7/24',
                ],
                [
                    'params' => [
                        "sugarSupportedRrule" => true,
                        "repeat_interval" => '1',
                        "date_start" => "2023-09-22T07:00:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "On",
                        "repeat_ordinal" => "fourth",
                        "repeat_unit" => "Tue",
                        "repeat_count" => '10',
                    ],
                    'RruleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T100000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;COUNT=10;BYDAY=4TU',
                    'humanReadableString' => 'Monthly on the 4th Tuesday of the month, starting from 9/22/23, 10 times',
                ],
                [
                    'params' => [
                        "sugarSupportedRrule" => true,
                        "repeat_interval" => '1',
                        "date_start" => "2023-09-22T07:00:00",
                        "repeat_type" => "Monthly",
                        "repeat_selector" => "On",
                        "repeat_ordinal" => "first",
                        "repeat_unit" => "WD",
                        "repeat_until" => "2024-02-13T23:59:59",
                    ],
                    'RruleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T100000
                    RRULE:FREQ=MONTHLY;INTERVAL=1;UNTIL=20240213T235959Z;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=1',
                    'humanReadableString' => 'Monthly on Monday, Tuesday, Wednesday, Thursday and Friday,'
                     . ' but only the first instance of this set, starting from 9/22/23, until 2/13/24',
                ],
                [
                    'params' => [
                        "sugarSupportedRrule" => false,
                        'date_start' => '2023-09-22T07:00:00',
                    ],
                    'RruleString' => 'DTSTART;TZID=Europe/Bucharest:20230922T100000
                    RRULE:FREQ=MINUTELY;INTERVAL=1;UNTIL=20240213T235959Z',
                    'humanReadableString' => 'Minutely, starting from 9/22/23, until 2/13/24',
                ],
        ];
    }

    /**
     * @covers ::buildRecurringSequence
     * @dataProvider dataProviderForBuildRecurringSequenceTests
     */
    public function testBuildRecurringSequence($dateStart, $params, $expCount, $expFirst, $expLast)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $timezone = new DateTimeZone('GMT');
        $GLOBALS['current_user']->setPreference('timezone', $timezone->getName());
        $GLOBALS['current_user']->setPreference('datef', 'Y-m-d');
        $GLOBALS['current_user']->setPreference('timef', 'H:i');

        $dateStart = new DateTime($dateStart, $timezone);
        $dateStart = $calendarEventsUtilsMock->formatDateTime(
            'datetime',
            $dateStart->format('c'),
            'user',
            $GLOBALS['current_user']
        );

        $defaultParams = [
            'type' => 'Daily',
            'interval' => 1,
            'count' => 0,
            'until' => '',
            'dow' => '',
            'selector' => 'None',
            'days' => '',
            'ordinal' => '',
            'unit' => '',
        ];
        $params = array_merge($defaultParams, $params);
        $events = $calendarEventsUtilsMock->buildRecurringSequence($dateStart, $params);

        $this->assertCount($expCount, $events, 'An unexpected number of events were generated');
        $this->assertEquals($expFirst, $events[0], 'Unexpected date for the first event');
        $this->assertEquals($expLast, $events[$expCount - 1], 'Unexpected date for the last event');
    }

    /**
     * @covers ::getRruleString
     * @dataProvider dataProviderForRecurringEventsTests
     */
    public function getRruleString($params, $expectedRrule, $expectedOccurrences, $RruleString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->getRruleString($expectedRrule);

        $this->assertEquals($RruleString, $result);
    }

    /**
     * @covers :: getOccurrences
     * @dataProvider dataProviderForRecurringEventsTests
     */
    public function testGetOccurrences($params, $expectedRrule, $expectedOccurrences, $RruleString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->createRsetAndGetOccurrences($RruleString);

        $this->assertEquals($expectedOccurrences, $result);
    }

    /**
     * @covers ::createRsetAndGetOccurrences
     * @dataProvider dataProviderForRecurringEventsTests
     */
    public function testCreateRsetAndGetOccurrences($params, $expectedRrule, $expectedOccurrences, $RruleString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->createRsetAndGetOccurrences($RruleString);

        $this->assertEquals($expectedOccurrences, $result);
    }

    /**
     * @covers :: translateSugarRecurrenceToRRule
     * @dataProvider dataProviderForRecurringEventsTests
     */
    public function testTranslateSugarRecurrenceToRRule($params, $expectedRrule, $expectedOccurrences, $RruleString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();
        $paramsMassaged = $calendarEventsUtilsMock->massageParams($params);

        $result = $calendarEventsUtilsMock->translateSugarRecurrenceToRRule($paramsMassaged);
        $this->assertEquals($expectedRrule, $result);
    }

    /**
     * @covers :: convertRepeatDowToByDay
     * @dataProvider dataProviderConvertRepeatDowToByDay
     */
    public function testConvertRepeatDowToByDay($repeatDow, $expectedBYDAY)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->convertRepeatDowToByDay($repeatDow);

        $this->assertEquals($expectedBYDAY, $result);
    }

    /**
     * @covers :: setOnRRule
     * @dataProvider dataProviderSetOnRRule
     */
    public function testSetOnRRule($repeatType, $repeatOrdinal, $repeatUnit, $dateStartFormatted, $expected)
    {
        $rrule = [
            'RRULE' => [
                'FREQ' => 'MONTHLY',
                'INTERVAL' => '1',
                'UNTIL' => '20240207T235959Z',
            ],
            'DTSTART' => '20230922T093000',
        ];

        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->setOnRRule(
            $rrule,
            $repeatOrdinal,
            $repeatUnit,
            $repeatType,
            $dateStartFormatted
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers :: convertByDayToRepeatDow
     * @dataProvider dataProviderConvertByDayToRepeatDow
     */
    public function testConvertByDayToRepeatDow($expectedRepeatDOW, $BYDAY)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->convertByDayToRepeatDow($BYDAY);
        $this->assertEquals($expectedRepeatDOW, $result);
    }

    /**
     * @covers :: convertByDayToRepeatUnitAndOrdinal
     * @dataProvider dataProviderConvertByDayToRepeatUnitAndOrdinal
     */
    public function testConvertByDayToRepeatUnitAndOrdinal($BYSETPOS, $BYDAY, $expectedRepeat)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->convertByDayToRepeatUnitAndOrdinal($BYDAY, $BYSETPOS);
        $this->assertEquals($expectedRepeat, $result);
    }

    /**
     * @covers :: translateRRuleToSugarRecurrence
     * @dataProvider dataProviderTranslateRRuleToSugarRecurrence
     */
    public function testTranslateRRuleToSugarRecurrence($params, $RruleString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();
        $timezone = new DateTimeZone('Europe/Bucharest');

        $paramsDateStart = $params['date_start'];
        $startDateTimezone = SugarDateTime::createFromFormat('Y-m-d\TH:i:s', $paramsDateStart, $timezone);
        $startDateFormatted = $startDateTimezone->format('Y-m-d'.'\T'.'H:i:s');
        $params['date_start'] = $startDateFormatted;

        if (isset($params['repeat_until'])) {
            $paramsRepeatUntil = $params['repeat_until'];

            $repeatUntilTimezone = SugarDateTime::createFromFormat('Y-m-d\TH:i:s', $paramsRepeatUntil, $timezone);
            $repeatUntilFormatted = $repeatUntilTimezone->format('Y-m-d'.'\T'.'H:i:s');
            $params['repeat_until'] = $repeatUntilFormatted;
        }

        $result = $calendarEventsUtilsMock->translateRRuleToSugarRecurrence($RruleString);

        $this->assertEquals($params, $result);
    }

    /**
     * @covers ::getHumanReadableString
     * @dataProvider dataProviderTranslateRRuleToSugarRecurrence
     */
    public function testGetHumanReadableString($params, $RruleString, $humanReadableString)
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        $result = $calendarEventsUtilsMock->getHumanReadableString($RruleString);

        $this->assertEquals($humanReadableString, $result);
    }

    /**
     * @covers ::getHumanReadableString
     */
    public function testGetHumanReadableStringDifferentLanguage()
    {
        $calendarEventsUtilsMock = $this->getCalendarEventsUtilsMock();

        global $current_user;
        $current_user->preferred_language = 'de_DE';
        $current_user->save();

        $RruleString = "DTSTART;TZID=Europe/Bucharest:20231103T180000
        RRULE:FREQ=YEARLY;INTERVAL=1;COUNT=4;BYMONTH=11;BYDAY=4FR";

        $result = $calendarEventsUtilsMock->getHumanReadableString($RruleString);

        $this->assertEquals('Jährlich am 4. Freitag des Monats im November, ab dem 11/3/23, 4 Mal insgesamt', $result);
    }

    private function getCalendarEventsUtilsMock()
    {
        $reflector = new \ReflectionClass(CalendarEventsUtils::class);

        return $reflector->newInstanceWithoutConstructor();
    }

    private function getCalendarEventsApisMock()
    {
        $reflector = new \ReflectionClass(CalendarEventsApi::class);

        return $reflector->newInstanceWithoutConstructor();
    }
}
