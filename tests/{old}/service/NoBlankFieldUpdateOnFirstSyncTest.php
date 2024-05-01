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
 * This unit test was written to test an Outlook Plugin Hotfix.  It is attempting to mimic
 * what would happen if a new Contact record was created in Sugar.  Then a record with the same
 * first and last name and a matching email was created in Outlook.  With the Outlook settings
 * set so that the Sugar server wins on conflicts, what was happening was that the new (blank) values
 * from the Outlook plugin were overriding the SugarCRM record values. Under the new test what should
 * happen is that blank values from the Outlook side do NOT wipe out the SugarCRM values on first sync.
 */
class NoBlankFieldUpdateOnFirstSyncTest extends SOAPTestCase
{
    private $resultId;
    private $resultId2;
    private $c;
    private $c2;

    protected function setUp(): void
    {
        global $current_user;
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';

        //Clean up any possible contacts not deleted
        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'NoBlankFieldUpdate' AND last_name = 'OnFirstSyncTest'");

        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();
        $contact->first_name = 'NoBlankFieldUpdate';
        $contact->last_name = 'OnFirstSyncTest';
        $contact->phone_mobile = '867-5309';
        $contact->email1 = 'noblankfieldupdateonfirstsync@example.com';
        $contact->title = 'Jenny - I Got Your Number';
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->c = $contact;

        $GLOBALS['db']->query("DELETE FROM contacts WHERE first_name = 'Collin' AND last_name = 'Lee'");

        //Manually create a contact entry
        $contact2 = new Contact();
        $contact2->title = 'Jenny - I Got Your Number';
        $contact2->first_name = 'Collin';
        $contact2->last_name = 'Lee';
        $contact2->phone_mobile = '867-5309';
        $contact2->disable_custom_fields = true;
        $contact2->email1 = '';
        $contact2->email2 = '';
        $contact2->team_id = '1';
        $contact2->team_set_id = '1';
        $contact2->save();
        $this->c2 = $contact2;
        //DELETE contact_users entries that may have remained
        $GLOBALS['db']->query("DELETE FROM contacts_users WHERE user_id = '{$current_user->id}'");
        parent::setUp();
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        global $current_user;
        SugarTestContactUtilities::removeAllCreatedContacts();
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id in ('{$this->resultId}', '{$this->resultId2}')");
        $GLOBALS['db']->query("DELETE FROM contacts_users WHERE user_id = '{$current_user->id}'");
        unset($this->c);
        unset($this->c2);
        parent::tearDown();
        $GLOBALS['db']->commit();
    }


    public function testNoBlankFieldUpdateOnFirstSyncTest()
    {
        global $current_user;
        $this->login();
        $contacts_list = [
            [
                ['name' => 'assigned_user_id', 'value' => "{$current_user->id}"],
                ['name' => 'first_name', 'value' => "{$this->c->first_name}"],
                ['name' => 'last_name', 'value' => "{$this->c->last_name}"],
                ['name' => 'email1', 'value' => 'noblankfieldupdateonfirstsync@example.com'],
                ['name' => 'phone_mobile', 'value' => ''],
                ['name' => 'contacts_users_id', 'value' => "{$current_user->id}"],
                ['name' => 'title', 'value' => ''],
                ['name' => 'do_not_call', 'value' => '1'],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);
        $this->resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], 'Found duplicate');

        $existingContact = new Contact();
        $existingContact->retrieve($this->c->id);

        $this->assertEquals('867-5309', $existingContact->phone_mobile, 'Assert that we have not changed the phone_mobile field from first sync');
        $this->assertEquals('Jenny - I Got Your Number', $existingContact->title, 'Assert that we have not changed the title field from first sync');
        $this->assertEquals(1, $existingContact->do_not_call, 'Assert the field "do_not_call" checkbox was checked and has value of 1');

        $total = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $this->assertEquals(1, $total, 'Assert we only have one Contact with the first and last name');

        //Now sync a second time
        $this->login();
        $contacts_list = [
            [
                ['name' => 'assigned_user_id', 'value' => "{$current_user->id}"],
                ['name' => 'first_name', 'value' => "{$this->c->first_name}"],
                ['name' => 'last_name', 'value' => "{$this->c->last_name}"],
                ['name' => 'email1', 'value' => 'noblankfieldupdateonfirstsync@example.com'],
                ['name' => 'phone_mobile', 'value' => '1-800-SUGARCRM'],
                ['name' => 'contacts_users_id', 'value' => "{$current_user->id}"],
                ['name' => 'title', 'value' => ''],
                ['name' => 'do_not_call', 'value' => '0'],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);
        $this->resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], 'Found duplicate');

        $existingContact = new Contact();
        $existingContact->retrieve($this->c->id);

        $this->assertEquals('1-800-SUGARCRM', $existingContact->phone_mobile, 'Assert that we have changed the phone_mobile field from second sync');
        $this->assertEquals('', $existingContact->title, 'Assert that we have changed the title field to be (blank) from second sync');
        $total = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $this->assertEquals(1, $total, 'Assert we only have one Contact with the first and last name');
        $this->assertEquals(0, $existingContact->do_not_call, 'Assert the field "do_not_call" checkbox was UN-checked and has value of 0');
    }


    public function testNoEmailsFindsDuplicates()
    {
        global $current_user;
        $this->login();
        $contacts_list = [
            [
                ['name' => 'assigned_user_id', 'value' => "{$current_user->id}"],
                ['name' => 'first_name', 'value' => "{$this->c2->first_name}"],
                ['name' => 'last_name', 'value' => "{$this->c2->last_name}"],
                ['name' => 'email1', 'value' => ''],
                ['name' => 'email2', 'value' => ''],
                ['name' => 'phone_mobile', 'value' => ''],
                ['name' => 'contacts_users_id', 'value' => "{$current_user->id}"],
                ['name' => 'title', 'value' => ''],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);
        $this->resultId2 = $result['ids'][0];
        $this->assertEquals($this->c2->id, $result['ids'][0], 'Found duplicate when both records have no email');

        $existingContact = new Contact();
        $existingContact->retrieve($this->c2->id);

        $this->assertEquals('867-5309', $existingContact->phone_mobile, 'Assert that we have not changed the phone_mobile field from first sync');
        $this->assertEquals('Jenny - I Got Your Number', $existingContact->title, 'Assert that we have not changed the title field from first sync');

        $total = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $this->assertEquals(1, $total, 'Assert we only have one Contact with the first and last name');

        //Now sync a second time
        $this->login();
        $contacts_list = [
            [
                ['name' => 'assigned_user_id', 'value' => "{$current_user->id}"],
                ['name' => 'first_name', 'value' => "{$this->c2->first_name}"],
                ['name' => 'last_name', 'value' => "{$this->c2->last_name}"],
                ['name' => 'email1', 'value' => ''],
                ['name' => 'email2', 'value' => ''],
                ['name' => 'phone_mobile', 'value' => '1-800-SUGARCRM'],
                ['name' => 'contacts_users_id', 'value' => "{$current_user->id}"],
                ['name' => 'title', 'value' => ''],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);

        $existingContact = new Contact();
        $existingContact->retrieve($this->c2->id);

        $this->assertEquals('1-800-SUGARCRM', $existingContact->phone_mobile, 'Assert that we have changed the phone_mobile field from second sync');
        $this->assertEquals('', $existingContact->title, 'Assert that we have changed the title field to be (blank) from second sync');
        $total = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM contacts WHERE first_name = '{$existingContact->first_name}' AND last_name = '{$existingContact->last_name}'");
        $this->assertEquals(1, $total, 'Assert we only have one Contact with the first and last name');
    }
}
