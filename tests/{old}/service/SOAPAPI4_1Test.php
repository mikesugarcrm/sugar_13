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

/***
 * SOAPAPI4_1Test.php
 * @author Collin Lee
 *
 * Unit test to test the get_modified_relationships function in SugarWebServiceImplv4_1.php
 */
class SOAPAPI4_1Test extends SOAPTestCase
{
    protected $contact1;
    protected $contact2;
    protected $another_user;
    protected $meeting1;
    protected $meeting2;
    protected $meeting3;
    protected $meeting4;
    protected $meeting5;
    protected $meeting6;
    protected $leads1;
    protected $leads2;
    protected $call1;
    protected $call2;

    /**
     * setUp
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v4_1/soap.php';
        parent::setUp();
        $this->login();
        global $current_user, $timedate;
        $this->another_user = SugarTestUserUtilities::createAnonymousUser();

        $this->contact1 = SugarTestContactUtilities::createContact();
        $this->contact1->contacts_users_id = $current_user->id;
        $this->contact1->first_name = 'First1';
        $this->contact1->last_name = 'Last1';
        $this->contact1->save();

        $this->contact1->user_sync->add($current_user);
        $this->contact1->sync_contact = 1;
        $this->contact1->save();

        $this->contact2 = SugarTestContactUtilities::createContact();
        $this->contact2->contacts_users_id = $this->another_user->id;
        $this->contact2->first_name = 'First2';
        $this->contact2->last_name = 'Last2';
        $this->contact2->save();

        $this->contact2->user_sync->add($this->another_user);
        $this->contact2->sync_contact = 1;
        $this->contact2->save();

        $this->call1 = SugarTestCallUtilities::createCall();
        $this->call1->name = 'SOAPAPI4_1Test1';
        $this->call1->load_relationship('users');
        $this->call1->users->add($current_user);
        $this->call1->save();

        $this->call2 = SugarTestCallUtilities::createCall();
        $this->call2->name = 'SOAPAPI4_1Test2';
        $this->call2->load_relationship('users');
        $this->call2->users->add($current_user);
        $this->call2->save();

        $this->meeting1 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting1->name = 'SOAPAPI4_1Test1';
        $this->meeting1->date_start = $timedate->nowDb();
        $this->meeting1->load_relationship('users');
        $this->meeting1->users->add($current_user);
        $this->meeting1->save();

        $this->meeting2 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting2->name = 'SOAPAPI4_1Test2';
        $this->meeting2->date_start = $timedate->nowDb();
        $this->meeting2->load_relationship('users');
        $this->meeting2->users->add($this->another_user);
        $this->meeting2->save();

        $this->meeting3 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting3->name = 'SOAPAPI4_1Test3';
        $this->meeting3->date_start = $timedate->nowDb();
        $this->meeting3->load_relationship('users');
        $this->meeting3->users->add($current_user);
        $this->meeting3->save();

        $this->meeting4 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting4->name = 'SOAPAPI4_1Test4';
        $this->meeting4->date_start = $timedate->nowDb();
        $this->meeting4->load_relationship('users');
        $this->meeting4->users->add($current_user);
        $this->meeting4->mark_deleted($this->meeting4->id);
        $this->meeting4->deleted = 1;
        $this->meeting4->save();

        $this->meeting5 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting5->name = 'SOAPAPI4_1Test5';
        //Set this to a week ago
        $this->meeting5->date_start = $timedate->asDb($timedate->getNow()->get('-7 days'));
        $this->meeting5->load_relationship('users');
        $this->meeting5->users->add($current_user);
        $this->meeting5->save();

        $this->meeting6 = SugarTestMeetingUtilities::createMeeting();
        $this->meeting6->name = 'SOAPAPI4_1Test6';
        //Set this to a week later
        $this->meeting6->date_start = $timedate->asDb($timedate->getNow()->get('+7 days'));
        $this->meeting6->load_relationship('users');
        $this->meeting6->users->add($current_user);
        $this->meeting6->save();

        $this->leads1 = SugarTestLeadUtilities::createLead();
        $this->leads1->first_name = 'First1';
        $this->leads1->last_name = 'Last1';
        $this->leads1->save();

        $this->meeting1->load_relationship('leads');
        $this->meeting1->leads->add($this->leads1);
        $this->meeting1->save();

        //We need to set date_modified manually since SugarBean code sets it upon call to save function
        $GLOBALS['db']->query('UPDATE meetings SET date_modified = ' . $GLOBALS['db']->quoteType('datetime', $timedate->asDb($timedate->getNow()->get('-7 days'))) . " WHERE id = '" . $this->meeting5->id . "'");
        $GLOBALS['db']->query('UPDATE meetings SET date_modified = ' . $GLOBALS['db']->quoteType('datetime', $timedate->asDb($timedate->getNow()->get('+7 days'))) . " WHERE id = '" . $this->meeting6->id . "'");
        $GLOBALS['db']->commit();
    }

    /**
     * tearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestCallUtilities::removeAllCreatedCalls();
    }

    /**
     * testGetModifiedRelationships
     */
    public function testGetModifiedRelationships()
    {
        global $timedate, $current_user;
        $one_hour_ago = $timedate->asDb($timedate->getNow()->get('-1 hours'));
        $one_hour_later = $timedate->asDb($timedate->getNow()->get('+1 hours'));
        $callsAndMeetingsFields = ['id', 'date_modified', 'deleted', 'name', 'status', 'rt.deleted synced'];
        $contactsSelectFields = ['id', 'date_modified', 'deleted', 'first_name', 'last_name', 'rt.deleted synced'];
        $leadsFields = ['id', 'name', 'date_modified', 'rt.lead_id', 'rt.meeting_id'];

        //Test that we only get 2 meetings based on the date_modified range
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', $one_hour_ago, $one_hour_later, 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(2, $result['result_count']);
        $this->assertEquals(2, $result['next_offset']);

        //Test that we get 4 meetings based on an expanded date_modified range
        $eight_days_ago = $timedate->asDb($timedate->getNow()->get('-8 days'));
        $eight_days_later = $timedate->asDb($timedate->getNow()->get('+8 days'));
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', $eight_days_ago, $eight_days_later, 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(4, $result['result_count']);
        $this->assertEquals(4, $result['next_offset']);

        //Test that we get an error if we don't supply a from_date value
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', '', $one_hour_later, 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertEmpty($result['entry_list']);
        $this->assertNotEmpty($result['error'], 'Failed to get error from result with empty from_date');

        //Test that we get an error if we don't supply a to_date value
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', $one_hour_ago, '', 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertEmpty($result['entry_list']);
        $this->assertNotEmpty($result['error'], 'Failed to get error from result with empty from_date');

        //Test that we get an error if we don't supply both a from_date or to_date value
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', '', '', 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertEmpty($result['entry_list']);
        $this->assertNotEmpty($result['error'], 'Failed to get error from result with empty from_date');

        //Test that we get 2 entries if we don't supply a user id (defaults to current user)
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', $one_hour_ago, $one_hour_later, 0, 10, 0, '', $callsAndMeetingsFields, 'meetings_users', '');
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(2, $result['result_count']);
        $this->assertEquals(2, $result['next_offset']);

        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Meetings', $one_hour_ago, $one_hour_later, 0, 10, '1', $current_user->id, $callsAndMeetingsFields, 'meetings_users', $one_hour_ago);
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(1, $result['result_count']);
        $this->assertEquals(1, $result['next_offset']);

        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Calls', $one_hour_ago, $one_hour_later, 0, 10, 0, $current_user->id, $callsAndMeetingsFields, 'calls_users', '');
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(2, $result['result_count']);
        $this->assertEquals(2, $result['next_offset']);

        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'Users', 'Contacts', $one_hour_ago, $one_hour_later, 0, 10, 0, $current_user->id, $contactsSelectFields, 'contacts_users', '');
        $result = object_to_array_deep($result);
        $this->assertNotEmpty($result['entry_list']);
        $this->assertEquals(1, $result['result_count']);
        $this->assertEquals(1, $result['next_offset']);

        //Test an incorrect relationship
        $result = $this->soapClient->get_modified_relationships($this->sessionId, 'GooberVille', 'UberVille', $one_hour_ago, $one_hour_later, 0, 10, 0, $current_user->id, $leadsFields, 'goober_uber', '');
        $result = object_to_array_deep($result);
        $this->assertEquals(20, $result['faultcode'], 'Failed to trigger a SOAP fault code for invalid relationship');
    }
}
