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
 * @group bug39234
 */
class SetEntriesContactsTest extends SOAPTestCase
{
    private $c1;
    private $c2;
    private $a1;

    /**
     * Create test user
     */
    protected function setUp(): void
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $unid = uniqid();
        $time = date('Y-m-d H:i:s');

        $contact = new Contact();
        $contact->id = 'c_' . $unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->email1 = 'fred@rogers.com';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->c1 = $contact;

        $account = new Account();
        $account->id = 'a_' . $unid;
        $account->name = 'acctfirst';
        $account->assigned_user_id = 'SugarUser';
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $this->a1 = $account;

        $this->c1->load_relationship('accounts');
        $this->c1->accounts->add($this->a1->id);

        $contact2 = new Contact();
        $contact2->id = 'c2_' . $unid;
        $contact2->first_name = 'testfirst';
        $contact2->last_name = 'testlast';
        $contact2->email1 = 'fred@rogers.com';
        $contact2->new_with_id = true;
        $contact2->disable_custom_fields = true;
        $contact2->save();
        $this->c2 = $contact2;
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown(): void
    {
        global $soap_version_test_accountId, $soap_version_test_opportunityId, $soap_version_test_contactId;
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c1->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->c1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->c2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->a1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE name = 'joe pizza'");
        unset($this->c1);
        unset($this->c2);
        unset($this->a1);
        unset($soap_version_test_accountId);
        unset($soap_version_test_opportunityId);
        unset($soap_version_test_contactId);

        SugarTestHelper::tearDown();
    }

    public function testSetEntries()
    {
        $this->login();
        $result = get_object_vars($this->soapClient->set_entries($this->sessionId, 'Contacts', [[['name' => 'last_name', 'value' => $this->c1->last_name], ['name' => 'email1', 'value' => $this->c1->email1], ['name' => 'first_name', 'value' => $this->c1->first_name], ['name' => 'account_name', 'value' => $this->a1->name]]]));
        $this->assertTrue(isset($result['ids']));
        $this->assertEquals($result['ids'][0], $this->c1->id);
    } // fn

    public function testSetEntries2()
    {
        $this->login();
        $result = get_object_vars($this->soapClient->set_entries($this->sessionId, 'Contacts', [[['name' => 'last_name', 'value' => $this->c2->last_name], ['name' => 'email1', 'value' => $this->c2->email1], ['name' => 'first_name', 'value' => $this->c2->first_name], ['name' => 'account_name', 'value' => 'joe pizza']]]));
        $this->assertTrue(isset($result['ids']));
        $this->assertNotEquals($result['ids'][0], $this->c1->id);
    } // fn
}
