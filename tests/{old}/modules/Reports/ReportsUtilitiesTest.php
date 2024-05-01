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

class ReportsUtilitiesTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * @group reports
     * @group email
     * @group mailer
     */
    public function testSendNotificationOfInvalidReport_InvalidRecipientAddress_ThrowsMailerException()
    {
        $recipient = new User();
        $recipient->email1 = null;
        $recipient->email2 = null;

        $this->expectException(MailerException::class);
        $reportsUtilities = new ReportsUtilities();
        $reportsUtilities->sendNotificationOfInvalidReport($recipient, 'asdf');
    }
}
