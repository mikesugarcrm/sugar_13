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


class RestListFTSTest extends RestTestBase
{
    /**
     * @var mixed[]|mixed
     */
    public $files;
    public $search_engine_name;
    /**
     * @var \SugarSearchEngineInterface|mixed
     */
    public $search_engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accounts = [];
        $this->opps = [];
        $this->contacts = [];
        $this->cases = [];
        $this->bugs = [];
        $this->files = [];
        $this->search_engine_name = SugarSearchEngineFactory::getFTSEngineNameFromConfig();
        $this->search_engine = SugarSearchEngineFactory::getInstance(SugarSearchEngineFactory::getFTSEngineNameFromConfig(), [], false);
    }

    protected function tearDown(): void
    {
        $accountIds = [];
        foreach ($this->accounts as $account) {
            $this->search_engine->delete($account);
            $accountIds[] = $account->id;
        }
        $accountIds = "('" . implode("','", $accountIds) . "')";
        $oppIds = [];
        foreach ($this->opps as $opp) {
            $this->search_engine->delete($opp);
            $oppIds[] = $opp->id;
        }
        $oppIds = "('" . implode("','", $oppIds) . "')";
        $contactIds = [];
        foreach ($this->contacts as $contact) {
            $this->search_engine->delete($contact);
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

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        foreach ($this->files as $file) {
            unlink($file);
        }

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testModuleSearch()
    {
        if ($this->search_engine_name != 'Elastic') {
            $this->markTestSkipped('Marking this skipped. Elastic Search is not installed.');
        }
        // Make sure there is at least one page of accounts
        for ($i = 0; $i < 40; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . safeCount($this->accounts) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', safeCount($this->accounts));
            $account->assigned_user_id = $GLOBALS['current_user']->id;
            $account->team_id = 1;
            $account->team_set_id = 1;
            $account->save();

            $this->accounts[] = $account;
            if ($i > 33) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts', $account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }

            $this->search_engine->indexBean($account, false);
        }

        $GLOBALS['db']->commit();

        // Test searching for a lot of records
        $restReply = $this->restCall('Accounts/?q=' . rawurlencode('UNIT TEST') . '&max_num=30');

        $this->assertEquals(30, $restReply['reply']['next_offset'], 'Next offset was set incorrectly.');

        // Test Offset
        $restReply2 = $this->restCall('Accounts?offset=' . $restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'], $restReply2['reply']['next_offset'], 'Next offset was not set correctly on the second page.');

        // Test finding one record exact match, needs quotes in elastic or it returns a match for each word..thus returning all unit tests
        $restReply3 = $this->restCall('Accounts/?q=' . rawurlencode('"' . $this->accounts[17]->name . '"'));

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->accounts[17]->name, $firstRecord['name'], 'The search failed for record: ' . $this->accounts[17]->name);

        // Test Favorites
        $restReply = $this->restCall('Accounts?favorites=1&max_num=10');

        $this->assertEquals(6, safeCount($restReply['reply']['records']));
    }

    /**
     * @group rest
     */
    public function testGlobalSearch()
    {
        if ($this->search_engine_name != 'Elastic') {
            $this->markTestSkipped('Marking this skipped. Elastic Search is not installed.');
        }
        // Make sure there is at least one page of accounts
        for ($i = 0; $i < 40; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . safeCount($this->accounts) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', safeCount($this->accounts));
            $account->assigned_user_id = $GLOBALS['current_user']->id;
            $account->save();
            $this->accounts[] = $account;
            if ($i > 33) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Accounts', $account->id);
                $fav->new_with_id = true;
                $fav->module = 'Accounts';
                $fav->record_id = $account->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            $this->search_engine->indexBean($account, false);
        }

        for ($i = 0; $i < 30; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT ' . safeCount($this->contacts);
            $contact->last_name = 'TEST ' . create_guid();
            $contact->assigned_user_id = $GLOBALS['current_user']->id;
            $contact->save();
            $this->contacts[] = $contact;
            if ($i > 33) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Contacts', $contact->id);
                $fav->new_with_id = true;
                $fav->module = 'Contacts';
                $fav->record_id = $contact->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            $this->search_engine->indexBean($contact, false);
        }

        for ($i = 0; $i < 30; $i++) {
            $opportunity = new Opportunity();
            $opportunity->name = 'UNIT ' . safeCount($this->opps) . ' TEST ' . create_guid();
            $opportunity->assigned_user_id = $GLOBALS['current_user']->id;
            $opportunity->save();
            $this->opps[] = $opportunity;
            if ($i > 33) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Opportunities', $contact->id);
                $fav->new_with_id = true;
                $fav->module = 'Opportunities';
                $fav->record_id = $opportunity->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
            $this->search_engine->indexBean($opportunity, false);
        }

        $GLOBALS['db']->commit();

        // Test searching for a lot of records
        $restReply = $this->restCall('search?q=' . rawurlencode('UNIT TEST') . '&max_num=5');

        $this->assertEquals(5, $restReply['reply']['next_offset'], 'Next offset was set incorrectly.');

        $this->assertNotEmpty($restReply['reply']['records'][0]['_search']['highlighted'], 'No highlighted Property');

        // Test Offset
        $restReply2 = $this->restCall('search/?offset=' . $restReply['reply']['next_offset']);

        $this->assertNotEquals($restReply['reply']['next_offset'], $restReply2['reply']['next_offset'], 'Next offset was not set correctly on the second page.');

        // Test finding one record
        $restReply3 = $this->restCall('search/?q=' . rawurlencode('"' . $this->opps[17]->name . '"'));

        $tmp = array_keys($restReply3['reply']['records']);
        $firstRecord = $restReply3['reply']['records'][$tmp[0]];
        $this->assertEquals($this->opps[17]->name, $firstRecord['name'], 'The search failed for record: ' . $this->opps[17]->name);
        // Get a list, no searching
        $restReply = $this->restCall('search?max_num=10');

        $this->assertEquals(10, safeCount($restReply['reply']['records']));

        // my favorites
        $restReply = $this->restCall("Accounts/{$this->accounts[0]->id}/favorite", [], 'PUT');
        $this->assertEquals($restReply['reply']['id'], $this->accounts[0]->id, 'Did not return the record');
        $this->assertEquals((bool)$restReply['reply']['my_favorite'], true, 'Did not favorite');

        $restReply = $this->restCall('search?favorites=1&max_num=10');

        foreach ($restReply['reply']['records'] as $record) {
            $this->assertEquals('true', (bool)$record['my_favorite'], 'Did not return a favorite');
        }
    }
}
