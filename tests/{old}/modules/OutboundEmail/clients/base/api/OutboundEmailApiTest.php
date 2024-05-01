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
 * @coversDefaultClass OutboundEmailApi
 * @group api
 * @group email
 */
class OutboundEmailApiTest extends TestCase
{
    private $api;
    private $service;
    private static $createdIds = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        $sql = "DELETE FROM outbound_email WHERE id IN ('" . implode("','", static::$createdIds) . "')";
        DBManagerFactory::getInstance()->query($sql);
    }

    protected function setUp(): void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OutboundEmailApi();
    }

    public function createRecordForTypeSystemOrSystemOverrideProvider()
    {
        return [
            ['system'],
            ['system-override'],
        ];
    }

    /**
     * @covers ::createRecord
     * @dataProvider createRecordForTypeSystemOrSystemOverrideProvider
     */
    public function testCreateRecord_TypeIsSystemOrSystemOverride($type)
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->createRecord($this->service, [
            'module' => 'OutboundEmail',
            'type' => $type,
        ]);
    }

    /**
     * @covers ::createRecord
     */
    public function testCreateRecord_TypeIsUser()
    {
        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->expects($this->once())->method('connect');

        $api = $this->getMockBuilder('OutboundEmailApi')
            ->onlyMethods(['getMailer'])
            ->getMock();
        $api->method('getMailer')->willReturn($mailer);

        $args = [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.x.y',
            'mail_smtpport' => 465,
        ];
        $response = $api->createRecord($this->service, $args);

        $this->assertNotEmpty($response['id'], 'The record should have an ID');
        $this->assertSame($args['mail_smtpserver'], $response['mail_smtpserver'], 'Incorrect mail_smtpserver');
        $this->assertSame($args['mail_smtpport'], $response['mail_smtpport'], 'Incorrect mail_smtpport');
        $this->assertSame($GLOBALS['current_user']->id, $response['user_id'], 'The current user should own the record');

        static::$createdIds[] = $response['id'];
    }

    /**
     * @covers ::createRecord
     */
    public function testCreateRecord_TypeIsUser_ConnectionFails()
    {
        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->method('connect')->willThrowException(new MailerException());

        $api = $this->getMockBuilder('OutboundEmailApi')
            ->onlyMethods(['getMailer'])
            ->getMock();
        $api->method('getMailer')->willReturn($mailer);

        $this->expectException(SugarApiException::class);
        $api->createRecord($this->service, [
            'module' => 'OutboundEmail',
            'mail_smtpserver' => 'smtp.a.b',
            'mail_smtpport' => 465,
        ]);
    }

    public function updateRecordProvider()
    {
        return [
            ['system', 1, 0],
            ['system-override', 0, 1],
            ['user', 0, 1],
        ];
    }

    /**
     * Tests that the correct save method is called depending on the type of record.
     *
     * @covers ::updateRecord
     * @covers ::saveBean
     * @dataProvider updateRecordProvider
     */
    public function testUpdateRecord($type, $saveSystemCallCount, $saveCallCount)
    {
        $oe = $this->getMockBuilder('OutboundEmail')
            ->setMethods(['saveSystem', 'save'])
            ->getMock();
        $oe->expects($this->exactly($saveSystemCallCount))->method('saveSystem')->with($this->equalTo(true));
        $oe->expects($this->exactly($saveCallCount))->method('save');

        $oe->id = Uuid::uuid1();
        $oe->type = $type;
        $oe->user_id = $GLOBALS['current_user']->id;
        $oe->mail_smtpport = 25;
        BeanFactory::registerBean($oe);

        $mailer = $this->createPartialMock('SmtpMailer', ['connect']);
        $mailer->expects($this->once())->method('connect');

        $api = $this->createPartialMock('OutboundEmailApi', ['getMailer', 'reloadBean']);
        $api->method('getMailer')->willReturn($mailer);
        // Avoids the strict retrieve without cache through BeanFactory that hits the database and results in errors
        // due to the record not really being saved.
        $api->method('reloadBean')->willReturn($oe);

        $args = [
            'module' => 'OutboundEmail',
            'record' => $oe->id,
            'mail_smtpport' => 465,
        ];
        $response = $api->updateRecord($this->service, $args);

        BeanFactory::unregisterBean($oe);
    }

    /**
     * @covers ::testUserSystemOverrideEmail
     * @dataProvider providerTestTestUserSystemOverrideEmail
     * @param array $args The set of arguments to the API endpoint
     */
    public function testTestUserSystemOverrideEmail($args)
    {
        $mockOutboundEmailApi = $this->getMockBuilder(OutboundEmailApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUserDefaultCredentials', 'getSystemEmailSettings', 'sendTestEmail'])
            ->getMock();
        $mockOutboundEmailApi->method('getUserDefaultCredentials')->willReturn([
            'name' => 'Users Full Name',
            'mail_smtpuser' => 'UsersEmailUsername',
            'mail_smtppass' => 'UsersEmailPassword',
            'eapm_id' => '',
            'email_address' => 'users_email@example.com',
        ]);
        $mockOutboundEmailApi->method('getSystemEmailSettings')->willReturn([
            'mail_smtpserver' => 'fake.smtp.server.com',
            'mail_smtpport' => '587',
            'mail_smtpssl' => 2,
            'mail_smtpauth_req' => 1,
            'mail_smtptype' => 'fakeService',
            'mail_authtype' => '',
        ]);

        $mockOutboundEmailApi->expects($this->once())
            ->method('sendTestEmail')
            ->with([
                'fake.smtp.server.com',
                '587',
                2,
                1,
                $args['mail_smtpuser'] ?? 'UsersEmailUsername',
                $args['mail_smtppass'] ?? 'UsersEmailPassword',
                $args['from_address'] ?? 'users_email@example.com',
                $args['to_address'],
                'SMTP',
                $args['name'] ?? 'Users Full Name',
                'fakeService',
                '',
                $args['eapm_id'] ?? '',
            ]);
        $mockOutboundEmailApi->testUserSystemOverrideEmail($this->service, $args);
    }

    /**
     * Provider for testTestUserSystemOverrideEmail, testing various sets of
     * API arguments
     *
     * @return array
     */
    public function providerTestTestUserSystemOverrideEmail()
    {
        return [
            // Minimum arguments, should fall back to saved user credentials
            [
                [
                    'user_id' => '1234',
                    'to_address' => 'fake_to_address@example.com',
                ],
            ],
            // Arguments that should override the saved user credentials
            [
                [
                    'user_id' => '1234',
                    'to_address' => 'fake_to_address@example.com',
                    'from_address' => 'fake_from_address@example.com',
                    'name' => 'Fake Name',
                    'mail_smtpuser' => 'fakeUserName',
                    'mail_smtppass' => 'fakePassword!',
                ],
            ],
        ];
    }
}
