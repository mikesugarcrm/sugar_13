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

class EmailRelationshipsTest extends TestCase
{
    /**
     * @var mixed
     */
    public $macro;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        if (!empty($GLOBALS['sugar_config']['inbound_email_case_subject_macro'])) {
            $this->macro = $GLOBALS['sugar_config']['inbound_email_case_subject_macro'];
            unset($GLOBALS['sugar_config']['inbound_email_case_subject_macro']);
        }
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestHelper::tearDown();
        if (!empty($this->macro)) {
            $GLOBALS['sugar_config']['inbound_email_case_subject_macro'] = $this->macro;
        } else {
            unset($GLOBALS['sugar_config']['inbound_email_case_subject_macro']);
        }
    }

    public function testContact()
    {
        $cont = SugarTestContactUtilities::createContact(
            '',
            ['email' => 'testcontact@test.com']
        );
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $cont->id,
                'parent_type' => 'Contacts',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 1',
            ]
        );
        // test link by email
        $email2 = SugarTestEmailUtilities::createEmail(
            '',
            ['from_addr' => 'testcontact@test.com', 'name' => 'Test email 2']
        );
        $email3 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'from_addr' => 'unit@test.com',
                'to_addrs' => 'unit@test.com,testcontact@test.com',
                'name' => 'Test email 3',
            ]
        );

        $newcont = $cont->getCleanCopy();
        $newcont->retrieve($cont->id);
        $newcont->load_relationship('archived_emails');
        $beans = $newcont->archived_emails->getBeans();
        $this->assertCount(3, $beans);
        $this->assertArrayHasKey($email1->id, $beans, 'Email 1 missing');
        $this->assertArrayHasKey($email2->id, $beans, 'Email 2 missing');
        $this->assertArrayHasKey($email3->id, $beans, 'Email 3 missing');
        $this->assertEquals($email1->name, $beans[$email1->id]->name, 'Email 1 subject wrong');
        $this->assertEquals($email2->name, $beans[$email2->id]->name, 'Email 2 subject wrong');
        $this->assertEquals($email3->name, $beans[$email3->id]->name, 'Email 3 subject wrong');
    }

    public function testAccount()
    {
        $acct = SugarTestAccountUtilities::createAccount('', ['email' => 'testacct@test.com']);
        $cont = SugarTestContactUtilities::createContact(
            '',
            ['email' => 'testcontact@test.com', 'account_id' => $acct->id]
        );
        $acct->load_relationship('contacts');
        $acct->contacts->add($cont);
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $acct->id,
                'parent_type' => 'Accounts',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 1',
            ]
        );
        // test link by email
        $email2 = SugarTestEmailUtilities::createEmail(
            '',
            ['from_addr' => 'testacct@test.com', 'name' => 'Test email 2']
        );
        // test link direct by contact
        $email3 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $cont->id,
                'parent_type' => 'Contacts',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 3',
            ]
        );
        // test link by contact email
        $email4 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'from_addr' => 'unit@test.com',
                'to_addrs' => 'unit@test.com,testcontact@test.com',
                'name' => 'Test email 4',
            ]
        );

        $newacc = $acct->getCleanCopy();
        $newacc->retrieve($acct->id);
        $newacc->load_relationship('archived_emails');
        $beans = $newacc->archived_emails->getBeans();
        $this->assertCount(4, $beans);
        $this->assertArrayHasKey($email1->id, $beans, 'Email 1 missing');
        $this->assertArrayHasKey($email2->id, $beans, 'Email 2 missing');
        $this->assertArrayHasKey($email3->id, $beans, 'Email 3 missing');
        $this->assertArrayHasKey($email4->id, $beans, 'Email 4 missing');
        $this->assertEquals($email1->name, $beans[$email1->id]->name, 'Email 1 subject wrong');
        $this->assertEquals($email2->name, $beans[$email2->id]->name, 'Email 2 subject wrong');
        $this->assertEquals($email3->name, $beans[$email3->id]->name, 'Email 3 subject wrong');
        $this->assertEquals($email4->name, $beans[$email4->id]->name, 'Email 4 subject wrong');
    }

    public function testCase()
    {
        $case = SugarTestCaseUtilities::createCase();
        $case->retrieve($case->id);
        $cont = SugarTestContactUtilities::createContact(
            '',
            ['email' => 'testcontact@test.com']
        );
        $case->load_relationship('contacts');
        $case->contacts->add($cont);
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $case->id,
                'parent_type' => 'Cases',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 1',
            ]
        );
        // test link direct by contact
        $email2 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $cont->id,
                'parent_type' => 'Contacts',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 2',
            ]
        );
        // test link direct by contact - right subject
        $email3 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $cont->id,
                'parent_type' => 'Contacts',
                'from_addr' => 'unit@test.com',
                'name' => "[CASE=>{$case->case_number}] Test email 3",
            ]
        );
        // test link by contact email
        $email4 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'from_addr' => 'unit@test.com',
                'to_addrs' => 'unit@test.com,testcontact@test.com',
                'name' => 'Test email 4',
            ]
        );
        // test link by contact email - - right subject
        $email5 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'from_addr' => 'unit@test.com',
                'cc_addrs' => 'unit@test.com,testcontact@test.com',
                'name' => "Re: [CASE=>{$case->case_number}] Test email 5",
            ]
        );

        $newcase = $case->getCleanCopy();
        $newcase->retrieve($case->id);
        $newcase->load_relationship('archived_emails');
        $newcase->emailSubjectMacro = '[CASE=>%1]';

        $beans = $newcase->archived_emails->getBeans();

        $this->assertCount(3, $beans);
        $this->assertArrayHasKey($email1->id, $beans, 'Email 1 missing');
        $this->assertArrayNotHasKey($email2->id, $beans, 'Email 2 should not be there');
        $this->assertArrayHasKey($email3->id, $beans, 'Email 3 missing');
        $this->assertArrayNotHasKey($email4->id, $beans, 'Email 4 should not be there');
        $this->assertArrayHasKey($email5->id, $beans, 'Email 5 missing');

        $this->assertEquals($email1->name, $beans[$email1->id]->name, 'Email 1 subject wrong');
        $this->assertEquals($email3->name, $beans[$email3->id]->name, 'Email 3 subject wrong');
        $this->assertEquals($email5->name, $beans[$email5->id]->name, 'Email 5 subject wrong');
    }

    public function testOpportunityLinkByContact()
    {
        $acct = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity('', $acct);
        $opp->retrieve($opp->id);
        $cont = SugarTestContactUtilities::createContact(
            '',
            ['email' => 'testcontact@test.com']
        );
        $opp->load_relationship('contacts');
        $opp->contacts->add($cont);
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $opp->id,
                'parent_type' => 'Opportunities',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 1',
            ]
        );
        // test link direct by contact
        $email2 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'parent_id' => $cont->id,
                'parent_type' => 'Contacts',
                'from_addr' => 'unit@test.com',
                'name' => 'Test email 2',
            ]
        );
        // test link by contact email
        $email3 = SugarTestEmailUtilities::createEmail(
            '',
            [
                'from_addr' => 'unit@test.com',
                'to_addrs' => 'unit@test.com,testcontact@test.com',
                'name' => 'Test email 4',
            ]
        );

        $newopp = $opp->getCleanCopy();
        $newopp->retrieve($opp->id);
        $newopp->load_relationship('archived_emails');

        $beans = $newopp->archived_emails->getBeans();

        $this->assertCount(1, $beans);
        $this->assertArrayHasKey($email1->id, $beans, 'Email 1 missing');
        $this->assertArrayNotHasKey($email2->id, $beans, 'Email 2 should not be there');
        $this->assertArrayNotHasKey($email3->id, $beans, 'Email 3 should not be there');
    }
}
