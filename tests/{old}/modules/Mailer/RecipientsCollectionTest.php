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

class RecipientsCollectionTest extends TestCase
{
    /**
     * This test essentially tests clearAll, clearTo, clearCc and clearBcc.
     *
     * @group email
     * @group mailer
     */
    public function testClearAll_ResultIsSuccessful()
    {
        $recipientsCollection = new RecipientsCollection();

        $to = [
            new EmailIdentity('foo@bar.com', 'Foo Bar'),
            new EmailIdentity('qux@baz.net'),
        ];
        $recipientsCollection->addRecipients($to);

        $bcc = [
            new EmailIdentity('abc@123.com'),
            new EmailIdentity('tester@test.org'),
        ];
        $recipientsCollection->addRecipients($bcc, RecipientsCollection::FunctionAddBcc);

        // make sure the recipients have been added
        $expected = 4;
        $allRecipients = $recipientsCollection->getAll();
        $actual = safeCount($allRecipients['to']) + safeCount($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been added");

        // now clear all recipients
        $recipientsCollection->clearAll();
        $expected = 0;
        $allRecipients = $recipientsCollection->getAll();
        $actual = safeCount($allRecipients['to']) + safeCount($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should remain");
    }

    /**
     * @group email
     * @group mailer
     */
    public function testAddRecipients_CallInvalidMethod_ThrowsException()
    {
        $recipientCollection = new RecipientsCollection();
        $recipients = []; // the recipients don't matter for this test case
        $function = 'asdf';  // some asinine value that wouldn't actually be used

        $this->expectException(MailerException::class);
        $recipientCollection->addRecipients($recipients, $function);
    }

    /**
     * This test essentially tests addRecipients and addTo.
     *
     * @group email
     * @group mailer
     */
    public function testAddRecipients_UseAddTo_PassInAnEmailIdentity_RecipientIsValidSoRecipientIsAdded()
    {
        $recipientsCollection = new RecipientsCollection();
        $recipient = new EmailIdentity('foo@bar.com', 'Foo Bar');

        $recipientsCollection->addRecipients($recipient);

        $expected = 1;
        $actual = $recipientsCollection->getTo();
        self::assertEquals($expected, count($actual), "{$expected} recipients should have been added to the TO list");

        $expected = $recipient->getEmail();
        self::assertEquals($expected, $actual[$expected]->getEmail());
    }

    /**
     * This test essentially tests addRecipients and addCc.
     *
     * @group email
     * @group mailer
     */
    public function testAddRecipients_UseAddCc_PassInAnArrayOfEmailIdentityObjects_NoInvalidRecipientsSoAllAreAdded()
    {
        $recipientsCollection = new RecipientsCollection();
        $recipients = [
            new EmailIdentity('foo@bar.com', 'Foo Bar'),
            new EmailIdentity('qux@baz.net'),
        ];

        $recipientsCollection->addRecipients($recipients, RecipientsCollection::FunctionAddCc);

        $expected = 2;
        $actual = $recipientsCollection->getCc();
        self::assertEquals($expected, count($actual), "{$expected} recipients should have been added to the CC list");

        $expected = $recipients[1]->getEmail();
        self::assertEquals($expected, $actual[$expected]->getEmail());
    }

    /**
     * This test essentially tests getAll, getTo, getCc and getBcc.
     *
     * @group email
     * @group mailer
     */
    public function testGetAll_HasRecipients_ReturnsNonEmptyArrays()
    {
        $recipientsCollection = new RecipientsCollection();

        $to = [
            new EmailIdentity('foo@bar.com', 'Foo Bar'),
            new EmailIdentity('qux@baz.net'),
        ];
        $recipientsCollection->addRecipients($to);

        $cc = [
            new EmailIdentity('abc@123.com'),
        ];
        $recipientsCollection->addRecipients($cc, RecipientsCollection::FunctionAddCc);

        $bcc = [
            new EmailIdentity('tester@test.org'),
        ];
        $recipientsCollection->addRecipients($bcc, RecipientsCollection::FunctionAddBcc);

        $expected = 4;
        $allRecipients = $recipientsCollection->getAll();
        $actual = safeCount($allRecipients['to']) + safeCount($allRecipients['cc']) + safeCount($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been added");

        $expected = $to[1]->getEmail();
        $actual = $allRecipients['to'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the TO list");

        $expected = $cc[0]->getEmail();
        $actual = $allRecipients['cc'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the CC list");

        $expected = $bcc[0]->getEmail();
        $actual = $allRecipients['bcc'][$expected]->getEmail();
        self::assertEquals($expected, $actual, "{$expected} should have been found in the BCC list");
    }

    /**
     * This test essentially tests getAll, getTo, getCc and getBcc.
     *
     * @group email
     * @group mailer
     */
    public function testGetAll_HasNoRecipients_ReturnsEmptyArrays()
    {
        $recipientsCollection = new RecipientsCollection();

        $expected = 0;
        $allRecipients = $recipientsCollection->getAll();
        $actual = safeCount($allRecipients['to']) + safeCount($allRecipients['cc']) + safeCount($allRecipients['bcc']);
        self::assertEquals($expected, $actual, "{$expected} recipients should have been found");
    }
}
