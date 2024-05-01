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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Contacts;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Contact
 */
class ContactTest extends TestCase
{
    /**
     * @covers ::getSiteUserId
     */
    public function testSetSiteUserIdOnAPIFetch()
    {
        $contact = $this->getMockBuilder('Contact')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $contact->expects($this->once())
            ->method('save');

        $contact->getSiteUserId(true);
    }

    /**
     * @covers ::getSiteUserId
     */
    public function testSetSiteUserIdNoOp()
    {
        $contact = $this->getMockBuilder('Contact')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $contact->expects($this->never())
            ->method('save');

        $contact->site_user_id = 'foo';
        $actual = $contact->getSiteUserId(true);
        $this->assertSame($actual, 'foo');
    }
}
