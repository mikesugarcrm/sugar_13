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
 * @group bug44280
 */
class SetEntriesMultipleTest extends SOAPTestCase
{
    public $accnt1;
    public $accnt2;
    public $cont1;
    public $cont2;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v2/soap.php';
        parent::setUp();
    }

    /**
     * Remove anything that was used during this test
     */
    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->cont1->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->cont2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->cont1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id= '{$this->cont2->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->accnt1->id}'");
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->accnt2->id}'");

        unset($this->accnt1);
        unset($this->accnt2);
        unset($this->cont1);
        unset($this->cont2);
        parent::tearDown();
    }

    public function createAccount($name, $user_id)
    {
        $account = new Account();
        $account->id = create_guid();
        $account->name = $name;
        $account->assigned_user_id = $user_id;
        $account->new_with_id = true;
        $account->disable_custom_fields = true;
        $account->save();
        $GLOBALS['db']->commit();
        return $account;
    }

    public function createContact($first_name, $last_name, $email)
    {
        $contact = new Contact();
        $contact->id = create_guid();
        $contact->first_name = $first_name;
        $contact->last_name = $last_name;
        $contact->email1 = $email;
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $GLOBALS['db']->commit();
        return $contact;
    }

    public function testSetEntries()
    {
        $this->login();

        // first create two accounts with identical account names
        $this->accnt1 = $this->createAccount('sugar_account_name', 'sugarUser1');
        $this->accnt2 = $this->createAccount('sugar_account_name', 'sugarUser2');

        // now creating two contacts and relate them to the above accounts

        $this->cont1 = $this->createContact('first1', 'last1', 'adsf@asdf.com');
        $this->cont2 = $this->createContact('first2', 'last2', 'adsf@asdf.com');

        // this will be used in set_entries call
        $accounts_list = [
            [
                ['name' => 'id', 'value' => $this->accnt1->id],
                ['name' => 'first_name', 'value' => $this->accnt1->name],
                ['name' => 'account_id', 'value' => $this->accnt1->id],
                ['name' => 'team_id', 'value' => '1'],
                ['name' => 'soap_dts_c', 'value' => '2011-06-02 17:37:49'],
                ['name' => 'contactid_4d_c', 'value' => '123456'],
                ['name' => 'phone_work', 'value' => '1234567890'],
                ['name' => 'title', 'value' => ''],
            ],
            [
                ['name' => 'id', 'value' => $this->accnt2->id],
                ['name' => 'first_name', 'value' => $this->accnt2->name],
                ['name' => 'account_id', 'value' => $this->accnt2->id],
                ['name' => 'team_id', 'value' => '1'],
                ['name' => 'soap_dts_c', 'value' => '2011-06-02 16:37:49'],
                ['name' => 'contactid_4d_c', 'value' => '999991'],
                ['name' => 'phone_work', 'value' => '987654321'],
                ['name' => 'title', 'value' => ''],
            ],
        ];
        // add the accounts
        $result = $this->soapClient->set_entries($this->sessionId, 'Accounts', $accounts_list);

        // add the contacts & set the relationship to account
        $contacts_list = [
            [
                ['name' => 'last_name', 'value' => $this->cont1->last_name],
                ['name' => 'email', 'value' => $this->cont1->email1],
                ['name' => 'first_name', 'value' => $this->cont1->first_name],
                ['name' => 'id', 'value' => $this->cont1->id],
                ['name' => 'account_name', 'value' => $this->accnt1->name],
                ['name' => 'account_id', 'value' => $this->accnt1->id],
            ],
            [
                ['name' => 'last_name', 'value' => $this->cont2->last_name],
                ['name' => 'email', 'value' => $this->cont2->email1],
                ['name' => 'first_name', 'value' => $this->cont2->first_name],
                ['name' => 'id', 'value' => $this->cont2->id],
                ['name' => 'account_name', 'value' => $this->accnt2->name],
                ['name' => 'account_id', 'value' => $this->accnt2->id],
            ],
        ];


        $result2 = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);

        // lets check first relationship
        $query1 = "SELECT account_id FROM accounts_contacts WHERE contact_id='{$this->cont1->id}'";
        $cont1_account_result = $GLOBALS['db']->query($query1, true, '');
        $row1 = $GLOBALS['db']->fetchByAssoc($cont1_account_result);
        $this->assertEquals(
            $this->accnt1->id,
            $row1['account_id'],
            'First account-contact relationship does not match with DB.'
        );

        // lets check second relationship
        $query2 = "SELECT account_id FROM accounts_contacts WHERE contact_id='{$this->cont2->id}'";
        $cont2_account_result = $GLOBALS['db']->query($query2, true, '');
        $row2 = $GLOBALS['db']->fetchByAssoc($cont2_account_result);
        $this->assertEquals(
            $this->accnt2->id,
            $row2['account_id'],
            'Second account-contact relationship returned does not match with DB.'
        );
    }
}
