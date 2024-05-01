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

class CalendarEventTest extends TestCase
{
    protected $meetingIds = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');

        $this->meetingIds = [];
    }

    protected function tearDown(): void
    {
        if (!empty($this->meetingIds)) {
            $ids = implode("','", $this->meetingIds);
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id IN ('" . $ids . "')");
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id IN ('" . $ids . "')");
            $this->meetingIds = [];
        }

        SugarTestHelper::tearDown();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @covers ::inviteParent
     */
    public function testInviteParent_ParentIsContact_ShouldInviteButNotReInvite()
    {
        global $current_user;

        $meeting = BeanFactory::newBean('Meetings');
        $meeting->name = 'Test Meeting';
        $meeting->date_start = '2024-01-01 13:00:00';
        $meeting->duration_hours = '1';
        $meeting->duration_minutes = '30';
        $meeting->assigned_user_id = $current_user->id;

        $meeting->save();

        $this->meetingIds[] = $meeting->id;

        $contact = SugarTestContactUtilities::createContact();

        $meeting->inviteParent('Contacts', $contact->id);
        $this->assertEquals([$contact->id], $meeting->contacts->get(), 'should be linked to the one contact');

        $meeting->inviteParent('Contacts', $contact->id);
        $this->assertEquals([$contact->id], $meeting->contacts->get(), 'should only have one link to the contact');
    }

    /**
     * @covers ::inviteParent
     */
    public function testInviteParent_ParentIsNotContactOrLead_ShouldNotInvite()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->inviteParent('Accounts', '123');

        $this->assertNull($meeting->accounts);
    }

    public function testGetBeanDataArray()
    {
        $parentMeeting = SugarTestMeetingUtilities::createMeeting();
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->parent_type = 'Meetings';
        $meeting->parent_id = $parentMeeting->id;

        $beanData = $meeting->getBeanDataArray();

        $this->assertEquals($beanData['access'], 'yes');
        $this->assertEquals($beanData['status'], 'Planned');
        $this->assertEquals($beanData['type'], 'meeting');
    }

    public function testGetTimeData()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->date_start = '2023-10-10 07:30:00';
        $meeting->date_end = '2023-10-10 08:30:00';

        $timeData = $meeting->getTimeData();

        $this->assertEquals($timeData['timestamp'], '1696923000');
        $this->assertEquals($timeData['ts_start'], '1696896000');
        $this->assertEquals($timeData['ts_end'], '1696982400');
    }

    public function testListviewACLHelper()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $aclHelperData = $meeting->listviewACLHelper();

        $this->assertEquals($aclHelperData['MAIN'], 'a');
        $this->assertEquals($aclHelperData['PARENT'], 'a');
        $this->assertEquals($aclHelperData['CONTACT'], 'a');
    }

    public function testGetDefaultStatus()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $defaultStatus = $meeting->getDefaultStatus();


        $this->assertEquals($defaultStatus, '1');
    }
}
