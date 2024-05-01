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
 * @coversDefaultClass EmailsApi
 * @group api
 * @group email
 */
class EmailsApiTest extends TestCase
{
    protected $systemConfiguration;
    protected $currentUserConfiguration;
    protected $service;

    protected function setUp(): void
    {
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        $this->systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        $this->currentUserConfiguration = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration($GLOBALS['current_user']->id);
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        OutboundEmailConfigurationTestHelper::tearDown();
    }

    public function testCreateRecord_NoEmailIsCreatedOnFailureToSend()
    {
        $before = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');

        $api = $this->getMockBuilder('EmailsApi')
            ->setMethods(['sendEmail'])
            ->getMock();
        $api->method('sendEmail')->willThrowException(new SugarApiExceptionRequestMethodFailure());

        $args = [
            'module' => 'Emails',
            'name' => 'Sugar Email' . random_int(0, mt_getrandmax()),
            'state' => Email::STATE_READY,
        ];

        $caught = false;

        try {
            $api->createRecord($this->service, $args);
        } catch (SugarApiExceptionRequestMethodFailure $e) {
            $caught = true;
        }

        $this->assertTrue($caught, 'SugarApiExceptionRequestMethodFailure was expected');

        $after = $GLOBALS['db']->fetchOne('SELECT COUNT(*) as num FROM emails WHERE deleted=0');
        $this->assertSame($before['num'], $after['num'], 'A new email should not have been created');

        // In reality, an email was created, but it was immediately deleted. SugarTestEmailUtilities has no knowledge of
        // it, so add the ID in order to allow teardown to clean up the database.
        $id = $GLOBALS['db']->fetchOne("SELECT id FROM emails WHERE name='{$args['name']}' AND deleted=1");
        SugarTestEmailUtilities::setCreatedEmail($id);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSpecifiedConfiguration()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        $configId = $this->currentUserConfiguration->id;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));
        $email->outbound_email_id = $this->currentUserConfiguration->id;

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSystemConfiguration()
    {
        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS['current_user']);
        $configId = $config->getConfigId();

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesSystemOverrideConfigurationForAdmin()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        // Pretend that the current user is the admin with id=1.
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $saveUserId = $GLOBALS['current_user']->id;
        $GLOBALS['current_user']->id = 1;

        // The admin should have a system-override configuration in addition to the system configuration.
        $override = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
        $configId = $override->id;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($config) use ($configId) {
                return $config->getConfigId() === $configId;
            }));

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
        $GLOBALS['current_user']->id = $saveUserId;
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UsesConfigurationReplyTo()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        $replyToName = $this->currentUserConfiguration->reply_to_name;
        $replyToAddress = $this->currentUserConfiguration->reply_to_email_address;

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->once())->method('sendEmail')->with($this->callback(
            function ($config) use ($replyToName, $replyToAddress) {
                return $config->getReplyTo()->getEmail() === $replyToAddress &&
                    $config->getReplyTo()->getName() === $replyToName;
            }
        ));
        $email->outbound_email_id = $this->currentUserConfiguration->id;

        $api = new EmailsApi();
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_CurrentUserHasNoConfigurations_ThrowsException()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);

        // Make sure the current user doesn't have any configurations. The existing current user does.
        $saveUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->never())->method('sendEmail');

        $caught = false;

        try {
            $api = new EmailsApi();
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
        } catch (SugarApiException $e) {
            $caught = true;
        }

        // Restore the current user to the previous user before asserting to guarantee that the next test gets the user
        // it expects.
        $GLOBALS['current_user'] = $saveUser;

        $this->assertTrue($caught);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_SpecifiedConfigurationCouldNotBeFound()
    {
        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(['sendEmail'])
            ->getMock();
        $email->expects($this->never())
            ->method('sendEmail');
        $email->outbound_email_id = Uuid::uuid1();

        $api = new EmailsApi();

        $this->expectException(SugarApiException::class);
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_ConfigurationIsNotComplete()
    {
        $oe = $this->createPartialMock('OutboundEmail', ['isConfigured']);
        $oe->method('isConfigured')->willReturn(false);
        BeanFactory::registerBean($oe);

        $email = $this->createPartialMock('Email', ['sendEmail']);
        $email->expects($this->never())->method('sendEmail');
        $email->outbound_email_id = $oe->id;

        $api = new EmailsApi();

        $this->expectException(SugarApiException::class);
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    /**
     * @covers ::sendEmail
     */
    public function testSendEmail_UnknownError()
    {
        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(['sendEmail'])
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->willThrowException(new Exception('something happened'));

        $api = new EmailsApi();

        $this->expectException(SugarApiExceptionError::class);
        SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
    }

    public function smtpServerErrorProvider()
    {
        return [
            [
                MailerException::FailedToSend,
                'smtp_server_error',
            ],
            [
                MailerException::FailedToConnectToRemoteServer,
                'smtp_server_error',
            ],
            [
                MailerException::InvalidConfiguration,
                'smtp_server_error',
            ],
            [
                MailerException::InvalidHeader,
                'smtp_payload_error',
            ],
            [
                MailerException::InvalidEmailAddress,
                'smtp_payload_error',
            ],
            [
                MailerException::InvalidAttachment,
                'smtp_payload_error',
            ],
            [
                MailerException::FailedToTransferHeaders,
                'smtp_payload_error',
            ],
            [
                MailerException::ExecutableAttachment,
                'smtp_payload_error',
            ],
        ];
    }

    /**
     * @covers ::sendEmail
     * @dataProvider smtpServerErrorProvider
     */
    public function testSendEmail_SmtpError($errorCode, $expectedErrorLabel)
    {
        $email = $this->getMockBuilder('Email')
            ->disableOriginalConstructor()
            ->setMethods(['sendEmail'])
            ->getMock();
        $email->expects($this->once())
            ->method('sendEmail')
            ->willThrowException(new MailerException('something happened', $errorCode));

        try {
            $api = new EmailsApi();
            SugarTestReflection::callProtectedMethod($api, 'sendEmail', [$email]);
        } catch (SugarApiException $e) {
            $this->assertEquals(
                500,
                $e->httpCode,
                'Should map this MailerException to a SugarApiException with code 500'
            );
            $this->assertEquals(
                $expectedErrorLabel,
                $e->errorLabel,
                "Should classify this error as a {$expectedErrorLabel}"
            );
        }
    }
}
