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

/**
 * @coversDefaultClass EmailRecipientsService
 * @group functional
 * @group email
 */
class EmailRecipientsServiceTest extends TestCase
{
    private $emailRecipientsService;
    private $salt;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->emailRecipientsService = new EmailRecipientsService();
        $this->salt = create_guid();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestHelper::tearDown();
    }

    public function testFindCount_SearchAllModulesForTerm_ReturnsTwo()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_sam_";
        $expected = 2;
        $actual = $this->emailRecipientsService->findCount($term);
        $this->assertEquals($expected, $actual, "Should have found {$expected} recipients who matched {$term}.");
    }

    public function testFindCountUsingFullNameOneModule()
    {
        $this->createRecipientsAcrossModules();
        $term = 'John Doe';
        $module = 'contacts';
        $expected = 1;
        $actual = $this->emailRecipientsService->findCount($term, $module);
        $this->assertEquals($expected, $actual, "Should have found {$expected} recipients who matched {$term}.");
    }

    public function testFindCountUsingFullNameAllModules()
    {
        $this->createRecipientsAcrossModules();
        $term = 'John Doe';
        $expected = 2;
        $actual = $this->emailRecipientsService->findCount($term);
        $this->assertEquals($expected, $actual, "Should have found {$expected} recipients who matched {$term}.");
    }

    public function testFindUsingFullNameOneModule()
    {
        $this->createRecipientsAcrossModules();
        $term = 'John Doe';
        $module = 'contacts';
        $results = $this->emailRecipientsService->find($term, $module);

        // Should return the correct number of results
        $expectedCount = 1;
        $actualCount = safeCount($results);
        $this->assertEquals($expectedCount, $actualCount, "Should have found {$expectedCount} recipients who matched {$term}.");

        // Should return the correct recipients in the results
        $expectedName = 'John Doe';
        foreach ($results as $result) {
            $actualName = $result['name'];
            $this->assertEquals($expectedName, $actualName, 'Recipient with name ' . $actualName . ' returned, expected ' . $expectedName);
        }
    }

    public function testFindUsingFullNameAllModules()
    {
        $this->createRecipientsAcrossModules();
        $term = 'John Doe';
        $results = $this->emailRecipientsService->find($term);

        // Should return the correct number of results
        $expectedCount = 2;
        $actualCount = safeCount($results);
        $this->assertEquals($expectedCount, $actualCount, "Should have found {$expectedCount} recipients who matched {$term}.");

        // Should return the correct recipients in the results
        $expectedName = 'John Doe';
        foreach ($results as $result) {
            $actualName = $result['name'];
            $this->assertEquals($expectedName, $actualName, 'Recipient with name ' . $actualName . ' returned, expected ' . $expectedName);
        }
    }

    public function testFindCount_SearchContactsForTerm_ReturnsOne()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_jiminy_";
        $module = 'contacts';
        $expected = 1;
        $actual = $this->emailRecipientsService->findCount($term, $module);
        $this->assertEquals($expected, $actual, "Should have found {$expected} {$module} who matched {$term}.");
    }

    public function testFind_SearchAllModulesForTerm_ReturnsTwo()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_sam_";
        $expected = 2;
        $recepients = $this->emailRecipientsService->find($term);
        $actual = safeCount($recepients);
        $this->assertEquals($expected, $actual, "Should have found {$expected} recipients who matched {$term}.");
    }

    public function testFind_SearchContactsForTerm_ReturnsOne()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_jiminy_";
        $module = 'contacts';
        $expected = 1;
        $beans = $this->emailRecipientsService->find($term, $module);
        $actual = safeCount($beans);
        $this->assertEquals($expected, $actual, "Should have found {$expected} {$module} who matched {$term}.");
    }

    public function testFind_SearchContactsForTermWithLimit_ReturnsOne()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_sam_";
        $module = 'contacts';
        $orderBy = [];
        $expected = 1;
        $beans = $this->emailRecipientsService->find($term, $module, $orderBy, $expected);
        $actual = safeCount($beans);
        $this->assertEquals($expected, $actual, "Should have found {$expected} {$module} who matched {$term}.");
    }

    public function testFind_SearchContactsForTerm_ReturnsNameCorrectly()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_john";
        $module = 'contacts';
        $expected = 'John Doe';
        $recipients = $this->emailRecipientsService->find($term, $module);
        $actual = $recipients[0]['name'];
        $this->assertEquals($expected, $actual, "Should have returned name of '{$expected}' instead of '{$actual}'.");

        $this->assertSame('John', $recipients[0]['first_name'], 'first_name should be John');
        $this->assertSame('Doe', $recipients[0]['last_name'], 'last_name should be Doe');
    }

    public function testFind_SearchAccountsForTerm_ReturnsNameCorrectly()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_this";
        $module = 'accounts';
        $expected = 'This Account';
        $recipients = $this->emailRecipientsService->find($term, $module);
        $actual = $recipients[0]['name'];
        $this->assertEquals($expected, $actual, "Should have returned name of '{$expected}' instead of '{$actual}'.");

        $this->assertEmpty($recipients[0]['first_name'], 'first_name should be empty');
        $this->assertSame('This Account', $recipients[0]['last_name'], 'last_name should be This Account');
    }

    public function testFind_SearchAccountsForTermAndOrderByEmailAsc_ReturnsSortedMatchingAccounts()
    {
        $this->createRecipientsAcrossModules();
        $term = "{$this->salt}_";
        $module = 'accounts';
        $orderBy = ['email' => 'ASC'];
        $limit = 3;
        $recipients = $this->emailRecipientsService->find($term, $module, $orderBy, $limit);

        $expected = "{$this->salt}_my_account@yahoo.com";
        $actual = $recipients[0]['email'];
        $this->assertEquals(
            $expected,
            $actual,
            "Should have sorted the recipients such that the recipient with the email address '{$expected}' was first."
        );

        $expected = "{$this->salt}_that_account@yahoo.com";
        $actual = $recipients[1]['email'];
        $this->assertEquals(
            $expected,
            $actual,
            "Should have sorted the recipients such that the recipient with the email address '{$expected}' was second."
        );

        $expected = "{$this->salt}_this_account@yahoo.com";
        $actual = $recipients[2]['email'];
        $this->assertEquals(
            $expected,
            $actual,
            "Should have sorted the recipients such that the recipient with the email address '{$expected}' was third."
        );
    }

    /**
     * @covers ::find
     */
    public function testFind_SearchContactsForTerm_EmailAddressIsOptedIn()
    {
        $this->createRecipientsAcrossModules();

        $emailAddress = "{$this->salt}_jiminy_crickett@gmail.com";
        $ea = BeanFactory::newBean('EmailAddresses');
        $ea->AddUpdateEmailAddress($emailAddress, false, false);

        $term = "{$this->salt}_jiminy_";
        $module = 'contacts';
        $recipients = $this->emailRecipientsService->find($term, $module);

        $this->assertFalse($recipients[0]['opt_out']);
    }

    /**
     * @covers ::find
     */
    public function testFind_SearchContactsForTerm_EmailAddressIsOptedOut()
    {
        $this->createRecipientsAcrossModules();

        $emailAddress = "{$this->salt}_jiminy_crickett@gmail.com";
        $ea = BeanFactory::newBean('EmailAddresses');
        $ea->AddUpdateEmailAddress($emailAddress, false, true);

        $term = "{$this->salt}_jiminy_";
        $module = 'contacts';
        $recipients = $this->emailRecipientsService->find($term, $module);

        $this->assertTrue($recipients[0]['opt_out']);
    }

    /**
     * @covers ::find
     */
    public function testFind_SearchContactsForTerm_EmailAddressIdValueIsReturned()
    {
        $this->createRecipientsAcrossModules();

        $term = "{$this->salt}_jiminy_";
        $module = 'contacts';
        $recipients = $this->emailRecipientsService->find($term, $module);

        $this->assertNotEmpty($recipients[0]['email_address_id']);
    }

    public function testLookup_SetAllProperties_RecipientResolved()
    {
        $contact = SugarTestContactUtilities::createContact();

        $input = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $contact->email1,
            'name' => $contact->name,
        ];
        $expected = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $contact->email1,
            'name' => $contact->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);

        $this->assertEquals($expected, $actual, 'Expected Recipient to be Resolved From ID and Module');
    }

    public function testLookup_SetIdAndModule_RecipientResolved()
    {
        $contact = SugarTestContactUtilities::createContact();

        $input = ['module' => 'Contacts', 'id' => $contact->id, 'email' => '', 'name' => ''];
        $expected = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $contact->email1,
            'name' => $contact->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);

        $this->assertEquals($expected, $actual, 'Expected Recipient to be Resolved From ID and Module');
    }

    public function testLookup_SetEmailAndModuleOnly_RecipientResolvesToModuleExpected()
    {
        $email = 'unit_test_' . create_guid() . '@yahoo.com';
        $contact = SugarTestContactUtilities::createContact();
        $contact->email1 = $email;
        $contact->save();

        $lead = SugarTestLeadUtilities::createLead();
        $lead->email1 = $email;
        $lead->save();

        $input = ['module' => 'Leads', 'id' => '', 'email' => $email, 'name' => ''];
        $expected = [
            'module' => 'Leads',
            'id' => $lead->id,
            'email' => $lead->email1,
            'name' => $lead->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Expected Lead Recipient to be Resolved From Email Address');

        $input = ['module' => 'Contacts', 'id' => '', 'email' => $email, 'name' => ''];
        $expected = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $contact->email1,
            'name' => $contact->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Expected Contact Recipient to be Resolved From Email Address');
    }

    public function testLookup_SetMultiplePotentialMatchesOnEmail_UnpredictableMatchingRecipientResolvedToFirstMatchFound()
    {
        $email = 'unit_test_' . create_guid() . '@yahoo.com';

        $contact = SugarTestContactUtilities::createContact();
        $contact->email1 = $email;
        $contact->save();

        $lead = SugarTestLeadUtilities::createLead();
        $lead->email1 = $email;
        $lead->save();

        $input = ['module' => '', 'id' => '', 'email' => $email, 'name' => ''];
        $expected1 = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $contact->email1,
            'name' => $contact->name,
            'resolved' => true,
        ];
        $expected2 = [
            'module' => 'Leads',
            'id' => $lead->id,
            'email' => $lead->email1,
            'name' => $lead->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);

        $this->assertTrue(
            ($expected1 == $actual) || ($expected2 == $actual),
            'Unexpected One of Multiple Recipients to Match'
        );
    }

    public function testLookup_SetInvalidContactId_RecipientNotFoundAndBadIdReturned()
    {
        $invalid_contact_id = create_guid();

        $input = ['module' => 'Contacts', 'id' => $invalid_contact_id, 'email' => '', 'name' => ''];
        $expected = [
            'module' => 'Contacts',
            'id' => $invalid_contact_id,
            'email' => '',
            'name' => '',
            'resolved' => false,
        ];
        $actual = $this->emailRecipientsService->lookup($input);

        $this->assertEquals($expected, $actual, 'Expected Recipient not to Resolve - Module Required with an ID');
    }

    public function testLookup_SetContactIdAndModuleAndUnmatchingName_RecipientResolvedAndInputPreserved()
    {
        $name = 'George Jetson';
        $email = 'unit_test_' . create_guid() . '@yahoo.com';

        $contact = SugarTestContactUtilities::createContact();

        $input = ['module' => 'Contacts', 'id' => $contact->id, 'email' => $email, 'name' => $name];
        $expected = [
            'module' => 'Contacts',
            'id' => $contact->id,
            'email' => $email,
            'name' => $name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Unexpected Recipient to Resolve and Supplied Name not to be Replaced');
    }

    public function testLookup_SetContactIdAndEmail_IdAndEmailFound_RecipientResolved()
    {
        $email = 'unit_test_' . create_guid() . '@yahoo.com';
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->email1 = $email;
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->email1 = $email;
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->email1 = $email;
        $contact3->save();

        $id = $contact2->id;

        $input = ['module' => '', 'id' => $id, 'email' => $contact2->email1, 'name' => ''];
        $expected = [
            'module' => 'Contacts',
            'id' => $id,
            'email' => $contact2->email1,
            'name' => $contact2->name,
            'resolved' => true,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Expected Recipient to Resolve to Matching ID and Email');
    }

    public function testLookup_SetEmailAndIDOnly_EmailFoundButNotID_RecipientNotResolved()
    {
        $email = 'unit_test_' . create_guid() . '@yahoo.com';
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->email1 = $email;
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->email1 = $email;
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->email1 = $email;
        $contact3->save();

        $id = $contact2->id . 'abcdefg';

        $input = ['module' => '', 'id' => $id, 'email' => $contact2->email1, 'name' => ''];
        $expected = [
            'module' => '',
            'id' => $id,
            'email' => $contact2->email1,
            'name' => '',
            'resolved' => false,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Expected Recipient Not to Resolve with unmatching ID');
    }

    public function testLookup_IDProvided_NoModule_EmailNotFound_IgnoreIDButReturnIt_Unresolved()
    {
        $email = 'unit_test_' . create_guid() . '@yahoo.com';
        $name = 'George Jetson';

        $input = ['module' => '', 'id' => '123', 'email' => $email, 'name' => $name];
        $expected = [
            'module' => '',
            'id' => '123',
            'email' => $email,
            'name' => $name,
            'resolved' => false,
        ];
        $actual = $this->emailRecipientsService->lookup($input);
        $this->assertEquals($expected, $actual, 'Expected Supplied Data to be Returned on Unresolved ID');
    }

    protected function createRecipientsAcrossModules()
    {
        $recipients = [
            [
                'type' => 'accounts',
                'name' => 'This Account',
                'email' => "{$this->salt}_this_account@yahoo.com",
            ],
            [
                'type' => 'accounts',
                'name' => 'My Account',
                'email' => "{$this->salt}_my_account@yahoo.com",
            ],
            [
                'type' => 'accounts',
                'name' => 'That Account',
                'email' => "{$this->salt}_that_account@yahoo.com",
            ],
            [
                'type' => 'contacts',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => "{$this->salt}_john_doe@yahoo.com",
            ],
            [
                'type' => 'contacts',
                'first_name' => 'Sam',
                'last_name' => 'The Sham',
                'email' => "{$this->salt}_sam_the_sham@yahoo.com",
            ],
            [
                'type' => 'contacts',
                'first_name' => 'Jiminy',
                'last_name' => 'Crickett',
                'email' => "{$this->salt}_jiminy_crickett@gmail.com",
            ],
            [
                'type' => 'leads',
                'first_name' => 'Davey',
                'last_name' => 'Crockett',
                'email' => "{$this->salt}_davey_crockett@alamo.com",
            ],
            [
                'type' => 'leads',
                'first_name' => 'Jim',
                'last_name' => 'Bowie',
                'email' => "{$this->salt}_jim_bowie@alamo.com",
            ],
            [
                'type' => 'leads',
                'first_name' => 'Sam',
                'last_name' => 'Houston',
                'email' => "{$this->salt}_sam_houston@alamo.com",
            ],
            [
                'type' => 'leads',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => "{$this->salt}_john_doe_lead@yahoo.com",
            ],
        ];

        foreach ($recipients as $recipient) {
            switch ($recipient['type']) {
                case 'accounts':
                    SugarTestAccountUtilities::createAccount(null, $recipient);
                    break;
                case 'contacts':
                    SugarTestContactUtilities::createContact(null, $recipient);
                    break;
                case 'leads':
                    SugarTestLeadUtilities::createLead(null, $recipient);
                    break;
                default:
                    break;
            }
        }
    }
}
