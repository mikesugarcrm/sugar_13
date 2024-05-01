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


class RestCallHelperTest extends RestTestBase
{
    public $call_id;

    protected function tearDown(): void
    {
        parent::tearDown();
        $GLOBALS['db']->query("DELETE FROM calls WHERE id = '{$this->call_id}'");
    }

    public function testCall()
    {
        // create a call linked to yourself, a contact, and a lead, verify the call is linked to each and on your calendar
        $call = [
            'name' => 'Test call',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
        ];

        $restReply = $this->restCall('Calls/', json_encode($call), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'call was not created, reply was: ' . print_r($restReply['reply'], true));

        $call_id = $restReply['reply']['id'];
        $this->call_id = $call_id;


        // verify the user has the Call, which will validate on calendar
        $restReplyUsers = $this->restCall("Calls/{$call_id}/link/users");
        $users_linked = [];
        foreach ($restReplyUsers['reply']['records'] as $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), 'Current User was not successfully linked');
        $this->assertTrue(in_array(1, $users_linked), 'Assigned User was not successfully linked');
    }

    public function testCallHeld()
    {
        // create a call linked to yourself, a contact, and a lead, verify the call is linked to each and on your calendar
        $call = [
            'name' => 'Test call',
            'duration' => 1,
            'date_start' => '2012-12-13T10:00:00-07:00',
            'date_end' => '2012-12-13T10:15:00-07:00',
            'assigned_user_id' => 1,
            'status' => 'Held',
        ];

        $restReply = $this->restCall('Calls/', json_encode($call), 'POST');

        $this->assertTrue(isset($restReply['reply']['id']), 'call was not created, reply was: ' . print_r($restReply['reply'], true));

        $call_id = $restReply['reply']['id'];
        $this->call_id = $call_id;


        // verify the user has the Call, which will validate on calendar
        $restReplyUsers = $this->restCall("Calls/{$call_id}/link/users");
        $users_linked = [];
        foreach ($restReplyUsers['reply']['records'] as $record) {
            $users_linked[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $users_linked), 'Current User was not successfully linked');
        $this->assertTrue(in_array(1, $users_linked), 'Assigned User was not successfully linked');
    }
}
