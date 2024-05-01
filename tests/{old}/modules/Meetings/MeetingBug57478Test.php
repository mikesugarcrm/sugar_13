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

class MeetingBug57478Test extends TestCase
{
    protected $bean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testSendInvites()
    {
        $userInvitees = [];
        $fields = [
            'name' => 'UNIT TEST - Meeting with parent contact',
            'deleted' => '0',
            'status' => 'Planned',
            'reminder_time' => -1,
            'email_reminder_time' => -1,
            'email_reminder_sent' => 0,
            'repeat_interval' => 1,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'date_start' => date('Y-m-d H:i:s'),
            'direction' => 'Inbound',
            'duration_hours' => '0',
            'duration_minutes' => '30',
            'parent_type' => 'Contacts',
            'send_invites' => true,
            'parent_id' => 1,
        ];
        $meeting = new MeetingBug57478TestMock();
        foreach ($fields as $k => $v) {
            $meeting->$k = $v;
        }
        $userInvitees[] = $GLOBALS['current_user']->id;
        $meeting->users_arr = $userInvitees;
        $meeting->setUserInvitees($userInvitees);

        $expected = [$GLOBALS['current_user']->id];

        $meeting->save();

        $this->assertEquals($expected, $meeting->notified_users);
    }
}

class MeetingBug57478TestMock extends Meeting
{
    public $notified_users = [];

    public function send_assignment_notifications($notify_user, $admin)
    {
        $this->notified_users[] = $notify_user->id;
    }
}
