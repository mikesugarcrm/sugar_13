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

class Bug58011Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testAcceptanceAfterDateUpdate()
    {
        global $current_user, $db;

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $user = SugarTestUserUtilities::createAnonymousUser();

        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $current_user->id);
        SugarTestMeetingUtilities::addMeetingUserRelation($meeting->id, $user->id);

        // set this to 'accept' before handleSave and make sure it gets set to 'none' after handleSave
        $meeting->set_accept_status($user, 'accept');
        $meeting->save();

        $_POST['record'] = $_REQUEST['record'] = $meeting->id;
        $_POST['user_invitees'] = $current_user->id . ',' . $user->id;
        $_POST['module'] = 'Meetings';
        $_POST['action'] = 'Save';
        $_POST['assigned_user_id'] = $current_user->id;
        $_POST['send_invites'] = $_REQUEST['send_invites'] = 1;
        $_POST['date_start'] = $GLOBALS['timedate']->getNow()->asDb();
        $_POST['date_end'] = $GLOBALS['timedate']->getNow()->modify('+900 seconds')->asDb();

        $formBase = new MeetingFormBase();
        $formBase->handleSave('', false, false);

        $sql = "SELECT accept_status FROM meetings_users WHERE meeting_id='{$meeting->id}' AND user_id='{$user->id}'";
        $result = $db->query($sql);
        if ($row = $db->fetchByAssoc($result)) {
            $this->assertEquals('none', $row['accept_status'], 'Should be none after date changed and invite sent.');
        }
    }
}
