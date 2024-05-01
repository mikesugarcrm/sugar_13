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
 * @coversDefaultClass CalendarEvents
 */
class CalendarEventsTest extends TestCase
{
    protected $calendarEventsService;
    protected $meetingIds = [];

    protected function setUp(): void
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $this->calendarEventsService = new CalendarEvents();
        $this->meetingIds = [];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('datef', 'Y-m-d');
        $GLOBALS['current_user']->setPreference('timef', 'H:i');
    }

    protected function tearDown(): void
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id IN ('" . $ids . "')");
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = [];
        }
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        sugar_cache_reset_full();
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoRepeatType()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = null;
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, 'Expected Meeting Event to be Non-Recurring');
    }

    public function testCalendarEvents_Meeting_EventRecurring_NoDateStart()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = null;

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertFalse($result, 'Expected Meeting Event to be Non-Recurring');
    }

    public function testCalendarEvents_Meeting_EventRecurring_OK()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->date_start = '2014-12-25 18:00:00';

        $result = $this->calendarEventsService->isEventRecurring($meeting);

        $this->assertTrue($result, 'Expected Meeting Event to be recognized as Recurring');
    }

    public function testCalendarEvents_Account_EventRecurring_UnsupportedCalendarEventModule()
    {
        $account = BeanFactory::newBean('Accounts');

        $this->expectException(SugarException::class);
        $this->calendarEventsService->isEventRecurring($account);
    }

    public function testCalendarEvents_NonRecurringMeeting_NoDuration_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meeting = BeanFactory::newBean('Meetings');
        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);

        $this->assertEquals($meeting->date_start, '2015-01-01 12:00:00');
        $this->assertEquals('', $meeting->recurrence_id);
        $this->assertEquals(0, (int)$meeting->duration_hours, 'Expected Duration of Zero Hours');
        $this->assertEquals(0, (int)$meeting->duration_minutes, 'Expected Duration of Zero Minutes');
        $this->assertEquals($datetimeStart->asDb(), $datetimeEnd->asDb(), 'Expected End Datetime = Start DateTime');
    }

    public function testCalendarEvents_NonRecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 30;
        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);
        $meetingInterval = date_diff($datetimeStart, $datetimeEnd);

        $this->assertEquals($meeting->date_start, '2015-01-01 12:00:00');
        $this->assertEquals('', $meeting->recurrence_id);
        $this->assertEquals(1, $meetingInterval->h, 'Incorrect Duration Hours - Non Recurring Meeting');
        $this->assertEquals(30, $meetingInterval->i, 'Incorrect Duration Minutes - Non Recurring Meeting');
    }

    /**
     * Test for setting dates for recurring meeting.
     * Checks date_start, recurrence_id and date_end.
     *
     * @covers \CalendarEvents::setStartAndEndDateTime
     */
    public function testCalendarEvents_RecurringMeeting_SetStartAndEndDate_OK()
    {
        $format = TimeDate::DB_DATETIME_FORMAT;
        $timezone = new DateTimeZone('UTC');

        $sugarDateTime = SugarDateTime::createFromFormat($format, '2015-01-01 12:00:00', $timezone);

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->repeat_type = 'Daily';
        $meeting->repeat_count = 3;
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 30;
        $this->calendarEventsService->setStartAndEndDateTime($meeting, $sugarDateTime);

        $datetimeStart = SugarDateTime::createFromFormat($format, $meeting->date_start, $timezone);
        $datetimeEnd = SugarDateTime::createFromFormat($format, $meeting->date_end, $timezone);
        $meetingInterval = date_diff($datetimeStart, $datetimeEnd);

        $this->assertEquals($meeting->date_start, '2015-01-01 12:00:00');
        $this->assertEquals($meeting->date_start, $meeting->recurrence_id);
        $this->assertEquals(1, $meetingInterval->h, 'Incorrect Duration Hours - Recurring Meeting');
        $this->assertEquals(30, $meetingInterval->i, 'Incorrect Duration Minutes - Recurring Meeting');
    }

    public function testCalendarEvents_SaveRecurringEvents_EventsSaved()
    {
        $args = [];
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end'] = '2030-08-15 18:15:00';
        $args['name'] = 'Test Meeting';
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 3;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach ($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }
        $this->assertEquals(
            $args['repeat_count'],
            safeCount($eventsCreated) + 1,
            'Unexpected Number of Recurring Meetings Created'
        );
    }

    public function testCalendarEvents_SaveRecurringEventWithTags_TagsPropagateCorrectly()
    {
        $args = [];
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end'] = '2030-08-15 18:15:00';
        $args['name'] = 'Test Meeting';
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 3;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;

        $meeting = $this->newMeeting('', $args);
        $parentTags = $this->addTags($meeting, 3);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach ($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
            $meeting = BeanFactory::getBean('Meetings', $eventCreated['id']);
            $meeting->load_relationship('tag_link');
            $tags = $meeting->tag_link->get();
            $tagIds = [];
            foreach ($tags as $tagId) {
                $tagIds[$tagId] = true;
            }
            foreach ($parentTags as $parentTag) {
                $this->assertTrue(isset($tagIds[$parentTag->id]), 'Child Meeting Missing Tag On Parent');
                unset($tagIds[$parentTag->id]);
            }
            $this->assertTrue(empty($tagIds), 'Child Meeting Has Unexpected Tag');
        }
    }

    public function testCalendarEvents_SaveRecurringEvents_CurrentAssignedUserAutoAccepted()
    {
        $args = [];
        global $current_user;
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['date_end'] = '2030-08-15 18:15:00';
        $args['name'] = 'Test Meeting';
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['repeat_type'] = 'Daily';
        $args['repeat_interval'] = 1;
        $args['repeat_count'] = 2;
        $args['repeat_until'] = null;
        $args['repeat_dow'] = null;
        $args['assigned_user_id'] = $current_user->id;

        $meeting = $this->newMeeting('', $args);

        $calEvents = new CalendarEventsTest_CalendarEvents();
        $calEvents->saveRecurringEvents($meeting);

        $eventsCreated = $calEvents->getEventsCreated();
        foreach ($eventsCreated as $eventCreated) {
            $this->meetingIds[] = $eventCreated['id'];
        }

        $parentMeetingAcceptStatus = $meeting->users->rows[$current_user->id]['accept_status'];

        $childMeeting = BeanFactory::getBean('Meetings', $eventsCreated[0]['id']);
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

    public function testInviteParent_ParentIsContact_ShouldInviteButNotReInvite()
    {
        $args = [];
        global $current_user;
        $args['name'] = 'Test Meeting';
        $args['date_start'] = '2030-08-15 13:00:00';
        $args['duration_hours'] = '1';
        $args['duration_minutes'] = '30';
        $args['assigned_user_id'] = $current_user->id;

        $meeting = $this->newMeeting('', $args);
        $contact = SugarTestContactUtilities::createContact();

        $this->calendarEventsService->inviteParent($meeting, 'Contacts', $contact->id);
        $this->assertEquals([$contact->id], $meeting->contacts->get(), 'should be linked to the one contact');

        // try inviting again
        $this->calendarEventsService->inviteParent($meeting, 'Contacts', $contact->id);
        $this->assertEquals([$contact->id], $meeting->contacts->get(), 'should only have one link to the contact');

        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public function testInviteParent_ParentIsNotContactOrLead_ShouldNotInvite()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $this->calendarEventsService->inviteParent($meeting, 'Accounts', '123');
        $this->assertNull($meeting->accounts);
    }

    public function updateAcceptStatusForInviteePrimaryEventStatusProvider()
    {
        return [
            ['Held'],
            ['Not Held'],
        ];
    }

    /**
     * The primary event is not updated because it is either held or canceled. Any child events may still be updated.
     *
     * @dataProvider updateAcceptStatusForInviteePrimaryEventStatusProvider
     * @param $status
     */
    public function testUpdateAcceptStatusForInvitee_EventIsNotScheduled_OnlyChildEventsAreUpdated($status)
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->status = $status;
        $meeting1->expects($this->never())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $meeting2 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting2->id = create_guid();
        $meeting2->module_name = 'Meetings';
        $meeting2->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting2);

        $meetings = [
            ['id' => $meeting2->id],
        ];

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn($meetings);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(['getChildrenQuery', 'isEventRecurring'])
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            ['disable_row_level_security' => true]
        );

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::unregisterBean($meeting2);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EventIsNotRecurring_OnlyParentEventIsUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting->id = create_guid();
        $meeting->module_name = 'Meetings';
        $meeting->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(['getChildrenQuery', 'isEventRecurring'])
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(false);
        $events->expects($this->never())->method('getChildrenQuery');

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee($meeting, $invitee, 'tentative');

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EventIsRecurring_ParentAndChildrenAreUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $meeting2 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting2->id = create_guid();
        $meeting2->module_name = 'Meetings';
        $meeting2->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting2);

        $meeting3 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting3->id = create_guid();
        $meeting3->module_name = 'Meetings';
        $meeting3->expects($this->once())->method('set_accept_status');
        BeanFactory::registerBean($meeting3);

        $meetings = [
            ['id' => $meeting2->id],
            ['id' => $meeting3->id],
        ];

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn($meetings);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(['getChildrenQuery', 'isEventRecurring'])
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            ['disable_row_level_security' => true]
        );

        $this->assertTrue($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::unregisterBean($meeting2);
        BeanFactory::unregisterBean($meeting3);
        BeanFactory::setBeanClass('Meetings');
    }

    public function testUpdateAcceptStatusForInvitee_EntireSeriesHasBeenHeld_NoEventsAreUpdated()
    {
        BeanFactory::setBeanClass('Meetings', 'MockMeeting');

        $meeting1 = $this->getMockBuilder('Meeting')
            ->disableOriginalConstructor()
            ->setMockClassName('MockMeeting')
            ->setMethods(['set_accept_status'])
            ->getMock();
        $meeting1->id = create_guid();
        $meeting1->module_name = 'Meetings';
        $meeting1->status = 'Held';
        $meeting1->expects($this->never())->method('set_accept_status');
        BeanFactory::registerBean($meeting1);

        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $q->expects($this->once())->method('execute')->willReturn([]);

        $events = $this->getMockBuilder('CalendarEvents')
            ->disableOriginalConstructor()
            ->setMethods(['getChildrenQuery', 'isEventRecurring'])
            ->getMock();
        $events->expects($this->once())->method('isEventRecurring')->willReturn(true);
        $events->expects($this->once())->method('getChildrenQuery')->willReturn($q);

        $invitee = BeanFactory::getBean('Contacts', create_guid());
        $updated = $events->updateAcceptStatusForInvitee(
            $meeting1,
            $invitee,
            'tentative',
            ['disable_row_level_security' => true]
        );

        $this->assertFalse($updated);

        BeanFactory::unregisterBean($meeting1);
        BeanFactory::setBeanClass('Meetings');
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

    /**
     * @covers ::buildRecurringSequence
     * @dataProvider dataProviderForBuildRecurringSequenceTests
     */
    public function testBuildRecurringSequence($dateStart, $params, $expCount, $expFirst, $expLast)
    {
        // This test assumes that the current user's timezone is GMT. If this assumption were to change, then the
        // expected values being asserted would likely also have to change.
        $timezone = new DateTimeZone('GMT');
        $GLOBALS['current_user']->setPreference('timezone', $timezone->getName());
        $GLOBALS['current_user']->setPreference('datef', 'Y-m-d');
        $GLOBALS['current_user']->setPreference('timef', 'H:i');

        // Make sure we are always operating in the same timezone as the current user's timezone.
        $dateStart = new DateTime($dateStart, $timezone);
        $dateStart = $this->calendarEventsService->formatDateTime(
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
        $events = $this->calendarEventsService->buildRecurringSequence($dateStart, $params);

        $this->assertCount($expCount, $events, 'An unexpected number of events were generated');
        $this->assertEquals($expFirst, $events[0], 'Unexpected date for the first event');
        $this->assertEquals($expLast, $events[$expCount - 1], 'Unexpected date for the last event');
    }

    /**
     * Instantiate a new Meeting and prepopulate values from args
     * Add Meeting to meetingIds array to ensure its deletion on teardown
     * @param string $id meeting ID to assign
     * @param array $args assign field values to newly created meeting
     * @return Meeting
     */
    protected function newMeeting($id = '', $args = [])
    {
        global $current_user;
        $meeting = SugarTestMeetingUtilities::createMeeting($id, $current_user);
        if (!empty($args)) {
            foreach ($args as $k => $v) {
                $meeting->$k = $v;
            }
            $meeting->save();
        }
        return $meeting;
    }

    protected function newTags($numTags = 1)
    {
        $tags = [];
        while (count($tags) < $numTags) {
            $tags[] = SugarTestTagUtilities::createTag();
        }
        return $tags;
    }

    protected function addTags($bean, $numTags = 1)
    {
        $tags = $this->newTags($numTags);
        $bean->load_relationship('tag_link');
        foreach ($tags as $tag) {
            $bean->tag_link->add($tag);
        }
        $tags = $bean->tag_link->getBeans();
        return $tags;
    }

    protected function removeTags($bean)
    {
        $bean->load_relationship('tag_link');
        $tags = $bean->tag_link->getBeans();
        foreach ($tags as $tag) {
            $bean->tag_link->delete($bean->id, $tag);
        }
    }
}


class CalendarEventsTest_CalendarEvents extends CalendarEvents
{
    protected $eventsCreated = [];

    public function getEventsCreated()
    {
        return $this->eventsCreated;
    }

    protected function saveRecurring(SugarBean $parentBean, array $repeatDateTimeArray)
    {
        $this->eventsCreated = parent::saveRecurring($parentBean, $repeatDateTimeArray);
    }
}
