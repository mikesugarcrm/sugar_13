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
use RRule\RfcParser;

class RecurringCalendarEventTest extends TestCase
{
    protected $meetingIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->meetingIds = [];
        $this->eolSequence = PHP_EOL === '\r\n' ? '\r\n' : '\n';
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers save
     */
    public function testMeetingEmptyStatus()
    {

        $meetingData = [
            'new_with_id' => 2,
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting('', null, $meetingData);

        $this->meetingId = $meeting->id;

        $this->meetingIds[] = $this->meetingId;

        $this->assertEquals('Planned', $meeting->status);
    }

    /**
     * @covers save
     */
    public function testMeetingIsExistingTheAssignedUserIsNotTheCurrentUserTheCurrentUserIsNotInvited()
    {
        $meeting = $this->getNewMeeting();

        $invitees = $meeting->users->get();

        $this->assertCount(1, $invitees, 'Should only contain the assigned user');
        $this->assertContains($meeting->assigned_user_id, $invitees, 'The assigned user was not found');
    }

    /**
     * @covers save
     */
    public function testGetSummaryText()
    {
        $meeting = $this->getNewMeeting();

        $summaryText = $meeting->get_summary_text();

        $this->assertEquals($summaryText, $meeting->name);
    }

    /**
     * @covers save
     */
    public function testBuildInvitesList()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $meeting = $this->getNewMeeting($user);

        $invitees = $meeting->buildInvitesList();

        $this->assertEquals($invitees[$user->id]->first_name, $user->first_name);
        $this->assertEquals($invitees[$user->id]->last_name, $user->last_name);
    }

    /**
     * @covers save
     */
    public function testSetFillAdditionalColumnFields()
    {
        $meeting = $this->getNewMeeting();

        $meeting->setFillAdditionalColumnFields(['last_name']);

        $this->assertEquals($meeting->fill_additional_column_fields, ['last_name']);
    }

    /**
     * @covers save
     */
    public function testHandleInviteesForUserAssign()
    {
        $meeting = $this->getNewMeeting(false, false);

        $meeting->handleInviteesForUserAssign(false);

        $users = $meeting->users->get();
        $this->assertEquals(count($users), 1);
    }

    /**
     * @covers save
     */
    public function testGetNotificationRecipients()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $meeting = $this->getNewMeeting($user);

        $recipients = $meeting->get_notification_recipients();

