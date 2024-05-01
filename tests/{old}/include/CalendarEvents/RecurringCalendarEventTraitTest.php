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

class RecurringCalendarEventTraitTest extends TestCase
{
    protected $meetingIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->meetingIds = [];
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ::isEventRecurring
     */
    public function testCalendarEvents_Meeting_EventRecurring_NoRepeatType()
    {
        $meetingData = [
            'repeat_type' => null,
            'date_start' => '2014-12-25 18:00:00',
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting('', null, $meetingData);

        $result = $meeting->isEventRecurring();

        $this->assertFalse($result, 'Expected Meeting Event to be Non-Recurring');
    }

    /**
     * @covers ::isEventRecurring
     */
    public function testCalendarEvents_Meeting_EventRecurring_NoDateStart()
    {
        $meetingData = [
            'repeat_type' => 'Daily',
            'date_start' => null,
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting('', null, $meetingData);

        $result = $meeting->isEventRecurring();

        $this->assertFalse($result, 'Expected Meeting Event to be Non-Recurring');
    }

    /**
     * @covers ::isEventRecurring
     */
    public function testCalendarEvents_Meeting_EventRecurring_OK()
    {
        $meetingData = [
            'repeat_type' => 'Daily',
            'date_start' => '2014-12-25 18:00:00',
        ];
        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', null, $meetingData);

        $result = $meeting->isEventRecurring();

        $this->assertTrue($result, 'Expected Meeting Event to be recognized as Recurring');
    }

    /**
     * @covers ::saveRecurringEvents
     */
    public function testCalendarEvents_SaveRecurringEvents_EventsSaved()
    {
        $meetingData = [
            'name' => 'SaveRecurringEvents_EventsSaved meeting',
            'repeat_type' => 'Daily',
            'date_start' => '2030-08-15 13:00:00',
            'date_end' => '2030-08-15 18:15:00',
            'duration_hours' => '1',
            'duration_minutes' => '30',
            'repeat_interval' => 1,
            'repeat_count' => 3,
            'repeat_until' => null,
            'repeat_dow' => null,
        ];
        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', null, $meetingData);

        $this->meetingIds[] = $meeting->id;

        $meeting->saveRecurringEvents();

        $sugarQuery = new SugarQuery();
        $sugarQuery->from(BeanFactory::newBean('Meetings'));
        $sugarQuery->where()->equals('repeat_parent_id', $meeting->id);
        $result = $sugarQuery->execute();

        $eventsCreatedIds = [];

        foreach ($result as $eventCreatedId) {
            $eventsCreatedIds[] = $eventCreatedId['id'];
            $this->meetingIds[] = $eventCreatedId['id'];
        }

        $this->assertEquals(
            $meeting->repeat_count,
            safeCount($eventsCreatedIds) + 1,
            'Unexpected Number of Recurring Meetings Created'
        );
    }

    /**
     * @covers ::saveRecurringEvents
     */
    public function testCalendarEvents_SaveRecurringEvents_CurrentAssignedUserAutoAccepted()
    {
        global $current_user;

        $meetingData = [
            'name' => 'SaveRecurringEvents_CurrentAssignedUserAutoAccepted',
            'repeat_type' => 'Daily',
            'date_start' => '2030-08-15 13:00:00',
            'date_end' => '2030-08-15 18:15:00',
            'duration_hours' => '1',
            'duration_minutes' => '30',
            'repeat_interval' => 1,
            'repeat_count' => 3,
            'repeat_until' => null,
            'repeat_dow' => null,
        ];
        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', null, $meetingData);

        $this->meetingIds[] = $meeting->id;

        $meeting->saveRecurringEvents();

        $sugarQuery = new SugarQuery();
        $sugarQuery->from(BeanFactory::newBean('Meetings'));
        $sugarQuery->where()->equals('repeat_parent_id', $meeting->id);
        $result = $sugarQuery->execute();

        $eventsCreatedIds = [];

        foreach ($result as $eventCreatedId) {
            $eventsCreatedIds[] = $eventCreatedId['id'];
            $this->meetingIds[] = $eventCreatedId['id'];
        }

        $parentMeetingAcceptStatus = $meeting->users->rows[$current_user->id]['accept_status'];

        $childMeeting = BeanFactory::getBean('Meetings', $eventsCreatedIds[0]);
        $childMeeting->load_relationship('users');
        $childMeeting->users->load();

        $childMeetingAcceptStatus = $childMeeting->users->rows[$current_user->id]['accept_status'];

        $this->assertEquals(
            $parentMeetingAcceptStatus,
            'accept',
            'Current user should have auto-accepted in parent meeting'
        );
        $this->assertEquals(
            $childMeetingAcceptStatus,
            'accept',
            'Current user should have auto-accepted in child meeting'
        );
    }

    /**
     * @covers ::setStartAndEndDateTime
     */
    public function testCalendarEvents_NonRecurringMeeting_NoDuration_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2024-01-01 12:00:00', $timezone);

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', null);

        $meeting->setStartAndEndDateTime($sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);

        $this->assertEquals($meeting->date_start, '2024-01-01 12:00:00');
        $this->assertEquals('', $meeting->recurrence_id);
        $this->assertEquals(0, (int)$meeting->duration_hours, 'Expected Duration of Zero Hours');
        $this->assertEquals(0, (int)$meeting->duration_minutes, 'Expected Duration of Zero Minutes');
        $this->assertEquals($datetimeStart->asDb(), $datetimeEnd->asDb(), 'Expected End Datetime = Start DateTime');
    }

    /**
     * @covers ::setStartAndEndDateTime
     */
    public function testCalendarEvents_NonRecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meetingData = [
            'duration_hours' => 1,
            'duration_minutes' => 30,
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting('', null, $meetingData);

        $meeting->setStartAndEndDateTime($sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);
        $meetingInterval = date_diff($datetimeStart, $datetimeEnd);

        $this->assertEquals($meeting->date_start, '2015-01-01 12:00:00');
        $this->assertEquals('', $meeting->recurrence_id);
        $this->assertEquals(1, $meetingInterval->h, 'Incorrect Duration Hours - Non Recurring Meeting');
        $this->assertEquals(30, $meetingInterval->i, 'Incorrect Duration Minutes - Non Recurring Meeting');
    }

    /**
     * @covers ::setStartAndEndDateTime
     */
    public function testCalendarEvents_RecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');
        $eolSequence = PHP_EOL === '\r\n' ? '\r\n' : '\n';

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 10:00:00', $timezone);

        $meetingData = [
            'duration_hours' => 1,
            'duration_minutes' => 30,
            'repeat_type' => 'Daily',
            'repeat_count' => 10,
            'name' => 'RecurringMeeting_SetStartAndEndDate_OK',
            'rset' => '{"rrule":"DTSTART;TZID=Europe/Bucharest:20150101T120000' . $eolSequence .
                'RRULE:FREQ=MONTHLY;INTERVAL=2;COUNT=30","exdate":[],"sugarSupportedRrule":false}',
        ];
        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', null, $meetingData);

        $meeting->setStartAndEndDateTime($sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);
        $meetingInterval = date_diff($datetimeStart, $datetimeEnd);

        $this->assertEquals($meeting->date_start, '2015-01-01 10:00:00');
        $this->assertEquals($meeting->date_start, $meeting->recurrence_id);
        $this->assertEquals(1, $meetingInterval->h, 'Incorrect Duration Hours - Recurring Meeting');
        $this->assertEquals(30, $meetingInterval->i, 'Incorrect Duration Minutes - Recurring Meeting');
    }

