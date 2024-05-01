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

class SimpleQueryTest extends TestCase
{
    /**
     * @var DBManager
     */
    private $db;
    protected $created = [];

    protected $backupGlobals = false;

    protected $contacts = [];
    protected $accounts = [];
    protected $notes = [];
    protected $kbDocuments = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    protected function setUp(): void
    {
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->contacts)) {
            $contactList = [];
            foreach ($this->contacts as $contact) {
                $contactList[] = $this->db->quoted($contact->id);
            }
            $this->db->query(
                'DELETE FROM contacts WHERE id IN (' . implode(',', $contactList) . ')'
            );
        }
        if (!empty($this->accounts)) {
            $accountList = [];
            foreach ($this->accounts as $account) {
                $accountList[] = $this->db->quoted($account->id);
            }
            $this->db->query(
                'DELETE FROM accounts WHERE id IN (' . implode(',', $accountList) . ')'
            );
        }

        if (!empty($this->notes)) {
            $notesList = [];
            foreach ($this->notes as $note) {
                $notesList[] = $this->db->quoted($note->id);
            }
            $this->db->query(
                'DELETE FROM notes WHERE id IN (' . implode(',', $notesList) . ')'
            );
        }

        if (!empty($this->kbDocuments)) {
            $kbDocumentsList = [];
            foreach ($this->kbDocuments as $kbDocument) {
                $kbDocumentsList[] = $this->db->quoted($kbDocument->id);
            }
            $this->db->query(
                'DELETE FROM kbdocuments WHERE id IN (' . implode(',', $kbDocumentsList) . ')'
            );
        }
    }

    public function testSelect()
    {
        // create a new contact
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $this->contacts[] = $contact;
        $id = $contact->id;
        // don't need the contact bean anymore, get rid of it
        unset($contact);
        // get the new contact

        $sq = new SugarQuery();
        $sq->select(['first_name', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->equals('id', $id);
        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match'
        );

        // delete contact verify I can't get it
        $contact = BeanFactory::getBean('Contacts', $id);
        $contact->mark_deleted($id);
        unset($contact);

        $result = $sq->execute();
        $this->assertTrue(
            empty($result),
            'Result Set was not empty, it contained: ' . print_r($result, true)
        );

        // get deleted items
        $sq = new SugarQuery();
        $sq->select(['first_name', 'last_name']);
        $sq->from(
            BeanFactory::newBean('Contacts'),
            ['add_deleted' => false]
        );
        $sq->where()->equals('id', $id);

        $result = $sq->execute();

        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match, the deleted record did not return'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match, the deleted record did not return'
        );
    }

    public function testSelectWithAlias()
    {
        // create a new contact
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $this->contacts[] = $contact;
        $id = $contact->id;
        // don't need the contact bean anymore, get rid of it
        unset($contact);
        // get the new contact

        $sq = new SugarQuery();
        $sq->select(['first_name', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'), ['alias' => 'c']);
        $sq->where()->equals('id', $id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match'
        );

        // delete contact verify I can't get it
        $contact = BeanFactory::getBean('Contacts', $id);
        $contact->mark_deleted($id);
        unset($contact);

        $result = $sq->execute();
        $this->assertTrue(
            empty($result),
            'Result Set was not empty, it contained: ' . print_r($result, true)
        );

        // get deleted items
        $sq = new SugarQuery();
        $sq->select(['first_name', 'last_name']);
        $sq->from(
            BeanFactory::newBean('Contacts'),
            ['add_deleted' => false]
        );
        $sq->where()->equals('id', $id);

        $result = $sq->execute();

        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match, the deleted record did not return'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match, the deleted record did not return'
        );
    }

    public function testSelectWithJoin()
    {
        // create a new contact
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $contact_id = $contact->id;


        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();

        $account->load_relationship('contacts');
        $account->contacts->add($contact->id);

        $this->accounts[] = $account;
        $this->contacts[] = $contact;

        // don't need the contact bean anymore, get rid of it
        unset($contact);
        unset($account);
        // get the new contact

        $sq = new SugarQuery();
        $sq->from(BeanFactory::newBean('Contacts'));
        $accounts = $sq->join('accounts')->joinName();
        $sq->select(
            ['first_name', 'last_name', ["$accounts.name", 'aname']]
        );

        $sq->where()->equals('id', $contact_id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Test',
            $result[0]['first_name'],
            'The First Name Did Not Match'
        );
        $this->assertEquals(
            'McTester',
            $result[0]['last_name'],
            'The Last Name Did Not Match'
        );
        $this->assertEquals(
            'Awesome',
            $result[0]['aname'],
            'The Account Name Did Not Match'
        );
    }

    public function testSelectWithJoinToSelf()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;

        $account2 = BeanFactory::newBean('Accounts');
        $account2->name = 'Awesome 2';
        $account2->save();

        $account->load_relationship('members');
        $account->members->add($account2->id);

        $this->accounts[] = $account;
        $this->accounts[] = $account2;

        // don't need the accounts beans anymore, get rid of'em
        unset($account2);
        unset($account);

        // lets try a query
        $sq = new SugarQuery();
        $sq->select([['accounts.name', 'aname']]);
        $sq->from(BeanFactory::newBean('Accounts'));
        $sq->join('members');
        $sq->where()->equals('id', $account_id);

        $result = $sq->execute();
        // only 1 record
        $this->assertEquals(
            'Awesome',
            $result[0]['aname'],
            "Account doesn't match"
        );
    }

    public function testSelectManyToMany()
    {
        global $current_user;

        $current_user->load_relationship('email_addresses');

        $email_address = BeanFactory::newBean('EmailAddresses');
        $email_address->email_address = 'test@test.com';
        $email_address->deleted = 0;
        $email_address->save();

        $current_user->email_addresses->add(
            $email_address->id,
            ['deleted' => 0]
        );

        // lets try a query
        $sq = new SugarQuery();
        $sq->select([['users.first_name', 'fname']]);
        $sq->from(BeanFactory::newBean('Users'));
        $email_addresses = $sq->join('email_addresses')->joinName();
        $sq->where()->starts("$email_addresses.email_address", 'test');
        $sq->where()->equals('users.id', $current_user->id);


        $result = $sq->execute();
        $this->assertEquals(
            $current_user->first_name,
            $result[0]['fname'],
            'Wrong Email Address Result Returned'
        );
    }

    public function testOrderByDerivedField()
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Super';
        $contact->last_name = 'Awesome-Sauce';
        $contact->save();
        $this->contacts[] = $contact;
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Super';
        $contact->last_name = 'Bad-Sauce';
        $contact->save();
        $this->contacts[] = $contact;

        $sq = new SugarQuery();

        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->in('contacts.last_name', ['Awesome-Sauce', 'Bad-Sauce']);
        $sq->orderBy('full_name', 'DESC');

        $result = $sq->execute();

        $expected = ['Bad-Sauce', 'Awesome-Sauce'];

        $lastNameResult = array_reduce(
            $result,
            function ($out, $val) {
                if (isset($val['contacts__last_name'])) {
                    $out[] = $val['contacts__last_name'];
                }
                return $out;
            }
        );

        $this->assertEquals($expected, $lastNameResult);

        $sq = new SugarQuery();
        $sq->select(['last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->in('contacts.last_name', ['Awesome-Sauce', 'Bad-Sauce']);
        $sq->orderBy('full_name', 'ASC');

        $result = $sq->execute();

        $expected = [
            [
                'last_name' => 'Awesome-Sauce',
                'contacts__last_name' => 'Awesome-Sauce',
            ],
            [
                'last_name' => 'Bad-Sauce',
                'contacts__last_name' => 'Bad-Sauce',
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSelectOneToManyWithRole()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Test Account';
        $account->save();
        $account_id = $account->id;

        // create a new note
        $note = BeanFactory::newBean('Notes');
        $note->name = 'Test Note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $note_id = $note->id;

        $this->accounts[] = $account;
        $this->notes[] = $note;

        // don't need the contact bean anymore, get rid of it
        unset($note);
        unset($account);
        // get the new contact
        $sq = new SugarQuery();
        $sq->from(BeanFactory::newBean('Notes'));
        $accounts = $sq->join('accounts')->joinName();
        $sq->select(["$accounts.name", "$accounts.id"]);
        $sq->where()->equals('id', $note_id);

        $result = $sq->execute();
        // only 1 record

        $this->assertEquals(
            'Test Account',
            $result[0]['name'],
            'The Name Did Not Match'
        );
        $this->assertEquals($result[0]['id'], $account_id, 'The ID Did Not Match');
    }

    public function testSelectDbConcatField()
    {
        /** @var KBDocument $kbDocument */
        $kbDocument = BeanFactory::newBean('KBDocuments');
        $kbDocument->name = 'Test Document';
        $kbDocument->save();
        $this->kbDocuments[] = $kbDocument;

        $sq = new SugarQuery();
        $sq->from($kbDocument);
        $sq->select('id', 'name');
        $sq->where()->equals('id', $kbDocument->id);

        $data = $sq->execute();
        $this->assertCount(1, $data);

        $row = array_shift($data);
        $this->assertArrayHasKey('name', $row);
        $this->assertEquals('Test Document', $row['name']);
    }

    public function testOrderBySortOn()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $sq = new SugarQuery();
        $sq->from($user);
        $sq->select('id');
        $sq->where()->equals('id', $user->id);
        $sq->orderBy('name');

        $data = $sq->execute();
        $this->assertCount(1, $data);

        $row = array_shift($data);
        $this->assertArrayHasKey('id', $row);
        $this->assertEquals($user->id, $row['id']);
    }

    public function testSelectWhereOrNotDeleted()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();
        $user3 = SugarTestUserUtilities::createAnonymousUser();
        $user3->mark_deleted($user3->id);

        $sq = new SugarQuery();
        $sq->from($user1);
        $sq->select('id');
        $sq->orWhere()
            ->equals('id', $user1->id)
            ->equals('id', $user2->id)
            ->equals('id', $user3->id);
        $sq->orderBy('id', 'ASC');

        $data = $sq->execute();
        $this->assertCount(2, $data);

        $expected = [$user1->id, $user2->id];
        sort($expected);

        $this->assertEquals($data[0]['id'], $expected[0]);
        $this->assertEquals($data[1]['id'], $expected[1]);
    }

    public function testSelectAssignedUserNameOwner()
    {
        global $current_user;

        $owner = SugarTestUserUtilities::createAnonymousUser();
        $account = SugarTestAccountUtilities::createAccount(null, [
            'assigned_user_id' => $owner->id,
        ]);

        $query = new SugarQuery();
        $query->from($account, [
            'team_security' => false,
        ]);
        $query->select('assigned_user_name');
        $query->where()->equals('id', $account->id);

        $accounts = $account->fetchFromQuery($query);
        $this->assertCount(1, $accounts);

        $account = array_shift($accounts);
        $this->assertEquals($current_user->id, $account->assigned_user_name_owner);
    }
}
