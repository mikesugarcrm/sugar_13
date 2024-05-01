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

class AttachmentTest extends TestCase
{
    /**
     * @group email
     * @group mailer
     */
    public function testFromSugarBean_BeanIsAccount_ThrowsException()
    {
        $mockAccount = self::getMockBuilder('Account')->setMethods(['Account'])->getMock();

        $mockAccount->expects(self::any())
            ->method('Account')
            ->will(self::returnValue(true));

        $this->expectException(MailerException::class);
        AttachmentPeer::attachmentFromSugarBean($mockAccount);
    }
}
