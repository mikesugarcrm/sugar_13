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


class RestPortalSecurityTest extends RestTestPortalBase
{
    // TODO XXX: refactor this test class
    // TODO XXX: Break the tests in this class up so that they only test 1 aspect
    // TODO XXX: Remove the environment setup from the tests themselves and move it into setup and teardown
    // The tests in this class are way too long an encompass way too much functionality.
    // Also consider the running time. This class in itself takes more than 25 seconds to complete on my
    // MacBook pro but only has 3 tests and 155 assertion. Try to reduce the number of text fixtures that
    // need to get created and the number of REST calls that need to be made.
    // Also considering rewriting this test so that it doesn't require HTTP requests, but runs direclty against
    // the API implementation classes.


    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group rest
     */
    public function testPortalSecurity()
    {
        // FIXME TY-1316: investigate why this test fails
        $cts = array_keys($GLOBALS['app_list_strings']['opportunity_relationship_type_dom']);
        // The first element is blank, ignore it
        array_shift($cts);
        $ctsCount = count($cts);
        // Build three accounts, we'll associate to two of them.
        for ($i = 0; $i < 3; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', ($i + 1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ($i = 0; $i < 10; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->assigned_user_id = '1';
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            if ($i > 4) {
                // The final account gets all the fun.
                $accountNum = 2;
            } else {
                $accountNum = $i % 2;
            }
            $contact->accounts->add([$this->accounts[$accountNum]]);
            $contact->save();
        }

        $this->portalGuy->load_relationship('accounts');
        $this->portalGuy->accounts->add([$this->accounts[1], $this->accounts[2]]);

        // Add some Opportunities to make sure we can't get to them.
        for ($i = 0; $i < 3; $i++) {
            $opp = new Opportunity();
            $opp->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $opp->amount = (10000 * $i) + 500;
            $opp->date_closed = sprintf('2014-12-%02d', ($i + 1));
            $opp->sales_stage = $GLOBALS['app_list_strings']['sales_stage_dom']['Qualification'];
            $opp->save();
            $this->opps[] = $opp;

            $opp->load_relationship('accounts');
            $accountNum = $i;
            $opp->accounts->add([$this->accounts[$accountNum]]);

            $contactNums = [$i];
            if ($i == 2) {
                // It's the last opportunity, give it all of the remaining contacts
                for ($ii = 2; $ii < 10; $ii++) {
                    $contactNums[] = $ii;
                }
            }

            foreach ($contactNums as $contactNum) {
                $opp->load_relationship('contacts');
                $contact_type = $cts[($contactNum % $ctsCount)];
                $opp->contacts->add([$this->contacts[$contactNum]], ['contact_role' => $contact_type]);
            }
        }

        // How about some cases?
        for ($i = 0; $i < 30; $i++) {
            $acase = new aCase();
            $acase->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $acase->work_log = 'The portal should never see this.';
            $acase->description = 'The portal can see this.';
            if ($i % 2 == 1) {
                $acase->portal_viewable = true;
            }
            $acase->save();
            $this->cases[] = $acase;

            $acase->load_relationship('accounts');
            $accountNum = $i % 3;
            $acase->_accountNum = $accountNum;
            $acase->accounts->add([$this->accounts[$accountNum]]);

            $acase->load_relationship('contacts');
            if ($accountNum == 2) {
                // It is the primary account we can see, contacts 5-10 are assigned to this
                $contactNum = 4 + $i % 6;
            } elseif ($accountNum == 1) {
                // This is the other account we can see, contact 2,4,5 are assigned to this
                $contactNums = [2, 4, 5];
                $contactNum = $contactNums[($i % 3)];
            } else {
                // Contacts 1 and 3 are assigned to this
                $contactNums = [1, 3];
                $contactNum = $contactNums[($i % 2)];
            }

            $acase->contacts->add([$this->contacts[$contactNum]]);

            // 4 out of 5 cases have bugs
            if (($i % 5) < 4) {
                $bug = new Bug();
                $bug->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
                $bug->work_log = 'The portal should never see this.';
                $bug->description = 'The portal can see this.';

                if ($i % 2 == 1 && $acase->portal_viewable == true) {
                    $bug->portal_viewable = true;
                }

                $bug->save();
                $bug->_accountNum = $accountNum;
                $this->bugs[] = $bug;

                $bug->load_relationship('cases');
                $bug->cases->add([$acase]);
            }
        }
        // Add some Notes
        $caseCount = safeCount($this->cases);
        $bugCount = safeCount($this->bugs);
        for ($i = 0; $i < 60; $i++) {
            $note = new Note();
            $note->name = 'UNIT TEST ' . ($i + 1);
            $note->description = 'This is a unit test note.';
            $note->save();
            if ($i % 3 < 2) {
                $linkBean = $this->cases[($i % $caseCount)];
                if ($i % 5 < 4) {
                    $note->portal_flag = true;
                }
            } else {
                $linkBean = $this->bugs[($i % $bugCount)];
                if ($i % 2 != 0) {
                    $note->portal_flag = true;
                }
            }
            $linkBean->load_relationship('notes');
            $linkBean->notes->add([$note]);

            $this->notes[] = $note;
        }
        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();

        $GLOBALS['db']->commit();

        // Negative test: Try and fetch a Contact you shouldn't be able to see
        $restReply = $this->restCall('Contacts/' . $this->contacts[2]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Contact that you should be able to see
        $restReply = $this->restCall('Contacts/' . $this->contacts[1]->id);
        $this->assertEquals($this->contacts[1]->id, $restReply['reply']['id']);

        // verify edit and create are no for this contact
        $this->assertEquals('no', $restReply['reply']['_acl']['edit'], 'Edit is not no');
        $this->assertEquals('no', $restReply['reply']['_acl']['create'], 'Create is not no');

        // Positive test: Should be able to change the name of our Contact
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id, json_encode(['last_name' => 'UnitTestMyGuy']), 'PUT');
        $this->assertEquals('UnitTestMyGuy', $restReply['reply']['last_name']);
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id);
        $this->assertEquals('UnitTestMyGuy', $restReply['reply']['last_name']);

