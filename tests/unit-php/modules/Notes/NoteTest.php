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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Notes;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Note
 */
class NoteTest extends TestCase
{
    /**
     * @covers ::send_assignment_notifications
     */
    public function testSendAssignmentNotifications()
    {
        $user = $this->createMock('\\User');
        $user->receive_notifications = true;
        $admin = $this->createMock('\\Administration');

        $note = $this->createPartialMock('\\Note', [
            'create_notification_email',
            'getTemplateNameForNotificationEmail',
            'createNotificationEmailTemplate',
        ]);
        $note->email_id = Uuid::uuid1();
        $note->expects($this->never())->method('create_notification_email');
        $note->expects($this->never())->method('getTemplateNameForNotificationEmail');
        $note->expects($this->never())->method('createNotificationEmailTemplate');
        $note->send_assignment_notifications($user, $admin);
    }

    /**
     * @covers ::setAttachmentTeams
     */
    public function testSetAttachmentTeams()
    {
        $note = $this->createMock(\Note::class);
        $attachment = $this->getMockBuilder(\Note::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $note->team_id = 'West';
        $note->team_set_id = 'West';
        $note->acl_team_set_id = 'West';
        $note->assigned_user_id = 'seed_jim_id';
        $attachment->team_id = 'East';
        $attachment->team_set_id = 'East';
        $attachment->acl_team_set_id = 'East';
        $attachment->assigned_user_id = 'seed_will_id';
        $attachment->setAttachmentTeams($note, false);
        $this->assertEquals($note->team_id, $attachment->team_id);
        $this->assertEquals($note->team_set_id, $attachment->team_set_id);
        $this->assertEquals($note->acl_team_id, $attachment->acl_team_id);
        $this->assertEquals($note->assigned_user_id, $attachment->assigned_user_id);
    }

    /**
     * @covers ::checkParentRelationship
     */
    public function testCheckParentRelationship()
    {
        $relMock = $this->getMockBuilder(\One2MBeanRelationship::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRelationshipRoleColumns'])
            ->getMock();
        $relMock->method('getRelationshipRoleColumns')->willReturn([
            'parent_type' => 'Cases',
            'attachment_flag' => 1,
        ]);
        $note = $this->getMockBuilder(\Note::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $args = ['parent_type', $relMock];
        $note->attachment_flag = 0;
        $result = TestReflection::callProtectedMethod($note, 'checkParentRelationship', $args);
        $this->assertFalse($result);
        $note->attachment_flag = 1;
        $result = TestReflection::callProtectedMethod($note, 'checkParentRelationship', $args);
        $this->assertTrue($result);
    }
}
