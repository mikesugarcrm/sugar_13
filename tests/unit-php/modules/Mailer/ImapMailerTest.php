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

/**
 * @coversDefaultClass ImapMailer
 */
class ImapMailerTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject $mailerMock Mock for ImapMailer class
     */
    private $mailerMock;

    protected function setUp(): void
    {
        $this->mailerMock = $this->getMockBuilder(ImapMailer::class)
            ->onlyMethods(['getMessageFromId'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @covers ::hasFlag
     */
    public function testHasFlag()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['hasFlag'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->expects($this->once())->method('hasFlag')->with(Laminas\Mail\Storage::FLAG_PASSED);
        $this->mailerMock->hasFlag('Passed', 123);
    }

    /**
     * @covers ::getHeaders
     */
    public function testGetHeaders()
    {
        $this->mailerMock
            ->method('getMessageFromId')
            ->will($this->throwException(new MailerException()));
        try {
            $this->mailerMock->getHeaders('123');
        } catch (MailerException $e) {
            $this->assertTrue(
                strpos(
                    $e->getMessage(),
                    'ImapMailer is unable to retrieve message with id: 123'
                ) === 0
            );
        }
    }

    /**
     * @covers ::getHeaderAsString
     */
    public function testGetHeaderAsString()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = 'Email Subject';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($subject);
        $this->assertEquals('', $this->mailerMock->getHeaderAsString(123, 'Subject'));

        $messageMock->Subject = $subject;
        $this->assertEquals($subject, $this->mailerMock->getHeaderAsString(123, 'Subject'));
    }

    /**
     * @covers ::getSubject
     */
    public function testGetSubject()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = 'Email Subject';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($subject);
        $this->assertEquals('', $this->mailerMock->getSubject(123));

        $messageMock->Subject = $subject;
        $this->assertEquals($subject, $this->mailerMock->getSubject(123));
    }

    /**
     * @covers ::getTo
     */
    public function testGetTo()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $to = 'Name <email@example.com>';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($to);
        $this->assertEquals('', $this->mailerMock->getTo(123));

        $messageMock->To = $to;
        $this->assertEquals($to, $this->mailerMock->getTo(123));
    }

    /**
     * @covers ::getFrom
     */
    public function testGetFrom()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $from = 'Name <email@example.com>';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($from);
        $this->assertEquals('', $this->mailerMock->getFrom(123));

        $messageMock->From = $from;
        $this->assertEquals($from, $this->mailerMock->getFrom(123));
    }

    /**
     * @covers ::getCc
     */
    public function testGetCc()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $cc = 'Name <email@example.com>';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($cc);
        $this->assertEquals('', $this->mailerMock->getCc(123));

        $messageMock->CC = $cc;
        $this->assertEquals($cc, $this->mailerMock->getCc(123));
    }

    /**
     * @covers ::getBcc
     */
    public function testGetBcc()
    {
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->onlyMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMock();

        $bcc = 'Name <email@example.com>';
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);
        $messageMock->method('getHeader')->willReturn($bcc);
        $this->assertEquals('', $this->mailerMock->getBcc(123));

        $messageMock->BCC = $bcc;
        $this->assertEquals($bcc, $this->mailerMock->getBcc(123));
    }

    /**
     * @covers ::getAddressesFromHeader
     */
    public function testGetAddressesFromHeader()
    {
        $to = 'Name1 <email1@example.com>, Name2 <email2@example.com>';
        $email = "To: {$to} \n" .
            "Subject: multipart\n" .
            "Date: Sun, 01 Jan 2000 00:00:00 +0000\n" .
            'From: <peter-mueller@example.com>';

        $message = new \Laminas\Mail\Storage\Message(['raw' => $email]);
        $expected = ['email1@example.com', 'email2@example.com'];
        $this->assertEquals($expected, $this->mailerMock->getAddressesFromHeader($message, 'To'));
    }

    /**
     * @covers ::getHeaderAddressList
     */
    public function testGetHeaderAddressList()
    {
        $to = 'Name1 <email1@example.com>, Name2 <email2@example.com>';
        $email = "To: {$to} \n" .
            "Subject: multipart\n" .
            "Date: Sun, 25 Jul 2021 00:00:00 +0000\n" .
            'From: <john-doe@example.com>';

        $header = new ArrayIterator();
        try {
            $list = $this->mailerMock->getHeaderAddressList($header, 'To');
        } catch (MailerException $e) {
            $this->assertTrue(
                strpos(
                    $e->getMessage(),
                    'System is unable to get address list for To address(es)'
                ) === 0
            );
        }
    }

    /**
     * @covers ::getFromAddress
     * @covers ::getToAddresses
     * @covers ::getCcAddresses
     * @covers ::getBccAddresses
     */
    public function testGetAddresses()
    {
        $email = "To: Name1 <to1@example.com>, Name2 <to2@example.com> \n" .
            "Subject: multipart\n" .
            "Date: Sun, 01 Jan 2000 00:00:00 +0000\n" .
            "From: Name <from@example.com>\n" .
            "Cc: Name1 <cc1@example.com>, Name2 <cc2@example.com>\n" .
            'Bcc: Name1 <bcc1@example.com>, Name2 <bcc2@example.com>';

        $message = new \Laminas\Mail\Storage\Message(['raw' => $email]);
        $this->mailerMock->method('getMessageFromId')->willReturn($message);

        $expectedFrom = ['from@example.com'];
        $this->assertEquals($expectedFrom, $this->mailerMock->getFromAddress(123));

        $expectedTo = ['to1@example.com', 'to2@example.com'];
        $this->assertEquals($expectedTo, $this->mailerMock->getToAddresses(123));

        $expectedCC = ['cc1@example.com', 'cc2@example.com'];
        $this->assertEquals($expectedCC, $this->mailerMock->getCcAddresses(123));

        $expectedBCC = ['bcc1@example.com', 'bcc2@example.com'];
        $this->assertEquals($expectedBCC, $this->mailerMock->getBccAddresses(123));
    }

    /**
     * @covers ::getFromAddress
     * @covers ::getToAddresses
     * @covers ::getCcAddresses
     * @covers ::getBccAddresses
     */
    public function testGetEmptyAddresses()
    {
        $email = "Subject: multipart\n" .
            "Date: Sun, 01 Jan 2000 00:00:00 +0000\n";

        $message = new \Laminas\Mail\Storage\Message(['raw' => $email]);
        $this->mailerMock->method('getMessageFromId')->willReturn($message);

        $expectedFrom = [];
        $this->assertEquals($expectedFrom, $this->mailerMock->getFromAddress(123));

        $expectedTo = [];
        $this->assertEquals($expectedTo, $this->mailerMock->getToAddresses(123));

        $expectedCC = [];
        $this->assertEquals($expectedCC, $this->mailerMock->getCcAddresses(123));

        $expectedBCC = [];
        $this->assertEquals($expectedBCC, $this->mailerMock->getBccAddresses(123));
    }

    /**
     * @covers ::getBody
     */
    public function testGetBody()
    {
        // Create a mock message with the test raw content
        $messageRaw = file_get_contents(__DIR__ . '/ImapMailerTestMessage.txt');
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->setConstructorArgs([['raw' => $messageRaw]])
            ->onlyMethods([])
            ->getMock();
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);

        // Assert that the correct plain and HTML versions of the message body
        // are returned by getBody()
        $result = $this->mailerMock->getBody(123);
        $this->assertEquals('The plain text message part', $result['plain']);
        $this->assertEquals('<a href="https://fake-link.com">The HTML message part</a>', $result['html']);
    }

    /**
     * @covers ::getAttachments
     *
     * @param string $messageFileName mock message file name
     * @param array $expected expected result
     * @dataProvider getAttachmentProvider
     */
    public function testGetAttachments(string $messageFileName, array $expected)
    {
        // Create a mock message with the test raw content
        $messageRaw = file_get_contents(__DIR__ . $messageFileName);
        $messageMock = $this->getMockBuilder(Laminas\Mail\Storage\Message::class)
            ->setConstructorArgs([['raw' => $messageRaw]])
            ->onlyMethods([])
            ->getMock();
        $this->mailerMock->method('getMessageFromId')->willReturn($messageMock);

        $result = $this->mailerMock->getAttachments(123);
        $this->assertNotEmpty($result[0]);
        $this->assertEquals($expected, $result[0]);
    }

    /**
     * Provider for testGetAttachments()
     * @return array
     */
    public function getAttachmentProvider(): array
    {
        return [
            // Assert that getAttachments() returns an array containing the expected
            // attachment from the test message
            'Content type is application/pdf' => [
                'messageFileName' => '/ImapMailerTestMessage.txt',
                'expected' => [
                    'contentType' => 'application/pdf',
                    'type' => 'application',
                    'subtype' => 'pdf',
                    'contentDisposition' => 'attachment',
                    'contentId' => '<123@fake.contentID.com>',
                    'encoding' => 'base64',
                    'charset' => null,
                    'fileName' => 'TestFile.pdf',
                    'content' => "ZmFrZV9lbmNvZGVkX2F0dGFjaG1lbnRfZGF0YQ==\n",
                    'randomFileName' => false,
                ],
            ],
            // Tests message from a Lotus Notes attachment
            'Content type is image/gif from Lotus' => [
                'messageFileName' => '/ImapMailerTestLotusMessage.txt',
                'expected' => [
                    'contentType' => 'image/gif',
                    'type' => 'image',
                    'subtype' => 'gif',
                    'contentDisposition' => null,
                    'contentId' => '<_1_0AEFF51C0AEFEEA0005B83FCC125865E>',
                    'encoding' => 'base64',
                    'charset' => null,
                    'fileName' => '_1_0AEFF51C0AEFEEA0005B83FCC125865E.gif',
                    'content' => '',
                    'randomFileName' => true,
                ],
            ],
            // Tests message from a Gmail attachment
            'Content type is image/png from Gmail' => [
                'messageFileName' => '/ImapMailerTestGmailMessage.txt',
                'expected' => [
                    'contentType' => 'image/png',
                    'type' => 'image',
                    'subtype' => 'png',
                    'contentDisposition' => 'inline',
                    'contentId' => '<image001.png@01D6EA82.D7C3CDB0>',
                    'encoding' => 'base64',
                    'charset' => null,
                    'fileName' => 'image001.png',
                    'content' => '',
                    'randomFileName' => false,
                ],
            ],
        ];
    }
}
