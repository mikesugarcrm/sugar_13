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
use PHPMailer\PHPMailer\Exception as PHPMailerExeption;

/**
 * @coversDefaultClass SmtpMailer
 * @group email
 * @group mailer
 */
class SmtpMailerTest extends TestCase
{
    /**
     * Stores the original sugar_config to restore after tests
     * @var array
     */
    protected $oldConfig;

    protected function setUp(): void
    {
        global $sugar_config;
        $this->oldConfig = $sugar_config;
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        global $sugar_config;
        $sugar_config = $this->oldConfig;
        SugarTestHelper::tearDown();
    }

    public function testGetMailTransmissionProtocol_ReturnsSmtp()
    {
        $mailer = new SmtpMailer(new OutboundSmtpEmailConfiguration($GLOBALS['current_user']));
        $expected = SmtpMailer::MailTransmissionProtocol;
        $actual = $mailer->getMailTransmissionProtocol();
        $this->assertEquals(
            $expected,
            $actual,
            "The SmtpMailer should have {$expected} for its mail transmission protocol"
        );
    }

    public function testConnect_ConnectionSucceed_MailerSet()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$config])
            ->setMethods(
                [
                    'transferConfigurations',
                    'connectToHost',
                ]
            )
            ->getMock();

        $mockMailer->expects($this->once())->method('transferConfigurations')->will($this->returnValue(true));
        $mockMailer->expects($this->once())->method('connectToHost')->will($this->returnValue(true));

        $mockMailer->connect();
    }

    /**
     * @covers ::connect
     * @dataProvider providerTestConnectKeepAliveConfig
     * @throws MailerException
     */
    public function testConnectKeepAliveConfig($alreadyHasProxy, $keepAliveConfigValue, $expectNewProxyCreated)
    {
        global $sugar_config, $current_user;
        $sugar_config['smtp_mailer_keep_alive'] = $keepAliveConfigValue;

        $mockProxy = $this->getMockBuilder('PHPMailerProxy')
            ->getMock();

        $config = new OutboundSmtpEmailConfiguration($current_user);
        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$config])
            ->onlyMethods(['generateMailer', 'transferConfigurations', 'connectToHost'])
            ->getMock();
        $mockMailer->method('generateMailer')->will($this->returnValue($mockProxy));

        if ($alreadyHasProxy) {
            SugarTestReflection::setProtectedValue($mockMailer, 'mailer', $mockProxy);
        }

        $mockMailer->expects($this->once())->method('transferConfigurations')->will($this->returnValue(true));
        $mockMailer->expects($this->once())->method('connectToHost')->will($this->returnValue(true));

        if ($expectNewProxyCreated) {
            $mockMailer->expects($this->once())->method('generateMailer');
        } else {
            $mockMailer->expects($this->never())->method('generateMailer');
        }

        $mockMailer->connect();
    }

    /**
     * Provider for testConnectKeepAliveConfig
     *
     * @return array
     */
    public function providerTestConnectKeepAliveConfig()
    {
        return [
            [true, true, false],
            [true, false, true],
            [false, true, true],
            [false, false, true],
        ];
    }

    public function testConnect_ConnectionFails_ExceptionThrown()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$config])
            ->setMethods(
                [
                    'transferConfigurations',
                    'connectToHost',
                ]
            )
            ->getMock();

        $mockMailer->expects($this->once())->method('transferConfigurations')->will($this->returnValue(true));
        $mockMailer->expects($this->once())->method('connectToHost')->will($this->throwException(new MailerException()));

        $this->expectException(MailerException::class);
        $mockMailer->connect();
    }

    public function testClearRecipients_ClearToAndBccButNotCc()
    {
        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setMethods([
                'clearRecipientsTo',
                'clearRecipientsCc',
                'clearRecipientsBcc',
            ])
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->getmock();

        $mockMailer->expects($this->once())
            ->method('clearRecipientsTo');

        $mockMailer->expects($this->never())
            ->method('clearRecipientsCc');

        $mockMailer->expects($this->once())
            ->method('clearRecipientsBcc');

        $mockMailer->clearRecipients(true, false, true);
    }

    public function testSend_PHPMailerSmtpConnectThrowsException_ConnectToHostCatchesAndThrowsMailerException()
    {
        $mockPhpMailerProxy = $this->createPartialMock('PHPMailerProxy', ['smtpConnect']);

        $mockPhpMailerProxy->expects($this->once())
            ->method('smtpConnect')
            ->will($this->throwException(new PHPMailerExeption()));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'transferHeaders',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->getmock();

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        // connectToHost should fail between transferConfigurations and transferHeaders

        $mockMailer->expects($this->never())
            ->method('transferHeaders');

        $mockMailer->expects($this->never())
            ->method('transferRecipients');

        $mockMailer->expects($this->never())
            ->method('transferBody');

        $mockMailer->expects($this->never())
            ->method('transferAttachments');

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_MessageIdHeaderIsSet()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $config->setHostname('mycompany.com');
        $config->setLocale($GLOBALS['locale']);

        $phpMailerProxy = new PHPMailerProxy();
        $phpMailerProxy->addAddress('foo@bar.com');
        $phpMailerProxy->Body = 'baz';

        $mailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$config])
            ->setMethods([
                'generateMailer',
                'connectToHost',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->getMock();
        $mailer->expects($this->once())->method('generateMailer')->willReturn($phpMailerProxy);
        $mailer->expects($this->once())->method('connectToHost')->willReturn(true);
        $mailer->expects($this->once())->method('transferRecipients')->willReturn(true);
        $mailer->expects($this->once())->method('transferBody')->willReturn(true);
        $mailer->expects($this->once())->method('transferAttachments')->willReturn(true);
        $mailer->setHeader(EmailHeaders::From, new EmailIdentity('sales@mycompany.com'));
        $mailer->setSubject('biz');

        $id = create_guid();
        $mailer->setMessageId($id);
        $expected = $mailer->getHeader(EmailHeaders::MessageId);

        $mailer->send();

        $actual = $mailer->getHeader(EmailHeaders::MessageId);
        $this->assertSame($expected, $actual);
    }

    public function testSend_MessageIdHeaderIsNotSet()
    {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $config->setHostname('mycompany.com');
        $config->setLocale($GLOBALS['locale']);

        $phpMailerProxy = new PHPMailerProxy();
        $phpMailerProxy->addAddress('foo@bar.com');
        $phpMailerProxy->Body = 'baz';

        $mailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$config])
            ->setMethods(
                [
                    'generateMailer',
                    'connectToHost',
                    'transferRecipients',
                    'transferBody',
                    'transferAttachments',
                ]
            )
            ->getMock();
        $mailer->expects($this->once())->method('generateMailer')->willReturn($phpMailerProxy);
        $mailer->expects($this->once())->method('connectToHost')->willReturn(true);
        $mailer->expects($this->once())->method('transferRecipients')->willReturn(true);
        $mailer->expects($this->once())->method('transferBody')->willReturn(true);
        $mailer->expects($this->once())->method('transferAttachments')->willReturn(true);
        $mailer->setHeader(EmailHeaders::From, new EmailIdentity('sales@mycompany.com'));
        $mailer->setSubject('biz');

        $this->assertEmpty($mailer->getHeader(EmailHeaders::MessageId), 'Should be empty before sending');

        $mailer->send();

        $this->assertNotEmpty($mailer->getHeader(EmailHeaders::MessageId), 'Should not be empty after sending');
    }

    public function testSend_PHPMailerSetFromThrowsException_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = [
            EmailHeaders::From => [
                'foo@bar.com',
                null,
            ],
        ];
        $mockEmailHeaders = $this->createPartialMock('EmailHeaders', ['packageHeaders']);

        $mockEmailHeaders->expects($this->once())
            ->method('packageHeaders')
            ->will($this->returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = $this->createPartialMock('PHPMailerProxy', ['setFrom']);

        $mockPhpMailerProxy->expects($this->once())
            ->method('setFrom')
            ->will($this->throwException(new PHPMailerExeption()));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->getMock();

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects($this->never())
            ->method('transferRecipients');

        $mockMailer->expects($this->never())
            ->method('transferBody');

        $mockMailer->expects($this->never())
            ->method('transferAttachments');

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddReplyToReturnsFalse_TransferHeadersThrowsMailerException()
    {
        $packagedEmailHeaders = [
            EmailHeaders::ReplyTo => [
                'foo@bar.com',
                null,
            ],
        ];
        $mockEmailHeaders = $this->createPartialMock('EmailHeaders', ['packageHeaders']);

        $mockEmailHeaders->expects($this->once())
            ->method('packageHeaders')
            ->will($this->returnValue($packagedEmailHeaders));

        $mockPhpMailerProxy = $this->createPartialMock('PHPMailerProxy', ['addReplyTo']);

        $mockPhpMailerProxy->expects($this->once())
            ->method('addReplyTo')
            ->will($this->returnValue(false));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->getMock();

        $mockMailer->setHeaders($mockEmailHeaders);

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        // transferHeaders should fail between connectToHost and transferRecipients

        $mockMailer->expects($this->never())
            ->method('transferRecipients');

        $mockMailer->expects($this->never())
            ->method('transferBody');

        $mockMailer->expects($this->never())
            ->method('transferAttachments');

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddAttachmentThrowsException_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = $this->getMockBuilder('Localization')->setMethods(['translateCharset'])->getMock();
        $mockLocale->expects($this->any())
            ->method('translateCharset')
            ->will($this->returnValue('foobar')); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = $this->getMockBuilder('PHPMailerProxy')->setMethods(['addAttachment'])->getMock();

        $mockPhpMailerProxy->expects($this->once())
            ->method('addAttachment')
            ->will($this->throwException(new PHPMailerExeption()));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferHeaders',
                'transferRecipients',
                'transferBody',
            ])
            ->setConstructorArgs([$mailerConfiguration])
            ->getMock();

        $attachment = new Attachment('/foo/bar.txt');
        $mockMailer->addAttachment($attachment);

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferRecipients')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferBody')
            ->will($this->returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_PHPMailerAddEmbeddedImageReturnsFalse_TransferAttachmentsThrowsMailerException()
    {
        $mockLocale = $this->getMockBuilder('Localization')->setMethods(['translateCharset'])->getMock();
        $mockLocale->expects($this->any())
            ->method('translateCharset')
            ->will($this->returnValue('foobar')); // the filename that Localization::translateCharset will return

        $mailerConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS['current_user']);
        $mailerConfiguration->setLocale($mockLocale);

        $mockPhpMailerProxy = $this->getMockBuilder('PHPMailerProxy')->setMethods(['addEmbeddedImage'])->getMock();

        $mockPhpMailerProxy->expects($this->once())
            ->method('addEmbeddedImage')
            ->will($this->returnValue(false));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([$mailerConfiguration])
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferHeaders',
                'transferRecipients',
                'transferBody',
            ])
            ->getMock();

        $embeddedImage = new EmbeddedImage('foobar', '/foo/bar.txt');
        $mockMailer->addAttachment($embeddedImage);

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferRecipients')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferBody')
            ->will($this->returnValue(true));

        // transferAttachments should fail after transferBody and before PHPMailer's Send is called

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_PHPMailerSendThrowsException_SendCatchesItAndThrowsMailerException()
    {
        $mockPhpMailerProxy = $this->createPartialMock('PHPMailerProxy', ['send']);

        $mockPhpMailerProxy->expects($this->once())
            ->method('send')
            ->will($this->throwException(new PHPMailerExeption()));

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferHeaders',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->getMock();

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferHeaders')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferRecipients')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferBody')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferAttachments')
            ->will($this->returnValue(true));

        $this->expectException(MailerException::class);
        $mockMailer->send();
    }

    public function testSend_AllMethodCallsAreSuccessful_ReturnsSentMessage()
    {
        $mockPhpMailerProxy = $this->createPartialMock('PHPMailerProxy', ['send', 'getSentMIMEMessage']);

        $mockPhpMailerProxy->expects($this->once())
            ->method('send')
            ->will($this->returnValue(true));

        $expected = 'the sent email';
        $mockPhpMailerProxy->expects($this->once())->method('getSentMIMEMessage')->willReturn($expected);

        $mockMailer = $this->getMockBuilder('SmtpMailer')
            ->setConstructorArgs([new OutboundSmtpEmailConfiguration($GLOBALS['current_user'])])
            ->setMethods([
                'generateMailer',
                'transferConfigurations',
                'connectToHost',
                'transferHeaders',
                'transferRecipients',
                'transferBody',
                'transferAttachments',
            ])
            ->getMock();

        $mockMailer->expects($this->once())
            ->method('generateMailer')
            ->will($this->returnValue($mockPhpMailerProxy));

        $mockMailer->expects($this->once())
            ->method('transferConfigurations')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('connectToHost')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferHeaders')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferRecipients')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferBody')
            ->will($this->returnValue(true));

        $mockMailer->expects($this->once())
            ->method('transferAttachments')
            ->will($this->returnValue(true));

        $actual = $mockMailer->send();
        $this->assertEquals(
            $expected,
            $actual,
            'The sent MIME message should have been returned as confirmation for the send'
        );
    }

    /**
     * @covers ::transferOauthConfigurations
     * @throws MailerException
     */
    public function testTransferOauthConfigurations()
    {
        // Mock the config object to be used by the SmtpMailer instance
        $mockConfig = $this->createPartialMock('OutboundSmtpEmailConfiguration', []);
        $mockConfig->setEAPMId('fake_eapm_id');

        // Mock the external API object to be used by the SmtpMailer instance
        $mockApi = $this->createPartialMock('ExtAPIGoogleEmail', ['getAccessToken']);
        $mockApi->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('fake_access_token');

        // Mock the EAPM bean to be used by the SmtpMailer instance
        $mockEAPMBean = $this->createPartialMock('EAPM', []);
        $mockEAPMBean->id = 'fake_eapm_id';

        // Mock the SmtpMailer instance
        $mockMailer = $this->createPartialMock('SmtpMailer', ['getExternalApi', 'getEAPMBean']);
        SugarTestReflection::setProtectedValue($mockMailer, 'config', $mockConfig);
        $mockMailer->expects($this->once())
            ->method('getExternalApi')
            ->willReturn($mockApi);
        $mockMailer->expects($this->once())
            ->method('getEAPMBean')
            ->willReturn($mockEAPMBean);

        // Assert that transferOauthConfigurations correctly assigns the access token to the mailer
        $mockPHPMailerProxy = $this->createPartialMock('PHPMailerProxy', []);
        SugarTestReflection::callProtectedMethod($mockMailer, 'transferOauthConfigurations', [&$mockPHPMailerProxy]);
        $this->assertEquals('fake_access_token', $mockPHPMailerProxy->accessToken);
    }
}
