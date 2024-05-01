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

/**
 * @coversDefaultClass \SugarACLArchivedEmails
 */
class SugarACLArchivedEmailsTest extends TestCase
{
    public function checkAccessNoTestProvider()
    {
        return [
            ['access'],
            ['team_security'],
            ['list'],
            ['view'],
        ];
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkAccessNoTestProvider
     */
    public function testCheckAccess_AllowOtherChecksToDetermineAccess($view)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'action' => $view,
            'bean' => $bean,
        ];

        $acl = new \SugarACLArchivedEmails();
        $actual = $acl->checkAccess('Emails', $view, $context);
        $this->assertTrue($actual);
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
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
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
                true,
            ],
            [
                'assigned_user_name',
                true,
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
                false,
            ],
            [
                'reply_to_addr',
                false,
            ],
            [
                'to_addrs_names',
                false,
            ],
            [
                'cc_addrs_names',
                false,
            ],
            [
                'bcc_addrs_names',
                false,
            ],
            [
                'raw_source',
                false,
            ],
            [
                'description_html',
                false,
            ],
            [
                'description',
                false,
            ],
            [
                'date_sent',
                false,
            ],
            [
                'message_id',
                false,
            ],
            [
                'message_uid',
                true,
            ],
            [
                'name',
                false,
            ],
            [
                'type',
                false,
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
                false,
            ],
            [
                'state',
                false,
            ],
            [
                'reply_to_id',
                false,
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
                false,
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
    public function testFieldCheckAccess_WritableFields($field, $isWritable)
    {
        $bean = $this->createPartialMock('\\Email', ['isUpdate']);
        $bean->method('isUpdate')->willReturn(true);
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertSame($isWritable, $actual);
    }

    /**
     * @covers ::checkAccess
     * @dataProvider checkFieldAccessReadableProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenEmailIsNotArchived($field)
    {
        $bean = $this->createMock('\\Email');
        $bean->state = \Email::STATE_DRAFT;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertTrue($actual);
    }

    public function checkFieldAccessWritableWhenArchivedEmailIsNewProvider()
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
                true,
            ],
            [
                'assigned_user_name',
                true,
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
                true,
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
                false,
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
     * @dataProvider checkFieldAccessWritableWhenArchivedEmailIsNewProvider
     */
    public function testFieldCheckAccess_WritableFieldsWhenArchivedEmailIsNew($field, $isWritable)
    {
        $bean = $this->createPartialMock('\\Email', ['isUpdate']);
        $bean->method('isUpdate')->willReturn(false);
        $bean->state = \Email::STATE_ARCHIVED;

        $context = [
            'bean' => $bean,
            'field' => $field,
        ];

        $acl = $this->createPartialMock('\\SugarACLArchivedEmails', ['isWriteOperation']);
        $acl->method('isWriteOperation')->willReturn(true);

        $actual = $acl->checkAccess('Emails', 'field', $context);
        $this->assertSame($isWritable, $actual);
    }
}
