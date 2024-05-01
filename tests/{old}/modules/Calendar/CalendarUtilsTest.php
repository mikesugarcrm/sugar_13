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

require_once 'modules/Meetings/Meeting.php';

class CalendarUtilsTest extends TestCase
{
    /**
     * @var \SugarBean|null|mixed
     */
    public $meeting;
    /**
     * @var \SugarBean|null|mixed
     */
    public $contact;
    /**
     * @var \SugarBean|null|mixed
     */
    public $lead;
    /**
     * @var TimeDate
     */
    protected $time_date;

    protected $meeting_id = null;

    protected function setUp(): void
    {
        global $current_user;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->time_date = new TimeDate();

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->name = 'Test Meeting';
        $meeting->assigned_user_id = $current_user->id;
        $meeting->save();
        $this->meeting = $meeting;

        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'MeetingTest';
        $contact->last_name = 'Contact';
        $contact->save();
        $this->contact = $contact;

        $lead = BeanFactory::newBean('Leads');
        $lead->first_name = 'MeetingTest';
        $lead->last_name = 'Lead';
        $lead->account_name = 'MeetingTest Lead Account';
        $lead->save();
        $this->lead = $lead;
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
        unset($this->meeting);

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
        unset($this->contact);

        $GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead->id}'");
        unset($this->lead);

