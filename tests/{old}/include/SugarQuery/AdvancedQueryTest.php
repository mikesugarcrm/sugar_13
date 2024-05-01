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

use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\TestCase;

class AdvancedQueryTest extends TestCase
{
    /**
     * @var mixed|\SugarBean[]|null[]|mixed[]|\aCase[]
     */
    public $cases;
    /**
     * @var mixed|\SugarBean[]|null[]
     */
    public $notes;
    /**
     * @var DBManager
     */
    private $db;
    protected $created = [];

    protected $backupGlobals = false;

    protected $contacts = [];
    protected $accounts = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('dictionary');
    }

    protected function setUp(): void
    {
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
        }
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();

        BeanFactory::setBeanClass('Contacts');

        if (!empty($this->contacts)) {
            $bean = BeanFactory::newBean('Contacts');
            $contactList = [];
            foreach ($this->contacts as $contact) {
                $contactList[] = $this->db->quoted($contact->id);
            }

            $this->db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(',', $contactList) . ')');
            if ($bean->hasCustomFields()) {
                $this->db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(',', $contactList) . ')'
                );
            }
        }
        if (!empty($this->accounts)) {
            $accountList = [];
            foreach ($this->accounts as $account) {
                $accountList[] = $this->db->quoted($account->id);
            }
            $bean = BeanFactory::newBean('Accounts');
            $this->db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(',', $accountList) . ')');
            if ($bean->hasCustomFields()) {
                $this->db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(',', $accountList) . ')'
                );
            }
        }

        if (!empty($this->cases)) {
            $casesList = [];
            foreach ($this->cases as $case) {
                $casesList[] = $this->db->quoted($case->id);
            }
            $bean = BeanFactory::newBean('Cases');
            $this->db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(',', $casesList) . ')');
            if ($bean->hasCustomFields()) {
                $this->db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(',', $casesList) . ')'
                );
            }
        }

        if (!empty($this->notes)) {
            $notesList = [];
            foreach ($this->notes as $note) {
                $notesList[] = $this->db->quoted($note->id);
            }
            $bean = BeanFactory::newBean('Notes');
            $this->db->query("DELETE FROM {$bean->getTableName()} WHERE id IN (" . implode(',', $notesList) . ')');
            if ($bean->hasCustomFields()) {
                $this->db->query(
                    "DELETE FROM {$bean->get_custom_table_name()} WHERE id_c IN (" . implode(',', $notesList) . ')'
                );
            }
        }

        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
    }

    public function testSelectInWhere()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();

        $sqWhere = new SugarQuery();
        $sqWhere->select('name');
        $sqWhere->from(BeanFactory::newBean('Accounts'));
        $sqWhere->where()->equals('name', 'Awesome')->equals('id', $account->id);
        $sqWhereResult = $sqWhere->execute();
        $sqWhereResult = reset($sqWhereResult);
        $this->assertEquals($sqWhereResult['name'], 'Awesome', 'The name Did Not Match it was ' . $sqWhereResult['name']);

        // create a new contact
        $case = BeanFactory::newBean('Cases');
        $case->name = 'Test Case';
        $case->account_id = $account->id;
        $case->save();

        $this->accounts[] = $account;
        $this->cases[] = $case;

        $sq = new SugarQuery();
        $sq->select(['name']);
        $sq->from(BeanFactory::newBean('Cases'));
        $sq->where()->in('account_id', [$account->id]);
        $result = $sq->execute();

        // only 1 record
        $result = reset($result);

        $this->assertEquals($result['name'], 'Test Case', 'The name Did Not Match it was ' . $result['name']);
    }

    public function testSelectUnionWithOrder()
    {
        $result = $this->getUnionResults(true);
        $this->assertCount(2, $result, 'Exactly 2 rows were expected to be returned');
        $this->assertSame('Awesome', $result[0]['name']);
        $this->assertSame('Not Awesome', $result[1]['name']);
    }

    public function testSelectUnionWithNoOrder()
    {
        $result = $this->getUnionResults(false);
        $this->assertCount(2, $result, 'Exactly 2 rows were expected to be returned');
        $this->assertTrue(in_array('Awesome', [$result[0]['name'], $result[1]['name']]));
        $this->assertTrue(in_array('Not Awesome', [$result[0]['name'], $result[1]['name']]));
    }

    private function getUnionResults(bool $useOrder): array
    {

        // create 2 new accounts
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $this->accounts[] = $account;

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Not Awesome';
        $account->save();

        $this->accounts[] = $account;

        $sq1 = new SugarQuery();
        $sq1->select(['id', 'name']);
        $sq1->from(BeanFactory::newBean('Accounts'));
        $sq1->where()->equals('name', 'Awesome');

        $sq2 = new SugarQuery();
        $sq2->select(['id', 'name']);
        $sq2->from(BeanFactory::newBean('Accounts'));
        $sq2->where()->equals('name', 'Not Awesome');

        $sqUnion = new SugarQuery();
        $sqUnion->union($sq1);
        $sqUnion->union($sq2);
        if ($useOrder) {
            $sqUnion->orderBy('name', 'ASC');
        }
        $sqUnion->limit(2);

        return $sqUnion->execute();
    }

    public function testSelectNotes()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Awesome';
        $account->save();
        $account_id = $account->id;
        $this->accounts[] = $account;

        $note = BeanFactory::newBean('Notes');
        $note->name = 'Test note';
        $note->parent_type = 'Accounts';
        $note->parent_id = $account_id;
        $note->save();
        $this->notes[] = $note;

        $sq = new SugarQuery();
        $sq->from($account);
        $sq->where()->equals('id', $account_id, $account);
        $notes = $sq->join('notes')->joinName();
        $sq->select([['accounts.name', 'a_name'], ["$notes.name", 'n_name']]);


        $results = $sq->execute();

        $result = reset($results);

        $this->assertEquals('Test note', $result['n_name'], "The note name was: {$result['n_name']}");
    }

    public function testSelectFavorites()
    {
        $this->cases = [];
        for ($i = 0; $i < 40; $i++) {
            $aCase = new aCase();
            $aCase->name = 'UNIT TEST ' . count($this->cases) . ' - ' . create_guid();
            $aCase->billing_address_postalcode = sprintf('%08d', count($this->cases));
            if ($i > 25 && $i < 36) {
                $aCase->assigned_user_id = $GLOBALS['current_user']->id;
            } else {
                // The rest are assigned to admin
                $aCase->assigned_user_id = '1';
            }
            $aCase->save();
            $this->cases[] = $aCase;
            if ($i > 33) {
                // Favorite the last six
                $fav = new SugarFavorites();
                $fav->id = SugarFavorites::generateGUID('Cases', $aCase->id);
                $fav->new_with_id = true;
                $fav->module = 'Cases';
                $fav->record_id = $aCase->id;
                $fav->created_by = $GLOBALS['current_user']->id;
                $fav->assigned_user_id = $GLOBALS['current_user']->id;
                $fav->deleted = 0;
                $fav->save();
            }
        }

        $sq = new SugarQuery();
        $sq->select(['id', 'name']);
        $sq->from($aCase);

        $sf = new SugarFavorites();
        $sfAlias = $sf->addToSugarQuery($sq);

        $results = $sq->execute();

        $this->assertEquals('6', count($results), "The number of rows returned doesn't match the number of favorites created: " . count($results));

        foreach ($results as $case) {
            $fav = SugarFavorites::isUserFavorite('Cases', $case['id'], $GLOBALS['current_user']->id);
            $this->assertEquals($fav, true, "The record: {$case['id']} was not set as a favorite it is marked:" . var_export($fav, true));
        }
    }

    public function testSelectCount()
    {
        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $this->assertStringContainsString('count', $sqCount->compile()->getSQL());

        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->select(['name', 'account_type']);
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $sql = $sqCount->compile()->getSQL();
        $this->assertStringContainsString('COUNT(0)', $sql);
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('account_type', $sql);
    }

    public function testSelectCountGroupBy()
    {
        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $this->assertStringContainsString('count', $sqCount->compile()->getSQL());

        $sqCount = new SugarQuery();
        $sqCount->select()->setCountQuery();
        $sqCount->select(['name', 'account_type']);
        $sqCount->from(BeanFactory::newBean('Accounts'));
        $sql = $sqCount->compile()->getSQL();
        $this->assertStringContainsString('COUNT(0)', $sql);
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('account_type', $sql);
        $this->assertStringContainsString('GROUP BY', $sql);
    }

    public function testBadFields()
    {
        $sq = new SugarQuery();
        $sq->select(['id', 'notARealField']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->equals('noWhere', 'nonYaBusiness');
        $sq->orderBy('yesIAmCertainlyAField');
        $sql = $sq->compile()->getSQL();

        $this->assertStringNotContainsString('yesIAmCertainlyAField', $sql);
        $this->assertStringNotContainsString('noWhere', $sql);
        $this->assertStringNotContainsString('notARealField', $sql);
    }

    public function testUniqueAliases()
    {
        $sq = new SugarQuery();
        $sq->select(['*', ['id', 'superAwesomeField']]);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->equals('id', '2');
        $sql = $sq->compile()->getSQL();
        $this->assertStringContainsString('id superAwesomeField', $sql);
    }

    /**
     * @ticket 62961
     */
    public function testCustomFields()
    {
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::newBean('Contacts');
        $this->assertArrayHasKey('report_to_bigname', $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        $sq = new SugarQuery();
        $sq->select(['id', 'last_name', 'bigname_c', 'report_to_bigname']);
        $sq->from($contact);

        $sql = $sq->compile()->getSQL();
        // ensure the query looks good
        $this->assertStringContainsString('contacts_cstm.bigname_c', $sql);
        $this->assertStringContainsString('_cstm.bigname_c report_to_bigname', $sql);
        $this->assertStringContainsString(
            'LEFT JOIN contacts_cstm contacts_cstm ON contacts_cstm.id_c = contacts.id',
            $sql
        );
        $this->assertMatchesRegularExpression('/LEFT JOIN contacts_cstm jt(\w+)_cstm ON jt\1_cstm.id_c = jt\1\.id/', $sql);
    }

    /**
     * Test rname exists
     */
    public function testRnameExists()
    {
        $contact = BeanFactory::newBean('Contacts');
        // will throw because name is composite
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name', 'account_name']);
        $sq->from($contact);
        $sq->where()->equals('sync_contact', 1);
        $sql = $sq->compile()->getSQL();
        // the field should not be there now
        $this->assertStringContainsString('id IS NOT NULL', $sql);
    }

    /**
     * Test bad conditions
     */
    public function testBadRelateConditions()
    {
        $contact = BeanFactory::newBean('Contacts');
        // will throw because name is composite
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name', 'account_name']);
        $sq->from($contact);
        $sq->where()->equals('email_and_name1', 'Awesome');
        $sql = $sq->compile()->getSQL();
        // the field should not be there now
        $this->assertStringNotContainsString("email_and_name1 = 'Awesome'", $sql);
    }

    public function testRelatedOrderBy()
    {
        BeanFactory::setBeanClass('Contacts', 'Contact_Mock_Bug62961');
        $contact = BeanFactory::newBean('Contacts');
        $this->assertArrayHasKey('report_to_bigname', $contact->field_defs);
        $this->assertTrue($contact->hasCustomFields());

        // by related field
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from($contact);
        $sq->orderBy('account_name');
        $this->assertMatchesRegularExpression('/.*ORDER BY jt\w+.name DESC.*/s', $sq->compile()->getSQL());

        // by custom field too
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from($contact);
        $sq->orderBy('account_name')->orderBy('bigname_c', 'ASC');
        $this->assertMatchesRegularExpression('/ORDER BY jt\w+.name DESC, contacts.last_name ASC/s', $sq->compile()->getSQL());

        // by related custom field
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from($contact);
        $sq->orderBy('report_to_bigname');
        $this->assertMatchesRegularExpression('/ORDER BY jt\w+.last_name DESC/s', $sq->compile()->getSQL());

        // skip bad one
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from($contact);
        $sq->orderBy('portal_password1')->orderBy('account_name', 'asc');
        $this->assertMatchesRegularExpression('/ORDER BY jt\w+.name asc/s', $sq->compile()->getSQL());
    }

    public function testOrderByRaw()
    {
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->orderByRaw('last_name+1', 'DESC');
        $sql = $sq->compile()->getSQL();
        $this->assertStringContainsString('ORDER BY last_name+1 DESC', $sql);
    }

    public function testGroupByRaw()
    {
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->groupByRaw('last_name is awesome');
        $sql = $sq->compile()->getSQL();
        $this->assertStringContainsString('GROUP BY last_name is awesome', $sql);
    }

    public function testHavingRaw()
    {
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->havingRaw('last_name > 55');
        $sql = $sq->compile()->getSQL();
        $this->assertStringContainsString('HAVING last_name > 55', $sql);
    }

    public function testChildJoins()
    {
        $sq = new SugarQuery();
        $sq->select(['id', 'last_name']);
        $sq->from(BeanFactory::newBean('Contacts'));
        $accounts = $sq->join('accounts');
        $opportunities = $accounts->join('opportunities');
        $opportunities->join('contacts');
        $sql = $sq->compile()->getSQL();
        $this->assertMatchesRegularExpression('/INNER JOIN contacts jt(\w+) ON /s', $sql);
        $this->assertMatchesRegularExpression('/INNER JOIN opportunities jt(\w+) ON /s', $sql);
    }

    public function testLongAlias()
    {
        $contact = BeanFactory::newBean('Contacts');
        $contact->first_name = 'Test';
        $contact->last_name = 'McTester';
        $contact->save();
        $this->contacts[] = $contact;
        $longAlias = 'alias_longer_than_128_characters_that_should_normally_fail_on_not_mysql_stacks_' .
            'alias_longer_than_128_characters_that_should_normally_fail_on_not_mysql_stacks';
        $sq = new SugarQuery();
        $sq->select(['id', ['last_name', $longAlias]]);
        $sq->from(BeanFactory::newBean('Contacts'));
        $sq->where()->equals('last_name', 'McTester');
        $data = $sq->execute();
        $this->assertEquals($contact->last_name, $data[0][$longAlias]);
    }

    public function testSelectMultipleLinksUsingSameTable()
    {
        $quote = SugarTestQuoteUtilities::createQuote();

        $quote->load_relationship('billing_accounts');
        $billingAccount = SugarTestAccountUtilities::createAccount();
        $quote->billing_accounts->add($billingAccount);

        $shippingAccount = SugarTestAccountUtilities::createAccount();
        $quote->load_relationship('shipping_accounts');
        $quote->shipping_accounts->add($shippingAccount);

        $query = new SugarQuery();
        $query->from($quote, [
            'team_security' => false,
        ]);
        $query->select('id', 'billing_account_name', 'shipping_account_name');
        $query->where()->equals('quotes.id', $quote->id);

        $data = $query->execute();
        $this->assertCount(1, $data);

        $row = array_shift($data);
        $this->assertEquals($billingAccount->name, $row['billing_account_name']);
        $this->assertEquals($shippingAccount->name, $row['shipping_account_name']);
    }

    public function testFetchPrimaryAndRelateFullNamesFromQuery()
    {
        $jim = SugarTestContactUtilities::createContact(null, [
            'first_name' => 'Jim',
            'last_name' => 'Brennan',
        ]);

        $max = SugarTestContactUtilities::createContact(null, [
            'first_name' => 'Max',
            'last_name' => 'Jensen',
        ]);

        $max->load_relationship('reports_to_link');
        $max->reports_to_link->add($jim);

        $query = new SugarQuery();
        $query->from($max);
        $query->where()
            ->equals('id', $max->id);

        $contacts = $max->fetchFromQuery($query, ['name', 'report_to_name']);
        $this->assertCount(1, $contacts);

        $contact = array_shift($contacts);
        $this->assertEquals($max->name, $contact->name);
        $this->assertEquals($jim->name, $contact->report_to_name);
    }
}

class Contact_Mock_Bug62961 extends Contact
{
    public function __construct()
    {
        parent::__construct();
        $this->field_defs['bigname_c'] = [
            'calculated' => 'true',
            'formula' => 'strToUpper($last_name)',
            'enforced' => 'true',
            'dependency' => '',
            'required' => false,
            'source' => 'custom_fields',
            'name' => 'bigname_c',
            'vname' => 'LBL_BIGNAME',
            'type' => 'varchar',
            'massupdate' => '0',
            'default' => null,
            'no_default' => false,
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'len' => '255',
            'size' => '20',
            'custom_module' => 'Contacts',
            'sort_on' => 'last_name',
        ];
        $this->field_defs['report_to_bigname'] = [
            'name' => 'report_to_bigname',
            'rname' => 'bigname_c',
            'id_name' => 'reports_to_id',
            'vname' => 'LBL_REPORTS_TO',
            'type' => 'relate',
            'link' => 'reports_to_link',
            'table' => 'contacts',
            'isnull' => 'true',
            'module' => 'Contacts',
            'dbType' => 'varchar',
            'len' => 'id',
            'reportable' => false,
            'source' => 'non-db',
        ];
    }

    public function hasCustomFields()
    {
        return true;
    }

    public function testTagsDoNotProduceDuplicates()
    {
        $contacts = [
            SugarTestContactUtilities::createContact(),
            SugarTestContactUtilities::createContact(),
        ];

        $tags = [
            SugarTestTagUtilities::createTag(),
            SugarTestTagUtilities::createTag(),
        ];

        $count = count($contacts);

        foreach ($contacts as $contact) {
            $contact->load_relationship('tag_link');
            foreach ($tags as $tag) {
                $contact->tag_link->add($tag);
            }
        }

        $seed = BeanFactory::getDefinition('Contacts');

        $query = new SugarQuery();
        $query->from($seed);
        $query->orderBy('id');
        $query->where()
            ->in('id', array_map(function (Contact $contact) {
                return $contact->id;
            }, $contacts));
        $query->limit($count);

        $this->assertCount($count, $seed->fetchFromQuery($query));
    }
}
