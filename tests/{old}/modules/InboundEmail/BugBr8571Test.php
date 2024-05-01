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

/**
 * Class BugBr8571Test
 */
class BugBr8571Test extends \PHPUnit\Framework\TestCase
{
    public const LOCALHOST = '127.0.0.1';
    public const UNAVAILABLE_PORT = 12345;

    /**
     * @covers InboundEmail::getImapMailer
     */
    public function testGetImapMailerWithUnavailableServer()
    {
        $mailbox = Mailbox::fromRemoteSystemName(RemoteSystemName::fromString(self::LOCALHOST), self::UNAVAILABLE_PORT);
        $inboundEmail = new InboundEmail();
        $inboundEmail->email_user = 'sugar@localhost';
        $inboundEmail->email_password = 'asdf';
        $inboundEmail->eapm_id = '';
        $mailer = SugarTestReflection::callProtectedMethod($inboundEmail, 'getImapMailer', [$mailbox]);
        $this->assertEquals(null, $mailer);
    }
}
