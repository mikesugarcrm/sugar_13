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

class M2MRelationshipPrimaryFlagTest extends TestCase
{
    private $accounts;
    private $contact;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        $this->accounts = [];
        for ($i = 0; $i < 3; $i++) {
            $this->accounts[] = SugarTestAccountUtilities::createAccount();
        }
        $this->contact = SugarTestContactUtilities::createContact();
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        global $db;

        $db->query("DELETE FROM accounts_contacts WHERE contact_id = '" . $this->contact->id . "'");
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testAddRelationship()
    {
        $this->contact->load_relationship('accounts');

        $this->contact->accounts->add([$this->accounts[0]]);
        $res = $this->getRelated();
        $this->assertEquals(1, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't set the primary account flag on the first record. #1");


        $this->contact->accounts->add([$this->accounts[1]]);
        $res = $this->getRelated();
        $this->assertEquals(0, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't unset the primary account flag on the first record. #2");
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't set the primary account flag on the second record. #2");
    }

    public function testDeleteRelationship()
    {
        $this->contact->load_relationship('accounts');

        $this->contact->accounts->add([$this->accounts[0]]);
        $this->contact->accounts->add([$this->accounts[1]]);
        $res = $this->getRelated();
        $this->assertEquals(0, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't unsetset the primary account flag on the first record. #1");
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't set the primary account flag on the second record. #1");

        // Delete non-primary
        $this->contact->accounts->delete($this->contact->id, $this->accounts[0]->id);
        $res = $this->getRelated();
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], 'Unset the primary account flag on the second record. #2');

        // Add another entry
        $this->contact->accounts->add([$this->accounts[2]]);
        $res = $this->getRelated();
        $this->assertEquals(0, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't unset the primary account flag on the second record. #3");
        $this->assertEquals(1, $res[$this->accounts[2]->id][0]['primary_account'], "Didn't set the primary account flag on the third record. #3");

        // Delete the new entry and make sure the primary flag goes back to the second entry
        $this->contact->accounts->delete($this->contact->id, $this->accounts[2]->id);
        $res = $this->getRelated();
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], 'Unset the primary account flag on the second record. #4');
    }

    public function testSugarQueryLoad()
    {
        global $db;

        $this->contact->load_relationship('accounts');

        $this->contact->accounts->add([$this->accounts[0]]);
        $this->contact->accounts->add([$this->accounts[1]]);
        $res = $this->getRelated();
        $this->assertEquals(0, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't unset the primary account flag on the first record. #1");
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't set the primary account flag on the second record. #1");

        $q = new SugarQuery();
        $q->select(['id', 'account_name']);
        $q->from($this->contact);
        $q->where()->equals('id', $this->contact->id);
        $rows = $q->execute();

        $this->assertEquals($this->accounts[1]->name, $rows[0]['account_name'], 'Fetched the incorrect account related to this contact. #2');

        // Force switch the primary flag, no way to do this normally without re-adding everything
        $db->query("UPDATE accounts_contacts SET primary_account = 0 WHERE account_id = '" . $this->accounts[1]->id . "' AND contact_id = '" . $this->contact->id . "'");
        $db->query("UPDATE accounts_contacts SET primary_account = 1 WHERE account_id = '" . $this->accounts[0]->id . "' AND contact_id = '" . $this->contact->id . "'");
        $res = $this->getRelated();
        $this->assertEquals(1, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't set the primary account flag on the first record. #3");
        $this->assertEquals(0, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't unset the primary account flag on the second record. #3");

        $q = new SugarQuery();
        $q->select(['id', 'account_name']);
        $q->from($this->contact);
        $q->where()->equals('id', $this->contact->id);
        $rows = $q->execute();

        $this->assertEquals($this->accounts[0]->name, $rows[0]['account_name'], 'Fetched the incorrect account related to this contact. #4');
    }

    public function testOldLoad()
    {
        global $db;

        $this->contact->load_relationship('accounts');

        $this->contact->accounts->add([$this->accounts[0]]);
        $this->contact->accounts->add([$this->accounts[1]]);
        $res = $this->getRelated();
        $this->assertEquals(0, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't unset the primary account flag on the first record. #1");
        $this->assertEquals(1, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't set the primary account flag on the second record. #1");

        $this->contact->accounts->load();
        $rows = $this->contact->accounts->rows;

        $this->assertEquals($this->accounts[1]->id, $rows[$this->accounts[1]->id]['id'], 'Fetched the incorrect account related to this contact. #2');
        $this->assertEquals(1, safeCount($rows), 'Returned too many rows #2');

        // Force switch the primary flag, no way to do this normally without re-adding everything
        $db->query("UPDATE accounts_contacts SET primary_account = 0 WHERE account_id = '" . $this->accounts[1]->id . "' AND contact_id = '" . $this->contact->id . "'");
        $db->query("UPDATE accounts_contacts SET primary_account = 1 WHERE account_id = '" . $this->accounts[0]->id . "' AND contact_id = '" . $this->contact->id . "'");
        $res = $this->getRelated();
        $this->assertEquals(1, $res[$this->accounts[0]->id][0]['primary_account'], "Didn't set the primary account flag on the first record. #3");
        $this->assertEquals(0, $res[$this->accounts[1]->id][0]['primary_account'], "Didn't unset the primary account flag on the second record. #3");

        $this->contact->accounts->load();
        $rows = $this->contact->accounts->rows;

        $this->assertEquals($this->accounts[0]->id, $rows[$this->accounts[0]->id]['id'], 'Fetched the incorrect account related to this contact. #4');
        $this->assertEquals(1, safeCount($rows), 'Returned too many rows #4');
    }

    protected function getRelated($deleted = false)
    {
        global $db;

        $query = "SELECT * FROM accounts_contacts WHERE contact_id = '" . $this->contact->id . "'";
        if ($deleted == false) {
            $query .= ' AND deleted = 0';
        }

        $ret = $db->query($query);

        $results = [];

        while ($row = $db->fetchByAssoc($ret)) {
            $results[$row['account_id']][] = $row;
        }

        return $results;
    }
}
