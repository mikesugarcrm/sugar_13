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


class RestRelateTest extends RestTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us');
        $this->accounts = [];
        $this->contacts = [];
        $this->opps = [];
    }

    protected function tearDown(): void
    {
        $accountIds = [];
        foreach ($this->accounts as $account) {
            $accountIds[] = $account->id;
        }
        $accountIds = "('" . implode("','", $accountIds) . "')";
        $oppIds = [];
        foreach ($this->opps as $opp) {
            $oppIds[] = $opp->id;
        }
        $oppIds = "('" . implode("','", $oppIds) . "')";
        $contactIds = [];
        foreach ($this->contacts as $contact) {
            $contactIds[] = $contact->id;
        }
        $contactIds = "('" . implode("','", $contactIds) . "')";

        $GLOBALS['db']->query("DELETE FROM accounts WHERE id IN {$accountIds}");
        if ($GLOBALS['db']->tableExists('accounts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c IN {$accountIds}");
        }
        $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        if ($GLOBALS['db']->tableExists('opportunities_cstm')) {
            $GLOBALS['db']->query("DELETE FROM opportunities_cstm WHERE id_c IN {$oppIds}");
        }
        $GLOBALS['db']->query("DELETE FROM accounts_opportunities WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM opportunities_contacts WHERE opportunity_id IN {$oppIds}");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id IN {$contactIds}");
        if ($GLOBALS['db']->tableExists('contacts_cstm')) {
            $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c IN {$contactIds}");
        }
        $GLOBALS['db']->query("DELETE FROM accounts_contacts WHERE contact_id IN {$contactIds}");

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRelateList()
    {
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Make sure there is at least one page of each of the related modules
        for ($i = 0; $i < 5; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', ($i + 1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ($i = 0; $i < 60; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();
            $this->contacts[] = $contact;

            $contact->load_relationship('accounts');
            if ($i > 4) {
                // The final account gets all the fun.
                $accountNum = 4;
            } else {
                $accountNum = $i;
            }
            $contact->accounts->add([$this->accounts[$accountNum]]);
        }
        for ($i = 0; $i < 30; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $opp->load_relationship('accounts');
            if ($i > 4) {
                // The final account gets all the fun.
                $accountNum = 4;
            } else {
                $accountNum = $i;
            }
            $opp->accounts->add([$this->accounts[$accountNum]]);

            $contactNums = [$i];
            if ($i == 29) {
                // It's the last opportunity, give it all of the remaining contacts
                for ($ii = 30; $ii < 60; $ii++) {
                    $contactNums[] = $ii;
                }
            }

            foreach ($contactNums as $contactNum) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum % $ctsCount)];
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $contact_type]);
            }
        }

        $GLOBALS['db']->commit();

        // Test normal fetch
        $restReply = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/opportunities');

        $this->assertEquals(10, $restReply['reply']['next_offset'], 'Next offset was set incorrectly.');

        // Test Offset
        $restReply2 = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/opportunities?offset=20');

        $this->assertEquals(-1, $restReply2['reply']['next_offset'], 'Next offset was set incorrectly on the second page.');

        // Test basic search
        $restReply3 = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/contacts?q=' . rawurlencode($this->contacts[47]->last_name));

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->contacts[47]->last_name, $firstRecord['last_name'], 'The search failed for record: ' . $this->contacts[47]->last_name);

        // Sorting descending
        $restReply4 = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/contacts?order_by=last_name:DESC');

        $tmp = array_keys($restReply4['reply']['records']);
        $this->assertLessThan(
            $restReply4['reply']['records'][$tmp[0]]['last_name'],
            $restReply4['reply']['records'][$tmp[1]]['last_name'],
            'Second record is not lower than the first, decending order failed.'
        );

        // Sorting ascending
        $restReply5 = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/contacts?order_by=first_name:ASC');

        $tmp = array_keys($restReply5['reply']['records']);
        $this->assertGreaterThan(
            $restReply5['reply']['records'][$tmp[0]]['first_name'],
            $restReply5['reply']['records'][$tmp[1]]['first_name'],
            'Second record is not lower than the first, ascending order failed.'
        );


        // Fetching the role field from the opportunity contact relationship
        $restReply6 = $this->restCall('Opportunities/' . $this->opps[29]->id . '/link/contacts?order_by=first_name:DESC');
        $this->assertNotEmpty($restReply6['reply']['records'][0]['opportunity_role'], 'The role field on the Opportunity -> Contact relationship was not populated.');

        // verify accounts don't return the same contacts
        $restReply7 = $this->restCall('Accounts/' . $this->accounts[4]->id . '/link/contacts?order_by=last_name:ASC');

        $restReply8 = $this->restCall('Accounts/' . $this->accounts[3]->id . '/link/contacts?order_by=last_name:ASC');

        $account3 = $restReply8['reply']['records'];

        $account4 = $restReply7['reply']['records'];

        // due to account #4 having the most we will loop thru that checking to see if the names are equal or not
        // we can assume the GUID's will never match
        foreach ($account4 as $account) {
            foreach ($account3 as $acc) {
                $this->assertNotEquals($account['last_name'], $acc['last_name'], 'GUIDs Match something is wrong');
            }
        }
    }

    /**
     * @group rest
     */
    public function testRelateListOrderBy()
    {
        // Make sure there is at least one page of each of the related modules
        $account = new Account();
        $account->name = 'UNIT TEST PART 2 - ' . create_guid();
        $account->billing_address_postalcode = '90210';
        $account->save();
        $this->accounts[] = $account;
        for ($i = 0; $i < 3; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            // We need to manually tweak the date modified's so that they come out in an order we expect.
            $contact->date_modified = '2012-10-01 1' . $i . ':14:15';
            $contact->update_date_modified = false;
            $contact->save();
            $this->contacts[] = $contact;

            $contact->load_relationship('accounts');
            $contact->accounts->add([$account]);
        }

        $reply = $this->restCall('Accounts/' . $account->id . '/link/contacts?fields=id,first_name,last_name,date_modified');

        $this->assertEquals($this->contacts[2]->id, $reply['reply']['records'][0]['id'], "First record didn't match");
        $this->assertEquals($this->contacts[1]->id, $reply['reply']['records'][1]['id'], "Second record didn't match");
        $this->assertEquals($this->contacts[0]->id, $reply['reply']['records'][2]['id'], "Third record didn't match");

        $reply = $this->restCall('Accounts/' . $account->id . '/link/contacts?order_by=first_name:ASC&fields=id,first_name,last_name,date_modified');

        $this->assertEquals($this->contacts[0]->id, $reply['reply']['records'][0]['id'], "First record didn't match (2)");
        $this->assertEquals($this->contacts[1]->id, $reply['reply']['records'][1]['id'], "Second record didn't match (2)");
        $this->assertEquals($this->contacts[2]->id, $reply['reply']['records'][2]['id'], "Third record didn't match (2)");
    }
}
