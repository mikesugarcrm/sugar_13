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
 * This class tests that get_modified_entries returns xml with CDATA for <value> tags
 */
class Bug47683Test extends SOAPTestCase
{
    private $contact;

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->setupTestContact();
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestContactUtilities::removeCreatedContactsUsersRelationships();
        SugarTestMeetingUtilities::removeMeetingContacts();
        SugarTestHelper::tearDown();
    }

    public function testGetModifiedEntries()
    {
        $this->login();
        $ids = [$this->contact->id];
        $result = $this->soapClient->get_modified_entries($this->sessionId, 'Contacts', $ids, []);
        $result = object_to_array_deep($result);
        $decoded = base64_decode($result['result']);

        $this->assertContains("<value>{$this->contact->first_name}</value>", $decoded, 'First name not found in data');
        $this->assertContains("<value>{$this->contact->last_name}</value>", $decoded, 'Last name not found in data');
    }


    /**********************************
     * HELPER PUBLIC FUNCTIONS
     **********************************/
    private function setupTestContact()
    {
        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->last_name .= ' Пупкин-Васильев'; // test special chars
        $this->contact->description = '<==>';
        $this->contact->save();
    }
}
