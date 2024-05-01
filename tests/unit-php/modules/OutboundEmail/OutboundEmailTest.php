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

namespace Sugarcrm\SugarcrmTestsUnit\modules\OutboundEmail;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \OutboundEmail
 */
class OutboundEmailTest extends TestCase
{
    private $bean;

    protected function setUp(): void
    {
        $this->bean = $this->createPartialMock('\\OutboundEmail', []);
        $this->bean->field_defs = [
            'id' => [
                'name' => 'id',
                'vname' => 'LBL_ID',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
                'mandatory_fetch' => true,
            ],
            'name' => [
                'name' => 'name',
                'vname' => 'LBL_NAME',
                'type' => 'name',
                'dbType' => 'varchar',
                'len' => 255,
                'required' => true,
                'reportable' => false,
            ],
            'type' => [
                'name' => 'type',
                'vname' => 'LBL_TYPE',
                'type' => 'varchar',
                'len' => 15,
                'required' => true,
                'default' => 'user',
                'reportable' => false,
                'mandatory_fetch' => true,
                'readonly' => true,
            ],
            'user_id' => [
                'name' => 'user_id',
                'vname' => 'LBL_USER_ID',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
                'mandatory_fetch' => true,
                'readonly' => true,
            ],
            'email_addresses' => [
                'name' => 'email_addresses',
                'relationship' => 'outbound_email_email_addresses',
                'source' => 'non-db',
                'type' => 'link',
                'vname' => 'LBL_EMAIL_ADDRESSES',
            ],
            'email_address_id' => [
                'name' => 'email_address_id',
                'duplicate_merge' => 'disabled',
                'id_name' => 'email_address_id',
                'link' => 'email_addresses',
                'massupdate' => false,
                'module' => 'EmailAddresses',
                'reportable' => false,
                'rname' => 'id',
                'table' => 'email_addresses',
                'type' => 'id',
                'vname' => 'LBL_EMAIL_ADDRESS_ID',
            ],
            'email_address' => [
                'name' => 'email_address',
                'id_name' => 'email_address_id',
                'link' => 'email_addresses',
                'module' => 'EmailAddresses',
                'required' => true,
                'rname' => 'email_address',
                'source' => 'non-db',
                'table' => 'email_addresses',
                'type' => 'relate',
                'vname' => 'LBL_EMAIL_ADDRESS',
            ],
            'reply_to_name' => [
                'name' => 'reply_to_name',
                'vname' => 'LBL_REPLY_TO_NAME',
                'type' => 'name',
                'dbType' => 'varchar',
                'len' => 255,
                'reportable' => false,
                'dependency' => 'not(equal($type, "system"))',
            ],
            'reply_to_email_addresses' => [
                'name' => 'reply_to_email_addresses',
                'relationship' => 'outbound_email_reply_to_email_addresses',
                'source' => 'non-db',
                'type' => 'link',
                'vname' => 'LBL_REPLY_TO_EMAIL_ADDRESSES',
            ],
            'reply_to_email_address_id' => [
                'name' => 'reply_to_email_address_id',
                'duplicate_merge' => 'disabled',
                'id_name' => 'reply_to_email_address_id',
                'link' => 'reply_to_email_addresses',
                'massupdate' => false,
                'module' => 'EmailAddresses',
                'reportable' => false,
                'rname' => 'id',
                'table' => 'email_addresses',
                'type' => 'id',
                'vname' => 'LBL_REPLY_TO_EMAIL_ADDRESS_ID',
            ],
            'reply_to_email_address' => [
                'name' => 'reply_to_email_address',
                'id_name' => 'reply_to_email_address_id',
                'link' => 'reply_to_email_addresses',
                'module' => 'EmailAddresses',
                'rname' => 'email_address',
                'source' => 'non-db',
                'table' => 'email_addresses',
                'type' => 'relate',
                'vname' => 'LBL_REPLY_TO_EMAIL_ADDRESS',
                'dependency' => 'not(equal($type, "system"))',
            ],
            'mail_sendtype' => [
                'name' => 'mail_sendtype',
                'vname' => 'LBL_MAIL_SENDTYPE',
                'type' => 'varchar',
                'len' => 8,
                'required' => true,
                'default' => 'SMTP',
                'reportable' => false,
            ],
            'mail_smtptype' => [
                'name' => 'mail_smtptype',
                'vname' => 'LBL_EMAIL_PROVIDER',
                'type' => 'enum',
                'options' => 'mail_smtptype_options',
                'len' => 20,
                'required' => true,
                'default' => 'other',
                'reportable' => false,
            ],
            'mail_smtpserver' => [
                'name' => 'mail_smtpserver',
                'vname' => 'LBL_MAIL_SMTPSERVER',
                'type' => 'varchar',
                'len' => 100,
                'required' => false,
                'reportable' => false,
                'mandatory_fetch' => true,
            ],
            'mail_smtpport' => [
                'name' => 'mail_smtpport',
                'vname' => 'LBL_MAIL_SMTPPORT',
                'type' => 'int',
                'len' => 5,
                'default' => 465,
                'reportable' => false,
                'disable_num_format' => true,
            ],
            'mail_smtpuser' => [
                'name' => 'mail_smtpuser',
                'vname' => 'LBL_MAIL_SMTPUSER',
                'type' => 'varchar',
                'len' => 100,
                'reportable' => false,
                'mandatory_fetch' => true,
            ],
            'mail_smtppass' => [
                'name' => 'mail_smtppass',
                'vname' => 'LBL_MAIL_SMTPPASS',
                'type' => 'encrypt',
                'len' => 100,
                'reportable' => false,
                'duplicate_on_record_copy' => 'no',
                'mandatory_fetch' => true,
            ],
            'mail_smtpauth_req' => [
                'name' => 'mail_smtpauth_req',
                'vname' => 'LBL_MAIL_SMTPAUTH_REQ',
                'type' => 'bool',
                'default' => 0,
                'reportable' => false,
                'mandatory_fetch' => true,
            ],
            'mail_smtpssl' => [
                'name' => 'mail_smtpssl',
                'vname' => 'LBL_MAIL_SMTPSSL',
                'type' => 'enum',
                'options' => 'email_settings_for_ssl',
                'len' => 1,
                'default' => 1,
                'reportable' => false,
            ],
            'deleted' => [
                'name' => 'deleted',
                'vname' => 'LBL_DELETED',
                'type' => 'bool',
                'default' => '0',
                'reportable' => false,
                'duplicate_on_record_copy' => 'no',
            ],
        ];
    }

