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


class RestMeetingHelperTest extends RestTestBase
{
    public $meeting_id;

    protected function tearDown(): void
    {
        parent::tearDown();
        $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting_id}'");
    }

    public function testMeeting()
    {
        // create a meeting linked to yourself, a contact, and a lead, verify the meeting is linked to each and on your calendar
        $meeting = [
            'name' => 'Test Meeting',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall('Meetings/', json_encode($meeting), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'Meeting was not created, reply was: ' . print_r($restReply, true));

        $meeting_id = $restReply['reply']['id'];
        $this->meeting_id = $meeting_id;


        // verify the user has the meeting, which will validate on calendar
        $restReplyUsers = $this->restCall("Meetings/{$meeting_id}/link/users");
        $users_linked = [];
        foreach ($restReplyUsers['reply']['records'] as $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), 'Current User was not successfully linked');
        $this->assertTrue(in_array(1, $users_linked), 'Assigned User was not successfully linked');
    }

    public function testMeetingHeld()
    {
        // create a meeting linked to yourself, a contact, and a lead, verify the meeting is linked to each and on your calendar
        $meeting = [
            'name' => 'Test Meeting',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
            'status' => 'Held',
        ];

        $restReply = $this->restCall('Meetings/', json_encode($meeting), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'Meeting was not created, reply was: ' . print_r($restReply, true));

        $meeting_id = $restReply['reply']['id'];
        $this->meeting_id = $meeting_id;


        // verify the user has the meeting, which will validate on calendar
        $restReplyUsers = $this->restCall("Meetings/{$meeting_id}/link/users");
        $users_linked = [];
        foreach ($restReplyUsers['reply']['records'] as $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), 'Current User was not successfully linked');
        $this->assertTrue(in_array(1, $users_linked), 'Assigned User was not successfully linked');
    }
}
