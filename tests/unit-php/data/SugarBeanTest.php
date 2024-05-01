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

namespace Sugarcrm\SugarcrmTestsUnit\data;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SugarBean
 */
class SugarBeanTest extends TestCase
{
    /**
     * @covers ::loadAutoIncrementValues()
     */
    public function testLoadAutoIncrementValuesWithAutoincrement()
    {
        // The field for the test
        $field = 'test_ai';

        // The value for the test
        $value = 4; // guaranteed to be random http://www.xkcd.com/221/

        // The sugar query object mock, needed to mock the return of execute
        $query = TestMockHelper::getObjectMock(
            $this,
            'SugarQuery',
            ['execute', 'from', 'select', 'where', 'equals']
        );

        // Mock out the chainable returns from the individual component method
        // calls since these are inside a private method that cannot be mocked
        $query->method('from')->will($this->returnValue($query));
        $query->method('select')->will($this->returnValue($query));
        $query->method('where')->will($this->returnValue($query));
        $query->method('equals')->will($this->returnValue($query));

        // The expected return of execute
        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([[$field => $value]]));

        // The mock bean object we will be working on
        $bean = TestMockHelper::getObjectMock(
            $this,
            'SugarBean',
            ['getSugarQueryObject']
        );

        // Set a bean ID since we need one
        $bean->id = 1;

        // Set the field defs on the bean
        $bean->field_defs = [
            $field => [
                'name' => $field,
                'type' => 'int',
                'auto_increment' => true,
            ],
        ];

        // And set the test property on the bean
        $bean->{$field} = null;

        // Set expectations
        $bean->expects($this->once())
            ->method('getSugarQueryObject')
            ->will($this->returnValue($query));

        // Call the method to test now
        TestReflection::callProtectedMethod($bean, 'loadAutoIncrementValues');

        // Verify what was done
        $this->assertEquals($bean->{$field}, $value);
    }

    /**
     * @covers ::populateDefaultValues()
     */
    public function testPopulateDefaultValues()
    {
        $bean = $this->getMockBuilder(\Lead::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $bean->field_defs['iFrameField_c'] = [
            'name' => 'iFrameField_c',
            'type' => 'iframe',
            'default' => 'abc.com/maps?q=&output',
        ];

        $bean->populateDefaultValues();
        $this->assertEquals(
            'abc.com/maps?q=&output',
            $bean->iFrameField_c,
            'iFrameField_c should contain "abc.com/maps?q=&output"'
        );
    }


    /**
     * @covers ::shouldSendAssignmentPushNotification
     * @dataProvider dataProviderShouldSendAssignmentPushNotification
     * @param $moduleName
     * @param $assignedToHasChanged
     * @param $assignedToId
     * @param $userCanReceivePush
     * @param $expected
     */
    public function testShouldSendAssignmentPushNotification(
        $moduleName,
        $assignedToHasChanged,
        $assignedToId,
        $userCanReceivePush,
        $expected
    ) {

        $mockBean = $this->createPartialMock('SugarBean', [
            'getCurrentUser',
            'createPushNotification',
            'getUserBean',
        ]);
        $mockBean->module_name = $moduleName;

        $stateChanges = [];
        if ($assignedToHasChanged) {
            $stateChanges['assigned_user_id'] = [
                'field_name' => 'assigned_user_id',
                'data_type' => 'id',
                'before' => '',
                'after' => $assignedToId,
            ];
        }

        $currentUserMock = $this->createPartialMock('User', []);
        $currentUserMock->full_name = 'Current User Name';
        $currentUserMock->id = 'current_user_id';
        $mockBean->method('getCurrentUser')->willReturn($currentUserMock);

        $userMock = $this->createPartialMock('User', [
            'canReceivePushNotifications',
        ]);
        $userMock->email1 = 'a@a.com';
        $userMock->id = 'recipient_id';
        $userMock->method('canReceivePushNotifications')->willReturn($userCanReceivePush);
        $mockBean->method('getUserBean')->will($this->returnValueMap([
            ['recipient_id', $userMock],
            ['non_existent_id', []],
        ]));

        $result = $mockBean->shouldSendAssignmentPushNotification($stateChanges);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function dataProviderShouldSendAssignmentPushNotification()
    {
        global $current_user;
        // module name, assigned to has changes, assigned to new value, user can receive push, expected value
        return [
            ['Accounts', true, 'recipient_id', true, true], // everything good here
            ['Accounts', false, null, true, false], // assigned to hasn't changed
            ['Accounts', true, '', true, false], // assigned to became empty
            ['Accounts', true, 'non_existent_id', true, false], // assigned to changed but user doesn't exist
            ['Accounts', true, $current_user->id, true, false], // assigned to same as editing user
            ['Accounts', true, 'recipient_id', false, false], // all good but user can't get pushes
            ['PushNotifications', true, 'recipient_id', true, false], // all good but module disallowed
        ];
    }

    /**
     * @covers ::sendPushNotificationOnAssignment
     */
    public function testSendPushNotificationOnAssignment()
    {
        $mockBean = $this->createPartialMock('SugarBean', [
            'getCurrentUser',
            'createPushNotification',
            'getUserBean',
            'getAssignmentPushNotificationText',
            'shouldSendAssignmentPushNotification',
        ]);
        $mockBean->method('getAssignmentPushNotificationText')->willReturn([
            'title' => 'Test push notification title',
            'description' => 'Test push notification description',
        ]);
        $mockBean->method('shouldSendAssignmentPushNotification')->willReturn(true);

        $mockBean->module_name = 'Accounts';
        $mockBean->id = 'test_account_id';
        $mockBean->name = 'Test Account';

        $stateChanges = [
            'assigned_user_id' => [
                'field_name' => 'assigned_user_id',
                'data_type' => 'id',
                'before' => '',
                'after' => 'my_user_id',
            ],
        ];

        $currentUserMock = $this->createPartialMock('User', []);
        $currentUserMock->full_name = 'Current User Name';
        $currentUserMock->id = 'current_user_id';
        $mockBean->method('getCurrentUser')->willReturn($currentUserMock);

        $userMock = $this->createPartialMock('User', []);
        $userMock->email1 = 'a@a.com';
        $userMock->id = 'recipient_id';
        $mockBean->method('getUserBean')->willReturn($userMock);

        $pushMock = $this->createPartialMock('PushNotification', [
            'send',
            'save',
        ]);
        $pushMock->method('send')->willReturn(true);
        $mockBean->method('createPushNotification')->willReturn($pushMock);

        $push = $mockBean->sendPushNotificationOnAssignment(true, $stateChanges);

        $this->assertEquals('record_assigned', $push->notification_type);
        $this->assertEquals($userMock->id, $push->assigned_user_id);
        $this->assertEquals($mockBean->module_name, $push->parent_type);
        $this->assertEquals($mockBean->id, $push->parent_id);

        $data = json_decode($push->extra_data, true);
        $this->assertEquals($currentUserMock->id, $data['data']['assigned_by_id']);
        $this->assertEquals($currentUserMock->full_name, $data['data']['assigned_by_name']);
        $this->assertEquals($mockBean->name, $data['data']['record_name']);
    }

}
