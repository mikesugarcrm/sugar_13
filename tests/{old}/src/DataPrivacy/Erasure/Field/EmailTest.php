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

namespace Sugarcrm\SugarcrmTests\DataPrivacy\Erasure\Field;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email;
use SugarTestContactUtilities;
use SugarTestEmailAddressUtilities;
use SugarTestLeadUtilities;
use SugarTestHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\DataPrivacy\Erasure\Field\Email
 */
class EmailTest extends TestCase
{
    private static $emailAddress = [];
    private static $contact;
    private static $lead;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);
        self::$contact = SugarTestContactUtilities::createContact();
        self::$lead = SugarTestLeadUtilities::createLead();
        // create 3 emails
        for ($i = 0; $i < 3; $i++) {
            $emailAddress = SugarTestEmailAddressUtilities::createEmailAddress();
            SugarTestEmailAddressUtilities::addAddressToPerson(self::$contact, $emailAddress);
            SugarTestEmailAddressUtilities::addAddressToPerson(self::$lead, $emailAddress);
            self::$emailAddress[] = $emailAddress;
        }
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
    }

    /**
     * @test
     * @covers ::__construct()
     * @covers ::erase()
     * @covers ::getAllRelatedBeans
     */
    public function erase()
    {
        $contactBean = \BeanFactory::retrieveBean('Contacts', self::$contact->id, ['use_cache' => false]);
        // email count
        $this->assertEquals(4, safeCount($contactBean->email));
        foreach (self::$emailAddress as $emailAddress) {
            $email = new Email($emailAddress->id);
            $email->erase($contactBean);
        }
        $contactBean = \BeanFactory::retrieveBean('Contacts', self::$contact->id, ['use_cache' => false]);
        // check email count
        $this->assertEquals(1, safeCount($contactBean->email));
        // make sure all linked emails have been erased
        foreach (self::$emailAddress as $emailAddress) {
            $beansHaveThisEmail = $emailAddress->getBeansByEmailAddress($emailAddress->email_address);
            $this->assertEmpty($beansHaveThisEmail);
        }
    }
}