        $this->assertEquals($recipients[0]->first_name, $user->first_name);
        $this->assertEquals($recipients[0]->last_name, $user->last_name);
    }

    /**
     * @covers save
     */
    public function testSetLeadInvitees()
    {
        $meeting = $this->getNewMeeting();

        $meeting->setLeadInvitees(['test_lead_id']);

        $this->assertEquals($meeting->leads_arr, ['test_lead_id']);
    }

    /**
     * @covers save
     */
    public function testSetContactInvitees()
    {
        $meeting = $this->getNewMeeting();

        $meeting->setContactInvitees(['test_contact_id']);

        $this->assertEquals($meeting->contacts_arr, ['test_contact_id']);
    }

    /**
     * @covers save
     */
    public function testSetUsersInvitees()
    {
        $meeting = $this->getNewMeeting();

        $meeting->setUserInvitees(['user_lead_id']);

        $this->assertEquals($meeting->users_arr, ['user_lead_id']);
    }

    public function getNewMeeting($user = false, $save = true)
    {

        if (!$user) {
            $user = SugarTestUserUtilities::createAnonymousUser();
        }

        $meetingData = [
            'name' => 'Test Meeting',
            'duration_hours' => '0',
            'duration_minutes' => '15',
            'duration_hours' => '0',
            'special_notification' => false,
            'id' => create_guid(),
            'users_arr' => [$user->id],
        ];
        $meeting = SugarTestMeetingUtilities::createMeeting('', $user, $meetingData, $save);

        $this->meetingIds[] = $meeting->id;

        return $meeting;
    }

    public function saveRecurringMeeting($name, $user = false, $save = true, $repeat_parent_id = null)
    {
        if (!$user) {
            $user = SugarTestUserUtilities::createAnonymousUser();
        }

        $meetingData = [
            'name' => $name,
            'repeat_type' => 'Daily',
            'date_start' => '2030-08-15 13:00:00',
            'date_end' => '2030-08-15 18:15:00',
            'duration_hours' => '1',
            'duration_minutes' => '30',
            'repeat_interval' => '1',
            'repeat_count' => '30',
            'repeat_until' => null,
            'repeat_dow' => null,
            'special_notification' => false,
            'users_arr' => [$user->id],
        ];

        if (isset($repeat_parent_id)) {
            $meetingData['repeat_parent_id'] = $repeat_parent_id;
        }

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $this->meetingIds[] = $meeting->id;

        return $meeting;
    }

    /**
     * @covers save
     */
    public function testManageRecurringMeetings()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $masterName = 'Test Recurring Master meeting';

        $masterMeetingSaved = $this->saveRecurringMeeting($masterName, false, true);

        $masterMeetingSaved->saveRecurringEvents();

        $occurrenceName = 'Test Recurring occurrence meeting';
        $repeat_parent_id = $masterMeetingSaved->id;

        $occurrenceMeetingSaved = $this->saveRecurringMeeting($occurrenceName, false, true, $repeat_parent_id);

        $this->assertEquals($masterMeetingSaved->event_type, 'master');
        $this->assertEquals($masterMeetingSaved->original_start_date, $masterMeetingSaved->date_start);

        $this->assertEquals($occurrenceMeetingSaved->event_type, 'occurrence');
        $this->assertEquals($occurrenceMeetingSaved->original_start_date, $occurrenceMeetingSaved->date_start);
    }

    /**
     * @covers generateRset
     */
    public function testGenerateRset()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting',
            'repeat_type' => 'Daily',
            'date_start' => '2023-08-15 13:00:00',
            'date_end' => '2030-08-15 18:15:00',
            'duration_hours' => '1',
            'duration_minutes' => '30',
            'repeat_interval' => '1',
            'repeat_count' => '30',
            'repeat_until' => null,
            'repeat_dow' => null,
            'special_notification' => false,
            'id' => create_guid(),
            'users_arr' => [$user->id],
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $this->meetingIds[] = $meeting->id;

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 1,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);
    }

    /**
     * @covers generateRecurrencePattern
     */
    public function testGenerateRecurrencePattern()
    {
        $targetRRule = 'DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence . 'RRULE:FREQ=DAILY;INTERVAL=1;COUNT=30';
        $rset = '{"rrule":"' . $targetRRule . '"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test testGenerateRecurrencePattern',
            'id' => create_guid(),
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 1,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        $this->assertEquals($meeting->repeat_type, 'Daily');
        $this->assertEquals($meeting->repeat_interval, '1');
        $this->assertEquals($meeting->repeat_count, '30');
    }

    /**
     * @covers generateRecurrencePattern
     */
    public function testGenerateRecurrencePattern_UnsupportedRrule()
    {
        $rset = '{"rrule":"DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence .
            'RRULE:FREQ=MINUTELY;INTERVAL=2;COUNT=30"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting unsupported from freq',
            'special_notification' => false,
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'MINUTELY',
            'INTERVAL' => 2,
            'COUNT' => 30,
            'sugarSupportedRrule' => false,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        $this->assertEquals($meeting->repeat_type, null);
        $this->assertEquals($meeting->repeat_interval, 1); //1 is the default value
        $this->assertEquals($meeting->repeat_count, null);
    }

    /**
     * @covers retrieving with malformed rset
     */
    public function testEventWithMalformedRset()
    {
        $rset = '{"rrule":"DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence .
            'RRULE:FREQ=DAILY;INTERVAL=2;COUNT=30"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting with malformed rset',
            'special_notification' => false,
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 2,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        // break rset make it a malformed JSON and save
        $meeting->rset = '{"exdate":';
        $meeting->processed = true;
        $meeting->save();
        $masterMeetingId = $meeting->id;

        $meeting = BeanFactory::retrieveBean('Meetings', $masterMeetingId);

        $meeting->retrieve($masterMeetingId);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;
        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        // now the rset should be good again
        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);

        $meeting->deleted = 1;
        $meeting->save();
    }

    /**
     * @covers retrieving with null rset
     */
    public function testEventWithNullRset()
    {
        $rset = '{"rrule":"DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence .
            'RRULE:FREQ=DAILY;INTERVAL=2;COUNT=30"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting with null rset',
            'special_notification' => false,
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 2,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        // break rset make it a null rset and save
        $meeting->rset = null;
        $meeting->processed = true;
        $meeting->save();
        $masterMeetingId = $meeting->id;

        $meeting = BeanFactory::retrieveBean('Meetings', $masterMeetingId);

        $meeting->retrieve($masterMeetingId);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        // now the rset should still be null
        $this->assertEquals($meeting->rset, null);

        // let's regenerate the rset
        $meeting->generateRset();

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;
        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        $meeting->deleted = 1;
        $meeting->save();
    }

    /**
     * @covers retrieving with empty rset
     */
    public function testEventWithEmptyRset()
    {
        $rset = '{"rrule":"DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence .
            'RRULE:FREQ=DAILY;INTERVAL=2;COUNT=30"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting with empty rset',
            'special_notification' => false,
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 2,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        // break rset make it an empty rset and save
        $meeting->rset = '';
        $meeting->processed = true;
        $meeting->save();
        $masterMeetingId = $meeting->id;

        $meeting = BeanFactory::retrieveBean('Meetings', $masterMeetingId);

        $meeting->retrieve($masterMeetingId);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        // now the rset should still be null
        $this->assertEquals($meeting->rset, null);

        // let's regenerate the rset
        $meeting->generateRset();

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;
        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        $meeting->deleted = 1;
        $meeting->save();
    }

    /**
     * @covers retrieving with valid rset
     */
    public function testEventWithValidRset()
    {
        $rset = '{"rrule":"DTSTART;TZID=UTC:20230815T130000' . $this->eolSequence .
            'RRULE:FREQ=DAILY;INTERVAL=2;COUNT=30"}';

        $user = SugarTestUserUtilities::createAnonymousUser();

        $meetingData = [
            'name' => 'Test Meeting with null rset',
            'special_notification' => false,
            'users_arr' => [$user->id],
            'rset' => $rset,
        ];

        $meeting = SugarTestMeetingUtilities::createRecurringMeeting('', $user, $meetingData);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;

        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        $expectedRSet = [
            'DTSTART' => new DateTime('2023-08-15 13:00:00', new DateTimeZone('UTC')),
            'FREQ' => 'DAILY',
            'INTERVAL' => 2,
            'COUNT' => 30,
            'sugarSupportedRrule' => true,
            'exdate' => [],
        ];

        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);
        $this->assertEquals($generatedRset['sugarSupportedRrule'], $expectedRSet['sugarSupportedRrule']);

        $meeting->save();
        $masterMeetingId = $meeting->id;

        $meeting = BeanFactory::retrieveBean('Meetings', $masterMeetingId);
        $meeting->retrieve($masterMeetingId);

        $decodedRset = json_decode($meeting->rset, true);
        $generatedRset = !empty($decodedRset) ? $decodedRset : $meeting->rset;
        $rruleProps = RfcParser::parseRRule($generatedRset['rrule']);

        // the rset should be still good
        $this->assertEquals($rruleProps['DTSTART'], $expectedRSet['DTSTART']);
        $this->assertEquals($rruleProps['FREQ'], $expectedRSet['FREQ']);
        $this->assertEquals($generatedRset['exdate'], $expectedRSet['exdate']);

        $meeting->deleted = 1;
        $meeting->save();
    }

    private function getCalendarEventsApisMock()
    {
        $reflector = new \ReflectionClass(CalendarEventsApi::class);

        return $reflector->newInstanceWithoutConstructor();
    }
}
