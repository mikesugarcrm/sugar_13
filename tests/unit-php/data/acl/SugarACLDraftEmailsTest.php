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

namespace Sugarcrm\SugarcrmTestsUnit\data\acl;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass \SugarACLDraftEmails
 */
class SugarACLDraftEmailsTest extends TestCase
{
    public function checkViewAccessProvider()
    {
        return [
            'access is a read operation' => [
                'access',
                true,
            ],
            'team security is a read operation' => [
                'team_security',
                true,
            ],
            'list is a read operation' => [
                'list',
                true,
            ],
            'view is a read operation' => [
                'view',
                true,
            ],
            'edit is a write operation' => [
                'edit',
                false,
            ],
            'delete is the only write operation a non-owner can perform' => [
                'delete',
                true,
            ],
            'import is a write operation' => [
                'import',
                false,
            ],
            'massupdate is a write operation' => [
                'massupdate',
                false,
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkViewAccessProvider
     */
    public function testViewCheckAccess($view, $expected)
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();

        $bean = $this->createPartialMock('\\Email', ['isOwner']);
        $bean->method('isOwner')->willReturn(false);
        $bean->state = \Email::STATE_DRAFT;
        $bean->assigned_user_id = Uuid::uuid1();

        $context = [
            'user' => $user,
            'action' => $view,
            'bean' => $bean,
        ];

        $acl = new \SugarACLDraftEmails();
        $actual = $acl->checkAccess('Emails', $view, $context);
        $this->assertSame($expected, $actual);
    }

    public function checkFieldAccessReadableProvider()
    {
        return [
            ['id'],
            ['date_entered'],
            ['date_modified'],
            ['assigned_user_id'],
            ['assigned_user_name'],
            ['modified_user_id'],
            ['created_by'],
            ['deleted'],
            ['from_addr_name'],
            ['reply_to_addr'],
            ['to_addrs_names'],
            ['cc_addrs_names'],
            ['bcc_addrs_names'],
            ['raw_source'],
            ['description_html'],
            ['description'],
            ['date_sent'],
            ['message_id'],
            ['message_uid'],
            ['name'],
            ['type'],
            ['status'],
            ['flagged'],
            ['reply_to_status'],
            ['intent'],
            ['mailbox_id'],
            ['state'],
            ['reply_to_id'],
            ['parent_type'],
            ['parent_id'],
            ['outbound_email_id'],
            ['team_id'],
            ['team_set_id'],
            ['tags'],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_ReadableFields($field)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_DRAFT;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLDraftEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(false);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertTrue($actual);
    }

    public function checkFieldAccessWritableProvider()
    {
        return [
            [
                'id',
                true,
            ],
            [
                'date_entered',
                true,
            ],
            [
                'date_modified',
                true,
            ],
            [
                'assigned_user_id',
                false,
            ],
            [
                'assigned_user_name',
                false,
            ],
            [
                'modified_user_id',
                true,
            ],
            [
                'created_by',
                true,
            ],
            [
                'deleted',
                true,
            ],
            [
                'from_addr_name',
                true,
            ],
            [
                'reply_to_addr',
                true,
            ],
            [
                'to_addrs_names',
                true,
            ],
            [
                'cc_addrs_names',
                true,
            ],
            [
                'bcc_addrs_names',
                true,
            ],
            [
                'raw_source',
                true,
            ],
            [
                'description_html',
                true,
            ],
            [
                'description',
                true,
            ],
            [
                'date_sent',
                false,
            ],
            [
                'message_id',
                true,
            ],
            [
                'message_uid',
                true,
            ],
            [
                'name',
                true,
            ],
            [
                'type',
                true,
            ],
            [
                'status',
                true,
            ],
            [
                'flagged',
                true,
            ],
            [
                'reply_to_status',
                true,
            ],
            [
                'intent',
                true,
            ],
            [
                'mailbox_id',
                true,
            ],
            [
                'state',
                true,
            ],
            [
                'reply_to_id',
                true,
            ],
            [
                'parent_type',
                true,
            ],
            [
                'parent_id',
                true,
            ],
            [
                'outbound_email_id',
                true,
            ],
            [
                'team_id',
                true,
            ],
            [
                'team_set_id',
                true,
            ],
            [
                'tags',
                true,
            ],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessWritableProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenUserIsOwner($field, $isWritable)
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();

        $bean = $this->createPartialMock('\\Email', ['isOwner']);
        $bean->method('isOwner')->willReturn(true);
        $bean->state = \Email::STATE_DRAFT;
        $bean->assigned_user_id = $user->id;

        $context = [
            'user' => $user,
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLDraftEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertSame($isWritable, $actual);
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenUserIsNotOwner($field)
    {
        $user = $this->createMock('\\User');
        $user->id = Uuid::uuid1();

        $bean = $this->createPartialMock('\\Email', ['isOwner']);
        $bean->method('isOwner')->willReturn(false);
        $bean->state = \Email::STATE_DRAFT;
        $bean->assigned_user_id = Uuid::uuid1();

        $context = [
            'user' => $user,
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLDraftEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertFalse($actual);
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenEmailIsNotADraft($field)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLDraftEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertTrue($actual);
    }
}