        // Negative test: Should not be able to create a new Contact
        $restReply = $this->restCall('Contacts/', json_encode(['last_name' => 'UnitTestNew', 'first_name' => 'NewGuy']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);

        // Fetch contacts, make sure we can only see the correct ones.
        $restReply = $this->restCall('Contacts');

        foreach ($restReply['reply']['records'] as $record) {
            // We should be linked to accounts[1] and accounts[2]
            $this->assertNotEquals($this->accounts[0]->id, $record['account_id']);
            $foundOne = ($record['account_id'] == $this->accounts[1]->id)
                || ($record['account_id'] == $this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }

        // Negative test: Try and fetch a Account you shouldn't be able to see
        $restReply = $this->restCall('Accounts/' . $this->accounts[0]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Account that you should be able to see
        $restReply = $this->restCall('Accounts/' . $this->accounts[1]->id);
        $this->assertEquals($this->accounts[1]->id, $restReply['reply']['id']);

        // Positive test: Fetch the other Account that you should be able to see
        $restReply = $this->restCall('Accounts/' . $this->accounts[2]->id);
        $this->assertEquals($this->accounts[2]->id, $restReply['reply']['id']);

        // Negative test: Should not be able to create a new Account
        $restReply = $this->restCall('Accounts/', json_encode(['name' => 'UnitTestNew']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);

        $restReply = $this->restCall('Accounts');

        foreach ($restReply['reply']['records'] as $record) {
            // We should be linked to accounts[1] and accounts[2]
            $this->assertNotEquals($this->accounts[0]->id, $record['id']);
            $foundOne = ($record['id'] == $this->accounts[1]->id)
                || ($record['id'] == $this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }


        $restReply = $this->restCall('Accounts/' . $this->accounts[2]->id . '/link/cases');

        foreach ($restReply['reply']['records'] as $record) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals(true, $record['portal_viewable']);
        }

        $restReply = $this->restCall('Accounts/' . $this->accounts[1]->id . '/link/bugs');

        foreach ($restReply['reply']['records'] as $record) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals(true, $record['portal_viewable']);
        }

        // Negative test: We should not be able to fetch an Opportunity
        $restReply = $this->restCall('Opportunities/' . $this->opps[1]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Negative test: We should not be able to list opportunities
        $restReply = $this->restCall('Opportunities/');
        $this->assertEquals(-1, $restReply['reply']['next_offset']);

        // Negative test: Should not be able to create a new Opportunity
        $restReply = $this->restCall('Opportunities/', json_encode(['name' => 'UnitTestNew', 'account_id' => $this->accounts[1]->id, 'expected_close_date' => '2012-10-11 12:00:00']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);


        // Negative test: Try and fetch a Case you shouldn't be able to see
        $restReply = $this->restCall('Cases/' . $this->cases[0]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Negative test: Fetch a Case that is related to an account you can see, but is not portal visible
        $restReply = $this->restCall('Cases/' . $this->cases[2]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Case assigned to the other account that you should be able to see
        $restReply = $this->restCall('Cases/' . $this->cases[1]->id);
        $this->assertEquals($this->cases[1]->id, $restReply['reply']['id']);

        // Positive test: Should be able to create a new Case
        $restReply = $this->restCall('Cases/', json_encode(['name' => 'UnitTestNew', 'account_id' => $this->accounts[1]->id, 'portal_viewable' => 1]), 'POST');
        $this->assertNotEmpty($restReply['reply']['id']);
        $createdCase = BeanFactory::getBean('Cases', $restReply['reply']['id']);
        $this->cases[] = $createdCase;

        // Positive test: Should be able to fetch this new bean
        $restReply = $this->restCall('Cases/' . $createdCase->id);
        $this->assertEquals($restReply['reply']['id'], $createdCase->id);

        $restReply = $this->restCall('Cases');

        foreach ($restReply['reply']['records'] as $record) {
            // Cases should be linked to accounts[1] or accounts[2]
            $this->assertEquals(true, $record['portal_viewable']);
            $foundOne = ($record['account_id'] == $this->accounts[1]->id)
                || ($record['account_id'] == $this->accounts[2]->id);
            $this->assertTrue($foundOne);
        }

        $restReply = $this->restCall('Cases/' . $this->cases[1]->id . '/link/bugs');

        foreach ($restReply['reply']['records'] as $record) {
            // We should only get cases that have the portal_viewable flag set to true
            $this->assertEquals(true, $record['portal_viewable']);
        }

        // Negative test: Try and fetch a Bug you shouldn't be able to see
        $restReply = $this->restCall('Bugs/' . $this->bugs[0]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Bug that is related to an account you can see, but is not portal visible
        $restReply = $this->restCall('Bugs/' . $this->bugs[5]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Bug assigned to the other account that you should be able to see
        $restReply = $this->restCall('Bugs/' . $this->bugs[1]->id);
        $this->assertEquals($this->bugs[1]->id, $restReply['reply']['id']);

        // Positive test: Should be able to create a new Bug, as long as it is related to a case.
        $restReply = $this->restCall('Cases/' . $this->cases[1]->id . '/link/bugs/', json_encode(['name' => 'UnitTestNew', 'portal_viewable' => 1]), 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdBug = BeanFactory::getBean('Bugs', $restReply['reply']['related_record']['id']);
        $this->bugs[] = $createdBug;

        // Positive test: Should be able to fetch this new bean
        $restReply = $this->restCall('Bugs/' . $createdBug->id);
        $this->assertEquals($restReply['reply']['id'], $createdBug->id);

        $restReply = $this->restCall('Bugs');

        foreach ($restReply['reply']['records'] as $record) {
            $this->assertEquals(true, $record['portal_viewable']);
        }

        // Note debugging, to figure out which notes have what properties
        /*
        foreach ( $this->notes as $i => $note ) {
            if ( $note->parent_type == 'Cases' ) {
                $parentNum = $i%$caseCount;
                $parent = $this->cases[$parentNum];
            } else {
                $parentNum = $i%$bugCount;
                $parent = $this->bugs[$parentNum];
            }
            printf("%3d = %1d %10s %1d %3d %2d\n",
                   $i,    $note->portal_flag, $note->parent_type, $parent->portal_viewable, $parentNum, $parent->_accountNum );
        }
        */

        // Note 2: no portal_flag, related to bug #2, bug not portal visible, related to account #2
        $restReply = $this->restCall('Notes/' . $this->notes[2]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Note 5: portal_flag, related to bug #5, bug not portal visible, related to account #0
        $restReply = $this->restCall('Notes/' . $this->notes[5]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Note 35: portal_flag, related to bug #11, bug portal visible, related to account #1
        $restReply = $this->restCall('Notes/' . $this->notes[35]->id);
        $this->assertEquals($this->notes[35]->id, $restReply['reply']['id']);

        // Note 17: portal_flag, related to bug #17, bug portal visible, related to account #0
        $restReply = $this->restCall('Notes/' . $this->notes[17]->id);
        $this->assertEquals($this->notes[17]->id, $restReply['reply']['id']);

        // Note 14: no portal_flag, related to bug #14, bug portal visible, related to account #2
        $restReply = $this->restCall('Notes/' . $this->notes[14]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Note 15: portal_flag, related to case #15, case portal visible, related to account #0
        $restReply = $this->restCall('Notes/' . $this->notes[15]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Note 13: portal_flag, related to case #13, case portal visible, related to account #1
        $restReply = $this->restCall('Notes/' . $this->notes[13]->id);
        $this->assertEquals($this->notes[13]->id, $restReply['reply']['id']);

        // Make sure we can find Note #13 through the relationship API
        $restReply = $this->restCall('Cases/' . $this->cases[13]->id . '/link/notes/' . $this->notes[13]->id);
        $this->assertEquals($this->notes[13]->id, $restReply['reply']['id']);

        // Make sure we can find Note #13 through the relationship list API
        $restReply = $this->restCall('Cases/' . $this->cases[13]->id . '/link/notes/');
        $foundIt = false;
        foreach ($restReply['reply']['records'] as $noteRecord) {
            if ($noteRecord['id'] == $this->notes[13]->id) {
                $foundIt = true;
            }
            $this->assertEquals(true, $noteRecord['portal_flag']);
        }
        $this->assertTrue($foundIt);

        // Note 22: portal_flag, related to case #22, case not portal visible, related to account #1
        $restReply = $this->restCall('Notes/' . $this->notes[22]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Note 49: no portal_flag, related to case #19, case portal visible, related to account #1
        $restReply = $this->restCall('Notes/' . $this->notes[49]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Should be able to create a new Note, as long as it is related to a case or a bug.
        $restReply = $this->restCall('Cases/' . $this->cases[25]->id . '/link/notes/', json_encode(['name' => 'UnitTestNew', 'portal_flag' => 1]), 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdNote = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->notes[] = $createdNote;

        $restReply = $this->restCall('Bugs/' . $this->bugs[20]->id . '/link/notes/', json_encode(['name' => 'UnitTestNew', 'portal_flag' => 1]), 'POST');
        $this->assertNotEmpty($restReply['reply']['related_record']['id']);
        $createdNote = BeanFactory::getBean('Notes', $restReply['reply']['related_record']['id']);
        $this->notes[] = $createdNote;
    }

    /**
     * @group rest
     */
    public function testPortalSecurityChangeAccount()
    {
        // Build three accounts, we'll associate to two of them.
        for ($i = 0; $i < 3; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', ($i + 1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ($i = 0; $i < 10; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            if ($i > 4) {
                // The final account gets all the fun.
                $accountNum = 2;
            } else {
                $accountNum = $i % 2;
            }
            $contact->accounts->add([$this->accounts[$accountNum]]);
            $contact->save();
        }

        $this->portalGuy->load_relationship('accounts');
        $this->portalGuy->accounts->add([$this->accounts[1], $this->accounts[2]]);

        $GLOBALS['db']->commit();

        // Negative test: Try and fetch a Contact you shouldn't be able to see
        $restReply = $this->restCall('Contacts/' . $this->contacts[2]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Fetch a Contact that you should be able to see
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id);
        $this->assertEquals($this->portalGuy->id, $restReply['reply']['id']);

        // Positive test: Fetch another Contact that you should be able to see
        $restReply = $this->restCall('Contacts/' . $this->contacts[1]->id);
        $this->assertEquals($this->contacts[1]->id, $restReply['reply']['id']);

        // Positive test: Should be able to change the name of our Contact
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id, json_encode(['last_name' => 'UnitTestMyGuy']), 'PUT');
        $this->assertEquals('UnitTestMyGuy', $restReply['reply']['last_name']);
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id);
        $this->assertEquals('UnitTestMyGuy', $restReply['reply']['last_name']);

        // Positive test: Make sure we can see both accounts for now
        $restReply = $this->restCall('Accounts/');
        foreach ($restReply['reply']['records'] as $record) {
            $this->assertNotEquals($this->accounts[0]->id, $record['id']);
        }

        // Positive test: Make sure we can access this account first
        $restReply = $this->restCall('Accounts/' . $this->accounts[1]->id);
        $this->assertEquals($this->accounts[1]->id, $restReply['reply']['id']);

        // Remove the contact from one of the accounts and make sure that
        // it doesn't come up on the list
        $this->portalGuy->accounts->delete($this->portalGuy->id, $this->accounts[1]);
        $GLOBALS['db']->commit();
        // Positive test: Make sure we can only see one account now
        $restReply = $this->restCall('Accounts/');
        foreach ($restReply['reply']['records'] as $record) {
            $this->assertEquals($this->accounts[2]->id, $record['id']);
        }

        // Negative test: Try and fetch the account we unlinked
        $restReply = $this->restCall('Accounts/' . $this->accounts[1]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);
    }

    /**
     * @group rest
     */
    public function testPortalSecurityNoAccount()
    {
        // Build three accounts, we'll associate to two of them.
        for ($i = 0; $i < 3; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', ($i + 1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ($i = 0; $i < 3; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $this->contacts[$i] = $contact;
            $contact->save();
        }

        $this->portalGuy->load_relationship('accounts');

        // How about some cases?
        for ($i = 0; $i < 3; $i++) {
            $acase = new aCase();
            $acase->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $acase->work_log = 'The portal should never see this.';
            $acase->description = 'The portal can see this.';
            if ($i != 2) {
                $acase->portal_viewable = true;
            }
            $acase->save();
            $this->cases[] = $acase;

            $acase->load_relationship('accounts');
            $accountNum = $i;
            $acase->_accountNum = $accountNum;
            $acase->accounts->add([$this->accounts[$accountNum]]);

            // All cases have bugs
            $bug = new Bug();
            $bug->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $bug->work_log = 'The portal should never see this.';
            $bug->description = 'The portal can see this.';

            if ($acase->portal_viewable == true) {
                $bug->portal_viewable = true;
            }

            $bug->save();
            $bug->_accountNum = $accountNum;
            $this->bugs[] = $bug;

            $bug->load_relationship('cases');
            $bug->cases->add([$acase]);

            $bug->load_relationship('notes');
            // All Bugs have two notes, one portal visible, one not
            for ($ii = 0; $ii < 2; $ii++) {
                $note = new Note();
                $note->name = 'UNIT TEST ' . ($i + 1) . ' - ' . ($ii + 1);
                $note->description = 'SOME UNIT TEST STUFF';
                if ($ii == 1 && $acase->portal_viewable == true && $bug->portal_viewable == true) {
                    $note->portal_flag = true;
                }
                $note->save();
                $this->notes[] = $note;

                $bug->notes->add([$note]);
            }
        }
        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();


        // Negative test: Try and fetch an account we never linked
        $restReply = $this->restCall('Accounts/' . $this->accounts[1]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Negative test: Try and fetch a contact we never linked
        $restReply = $this->restCall('Contacts/' . $this->contacts[1]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Make sure we can access this contact
        $restReply = $this->restCall('Contacts/' . $this->portalGuy->id);
        $this->assertEquals($this->portalGuy->id, $restReply['reply']['id']);

        // Positive test: Make sure we can only see this contact in the contact list
        $restReply = $this->restCall('Contacts/');
        foreach ($restReply['reply']['records'] as $record) {
            $this->assertEquals($this->portalGuy->id, $record['id']);
        }

        // Negative test: Should not be able to create a new Contact
        $restReply = $this->restCall('Contacts/', json_encode(['last_name' => 'UnitTestNew', 'first_name' => 'NewGuy']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);

        // Negative test: Should not be able to create a new Case
        $restReply = $this->restCall('Cases/', json_encode(['name' => 'UnitTestNew', 'description' => 'UNIT TEST SHOULD FAIL']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);

        // Fetch contacts, make sure we can only see the correct one.
        $restReply = $this->restCall('Contacts');

        foreach ($restReply['reply']['records'] as $record) {
            $this->assertEquals($this->portalGuy->id, $record['id']);
        }

        // Positive test: Make sure we can fetch a bug that is visible
        $restReply = $this->restCall('Bugs/' . $this->bugs[1]->id);
        $this->assertEquals($this->bugs[1]->id, $restReply['reply']['id']);

        // Negative test: Try and fetch a non-visible bug
        $restReply = $this->restCall('Bugs/' . $this->bugs[2]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Make sure we can fetch a note that is related to a visible bug
        $restReply = $this->restCall('Notes/' . $this->notes[1]->id);
        $this->assertEquals($this->notes[1]->id, $restReply['reply']['id']);

        // Negative test: Try and fetch a non-visible note
        $restReply = $this->restCall('Notes/' . $this->notes[0]->id);
        $this->assertEquals('not_found', $restReply['reply']['error']);

        // Positive test: Make sure we can create a new note
        $restReply = $this->restCall('Notes/', json_encode(['name' => 'UNIT TEST POSTED', 'description' => 'This was posted by a unit test.', 'parent_type' => 'Bugs', 'parent_id' => $this->bugs[1]->id, 'portal_flag' => true]), 'POST');
        $this->assertTrue(!empty($restReply['reply']['id']));

        // Add it to the list of beans so we can properly delete it.
        $this->notes[] = BeanFactory::getBean('Notes', $restReply['reply']['id']);

        // Negative test: Should not be able to create a new Opportunity
        $restReply = $this->restCall('Opportunities/', json_encode(['name' => 'UnitTestNew', 'account_id' => $this->accounts[1]->id, 'expected_close_date' => '2012-10-11 12:00:00']), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);

        // Negative test: Should not be able to create a new Case
        $restReply = $this->restCall('Cases/', json_encode(['name' => 'UnitTestNew', 'account_id' => '', 'portal_viewable' => 1]), 'POST');
        $this->assertEquals('not_authorized', $restReply['reply']['error']);
    }
}
