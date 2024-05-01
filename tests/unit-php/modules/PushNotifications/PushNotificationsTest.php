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

namespace Sugarcrm\SugarcrmTestsUnit\modules\PushNotifications;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\PushNotification\SugarPush\SugarPush;

/**
 * @coversDefaultClass \PushNotification
 */
class PushNotificationsTest extends TestCase
{
    /**
     * @covers ::send
     */
    public function testSend()
    {
        $user = 'test_user';
        $title = 'message title';
        $body = 'message body';
        $module = 'Accounts';
        $record = 'account_id';
        $type = 'record_assignment';
        $android = [
            'icon' => 'android_icon_url',
        ];
        $ios = [
            'icon' => 'ios_icon_url',
        ];
        $extra = 'something';
        $data = [
            'notification_type' => $type,
            'module_name' => $module,
            'record_id' => $record,
            'extra_data' => json_encode($extra),
        ];
        $message = [
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'android' => $android,
            'ios' => $ios,
        ];
        $serviceMock = $this->getMockBuilder(SugarPush::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();
        $serviceMock->expects($this->once())
            ->method('send')
            ->with($this->equalTo([$user]), $this->equalTo($message));
        $userMock = $this->getMockBuilder(\User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $userMock->id = $user;
        $pushMock = $this->createPartialMock('PushNotification', ['getService', 'getAssignedUser']);
        $pushMock->expects($this->once())
            ->method('getService')
            ->willReturn($serviceMock);
        $pushMock->expects($this->once())
            ->method('getAssignedUser')
            ->willReturn($userMock);
        $pushMock->name = $title;
        $pushMock->description = $body;
        $pushMock->notification_type = $type;
        $pushMock->parent_type = $module;
        $pushMock->parent_id = $record;
        $pushMock->extra_data = json_encode([
            'data' => $extra,
            'android' => $android,
            'ios' => $ios,
        ]);
        $pushMock->send();
    }
}