    /**
     * A few test cases require a current user. Unsets the current user from $GLOBALS.
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @covers ::getOwnerField
     */
    public function testGetOwnerField()
    {
        $ownerField = $this->bean->getOwnerField();
        $this->assertSame('user_id', $ownerField);
    }

    /**
     * @covers ::isOwner
     */
    public function testIsOwner()
    {
        $GLOBALS['current_user'] = $this->createMock('\\User');
        $GLOBALS['current_user']->id = Uuid::uuid1();

        $isOwner = $this->bean->isOwner($GLOBALS['current_user']->id);
        $this->assertTrue($isOwner, 'Should be true for the current user when creating a new record');

        $isOwner = $this->bean->isOwner(Uuid::uuid1());
        $this->assertFalse($isOwner, 'Should be false a non-current user when creating a new record');

        $this->bean->id = Uuid::uuid1();
        $this->bean->new_with_id = false;
        $this->bean->user_id = $GLOBALS['current_user']->id;

        $isOwner = $this->bean->isOwner($GLOBALS['current_user']->id);
        $this->assertTrue($isOwner, 'Should be true for the owner of an existing record');

        $isOwner = $this->bean->isOwner(Uuid::uuid1());
        $this->assertFalse($isOwner, 'Should be false for the non-owner of an existing record');
    }

