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
 * @covers SugarJobSendScheduledReport
 */
class SugarJobSendScheduledReportTest extends TestCase
{
    public function providerAttachFile()
    {
        return [
            [
                'CSV',
                'aaa',
                'bbb',
                'bbb',
                'aaa.csv',
                'application/csv',
                'base64',
            ],
            [
                'PDF',
                'aaa a',
                'bbb',
                'bbb',
                'aaa_a.pdf',
                'application/pdf',
                'base64',
            ],
        ];
    }

    /**
     * @dataProvider providerAttachFile
     * @covers ::attachFile
     */
    public function testAttachFile(
        $type,
        $attachmentName,
        $filename,
        $expectedPath,
        $expectedName,
        $expectedMimeType,
        $expectedEncoding
    ) {

        $mailer = new MailerMock();
        $job = $this->createPartialMock('SugarJobSendScheduledReport', []);
        $actual = SugarTestReflection::callProtectedMethod(
            $job,
            'attachFile',
            [$type, $mailer, $attachmentName, $filename]
        );
        $attachment = $mailer->getAttachment();

        // to make sure the attachment has the right properties
        $this->assertEquals($attachment->getPath(), $expectedPath);
        $this->assertEquals($attachment->getName(), $expectedName);
        $this->assertEquals($attachment->getMimeType(), $expectedMimeType);
        $this->assertEquals($attachment->getEncoding(), $expectedEncoding);
    }
}

/**
 * Mailer Mock Class
 */
class MailerMock
{
    protected $attachment;

    /**
     * @access public
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return mixed
     */
    public function getAttachment()
    {
        return $this->attachment;
    }
}