        SugarTestHelper::tearDown();
    }

    public function testGetInvites()
    {
        global $current_user;
        global $locale;
        /** @var Meeting $meeting */
        $meeting = \BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->new_with_id = false;
        $meeting->processed = true;
        $meeting->email_reminder_time = '20';
        $meeting->name = 'Test Email Reminder';
        $meeting->assigned_user_id = $current_user->id;
        $meeting->status = 'Planned';
        $meeting->date_start = '2015-11-18 18:00:00';
        $meeting->save();

        $relate_values = ['contact_id' => $this->contact->id, 'meeting_id' => $meeting->id];
        $data_values = ['accept_status' => 'none'];
        $meeting->set_relationship($meeting->rel_contacts_table, $relate_values, true, true, $data_values);

        $invitesBefore = CalendarUtils::getInvitees($meeting);

        $relate_values = ['lead_id' => $this->lead->id, 'meeting_id' => $meeting->id];
        $data_values = ['accept_status' => 'accept'];
        $meeting->set_relationship($meeting->rel_leads_table, $relate_values, true, true, $data_values);

        $relate_values = ['user_id' => $current_user->id, 'meeting_id' => $meeting->id];
        $data_values = ['accept_status' => 'accept'];
        $meeting->set_relationship($meeting->rel_users_table, $relate_values, true, true, $data_values);

        $invitesAfter = CalendarUtils::getInvitees($meeting);

        $invitesBeforeExpected = [
            [
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact),
            ],
            [
                'Users',
                $meeting->assigned_user_id,
                $current_user->emailAddress->getPrimaryAddress($current_user),
                'none',
                $locale->formatName($current_user),
            ],
        ];

        $invitesAfterExpected = [
            [
                'Contacts',
                $this->contact->id,
                '',
                'none',
                $locale->formatName($this->contact),
            ],
            [
                'Leads',
                $this->lead->id,
                '',
                'accept',
                $locale->formatName($this->lead),
            ],
            [
                'Users',
                $current_user->id,
                $current_user->emailAddress->getPrimaryAddress($current_user),
                'accept',
                $locale->formatName($current_user),
            ],
        ];

        $this->assertEquals($invitesBeforeExpected, $invitesBefore);
        $this->assertEquals($invitesAfterExpected, $invitesAfter);
    }

    /**
     * @covers CalendarUtils::saveRecurring
     */
    public function testSaveRecurringUsesRelationshipFramework()
    {
        global $current_user;

        $meeting = $this->getMockBuilder('Meeting')->getMock();
        $meeting->id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $meeting->date_start = '2030-08-15 13:00:00';
        $meeting->date_end = '2030-08-15 18:15:00';
        $meeting->name = 'Test Meeting';
        $meeting->duration_hours = '1';
        $meeting->duration_minutes = '30';
        $meeting->repeat_type = 'Daily';
        $meeting->repeat_interval = 1;
        $meeting->repeat_count = 2;
        $meeting->repeat_until = null;
        $meeting->repeat_dow = null;
        $meeting->assigned_user_id = $current_user->id;
        $recurrencesTimeArray = ['2030-08-16 13:00'];

        $attendees = [new SugarBean(), new SugarBean()];

        $contactsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $contactsLinkMock->expects($this->once())->method('add');
        $meeting->contacts = $contactsLinkMock;

        $leadsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $leadsLinkMock->expects($this->once())->method('add');
        $meeting->leads = $leadsLinkMock;

        $usersLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $usersLinkMock->expects($this->exactly(count($attendees)))->method('add');
        $meeting->users = $usersLinkMock;

        $tagsLinkMock = $this->getMockBuilder('Link2')->disableOriginalConstructor()->getMock();
        $meeting->tag_link = $tagsLinkMock;

        $meeting->expects($this->any())
            ->method('get_linked_beans')
            ->will($this->returnValue($attendees));

        $meeting->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValueMap([
                ['contacts', true],
                ['leads', true],
                ['users', true],
            ]));

        CalendarUtils::saveRecurring($meeting, $recurrencesTimeArray);
    }

    public function activityStartAndEndDateProvider()
    {
        return [
            [
                '2018-03-28 16:00:00',
                '2018-03-28 16:30:00',
                'Y-m-d',
                'H:i:s',
                'America/New_York',
                [
                    'timestamp' => '1522238400',
                    'time_start' => '12:00:00',
                    'ts_start' => '1522195200',
                    'offset' => 43200,
                    'ts_end' => '1522281600',
                    'days' => 1,
                ],
            ],
            [
                '2018-03-28 12:00',
                '2018-03-28 12:30',
                'Y-m-d',
                'H:i',
                'America/New_York',
                [
                    'timestamp' => '1522238400',
                    'time_start' => '12:00',
                    'ts_start' => '1522195200',
                    'offset' => 43200,
                    'ts_end' => '1522281600',
                    'days' => 1,
                ],
            ],
            [
                '12-25-2018 11:00am',
                '12-25-2018 03:00pm',
                'm-d-Y',
                'h:ia',
                'America/Denver',
                [
                    'timestamp' => '1545735600',
                    'time_start' => '11:00am',
                    'ts_start' => '1545696000',
                    'offset' => 39600,
                    'ts_end' => '1545782400',
                    'days' => 1,
                ],
            ],
            [
                '15-12-2018 06:00 PM',
                '16-12-2018 02:00 PM',
                'd-m-Y',
                'h:i A',
                'America/Chicago',
                [
                    'timestamp' => '1544896800',
                    'time_start' => '06:00 PM',
                    'ts_start' => '1544832000',
                    'offset' => 64800,
                    'ts_end' => '1545004800',
                    'days' => 2,
                ],
            ],
            [
                '2018.12.21 16.45',
                '2018.12.24 13.00',
                'Y.m.d',
                'H.i',
                'Europe/Helsinki',
                [
                    'timestamp' => '1545410700',
                    'time_start' => '16.45',
                    'ts_start' => '1545350400',
                    'offset' => 60300,
                    'ts_end' => '1545696000',
                    'days' => 4,
                ],
            ],
            [
                '15,12,2018 06?00 PM',
                '16,12,2018 02?00 PM',
                'd,m,Y',
                'h?i A',
                'America/Los_Angeles',
                [
                    'timestamp' => '1544896800',
                    'time_start' => '06?00 PM',
                    'ts_start' => '1544832000',
                    'offset' => 64800,
                    'ts_end' => '1545004800',
                    'days' => 2,
                ],
            ],
        ];
    }

    /**
     * @covers       CalendarUtils::get_time_data
     *
     * @dataProvider activityStartAndEndDateProvider
     */
    public function testGetActivityTimeData($dateStart, $dateEnd, $datef, $timef, $tzone, $expected)
    {
        $GLOBALS['current_user']->setPreference('datef', $datef);
        $GLOBALS['current_user']->setPreference('timef', $timef);
        $GLOBALS['current_user']->setPreference('timezone', $tzone);

        $bean = new Meeting();
        $bean->date_start = $dateStart;
        $bean->date_end = $dateEnd;
        $result = CalendarUtils::get_time_data($bean);

        $this->assertSame(
            $expected,
            $result,
            "Activity start and end date in format {$datef} {$timef} with {$tzone} timezone has unexpected time data"
        );
    }
}