    public function isConfiguredProvider()
    {
        $server = 'smtp.example.com';
        $username = 'julio';
        $password = 'xhjd7h3kHjkhas';

        return [
            [
                $server,
                true,
                $username,
                $password,
                true,
            ],
            [
                $server,
                false,
                $username,
                $password,
                true,
            ],
            [
                $server,
                false,
                $username,
                null,
                true,
            ],
            [
                $server,
                false,
                null,
                $password,
                true,
            ],
            [
                $server,
                false,
                null,
                null,
                true,
            ],
            [
                null,
                false,
                null,
                null,
                false,
            ],
            [
                null,
                true,
                $username,
                $password,
                false,
            ],
            [
                $server,
                true,
                null,
                $password,
                false,
            ],
            [
                $server,
                true,
                $username,
                null,
                false,
            ],
            [
                $server,
                true,
                null,
                null,
                false,
            ],
        ];
    }

    /**
     * @covers ::isConfigured
     * @dataProvider isConfiguredProvider
     * @param $server
     * @param $auth
     * @param $username
     * @param $password
     * @param $expected
     */
    public function testIsConfigured($server, $auth, $username, $password, $expected)
    {
        $this->bean->mail_smtpserver = $server;
        $this->bean->mail_smtpauth_req = $auth;
        $this->bean->mail_smtpuser = $username;
        $this->bean->mail_smtppass = $password;
        $actual = $this->bean->isConfigured();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::populateFromRow
     */
    public function testPopulateFromRow()
    {
        $primary = 'foo@bar.com';
        $primaryId = Uuid::uuid1();
        $ea = $this->createPartialMock('\\EmailAddress', ['getEmailGUID']);
        $ea->method('getEmailGUID')->willReturn($primaryId);

        $user = $this->createPartialMock('\\User', ['getUsersNameAndEmail']);
        $user->id = Uuid::uuid1();
        $user->name = 'George Butler';
        $user->emailAddress = $ea;
        $user->method('getUsersNameAndEmail')->willReturn(['email' => $primary, 'name' => $user->name]);
        $GLOBALS['current_user'] = $user;

        $bean = $this->createPartialMock('\\OutboundEmail', [
            'isAllowUserAccessToSystemDefaultOutbound',
            'decrypt_after_retrieve',
        ]);
        $bean->method('isAllowUserAccessToSystemDefaultOutbound')->willReturn(true);
        // Just return the password as is for testing.
        $bean->method('decrypt_after_retrieve')->willReturn('xhjd7h3kHjkhas');
        $bean->field_defs = $this->bean->field_defs;

        $row = [
            'id' => Uuid::uuid1(),
            'name' => 'My User Account',
            'type' => 'user',
            'user_id' => $user->id,
            'email_address' => 'biz@baz.net',
            'email_address_id' => Uuid::uuid1(),
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '587',
            'mail_smtpuser' => 'gbutler',
            'mail_smtppass' => 'xhjd7h3kHjkhas',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '2',
            'deleted' => '0',
        ];
        $bean->populateFromRow($row);

        $this->assertSame($row['id'], $bean->id, 'The IDs should match');
        $this->assertSame($row['name'], $bean->name, 'The names should match');
        $this->assertSame($row['type'], $bean->type, 'The types should match');
        $this->assertSame($row['user_id'], $bean->user_id, 'The owners should match');
        $this->assertSame($row['email_address'], $bean->email_address, 'The email addresses should match');
        $this->assertSame($row['email_address_id'], $bean->email_address_id, 'The email address IDs should match');
        $this->assertSame($row['mail_sendtype'], $bean->mail_sendtype, 'The send types should match');
        $this->assertSame($row['mail_smtptype'], $bean->mail_smtptype, 'The SMTP types should match');
        $this->assertSame($row['mail_smtpserver'], $bean->mail_smtpserver, 'The servers should match');
        $this->assertSame($row['mail_smtpport'], $bean->mail_smtpport, 'The ports should match');
        $this->assertSame($row['mail_smtpuser'], $bean->mail_smtpuser, 'The usernames should match');
        $this->assertSame($row['mail_smtppass'], $bean->mail_smtppass, 'The passwords should match');
        $this->assertSame($row['mail_smtpauth_req'], $bean->mail_smtpauth_req, 'The auth requirements should match');
        $this->assertSame($row['mail_smtpssl'], $bean->mail_smtpssl, 'The security settings should match');
        $this->assertEquals(0, $bean->deleted, 'Should not be deleted');

        $system = TestReflection::getProtectedValue($bean, 'sysMailerCache');
        $this->assertNull($system, 'The system configuration should not have been cached');
    }

    /**
     * @covers ::populateFromRow
     * @covers ::populateFromUser
     */
    public function testPopulateSystemFromRow()
    {
        $primary = 'foo@bar.com';
        $primaryId = Uuid::uuid1();
        $ea = $this->createPartialMock('\\EmailAddress', ['getEmailGUID']);
        $ea->method('getEmailGUID')->willReturn($primaryId);

        $user = $this->createPartialMock('\\User', ['getUsersNameAndEmail']);
        $user->id = Uuid::uuid1();
        $user->name = 'George Butler';
        $user->emailAddress = $ea;
        $user->method('getUsersNameAndEmail')->willReturn(['email' => $primary, 'name' => $user->name]);
        $GLOBALS['current_user'] = $user;

        $bean = $this->createPartialMock('\\OutboundEmail', [
            'isAllowUserAccessToSystemDefaultOutbound',
            'decrypt_after_retrieve',
        ]);
        $bean->method('isAllowUserAccessToSystemDefaultOutbound')->willReturn(true);
        // Just return the password as is for testing.
        $bean->method('decrypt_after_retrieve')->willReturn('xhjd7h3kHjkhas');
        $bean->field_defs = $this->bean->field_defs;

        $row = [
            'id' => Uuid::uuid1(),
            'name' => 'SugarCRM',
            'type' => 'system',
            'user_id' => '1',
            'email_address' => 'admin@sugarcrm.com',
            'email_address_id' => Uuid::uuid1(),
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.example.com',
            'mail_smtpport' => '587',
            'mail_smtpuser' => 'julio',
            'mail_smtppass' => 'xhjd7h3kHjkhas',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '2',
            'deleted' => '0',
        ];
        $bean->populateFromRow($row);

        $this->assertSame($row['id'], $bean->id, 'The IDs should match');
        $this->assertSame($user->name, $bean->name, 'The names should match');
        $this->assertSame($row['type'], $bean->type, 'The types should match');
        $this->assertSame($row['user_id'], $bean->user_id, 'The owners should match');
        $this->assertSame($primary, $bean->email_address, 'The email addresses should match');
        $this->assertSame($primaryId, $bean->email_address_id, 'The email address IDs should match');
        $this->assertSame($row['mail_sendtype'], $bean->mail_sendtype, 'The send types should match');
        $this->assertSame($row['mail_smtptype'], $bean->mail_smtptype, 'The SMTP types should match');
        $this->assertSame($row['mail_smtpserver'], $bean->mail_smtpserver, 'The servers should match');
        $this->assertSame($row['mail_smtpport'], $bean->mail_smtpport, 'The ports should match');
        $this->assertSame($row['mail_smtpuser'], $bean->mail_smtpuser, 'The usernames should match');
        $this->assertSame($row['mail_smtppass'], $bean->mail_smtppass, 'The passwords should match');
        $this->assertSame($row['mail_smtpauth_req'], $bean->mail_smtpauth_req, 'The auth requirements should match');
        $this->assertSame($row['mail_smtpssl'], $bean->mail_smtpssl, 'The security settings should match');
        $this->assertEquals(0, $bean->deleted, 'Should not be deleted');

        $system = TestReflection::getProtectedValue($bean, 'sysMailerCache');
        $this->assertSame($bean, $system, 'The system configuration should have been cached');
    }

    /**
     * @covers ::populateFromPost
     */
    public function testPopulateFromPost()
    {
        $this->bean->name = 'Foo';
        $this->bean->mail_smtpserver = 'smtp.example.com';
        $this->bean->mail_smtpport = 1025;
        $this->bean->mail_smtppass = 'xhjd7h3kHjkhas';

        $_POST['name'] = 'Bar';
        $_POST['mail_smtpport'] = 1125;
        $this->bean->populateFromPost();
        unset($_POST['name']);
        unset($_POST['mail_smtpport']);

        $this->assertSame('Bar', $this->bean->name, 'The name should be changed');
        $this->assertEmpty($this->bean->mail_smtpserver, 'The server should be empty');
        $this->assertSame(1125, $this->bean->mail_smtpport, 'The port should be changed');
        $this->assertSame('xhjd7h3kHjkhas', $this->bean->mail_smtppass, 'The password should not be changed');
    }

    /**
     * @covers ::populateDefaultValues
     * @covers ::populateFromUser
     * @covers ::setDefaultTeam
     * @covers ::setDefaultTeamFromUser
     */
    public function testPopulateDefaultValues()
    {
        $primary = 'foo@bar.com';
        $primaryId = Uuid::uuid1();
        $privateTeamId = Uuid::uuid1();
        $ea = $this->createPartialMock('\\EmailAddress', ['getEmailGUID']);
        $ea->method('getEmailGUID')->willReturn($primaryId);

        $user = $this->createPartialMock('\\User', [
            'getUsersNameAndEmail',
            'getPrivateTeamID',
        ]);
        $user->id = Uuid::uuid1();
        $user->name = 'George Butler';
        $user->emailAddress = $ea;
        $user->method('getUsersNameAndEmail')->willReturn(['email' => $primary, 'name' => $user->name]);
        $user->method('getPrivateTeamID')->willReturn($privateTeamId);
        $GLOBALS['current_user'] = $user;

        $this->bean->populateDefaultValues();

        $this->assertSame($user->name, $this->bean->name, 'The names should match');
        $this->assertSame($primary, $this->bean->email_address, 'The email addresses should match');
        $this->assertSame($primaryId, $this->bean->email_address_id, 'The email address IDs should match');
        $this->assertSame($privateTeamId, $this->bean->team_id, 'The team ID should match');
    }

    /**
     * @covers ::populateFromUser
     */
    public function testPopulateFromUser()
    {
        $primary = 'foo@bar.com';
        $primaryId = Uuid::uuid1();
        $replyTo = 'reply.foo@bar.com';
        $replyToId = Uuid::uuid1();
        $ea = $this->createPartialMock('\\EmailAddress', ['getEmailGUID', 'getReplyToAddress']);
        $ea->method('getEmailGUID')->will($this->returnCallback(
            function ($email) use ($primary, $primaryId, $replyTo, $replyToId) {
                $emailGUIDs = [
                    $primary => $primaryId,
                    $replyTo => $replyToId,
                ];
                return $emailGUIDs[$email];
            }
        ));
        $ea->method('getReplyToAddress')->willReturn($replyTo);

        $user = $this->createPartialMock('\\User', ['getUsersNameAndEmail']);
        $user->id = Uuid::uuid1();
        $user->name = 'George Butler';
        $user->emailAddress = $ea;
        $user->method('getUsersNameAndEmail')->willReturn(['email' => $primary, 'name' => $user->name]);

        $this->bean->type = \OutboundEmail::TYPE_SYSTEM_OVERRIDE;
        $this->bean->emailAddress = $ea;
        $this->bean->populateFromUser($user);

        $this->assertSame($user->name, $this->bean->name, 'The names should match');
        $this->assertSame($primary, $this->bean->email_address, 'The email addresses should match');
        $this->assertSame($primaryId, $this->bean->email_address_id, 'The email address IDs should match');
        $this->assertSame($user->name, $this->bean->reply_to_name, 'The reply-to names should match');
        $this->assertSame($replyTo, $this->bean->reply_to_email_address, 'The reply-to email addresses should match');
        $this->assertSame($replyToId, $this->bean->reply_to_email_address_id, 'The reply-to email address IDs should match');
    }
}
