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
 * @group api
 * @group meetings
 */
class MeetingsApiTest extends TestCase
{
    private $api;
    private $meetingsApi;

    protected function setUp(): void
    {
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = SugarTestUserUtilities::createAnonymousUser(false, false);
        $this->api->user->id = 'foo';
        $GLOBALS['current_user'] = $this->api->user;

        $this->meetingsApi = $this->createPartialMock('MeetingsApi', ['isUserInvitedToMeeting']);
    }

    protected function tearDown(): void
    {
        BeanFactory::setBeanClass('Meetings');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestHelper::tearDown();
    }

    public function testGetExternalInfo_UserIsAdmin_CanHostAndJoin()
    {
        //create an admin user
        $this->api->user = SugarTestUserUtilities::createAnonymousUser(false, true);
        $meeting = $this->mockMeetingForGetExternalInfo();
        BeanFactory::registerBean($meeting);
        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
        ];

        $actual = $this->meetingsApi->getExternalInfo($this->api, $args);
        $expected = [
            'is_host_option_allowed' => true,
            'host_url' => $meeting->host_url,
            'is_join_option_allowed' => true,
            'join_url' => $meeting->join_url,
        ];
        $this->assertEquals($expected, $actual);
        BeanFactory::unregisterBean($meeting);
    }

    public function testGetExternalInfo_UserIsDeveloperForMeetings_CanHostAndJoin()
    {
        //mock out user so we can return isDeveloperForModule = true
        $this->api->user = $this->createPartialMock('User', ['isAdmin', 'isDeveloperForModule']);
        $this->api->user->id = 'foo';
        $this->api->user->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(false));
        $this->api->user->expects($this->any())
            ->method('isDeveloperForModule')
            ->will($this->returnValue(true));

        $meeting = $this->mockMeetingForGetExternalInfo();
        BeanFactory::registerBean($meeting);
        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
        ];

        $actual = $this->meetingsApi->getExternalInfo($this->api, $args);
        $expected = [
            'is_host_option_allowed' => true,
            'host_url' => $meeting->host_url,
            'is_join_option_allowed' => true,
            'join_url' => $meeting->join_url,
        ];
        $this->assertEquals($expected, $actual);
        BeanFactory::unregisterBean($meeting);
    }

    public function testGetExternalInfo_UserIsAssignedUser_CanHostAndJoin()
    {
        $meeting = $this->mockMeetingForGetExternalInfo();
        $meeting->assigned_user_id = $this->api->user->id;
        BeanFactory::registerBean($meeting);
        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
        ];

        $actual = $this->meetingsApi->getExternalInfo($this->api, $args);
        $expected = [
            'is_host_option_allowed' => true,
            'host_url' => $meeting->host_url,
            'is_join_option_allowed' => true,
            'join_url' => $meeting->join_url,
        ];
        $this->assertEquals($expected, $actual);
        BeanFactory::unregisterBean($meeting);
    }

    public function testGetExternalInfo_UserIsAnInvitee_CanJoinOnly()
    {
        $meeting = $this->mockMeetingForGetExternalInfo();
        BeanFactory::registerBean($meeting);
        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
        ];

        $this->meetingsApi->expects($this->any())
            ->method('isUserInvitedToMeeting')
            ->will($this->returnValue(true));

        $actual = $this->meetingsApi->getExternalInfo($this->api, $args);
        $expected = [
            'is_host_option_allowed' => false,
            'host_url' => '',
            'is_join_option_allowed' => true,
            'join_url' => $meeting->join_url,
        ];
        $this->assertEquals($expected, $actual);
        BeanFactory::unregisterBean($meeting);
    }

    public function testGetExternalInfo_UserHasNoAssociationToMeeting_CanNotHostOrJoin()
    {
        $meeting = $this->mockMeetingForGetExternalInfo();
        BeanFactory::registerBean($meeting);
        $args = [
            'module' => 'Meetings',
            'record' => $meeting->id,
        ];

        $this->meetingsApi->expects($this->any())
            ->method('isUserInvitedToMeeting')
            ->will($this->returnValue(false));

        $actual = $this->meetingsApi->getExternalInfo($this->api, $args);
        $expected = [
            'is_host_option_allowed' => false,
            'host_url' => '',
            'is_join_option_allowed' => false,
            'join_url' => '',
        ];
        $this->assertEquals($expected, $actual);
        BeanFactory::unregisterBean($meeting);
    }

    protected function mockMeetingForGetExternalInfo()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->id = '123';
        $meeting->host_url = 'http://hosturl';
        $meeting->join_url = 'http://joinurl';
        $meeting->assigned_user_id = 'bar';
        return $meeting;
    }
}
