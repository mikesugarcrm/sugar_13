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

class Bug42279Test extends TestCase
{
    private $contact;

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->contact = SugarTestContactUtilities::createContact();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group bug42279
     */
    public function testEmailAddressInFetchedRow()
    {
        $sea = BeanFactory::newBean('EmailAddresses');

        // this will populate contact->email1
        $sea->populateLegacyFields($this->contact);
        $email1 = $this->contact->email1;

        // this should set fetched_row['email1'] to contatc->email1
        $sea->handleLegacyRetrieve($this->contact);
        $this->assertEquals($email1, $this->contact->fetched_row['email1']);
    }
}