    public function createRecurringMeeting($user = null)
    {
        $meetingData = [
            'name' => 'Test Meeting',
            'repeat_type' => 'Daily',
            'date_start' => '2023-08-15 13:00:00',
            'date_end' => '2023-08-15 14:30:00',
            'duration_hours' => '1',
            'duration_minutes' => '30',
            'repeat_interval' => 1,
            'repeat_count' => 3,
            'repeat_until' => null,
            'repeat_dow' => null,
        ];
        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        return $meeting;
    }

    /**
     * @covers ::getOccurrencesArray
     */
    public function testGetOccurrencesArray()
    {
        global $timedate;

        $meeting = $this->createRecurringMeeting();

        $expectedOccurrences  = [
            '2023-08-15 13:00',
            '2023-08-16 13:00',
            '2023-08-17 13:00',
        ];


        foreach ($expectedOccurrences as $key => $occurenceDatetime) {
            $datetimeFormat = $timedate->get_date_time_format();
            $expectedDate = SugarDateTime::createFromFormat('Y-m-d H:i', $occurenceDatetime);
            $expectedDate = $expectedDate->format($datetimeFormat);

            $expectedOccurrences[$key] = $expectedDate;
        }

        $this->assertEquals($meeting->getOccurrencesArray(), $expectedOccurrences);
    }

    /**
     * @covers ::updateRsetExDate
     */
    public function testUpdateRsetExDate()
    {
        $meeting = $this->createRecurringMeeting();

        $this->meetingIds[] = $meeting->id;

        $meeting->updateRsetExDate('2014-12-27 13:00:00');

        $sugarQuery = new SugarQuery();
        $sugarQuery->from(BeanFactory::newBean('Meetings'));
        $sugarQuery->where()->equals('id', $meeting->id);
        $result = $sugarQuery->execute();

        $RSET = $result[0]['rset'];

        $rsetNew = json_decode($RSET);
        $this->assertEquals('2014-12-27 13:00:00', $rsetNew->exdate[0]);
    }

    /**
     * @covers ::updateRsetExDate
     */
    public function testDoesntUpdateRsetExDate()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting('', null);

        $meeting->updateRsetExDate('2014-12-27 13:00:00');

        $this->assertEquals('', $meeting->rset);
    }

     /**
     * @covers ::getRsetExDate, updateRsetExDate
     */
    public function testGetRsetExDate()
    {
        $meeting = $this->createRecurringMeeting();

        $this->meetingIds[] = $meeting->id;

        $result = $meeting->getRsetExDate();

        $this->assertEquals([], $result);

        $meeting->updateRsetExDate('2023-11-01 13:30:00');

        $resultAddedValue = $meeting->getRsetExDate();

        $this->assertEquals(['2023-11-01 13:30:00'], $resultAddedValue);
    }

    /**
     * @covers ::getRsetRrule
     */
    public function testGetRsetRrule()
    {

        $meeting = $this->createRecurringMeeting();

        $this->meetingIds[] = $meeting->id;

        $result = $meeting->getRsetRrule();

        $expRrule = 'DTSTART;TZID=UTC:20230815T130000' . PHP_EOL . 'RRULE:FREQ=DAILY;INTERVAL=1;COUNT=3';
        $this->assertEquals($expRrule, $result);
    }

    /**
     * @covers ::getRsetSugarSupportedRrule
     */
    public function testGetRsetSugarSupportedRrule()
    {

        $meeting = $this->createRecurringMeeting();

        $this->meetingIds[] = $meeting->id;

        $result = $meeting->getRsetSugarSupportedRrule();

        $this->assertEquals(true, $result);
    }
}
