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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass OutboundEmailApiHelper
 */
class OutboundEmailApiHelperTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public function populateFromApiProvider()
    {
        return [
            [
                [
                    'name' => 'system',
                    'type' => 'system',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'system',
                    'type' => 'system',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
            ],
            [
                [
                    'name' => 'system',
                    'type' => 'system-override',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'system',
                    'type' => 'system-override',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'bar',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'user_id' => Uuid::uuid1(),
                    'name' => 'bar',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
                [
                    'name' => 'bar',
                    'type' => 'user',
                    'mail_sendtype' => 'web',
                    'mail_smtptype' => 'other',
                    'mail_smtpserver' => 'foo.com',
                    'mail_smtpuser' => 'biz',
                    'mail_smtppass' => 'baz',
                    'mail_smtpport' => 443,
                    'mail_smtpauth_req' => 0,
                    'mail_smtpssl' => 1,
                ],
            ],
        ];
    }

    /**
     * @covers ::populateFromApi
     * @dataProvider populateFromApiProvider
     */
    public function testPopulateFromApi($initialData, $submittedData, $expectedData)
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->id = Uuid::uuid1();

        // The owner is not allowed to change.
        $expectedData['user_id'] = $bean->user_id = $GLOBALS['current_user']->id;

        foreach ($initialData as $field => $value) {
            $bean->{$field} = $value;
        }

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $helper->populateFromApi($bean, $submittedData);

        foreach ($expectedData as $field => $value) {
            $this->assertSame($value, $bean->{$field}, "{$field} should be {$value}");
        }
    }

    public function formatForApiProvider()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'P@55w0rd',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => '',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => 'P@55w0rd',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                    'mail_smtpuser',
                    'mail_smtppass',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => true,
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_sendtype' => 'SMTP',
                    'mail_smtptype' => 'exchange',
                    'mail_smtpserver' => 'smtp.sugarcrm.com',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => '',
                    'mail_smtpport' => 25,
                    'mail_smtpauth_req' => 1,
                    'mail_smtpssl' => 2,
                ],
                [
                    'name',
                    'type',
                    'mail_smtpuser',
                    'mail_smtppass',
                ],
                [
                    'name' => 'foo',
                    'type' => 'user',
                    'mail_smtpuser' => 'foo',
                    'mail_smtppass' => null,
                ],
            ],
        ];
    }

    /**
     * @covers ::formatForApi
     * @dataProvider formatForApiProvider
     */
    public function testFormatForApi($initialData, $fieldList, $expected)
    {
        $bean = BeanFactory::newBean('OutboundEmail');
        $bean->id = Uuid::uuid1();
        $bean->user_id = $GLOBALS['current_user']->id;

        foreach ($initialData as $field => $value) {
            $bean->{$field} = $value;
        }

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $actual = $helper->formatForApi($bean, $fieldList);

        // Testing for these attributes is unnecessary.
        unset($actual['_acl']);
        unset($actual['locked_fields']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers ::formatForApi
     */
    public function testFormatForApi_TheSystemAccountCanBeUsedByUsers_OverwritesTheNameAndEmailAddressForTheSystemAccount()
    {
        $bean = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isAllowUserAccessToSystemDefaultOutbound'])->getMock();
        $bean->method('isAllowUserAccessToSystemDefaultOutbound')->willReturn(true);

        $row = [
            'id' => Uuid::uuid1(),
            'name' => 'SugarCRM',
            'type' => 'system',
            'user_id' => '1',
            'email_address' => 'admin@sugarcrm.com',
            'email_address_id' => Uuid::uuid1(),
        ];
        $bean->populateFromRow($row);

        $primaryId = $GLOBALS['current_user']->emailAddress->getGuid($GLOBALS['current_user']->email1);

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $actual = $helper->formatForApi($bean, ['name', 'email_address', 'email_address_id']);

        $this->assertSame($GLOBALS['current_user']->name, $bean->name, 'The names should match');
        $this->assertSame($GLOBALS['current_user']->email1, $bean->email_address, 'The email addresses should match');
        $this->assertSame($primaryId, $bean->email_address_id, 'The email address IDs should match');
    }

    public function typeProvider()
    {
        return [
            'system_account_cannot_be_used_by_users' => ['system'],
            'system_override_account' => ['system-override'],
            'user_account' => ['user'],
        ];
    }

    /**
     * @dataProvider typeProvider
     * @covers ::formatForApi
     */
    public function testFormatForApi_DoesNotOverwriteTheNameAndEmailAddress($type)
    {
        $bean = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['isAllowUserAccessToSystemDefaultOutbound'])->getMock();
        $bean->method('isAllowUserAccessToSystemDefaultOutbound')->willReturn(false);

        $row = [
            'id' => Uuid::uuid1(),
            'name' => 'SugarCRM',
            'type' => $type,
            'user_id' => '1',
            'email_address' => 'admin@sugarcrm.com',
            'email_address_id' => Uuid::uuid1(),
        ];
        $bean->populateFromRow($row);

        $helper = new OutboundEmailApiHelper(SugarTestRestUtilities::getRestServiceMock());
        $actual = $helper->formatForApi($bean, ['name', 'email_address', 'email_address_id']);

        $this->assertSame($row['name'], $bean->name, 'The names should match');
        $this->assertSame($row['email_address'], $bean->email_address, 'The email addresses should match');
        $this->assertSame($row['email_address_id'], $bean->email_address_id, 'The email address IDs should match');
    }
}
