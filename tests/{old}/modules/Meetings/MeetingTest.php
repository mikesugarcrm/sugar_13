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

class MeetingTest extends TestCase
{
    public $meeting = null;
    public $contact = null;
    public $lead = null;

    protected function setUp(): void
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        SugarTestHelper::setUp('app_list_strings');

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
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        unset($GLOBALS['current_user']);
        unset($GLOBALS['mod_strings']);

        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting->id}'");
        unset($this->meeting);

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
        unset($this->contact);

        $GLOBALS['db']->query("DELETE FROM leads WHERE id = '{$this->lead->id}'");
        unset($this->lead);

        SugarTestHelper::tearDown();
    }

    public function testMeetingTypeSaveDefault()
    {
        // Assert doc type default is 'Sugar'
        $this->assertEquals($this->meeting->type, 'Sugar');
    }

    public function testMeetingTypeSaveDefaultInDb()
    {
        $query = "SELECT * FROM meetings WHERE id = '{$this->meeting->id}'";
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            // Assert doc type default is 'Sugar'
            $this->assertEquals($row['type'], 'Sugar');
        }
    }

    public function testEmailReminder()
    {
        global $current_user;
        $meeting = new Meeting();
        $meeting->email_reminder_time = '20';
        $meeting->name = 'Test Email Reminder';
        $meeting->assigned_user_id = $current_user->id;
        $meeting->status = 'Planned';
        $meeting->date_start = $GLOBALS['timedate']->nowDb();
        $meeting->save();

        $er = new EmailReminder();
        $to_remind = $er->getMeetingsForRemind();

        $this->assertTrue(in_array($meeting->id, $to_remind));
        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$meeting->id}'");
    }

    public function testMeetingFormBaseRelationshipsSetTest()
    {
        global $db;
        // setup $_POST
        $_POST = [];
        $_POST['name'] = 'MeetingTestMeeting';
        $_POST['lead_invitees'] = $this->lead->id;
        $_POST['contact_invitees'] = $this->contact->id;
        $_POST['assigned_user_id'] = $GLOBALS['current_user']->id;
        $_POST['date_start'] = date('Y-m-d H:i:s');
        // call handleSave
        $mfb = new MeetingFormBase();
        $meeting = $mfb->handleSave(null, false, false);
        // verify the relationships exist
        $q = "SELECT mu.contact_id FROM meetings_contacts mu WHERE mu.meeting_id = '{$meeting->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals($this->contact->id, $a['contact_id'], "Contact wasn't set as an invitee");

        $q = "SELECT mu.lead_id FROM meetings_leads mu WHERE mu.meeting_id = '{$meeting->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals($this->lead->id, $a['lead_id'], "Lead wasn't set as an invitee");

        $q = "SELECT mu.accept_status
              FROM meetings_users mu WHERE mu.meeting_id = '{$meeting->id}' AND user_id = '{$GLOBALS['current_user']->id}'";
        $r = $db->query($q);
        $a = $db->fetchByAssoc($r);
        $this->assertEquals('accept', $a['accept_status'], "Meeting wasn't accepted by the User");
    }

    public function testLoadFromRow()
    {
        /** @var Meeting $meeting */
        $meeting = BeanFactory::newBean('Meetings');
        $this->assertEmpty($meeting->reminder_checked);
        $this->assertEmpty($meeting->email_reminder_checked);

        $meeting->loadFromRow([
            'reminder_time' => 30,
            'email_reminder_time' => 30,
        ]);

        $this->assertTrue($meeting->reminder_checked);
        $this->assertTrue($meeting->email_reminder_checked);
    }

    public function testGetMeetingsExternalApiDropDown_NoCachedValues_ReturnsExternalAPIResults()
    {
        sugar_cache_clear('meetings_type_drop_down');
        //no way to mock out ExternalAPIFactory, so just using the value returned, likely empty array
        $expected = ExternalAPIFactory::getModuleDropDown('Meetings');
        $expected = array_merge(['Sugar' => 'Sugar'], $expected);
        $actual = getMeetingsExternalApiDropDown();
        $this->assertEquals($expected, $actual);
    }

    public function testGetMeetingsExternalApiDropDown_WithCachedValues_ReturnsCachedValues()
    {
        $cachedValues = ['Cached' => 'Cached'];
        sugar_cache_put('meetings_type_drop_down', $cachedValues);
        $actual = getMeetingsExternalApiDropDown();
        $this->assertEquals($cachedValues, $actual);
    }

    public function testGetMeetingsExternalApiDropDown_WithValuePassed_AppendValueToList()
    {
        $passedValue = 'PassedIn';
        $cachedValues = ['Cached' => 'Cached'];
        sugar_cache_put('meetings_type_drop_down', $cachedValues);
        $expected = array_merge($cachedValues, [$passedValue => $passedValue]);
        $actual = getMeetingsExternalApiDropDown(null, null, $passedValue);
        $this->assertEquals($expected, $actual);
    }

    public function testGetMeetingsExternalApiDropDown_OptionsOnMeta_AppendToList()
    {
        SugarTestHelper::setUp('dictionary');
        global $dictionary, $app_list_strings;
        $dictionary['Meeting']['fields']['type']['options'] = 'foo_type';
        $app_list_strings['foo_type'] = ['Foo' => 'Foo'];
        $cachedValues = ['Cached' => 'Cached'];
        sugar_cache_put('meetings_type_drop_down', $cachedValues);
        $expected = array_merge($cachedValues, $app_list_strings['foo_type']);
        $actual = getMeetingsExternalApiDropDown();
        $this->assertEquals($expected, $actual);
        unset($dictionary['Meeting']['fields']['type']['options']);
    }

    public function testGetNotificationRecipients_RecipientsAreAlreadyLoaded_ReturnsRecipients()
    {
        $contacts = [
            SugarTestContactUtilities::createContact(),
            SugarTestContactUtilities::createContact(),
        ];

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->users_arr = [$GLOBALS['current_user']->id];
        $meeting->contacts_arr = [$contacts[0]->id, $contacts[1]->id];

        $actual = $meeting->get_notification_recipients();
        $this->assertArrayHasKey($GLOBALS['current_user']->id, $actual, 'The current user should be in the list.');
        $this->assertArrayHasKey($contacts[0]->id, $actual, 'The first contact should be in the list.');
        $this->assertArrayHasKey($contacts[1]->id, $actual, 'The second contact should be in the list.');
    }

    public function testGetNotificationRecipients_RecipientsAreNotAlreadyLoaded_ReturnsEmptyRecipients()
    {
        $contacts = [
            SugarTestContactUtilities::createContact(),
            SugarTestContactUtilities::createContact(),
        ];

        $meeting = SugarTestMeetingUtilities::createMeeting();
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $GLOBALS['current_user']->id);
        SugarTestMeetingUtilities::addMeetingContactRelation($meeting->id, $contacts[0]->id);
        SugarTestMeetingUtilities::addMeetingContactRelation($meeting->id, $contacts[1]->id);

        $actual = $meeting->get_notification_recipients();
        $this->assertEmpty($actual, 'No invitees should have been loaded for this meeting.');
    }

    /**
     * Test that when assigned user is not a current one re-saved Meeting will contain only assigned user.
     * @covers \Meeting::save
     */
    public function testMeetingIsExistingTheAssignedUserIsNotTheCurrentUserTheCurrentUserIsNotInvited()
    {
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $meeting = $this->meeting = BeanFactory::newBean('Meetings');
        $meeting->id = create_guid();
        $meeting->name = 'Test Meeting';
        $meeting->duration_hours = '0';
        $meeting->duration_minutes = '15';
        $meeting->date_start = TimeDate::getInstance()->getNow()->asDb();
        $meeting->assigned_user_id = $user2->id;
        $meeting->save();

        $invitees = $meeting->users->get();
        $this->assertCount(1, $invitees, 'Should only contain the assigned user');
        $this->assertContains($meeting->assigned_user_id, $invitees, 'The assigned user was not found');
    }
}
