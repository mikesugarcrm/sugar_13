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

class Bug40989Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();
    }

    /*
     * @group bug40989
     */
    public function testRetrieveByStringFieldsFetchedRow()
    {
        $contact = SugarTestContactUtilities::createContact(null, [
            'last_name' => 'Bug40989Test',
        ]);

        $loadedContact = BeanFactory::newBean('Contacts');
        $loadedContact = $loadedContact->retrieve_by_string_fields([
            'last_name' => $contact->last_name,
        ]);
        $this->assertEquals($contact->last_name, $loadedContact->fetched_row['last_name']);
    }

    public function testProcessFullListQuery()
    {
        $loadedContact = SugarTestContactUtilities::createContact();
        $loadedContact->disable_row_level_security = true;
        $contactList = $loadedContact->get_full_list();
        $exampleContact = array_pop($contactList);
        $this->assertNotNull($exampleContact->fetched_row['id']);
    }
}
