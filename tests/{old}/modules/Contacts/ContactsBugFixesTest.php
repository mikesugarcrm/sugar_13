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

class ContactsBugFixesTest extends TestCase
{
    /**
     * @var array<string, class-string<\contact>>|array<string, string>|mixed
     */
    public $fields;
    /**
     * @var string|mixed
     */
    public $prefix;
    /**
     * @var mixed[]|mixed
     */
    public $contacts;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->fields = ['first_name' => 'contact', 'last_name' => 'unitTester', 'sync_contact' => '1'];
        $this->prefix = 'unittest_contacts_bugfixes';
        $this->contacts = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->fields as $fieldName => $fieldValue) {
            unset($_POST[$fieldName]);
        }
        foreach ($this->contacts as $contact) {
            $contact->mark_deleted($contact->id);
        }

        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    public function testBug59675ContactFormBaseRefactor()
    {
        $formBase = new ContactFormBase();
        foreach ($this->fields as $fieldName => $fieldValue) {
            $_POST[$this->prefix . $fieldName] = $fieldValue;
        }
        $_POST['record'] = 'asdf';
        $_REQUEST['action'] = 'save';

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertTrue($bean->sync_contact == true, 'Sync Contact was not set to true');

        unset($bean);
        $_POST[$this->prefix . 'sync_contact'] = '0';

        $bean = $formBase->handleSave($this->prefix, false);
        $this->contacts[] = $bean;

        $this->assertFalse($bean->sync_contact == true, 'Sync Contact was not set to false');
    }

    public function testPopulateFromApiSyncContactTrue()
    {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup());
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = ['sync_contact' => true];
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertTrue($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }

    public function testPopulateFromApiSyncContactFalse()
    {
        $capih = new ContactsApiHelper(new ContactsBugFixesServiceMockup());
        $contact = BeanFactory::newBean('Contacts');
        $submittedData = ['sync_contact' => false];
        $data = $capih->populateFromApi($contact, $submittedData);
        $contact->save();
        $contact->retrieve($contact->id);
        $this->assertEmpty($contact->sync_contact);
        $contact->mark_deleted($contact->id);
    }

    public function testCRYS461Fix()
    {
        $contactApi = new ContactsApiHelper(new ContactsBugFixesServiceMockup());
        $contact = SugarTestContactUtilities::createContact();
        $contact->retrieve($contact->id);
        $this->assertEquals($contact->email1, $contact->fetched_row['email1']);

        $submittedData = [
            'email' => [
                ['email_address' => 'testnew@example.com', 'primary_address' => true],
                ['email_address' => 'test2@example.com', 'primary_address' => false],
            ],
        ];
        $contactApi->populateFromApi($contact, $submittedData);
        $this->assertNotEquals($contact->email1, $contact->fetched_row['email1']);
    }
}

class ContactsBugFixesServiceMockup extends ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
