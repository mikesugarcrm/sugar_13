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

class LinkTest extends TestCase
{
    protected $createdBeans = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $GLOBALS['current_user']->setPreference('timezone', 'America/Los_Angeles');
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y');
        $GLOBALS['current_user']->setPreference('timef', 'h.iA');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdBeans as $bean) {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestNoteUtilities::removeAllCreatedNotes();
    }


    /**
     * Create a new account and bug, then link them.
     * @return void
     */
    public function testManytoMany()
    {
        global $beanList, $beanFiles;
        require 'include/modules.php';
        $module = 'Accounts';

        $account = BeanFactory::newBean($module);
        $account->name = 'LinkTestAccount';
        $account->save();
        $this->createdBeans[] = $account;

        $bug = BeanFactory::newBean('Bugs');
        $bug->name = 'LinkTestBug';
        $bug->save();
        $this->createdBeans[] = $bug;

        $accountsLink = new Link2('bugs', $account);
        $accountsLink->add($bug);

        //Create a new link to refresh from the database
        $accountsLink2 = new Link2('bugs', $account);
        $related = $accountsLink2->getBeans(null);
        $this->assertNotEmpty($related);

        $this->assertNotEmpty($related[$bug->id]);

        //Now test deleting the link
        $accountsLink2->delete($account, $bug);

        //Create a new link to refresh from the database
        $accountsLink3 = new Link2('bugs', $account);

        $related = $accountsLink3->getBeans(null);
        $this->assertEmpty($related);
    }

    public function testOnetoMany()
    {
        //Test the accounts_leads relationship
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Link 1->M Test Account';
        $account->save();
        $this->createdBeans[] = $account;

        $account2 = BeanFactory::newBean('Accounts');
        $account2->name = 'Link 1->M Test Account2';
        $account2->save();
        $this->createdBeans[] = $account2;

        $lead = SugarTestLeadUtilities::createLead(null, [
            'last_name' => 'Link 1->M Test Lead',
        ]);

        //Start by adding it from the Account side.
        $this->assertTrue($account->load_relationship('leads'));
        $this->assertInstanceOf('Link2', $account->leads);
        $this->assertTrue($account->leads->loadedSuccesfully());
        $account->leads->add($lead);

        $related = $account->leads->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$lead->id]);


        //Test loading the link from the Lead side.
        $this->assertTrue($lead->load_relationship('accounts'));
        $this->assertInstanceOf('Link2', $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());

        $related = $lead->accounts->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$account->id]);


        //Test overriding the one side
        $this->assertTrue($account2->load_relationship('leads'));
        $this->assertInstanceOf('Link2', $account2->leads);
        $this->assertTrue($account2->leads->loadedSuccesfully());
        $account2->leads->add($lead);
        $related = $account2->leads->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$lead->id]);

        //Verify only one on the Lead side.
        $this->assertTrue($lead->load_relationship('accounts'));
        $this->assertInstanceOf('Link2', $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());

        $related = $lead->accounts->getBeans();
        $this->assertNotEmpty($related);
        $this->assertTrue(empty($related[$account->id]));
        $this->assertNotEmpty($related[$account2->id]);
    }

    public function testParentRelationships()
    {
        $lead = SugarTestLeadUtilities::createLead(null, [
            'last_name' => 'Parent Lead',
        ]);

        $note1 = SugarTestNoteUtilities::createNote(null, [
            'name' => 'Lead Note 1',
        ]);
        $note2 = SugarTestNoteUtilities::createNote(null, [
            'name' => 'Lead Note 2',
        ]);

        //Test saving from the RHS
        $note1->load_relationship('leads');
        $note1->leads->add($lead);

        $this->assertEquals($note1->parent_id, $lead->id);
        $this->assertEquals($note1->parent_type, 'Leads');

        //Test saving from the LHS
        $lead->load_relationship('notes');
        $lead->notes->add($note2);

        $this->assertEquals($note2->parent_id, $lead->id);
        $this->assertEquals($note2->parent_type, 'Leads');

        $result = $note1->retrieve_parent_fields([
            'Leads' => [
                [
                    'child_id' => $note1->id,
                    'parent_id' => $note1->parent_id,
                ],
            ],
        ]);
        $this->assertEquals(array_keys($result)[0], $note1->id);
        $this->assertEquals($result[$note1->id]['parent_name'], $lead->name);
    }

    public function testGetBeansWithParameters()
    {
        $module = 'Accounts';
        require 'include/modules.php';

        $account = BeanFactory::newBean($module);
        $account->name = 'LinkTestAccount';
        $account->save();
        $this->createdBeans[] = $account;

        $bug = BeanFactory::newBean('Bugs');
        $bug->name = 'LinkTestBug';
        $bug->save();
        $this->createdBeans[] = $bug;

        $bug2 = BeanFactory::newBean('Bugs');
        $bug2->name = 'LinkTestBug1';
        $bug2->save();
        $this->createdBeans[] = $bug2;

        $bug3 = BeanFactory::newBean('Bugs');
        $bug3->name = 'LinkTestBug3';
        $bug3->source = 'external';
        $bug3->save();
        $this->createdBeans[] = $bug3;

        $accountsLink = new Link2('bugs', $account);
        $accountsLink->add($bug);
        $accountsLink->add($bug2);
        $accountsLink->add($bug3);

        //First test the generic result
        $result = $accountsLink->getBeans();
        $expected = [
            $bug->id => $bug,
            $bug2->id => $bug2,
            $bug3->id => $bug3,
        ];
        ksort($result);
        ksort($expected);

        $this->assertEquals(array_keys($expected), array_keys($result));
        foreach ($expected as $key => $val) {
            $this->assertEquals($expected[$key]->id, $result[$key]->id, "Wrong data in key $key");
        }

        //Test a limited set
        $result = $accountsLink->getBeans(['limit' => 2]);
        $this->assertEquals(2, sizeof($result));

        //Test a custom where
        $result = $accountsLink->getBeans([
            'where' => [
                'lhs_field' => 'source',
                'operator' => '=',
                'rhs_value' => 'external',
            ],
        ]);
        $this->assertEquals(1, sizeof($result));
        $this->assertEquals($bug3->id, $result[$bug3->id]->id);

        //Test offset/pagination
        $allIds = array_keys($accountsLink->getBeans([
            'orderby' => 'id',
        ]));
        $this->assertEquals(3, sizeof($allIds));
        $result = $accountsLink->getBeans([
            'limit' => 1,
            'offset' => 1,
            'orderby' => 'id',
        ]);
        $this->assertEquals(1, sizeof($result));
        $this->assertArrayHasKey($allIds[1], $result);


        //Test a custom where on a One2M Relationship
        $contract1 = BeanFactory::newBean('Contracts');
        $contract1->name = 'Contract 1';
        $contract1->status = 'closed';
        $contract1->account_id = $account->id;
        $contract1->save();
        $this->createdBeans[] = $contract1;

        $contract2 = BeanFactory::newBean('Contracts');
        $contract2->name = 'Contract 2';
        $contract2->status = 'inprogress';
        $contract2->account_id = $account->id;
        $contract2->save();
        $this->createdBeans[] = $contract2;


        $account->load_relationship('contracts');
        $account->contracts->add($contract1);
        $account->contracts->add($contract2);

        $result = $account->get_linked_beans('contracts', 'Contract');
        $this->assertEquals(2, sizeof($result));

        $result = $account->get_linked_beans(
            'contracts',
            'Contract',
            null,
            0,
            -1,
            0,
            [
                'lhs_field' => 'status',
                'operator' => '=',
                'rhs_value' => 'inprogress',
            ]
        );
        $this->assertEquals(1, sizeof($result));
        $this->assertEquals($contract2->id, $result[0]->id);

        $result = $account->get_linked_beans(
            'contracts',
            'Contract',
            null,
            '', //Checking how empty string is converted into integer
            '', //Checking how empty string is converted into integer
            0,
            [
                'lhs_field' => 'status',
                'operator' => '=',
                'rhs_value' => 'inprogress',
            ]
        );
        $this->assertEquals(1, sizeof($result));
        $this->assertEquals($contract2->id, $result[0]->id);

        //Test offset/pagination on One2MBean
        $allIds = array_keys($account->contracts->getBeans());
        $this->assertEquals(2, sizeof($allIds));
        $result = $account->contracts->getBeans(['limit' => 1, 'offset' => 1]);
        $this->assertEquals(1, sizeof($result));
        $this->assertTrue(in_array(key($result), $allIds), 'Link returned by limit/offset is not in list of all links returned');

        // This test assumes that the order of IDs gotten in $allIds will be the same order the DB uses for the offset query.
        //$this->assertArrayHasKey($allIds[1], $result);
    }

    public function testGetBeansWithOrderBy()
    {
        $module = 'Accounts';
        require 'include/modules.php';

        $account = BeanFactory::newBean($module);
        $account->name = 'LinkTestAccount';
        $account->save();
        $this->createdBeans[] = $account;

        $bug = BeanFactory::newBean('Bugs');
        $bug->name = 'LinkTestBug Z';
        $bug->description = 'z';
        $bug->save();
        $this->createdBeans[] = $bug;

        $bug2 = BeanFactory::newBean('Bugs');
        $bug2->name = 'LinkTestBug Y';
        $bug->description = 'y';
        $bug2->save();
        $this->createdBeans[] = $bug2;

        $bug3 = BeanFactory::newBean('Bugs');
        $bug3->name = 'LinkTestBug X';
        $bug3->source = 'external';
        $bug->description = 'x';
        $bug3->save();
        $this->createdBeans[] = $bug3;

        $accountsLink = new Link2('bugs', $account);
        $accountsLink->add($bug);
        $accountsLink->add($bug2);
        $accountsLink->add($bug3);

        $result = $accountsLink->getBeans([
            'orderby' => 'name',
        ]);
        $expected = [
            $bug3->id => $bug3,
            $bug2->id => $bug2,
            $bug->id => $bug,
        ];

        $this->assertEquals(array_keys($expected), array_keys($result));
        foreach ($expected as $key => $val) {
            $this->assertEquals($expected[$key]->id, $result[$key]->id, "Wrong data in key $key");
        }
        //test order DESC and ASC
        $result = $accountsLink->getBeans([
            'orderby' => 'name',
        ]);
        $expected = [
            $bug3->id => $bug3,
            $bug2->id => $bug2,
            $bug->id => $bug,
        ];

        $this->assertEquals(array_keys($expected), array_keys($result));
        foreach ($expected as $key => $val) {
            $this->assertEquals($expected[$key]->id, $result[$key]->id, "Wrong data in key $key");
        }

        $result = $accountsLink->getBeans([
            'orderby' => 'name DESC',
        ]);
        $expected = [
            $bug->id => $bug,
            $bug2->id => $bug2,
            $bug3->id => $bug3,
        ];

        $this->assertEquals(array_keys($expected), array_keys($result));
        foreach ($expected as $key => $val) {
            $this->assertEquals($expected[$key]->id, $result[$key]->id, "Wrong data in key $key");
        }
    }

    public function testLink2WithRelationshipFields()
    {
        require 'include/modules.php';

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->name = 'A test Opp';
        $opp->save();
        $this->createdBeans[] = $opp;

        $contact = BeanFactory::newBean('Contacts');
        $contact->last_name = 'Another test Contact';
        $contact->save();
        $this->createdBeans[] = $contact;

        $opp->load_relationship('contacts');
        $opp->contacts->add($contact, [
            'contact_role' => 'Observer',
        ]);

        $this->assertEmpty($contact->opportunity_role);

        $result = array_values($opp->contacts->getBeans());
        $this->assertEquals($contact->id, $result[0]->id);
        $this->assertEquals('Observer', $result[0]->opportunity_role);
    }

    public function testGetBeans()
    {
        global $db;

        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();
        $account->load_relationship('contacts');
        $account->contacts->add($contact);

        $beans = $account->contacts->getBeans();
        $this->assertCount(1, $beans);
        $bean = array_shift($beans);
        $this->assertEquals($contact->id, $bean->id, 'Related bean is not retrieved');

        // manually remove related bean in order to not let the link know about that
        BeanFactory::unregisterBean($contact);
        $query = 'DELETE FROM ' . $contact->table_name . ' WHERE id = ' . $db->quoted($contact->id);
        $db->query($query);

        $account->contacts->beans = null;
        $beans = $account->contacts->getBeans();
        $this->assertCount(0, $beans, 'Empty bean is retrieved instead of deleted one');
    }

    /**
     * @covers Link2::getType
     */
    public function testGetType()
    {
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['getSide'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->atLeastOnce())
            ->method('getSide')
            ->willReturn(REL_LHS);

        $relationship = $this->getMockForAbstractClass(
            'AbstractRelationship',
            [],
            '',
            false,
            false,
            false,
            ['getType']
        );

        $relationship->expects($this->atLeastOnce())
            ->method('getType')
            ->with(REL_LHS)
            ->willReturn(REL_TYPE_MANY);

        SugarTestReflection::setProtectedValue($link2, 'relationship', $relationship);

        $this->assertEquals(REL_TYPE_MANY, $link2->getType());
    }

    /**
     * The bug described below only occurred when a {@link Link2} instance was used in the following sequence:
     *
     * 1. {@link Link2::resetLoaded}
     * 2. {@link Link2::get}
     * 3. {@link Link2::getBeans} with parameters
     * 4. {@link Link2::getBeans} without parameters
     *
     * We start with a link that hasn't been loaded. I chose to explicitly reset the loaded flag. Next we call
     * {@link Link2::get} to return the IDs of any linked records. This has the effect of changing
     * {@link Link2::loaded} to `true` while {@link Link2::$beans} is `null`. Next we call {@link Link2::getBeans} with
     * parameters. Before fixing this bug, this had the effect of changing {@link Link2::$beans} to an empty array. Next
     * we call {@link Link2::getBeans} without parameters. The {@link Link2} instance believed it had been loaded
     * because {@link Link2::beansAreLoaded} returns `true` when {@link Link2::$beans} is an array. We're simultaneously
     * using {@link Link2::$loaded} and {@link Link2::beansAreLoaded} to indicate if a link has been loaded, but they
     * aren't always in sync. So the current state of {@link Link2::$beans} was returned instead of executing the query
     * and populating {@link Link2::$beans} with its results.
     *
     * After fixing this bug, calling {@link Link2::getBeans} with parameters will not change {@link Link2::$beans} to
     * an empty array. This allows {@link Link2::beansAreLoaded} to return `false` when we call {@link Link2::getBeans}
     * without parameters, so {@link Link2::$beans} will be populated with the data from {@link Link2::$rows} before it
     * is returned.
     *
     * @covers Link2::get
     * @covers Link2::getBeans
     */
    public function testGetBeans_SequenceShouldReturnTheCorrectBeans()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->load_relationship('contacts');
        $contact1 = SugarTestContactUtilities::createContact();
        $account->contacts->add($contact1);
        $contact2 = SugarTestContactUtilities::createContact();
        $account->contacts->add($contact2);
        $account->contacts->resetLoaded();
        $ids = $account->contacts->get();
        $this->assertCount(2, $ids, 'Should have returned the IDs of the related contacts');
        $beans = $account->contacts->getBeans(
            [
                'where' => [
                    'lhs_field' => 'id',
                    'operator' => '=',
                    'rhs_value' => $contact1->id,
                ],
            ]
        );
        $this->assertCount(1, $beans, "Should have returned the related bean with id={$contact1->id}");
        $beans = $account->contacts->getBeans();
        $this->assertCount(2, $beans, 'Should have returned the two related beans');
    }
}
