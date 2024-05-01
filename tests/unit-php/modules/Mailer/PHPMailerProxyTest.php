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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Mailer;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \PHPMailerProxy
 */
class PHPMailerProxyTest extends TestCase
{
    /**
     * @covers ::smtpConnect
     */
    public function testSmtpConnect()
    {
        $mailer = $this->createPartialMock('\\PHPMailerProxy', ['getSMTPInstance']);
        $mailer->expects($this->never())->method('getSMTPInstance');
        $actual = $mailer->smtpConnect();
        $this->assertTrue($actual);
    }

    /**
     * \PHPMailer::postSend() will not be called when DISABLE_EMAIL_SEND is true.
     *
     * @covers ::send
     */
    public function testSend()
    {
        $mailer = $this->createPartialMock('\\PHPMailerProxy', ['preSend', 'postSend']);
        $mailer->expects($this->once())->method('preSend')->willReturn(true);
        $mailer->expects($this->never())->method('postSend');
        $mailer->send();
    }

    public function emptyOrInvalidMessageIdProvider()
    {
        return [
            [null],
            [''],
            ['foo'],
            ['foo@bar'],
            ['foo@bar.com'],
            ['<>'],
            ['<foo>'],
        ];
    }

    /**
     * @covers ::createHeader
     * @covers ::generateId
     * @dataProvider emptyOrInvalidMessageIdProvider
     * @param null|string $messageId
     */
    public function testCreateHeader_GeneratesMessageID($messageId)
    {
        $hostname = 'mycompany.com';

        $mailer = $this->createPartialMock('\\PHPMailerProxy', [
            'headerLine',
            'addrFormat',
            'addrAppend',
            'encodeHeader',
            'secureHeader',
            'serverHostname',
            'getMailMIME',
        ]);
        $mailer->method('headerLine')->willReturn('');
        $mailer->method('addrFormat')->willReturn('');
        $mailer->method('addrAppend')->willReturn('');
        $mailer->method('encodeHeader')->willReturn('');
        $mailer->method('secureHeader')->willReturn('');
        $mailer->method('serverHostname')->willReturn($hostname);
        $mailer->method('getMailMIME')->willReturn('');

        $mailer->MessageId = $messageId;
        $this->generateUniqueId($mailer);

        $mailer->createHeader();
        $this->assertMatchesRegularExpression('/\<\d+\.[a-zA-Z0-9]+@' . $hostname . '\>/', $mailer->getLastMessageId());
    }

    /**
     * @covers ::createHeader
     * @covers ::generateId
     */
    public function testCreateHeader_UsesExistingMessageID()
    {
        $mailer = $this->createPartialMock('\\PHPMailerProxy', [
            'headerLine',
            'addrFormat',
            'addrAppend',
            'encodeHeader',
            'secureHeader',
            'getMailMIME',
        ]);
        $mailer->method('headerLine')->willReturn('');
        $mailer->method('addrFormat')->willReturn('');
        $mailer->method('addrAppend')->willReturn('');
        $mailer->method('encodeHeader')->willReturn('');
        $mailer->method('secureHeader')->willReturn('');
        $mailer->method('getMailMIME')->willReturn('');

        $mailer->MessageID = '<foo@bar.com>';
        $this->generateUniqueId($mailer);

        $mailer->createHeader();
        $this->assertSame('<foo@bar.com>', $mailer->getLastMessageID());
    }

    /**
     * PHPMailer::createBody() is called before PHPMailer::createHeader() and establishes the unique ID. Mimic this
     * behavior for code that assumes the unique ID has already been generated.
     *
     * @param $mailer
     */
    private function generateUniqueId($mailer)
    {
        $uniqueid = TestReflection::callProtectedMethod($mailer, 'generateId');
        TestReflection::setProtectedValue($mailer, 'uniqueid', $uniqueid);
    }
}
