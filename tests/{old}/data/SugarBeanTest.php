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
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use SugarTestAccountUtilities as AccountHelper;
use SugarTestUserUtilities as UserHelper;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\User;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;

/**
 * Class SugarBeanTest
 * @coversDefaultClass SugarBean
 */
class SugarBeanTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCaseUtilities::removeAllCreatedCases();
    }

    public function testAuditLogForBeanCreate()
    {
        $contact = SugarTestContactUtilities::createContact(null, ['phone_mobile' => '(111) 111-1111']);
        $auditLog = $this->getFieldAuditRecords($contact, 'phone_mobile');
        $this->assertCount(1, $auditLog, 'Audit log not created for create action.');
    }

    public function testErasure()
    {
        $contact = SugarTestContactUtilities::createContact(null, [
            'phone_mobile' => '(111) 111-1111',
            'phone_home' => '(222) 222-2222',
        ]);
        $this->assertEquals('(111) 111-1111', $contact->phone_mobile);
        $this->assertEquals('(222) 222-2222', $contact->phone_home);

        $list = FieldList::fromArray(['phone_mobile', 'phone_home', ['field_name' => 'email', 'id' => 'email id xxx']]);
        $contact->erase($list, false);

        $retrievedContact = BeanFactory::retrieveBean($contact->module_name, $contact->id, [
            'use_cache' => false,
            'disable_row_level_security' => true,
        ]);
        $this->assertEmpty($retrievedContact->phone_mobile);
        $this->assertEmpty($retrievedContact->phone_home);

        $auditLog = $this->getFieldAuditRecords($contact, 'phone_mobile');
        // there should be two entries for phone_mobile. One for create and second one for erasure
        $this->assertCount(2, $auditLog, 'Audit log not created for create or erasure.');
    }

    public function testGetObjectName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->getObjectName(), 'my_table', 'SugarBean->getObjectName() is not returning the table name when object_name is empty.');
    }

    public function testGetAuditTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_audit_table_name(), 'my_table_audit', 'SugarBean->get_audit_table_name() is not returning the correct audit table name.');
    }

    /**
     * @ticket 47261
     */
    public function testGetCustomTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_custom_table_name(), 'my_table_cstm', 'SugarBean->get_custom_table_name() is not returning the correct custom table name.');
    }

    public function testRetrieveStringQuoting()
    {
        $db = $this->getMockBuilder('DBManager')
            ->setMethods(['checkError'])
            ->getMockForAbstractClass();
        $db->expects($this->any())
            ->method('fromConvert')
            ->willReturnCallback(function ($string) {
                return $string;
            });
        $db->method('quote')
            ->withConsecutive(['bad\'string'])
            ->willReturnOnConsecutiveCalls('quoted string');

        $bean = new BeanMockTestObjectName();
        $bean->db = $db;
        $where = $bean->get_where([
            'test1' => 'bad\'string',
            'evil\'key' => 'data',
            'tricky-(select * from config)' => 'test',
        ]);

        $this->assertStringNotContainsString('bad\'string', $where);
        $this->assertStringContainsString('quoted string', $where);
        $this->assertStringNotContainsString('evil\'key', $where);
        $this->assertStringNotContainsString('select * from config', $where);
    }

    /**
     * This test makes sure that the object we are looking for is returned from the build_related_list method as
     * something changed someplace that is causing it to return the template that was passed in.
     */
    public function testBuildRelatedListReturnsRecordBeanVsEmptyBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $bean = new SugarBean();

        $query = 'select id FROM ' . $account->table_name . " where id = '" . $account->id . "'";
        $relatedList = $bean->build_related_list($query, BeanFactory::newBean('Accounts'));
        $return = array_shift($relatedList);

        $this->assertEquals($account->id, $return->id);
    }

    /**
     * Test to make sure that when a bean is cloned it removes all loaded relationships so they can be recreated on
     * the cloned copy if they are called.
     *
     * @group 51630
     * @return void
     */
    public function testCloneBeanDoesntKeepRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');

        // lets make sure the relationship is loaded
        $this->assertTrue(isset($account->contacts));

        $clone_account = clone $account;

        // lets make sure that the relationship is not on the cloned record
        $this->assertFalse(isset($clone_account->contacts));
    }

    /**
     * Test whether a relate field is determined correctly
     *
     * @param array $field_defs
     * @param string $field_name
     * @param bool $is_relate
     * @dataProvider isRelateFieldProvider
     * @covers       SugarBean::is_relate_field
     */
    public function testIsRelateField(array $field_defs, $field_name, $is_relate)
    {
        $bean = new SugarBean();
        $bean->field_defs = $field_defs;
        $actual = SugarTestReflection::callProtectedMethod($bean, 'is_relate_field', [$field_name]);

        $this->assertSame($is_relate, $actual);
    }

    public static function isRelateFieldProvider()
    {
        return [
            // test for on a non-existing field
            [
                [], 'dummy', false,
            ],
            // test for non-specified field type
            [
                [
                    'my_field' => [],
                ], 'my_field', false,
            ],
            // test on a non-relate field type
            [
                [
                    'my_field' => [
                        'type' => 'varchar',
                    ],
                ], 'my_field', false,
            ],
            // test on a relate field type but link not specified
            [
                [
                    'my_field' => [
                        'type' => 'relate',
                    ],
                ], 'my_field', false,
            ],
            // test when only link is specified
            [
                [
                    'my_field' => [
                        'link' => 'my_link',
                    ],
                ], 'my_field', false,
            ],
            // test on a relate field type
            [
                [
                    'my_field' => [
                        'type' => 'relate',
                        'link' => 'my_link',
                    ],
                ], 'my_field', true,
            ],
        ];
    }

    /**
     * test that currency/decimal from db is a string value
     * @dataProvider provideCurrencyFieldStringValues
     * @group sugarbean
     * @group currency
     */
    public function testCurrencyFieldStringValue($type, $actual, $expected)
    {
        $mock = new SugarBean();
        $mock->id = 'SugarBeanMockStringTest';
        $mock->field_defs = [
            'testDecimal' => [
                'type' => $type,
            ],
        ];

        $mock->testDecimal = $actual;
        $mock->fixUpFormatting();
        $this->assertSame($expected, $mock->testDecimal);
    }

    public function provideCurrencyFieldStringValues()
    {
        return [
            ['decimal', '500.01', '500.01'],
            ['decimal', 500.01, '500.01'],
            ['decimal', '-500.01', '-500.01'],
            ['currency', '500.01', '500.01'],
            ['currency', 500.01, '500.01'],
            ['currency', '-500.01', '-500.01'],
        ];
    }

    /**
     * SP-618
     * Verify that calling getCleanCopy on uncommon beans (like SessionManager) and common beans returns a new instance of the bean and not a null
     * @group sugarbean
     */
    public function testGetCopyNotNull()
    {
        $mock = new SessionManager();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, 'New instance of SessionManager SugarBean should not be null');
        $this->assertEquals($mock->module_name, $newInstance->module_name);

        $mock = new SugarBean();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, 'New instance of SugarBean should not be null');

        $mock = BeanFactory::newBean('Accounts');
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, 'New instance of Accounts SugarBean should not be null');
        $this->assertEquals('Accounts', $newInstance->module_name);
    }

    /**
     * @group sugarbean
     */
    public function testGetNotificationRecipientsReturnsEmptyArray()
    {
        $mock = new SugarBean();
        unset($mock->assigned_user_id);

        $ret = $mock->get_notification_recipients();

        $this->assertEmpty($ret);
    }

    public function testGetNotificationRecipientsReturnsNonEmptyArray()
    {
        $mock = new SugarBean();
        $mock->assigned_user_id = '1';

        $ret = $mock->get_notification_recipients();

        $this->assertEquals('1', $ret[0]->id);
    }

    /**
     * Check that the decryption is not called until the actual value is used
     * @return void
     */
    public function testDecryptCallsNumber()
    {
        $oSugarBean = new BeanMockTestObjectName();

        $oSugarBean->field_defs = [
            'test_field' => [
                'name' => 'test_field',
                'type' => 'encrypt',
            ],
        ];

        // must be a valid base64-encoded string
        $original_encrypted_value = $encrypted_value = base64_encode('smth');
        $oSugarBean->test_field = ''; //initialization to avoid "Indirect modification of overloaded property..." error
        $oSugarBean->test_field =& $encrypted_value; //use link to avoid calling __get method in assertEquals
        $oSugarBean->field_defs['test_field']['type'] = 'encrypt';
        $oSugarBean->check_date_relationships_load(); //$oSugarBean->test_field shouldn't be changed
        $this->assertEquals($original_encrypted_value, $encrypted_value);
        $decrypted_value = $oSugarBean->test_field; //$oSugarBean->test_field should be changed
        $this->assertNotEquals($encrypted_value, $decrypted_value);
    }

    /**
     * Check if SugarBean::checkUserAccess returns true for a valid case.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccess()
    {
        $user = UserHelper::createAnonymousUser(true, 1);
        $account = AccountHelper::createAccount();

        $this->assertTrue($account->checkUserAccess($user));
    }

    /**
     * @param array $parent_data Parent bean data
     * @param array $child_data Child bean data
     * @param array $fn_field_defs Function field definition
     * @param mixed $expected Expected value
     *
     * @dataProvider functionFieldProvider
     */
    public function testProcessFunctionFields(array $parent_data, array $child_data, array $fn_field_defs, $expected)
    {
        $parent = new BeanFunctionFieldsMock();
        $child = new SugarBean();
        $child->field_defs['fn_field'] = $fn_field_defs;
        $child->fn_field = null;

        foreach ($parent_data as $key => $value) {
            $parent->$key = $value;
        }

        foreach ($child_data as $key => $value) {
            $child->$key = $value;
        }

        $child->field_defs = $fn_field_defs;

        $parent->processFunctionFields($child, ['fn_field' => $fn_field_defs]);

        $this->assertEquals($expected, $child->fn_field);
    }

    public static function functionFieldProvider()
    {
        $parent_data = ['foo' => 'bar'];
        $child_data = ['baz' => 'quux'];

        return [
            // source is parent bean, function is global function
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['foo'],
                    'function_name' => 'strlen',
                ],
                3,
            ],
            // source is child bean, function is static function of a class
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['baz'],
                    'function_params_source' => 'this',
                    'function_class' => 'BeanFunctionFieldsMock',
                    'function_name' => 'toUpper',
                ],
                'QUUX',
            ],
            // function declaration is in external file
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['foo'],
                    'function_name' => 'SugarBeanTest_external_function',
                    'function_require' => __DIR__ . '/SugarBeanTest/external_function.php',
                ],
                'bar',
            ],
            // argument is $this
            [
                $parent_data,
                [],
                [
                    'function_params' => ['$this'],
                    'function_name' => 'get_class',
                ],
                'BeanFunctionFieldsMock',
            ],
            // param source is wrong
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['foo'],
                    'function_params_source' => 'unknown',
                    'function_name' => 'strlen',
                ],
                null,
            ],
            // function doesn't exist
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['foo'],
                    'function_name' => 'SugarBeanTest_unknown',
                ],
                null,
            ],
            // source field is not set
            [
                $parent_data,
                $child_data,
                [
                    'function_params' => ['bar'],
                    'function_name' => 'strlen',
                ],
                null,
            ],
        ];
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without team access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutTeamAccess()
    {
        global $current_user;
        $owner = UserHelper::createAnonymousUser();

        $account = AccountHelper::createAccount(
            null,
            ['team_id' => $owner->id, 'team_set_id' => $owner->id,]
        );
        $current_user = UserHelper::createAnonymousUser();
        $this->assertFalse($account->checkUserAccess($current_user));
    }

    /**
     * Check if SugarBean::checkUserAccess returns false without ACL access.
     * @covers SugarBean::checkUserAccess
     */
    public function testCheckUserAccessWithoutACLAccess()
    {
        $user = UserHelper::createAnonymousUser();
        $account = $this->getMockBuilder('Account')
            ->setMethods(['ACLAccess'])
            ->getMock();
        $account->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $account->id = 'foo';

        $this->assertFalse($account->checkUserAccess($user));
    }

    /**
     * This test will make sure that when you enter an operation that the one that actually entered the operation
     * actually is the one to leave it.
     */
    public function testEnterLeaveOperationMultipleTimes()
    {
        $ret1 = SugarBean::enterOperation('unit_test');
        $this->assertTrue($ret1);

        $ret2 = SugarBean::enterOperation('unit_test');
        $this->assertFalse($ret2);

        $this->assertFalse(SugarBean::leaveOperation('unit_test', $ret2));

        $this->assertTrue(SugarBean::leaveOperation('unit_test', $ret1));

        SugarBean::resetOperations();
    }

    /**
     * Test logging for distinct mismatch/compensation and the
     * proper return of offending record ids.
     *
     * @covers       SugarBean::logDistinctMismatch
     * @dataProvider providerTestLogDistinctMismatch
     * @group unit
     *
     * @param array $sqlRows
     * @param array $beans
     * @param string $level
     * @param array $expected
     */
    public function testLogDistinctMismatch(array $sqlRows, array $beans, $level, array $expected)
    {
        LoggerManager::getLogger()->setLevel($level);

        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $methodArgs = [$sqlRows, $beans];
        $this->assertEquals(
            $expected,
            SugarTestReflection::callProtectedMethod($bean, 'logDistinctMismatch', $methodArgs),
            'Wrong offending record ids returned'
        );
    }

    public function providerTestLogDistinctMismatch()
    {
        return [

            // matching sqlRows vs beanSet
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a2', 'name' => 'record2'],
                    2 => ['id' => 'a3', 'name' => 'record3'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                ],
                'debug',
                [],
            ],

            // duplicate sqlRows
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a1', 'name' => 'record1'],
                    2 => ['id' => 'a2', 'name' => 'record2'],
                    3 => ['id' => 'a3', 'name' => 'record3'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                ],
                'debug',
                ['a1' => 2],
            ],

            // duplicate sqlRows, no detailed logging (not enabled by default)
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a1', 'name' => 'record1'],
                    2 => ['id' => 'a2', 'name' => 'record2'],
                    3 => ['id' => 'a3', 'name' => 'record3'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                ],
                'fatal',
                [],
            ],
        ];
    }

    /**
     * Test fetchFromQuery with distinct compensation.
     *
     * @covers       SugarBean::fetchFromQuery
     * @covers       SugarBean::computeDistinctCompensation
     * @dataProvider providerTestFetchFromQueryWithDistinctCompensation
     * @group unit
     */
    public function testFetchFromQueryWithDistinctCompensation($sqlRows, $expected, $compensation)
    {
        // prepare SugarQuery
        $query = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();

        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($sqlRows));

        // sut
        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['call_custom_logic', 'logDistinctMismatch', 'getCleanCopy'])
            ->getMock();
        $bean->field_defs = [
            'id' => [
                'type' => 'id',
            ],
            'name' => [
                'type' => 'name',
            ],
        ];
        //Setup the beans returned by cleanCopy to be clones of the mock rather than real beans
        $bean->method('getCleanCopy')->will($this->returnCallback(
            function () use ($bean) {
                return clone $bean;
            }
        ));

        if ($compensation) {
            $bean->expects($this->once())
                ->method('logDistinctMismatch');
        }

        // execute fetch
        $options = ['compensateDistinct' => true];
        $results = $bean->fetchFromQuery($query, [], $options);

        // tests
        $this->assertArrayHasKey(
            '_distinctCompensation',
            $results,
            'No distinct compensation returned'
        );

        $this->assertEquals(
            $compensation,
            $results['_distinctCompensation'],
            'Incorrect compensation result'
        );

        unset($results['_distinctCompensation']);

        foreach ($results as $key => $bean) {
            $this->assertEquals(
                $expected[$key],
                $bean->toArray(),
                'Incorrect bean result set'
            );
        }
    }

    public function providerTestFetchFromQueryWithDistinctCompensation()
    {
        return [

            // matching sqlRows vs beanSet
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a2', 'name' => 'record2'],
                    2 => ['id' => 'a3', 'name' => 'record3'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                ],
                0,
            ],

            // one duplicate sqlRows
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a1', 'name' => 'record1'],
                    2 => ['id' => 'a2', 'name' => 'record2'],
                    3 => ['id' => 'a3', 'name' => 'record3'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                ],
                1,
            ],

            // multiple duplicate sqlRows with different records
            [
                [
                    0 => ['id' => 'a1', 'name' => 'record1'],
                    1 => ['id' => 'a1', 'name' => 'record1'],
                    2 => ['id' => 'a2', 'name' => 'record2'],
                    3 => ['id' => 'a3', 'name' => 'record3'],
                    4 => ['id' => 'a3', 'name' => 'record3'],
                    5 => ['id' => 'a3', 'name' => 'record3'],
                    6 => ['id' => 'a4', 'name' => 'record4'],
                    7 => ['id' => 'a5', 'name' => 'record5'],
                    8 => ['id' => 'a5', 'name' => 'record5'],
                    9 => ['id' => 'a1', 'name' => 'record1'],
                ],
                [
                    'a1' => ['id' => 'a1', 'name' => 'record1'],
                    'a2' => ['id' => 'a2', 'name' => 'record2'],
                    'a3' => ['id' => 'a3', 'name' => 'record3'],
                    'a4' => ['id' => 'a4', 'name' => 'record4'],
                    'a5' => ['id' => 'a5', 'name' => 'record5'],
                ],
                5,
            ],
        ];
    }

    /**
     * Tests SugarBean::create_new_list_query
     * This test is to make sure ret_array['secondary_select'] should not contain fields with relationship_fields defined
     */
    public function testCreateNewListQuery()
    {
        $bean = BeanFactory::newBean('Contacts');
        $filter = [
            'account_id',
            'opportunity_role_fields',
            'opportunity_role_id',
            'opportunity_role',
            'a_field' => ['force_exists' => true],
            56 => ['force_exists' => true],
        ];
        $params = [
            'distinct' => false,
            'joined_tables' => [0 => 'opportunities_contacts'],
            'include_custom_fields' => true,
            'collection_list' => null,
        ];
        $query = $bean->create_new_list_query('', '', $filter, $params, 0, '', true);

        $this->assertStringNotContainsString('opportunity_role_fields', $query['secondary_select']);
        $this->assertStringContainsString('opportunity_role_id', $query['secondary_select']);
        $this->assertStringContainsString('a_field', $query['select']);
        $this->assertStringNotContainsString('56', $query['select']);

        $bean = BeanFactory::newBean('Contacts');
        $filter = [
            'account_name',
            'account_id',
        ];
        $params = [
            'join_type' => 'LEFT JOIN',
            'join_table_alias' => 'accounts',
            'join_table_link_alias' => 'jtl0',
        ];
        $query = $bean->create_new_list_query('', '', $filter, $params, 0, '', true);

        $this->assertEquals(1, substr_count($query['secondary_select'], ' account_id'), 'secondary_select should not contain duplicate alias names.');

        $bean = BeanFactory::newBean('Calls');
        $query = $bean->create_new_list_query('', '', ['contact_name', 'contact_id'], [], 0, '', true);

        $this->assertStringContainsString('contact_id', $query['secondary_select']);
    }

    /**
     * @dataProvider dataProviderFieldDefs
     * @param array $defs
     * @covers ::getFieldDefinitions
     */
    public function testGetFieldDefinitionsWithNoFilter($defs)
    {
        $bean = new BeanMockTestObjectName();

        $bean->field_defs = $defs;

        $actual = $bean->getFieldDefinitions();
        $this->assertSameSize($defs, $actual);
        $this->assertSame($defs, $actual);
    }

    /**
     * @dataProvider dataProviderFieldDefs
     * @param array $defs
     * @covers ::getFieldDefinitions
     */
    public function testGetFieldDefinitionsWithFilter($defs)
    {
        $bean = new BeanMockTestObjectName();

        $bean->field_defs = $defs;

        $actual = $bean->getFieldDefinitions('id_name', ['opportunity_id']);
        $this->assertCount(1, $actual);
        $this->assertArrayHasKey('id_name', $actual['opportunity_name']);
    }


    public static function dataProviderFieldDefs()
    {
        return [
            [
                [
                    'opportunity_id' => [
                        'name' => 'opportunity_id',
                    ],
                    'opportunity_name' => [
                        'name' => 'opportunity_name',
                        'id_name' => 'opportunity_id',
                    ],
                    'name' => [
                        'name' => 'name',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFields()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = 'new_unit_test_id';

        $bean->rel_fields_before_value = ['opportunity_id' => 'old_unit_test_id'];

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['add', 'delete', 'resetLoaded'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('delete')
            ->with('unit_test_id', 'old_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->once())
            ->method('add')
            ->with('new_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = [
            'opportunity_id' => [
                'name' => 'opportunity_id',
                'type' => 'id',
            ],
            'opportunity_name' => [
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate',
            ],
            'opportunities' => [
                'name' => 'opportunities',
                'type' => 'link',
            ],
        ];

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['add']['success']);
        $this->assertContains('opportunities', $actual['remove']['success']);
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFieldsDoesNotRemove()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = 'new_unit_test_id';

        $bean->rel_fields_before_value = [];

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['add', 'delete', 'resetLoaded'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->never())
            ->method('delete');

        $link2->expects($this->once())
            ->method('add')
            ->with('new_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = [
            'opportunity_id' => [
                'name' => 'opportunity_id',
                'type' => 'id',
            ],
            'opportunity_name' => [
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate',
            ],
            'opportunities' => [
                'name' => 'opportunities',
                'type' => 'link',
            ],
        ];

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['add']['success']);
        $this->assertEmpty($actual['remove']['success']);
    }

    /**
     * @covers ::handle_remaining_relate_fields
     */
    public function testHandleRemainingRelateFieldsDoesNotAdd()
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $bean->id = 'unit_test_id';
        $bean->opportunity_id = '';

        $bean->rel_fields_before_value = ['opportunity_id' => 'old_unit_test_id'];

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['add', 'delete', 'resetLoaded'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->never())
            ->method('add');

        $link2->expects($this->once())
            ->method('delete')
            ->with('unit_test_id', 'old_unit_test_id')
            ->willReturn(true);

        $link2->expects($this->never())
            ->method('resetLoaded');

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities')
            ->willReturn(true);

        $bean->opportunities = $link2;
        $bean->field_defs = [
            'opportunity_id' => [
                'name' => 'opportunity_id',
                'type' => 'id',
            ],
            'opportunity_name' => [
                'name' => 'opportunity_name',
                'id_name' => 'opportunity_id',
                'link' => 'opportunities',
                'save' => true,
                'type' => 'relate',
            ],
            'opportunities' => [
                'name' => 'opportunities',
                'type' => 'link',
            ],
        ];

        $actual = SugarTestReflection::callProtectedMethod($bean, 'handle_remaining_relate_fields');

        $this->assertContains('opportunities', $actual['remove']['success']);
        $this->assertEmpty($actual['add']['success']);
    }

    /**
     * @test
     */
    public function auditChangesAreNotDuplicatedAfterResave()
    {
        $contact = SugarTestContactUtilities::createContact(null, [
            'first_name' => null,
            'last_name' => 'Doe',
        ]);

        $this->assertCount(0, $this->getFieldAuditRecords($contact, 'first_name'));
        $this->assertCount(1, $this->getFieldAuditRecords($contact, 'last_name'));

        $contact->first_name = 'John';
        $contact->save();

        $this->assertCount(1, $this->getFieldAuditRecords($contact, 'first_name'));
        $this->assertCount(1, $this->getFieldAuditRecords($contact, 'last_name'));
    }

    /**
     * @test
     */
    public function selfReferenceAsParent()
    {
        $task = SugarTestTaskUtilities::createTask();
        $task->parent_type = $task->module_name;
        $task->parent_id = $task->id;
        $task->save();

        /** @var Task $retrievedTask */
        $retrievedTask = BeanFactory::retrieveBean($task->module_name, $task->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals($task->name, $retrievedTask->parent_name);
    }

    /**
     * @test
     */
    public function selfReferencingRelateField()
    {
        $acc = SugarTestAccountUtilities::createAccount();
        $acc->parent_id = $acc->id;
        $acc->save();

        $retrievedAcc = BeanFactory::retrieveBean($acc->module_name, $acc->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals($retrievedAcc->name, $retrievedAcc->parent_name);
    }

    /**
     * @covers ::getErasedFields
     */
    public function testGetErasedFields()
    {
        // The current user must be an admin to close a Data Privacy record.
        $isAdmin = $GLOBALS['current_user']->isAdmin();
        $GLOBALS['current_user']->setAdmin(true);

        $bean = SugarTestContactUtilities::createContact();

        $dp = BeanFactory::newBean('DataPrivacy');
        $dp->name = 'Erasure Test';
        $dp->type = 'Request to Erase Information';
        $dp->status = 'Open';
        $dp->priority = 'Low';
        $dp->assigned_user_id = $GLOBALS['current_user']->id;
        $dp->date_opened = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->date_due = $GLOBALS['timedate']->getDatePart($GLOBALS['timedate']->nowDb());
        $dp->save();

        // Link the contact to the Data Privacy record.
        $dp->load_relationship('contacts');
        $dp->contacts->add($bean);

        // Must re-retrieve the Data Privacy record so that it's fetched_row array contains its current status before we
        // can change its status.
        $dp->retrieve();

        // Set the context and subject for Data Privacy.
        $context = Container::getInstance()->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');

        // Erase the fields.
        $dp->status = 'Closed';
        $dp->fields_to_erase = '{"contacts":{"' . $bean->id . '":["first_name","phone_mobile"]}}';
        $dp->save();
        $GLOBALS['db']->commit();

        $actual = $bean->getErasedFields();

        $this->assertEquals(['first_name', 'phone_mobile'], $actual);

        // Tear down.
        $dp->mark_deleted($dp->id);
        $GLOBALS['current_user']->setAdmin($isAdmin);
    }

    public function noErasedFieldsProvider()
    {
        return [
            'Cases has no PII fields' => [
                'SugarTestCaseUtilities::createCase',
                null,
                null,
            ],
            'erased_fields is not set for the contact but the contact has no erased fields' => [
                'SugarTestContactUtilities::createContact',
                null,
                [],
            ],
        ];
    }

    /**
     * @dataProvider noErasedFieldsProvider
     * @covers ::getErasedFields
     * @param string $testUtilCreateFunction
     * @param null|array $erasedFields
     * @param null|array $expected
     */
    public function testGetErasedFields_NoErasedFields($testUtilCreateFunction, $erasedFields, $expected)
    {
        $bean = call_user_func($testUtilCreateFunction);
        $bean->erased_fields = $erasedFields;

        $actual = $bean->getErasedFields();

        $this->assertSame($expected, $actual);
    }

    private function getFieldAuditRecords(SugarBean $bean, $field)
    {
        /** @var Audit $audit */
        $audit = BeanFactory::newBean('Audit');

        return array_filter($audit->getAuditLog($bean), function (array $row) use ($field) {
            return $row['field_name'] === $field;
        });
    }

    /**
     * @covers ::fill_in_link_field
     */
    public function testFillInLinkField_SetOwnerId()
    {
        $assignedUserId = create_guid();
        $account = SugarTestAccountUtilities::createAccount('', [
            'assigned_user_id' => $assignedUserId,
        ]);
        $contact = SugarTestContactUtilities::createContact();
        $contact->load_relationship('accounts');
        $contact->accounts->add($account);

        $contact = BeanFactory::retrieveBean($contact->module_name, $contact->id, [
            'use_cache' => false,
            'disable_row_level_security' => true,
        ]);
        $fieldDef = $contact->getFieldDefinition('account_id');
        $contact->fill_in_link_field('account_id', $fieldDef);
        $this->assertEquals($account->id, $contact->account_id);
        $this->assertEquals(
            $assignedUserId,
            $contact->account_id_owner,
            'Expected related record owner id to be set along with the related record\'s field value'
        );
    }

    /**
     * @covers ::retrieve
     */
    public function testFetchedRelRowIncludesRelationshipIds()
    {
        $bean = SugarTestAccountUtilities::createAccount();
        $bean->assigned_user_id = $GLOBALS['current_user']->id;
        $bean->save();

        $bean = $bean->retrieve($bean->id);

        $this->assertTrue(array_key_exists('assigned_user_id', $bean->fetched_rel_row));
        $this->assertEquals($GLOBALS['current_user']->id, $bean->fetched_rel_row['assigned_user_id']);
    }

    /**
     * @covers ::getTimeAwareRecalculationNextRun
     * @dataProvider providerGetTimeAwareRecalculationNextRun
     * @throws Exception
     */
    public function testGetTimeAwareRecalculationNextRun($startFromNow, $endFromNow, $expectedField, $expectedMods)
    {
        // Mock the bean to return fake vardef values for 'recalculations' property
        $mockBean = $this->getMockBuilder('SugarBean')
            ->onlyMethods(['getTimeAwareRecalculationsVardefs'])
            ->getMock();
        $mockBean->method('getTimeAwareRecalculationsVardefs')
            ->willReturn([
                [
                    'field' => 'service_start_date',
                ],
                [
                    'field' => 'service_end_date',
                    'modifications' => [
                        '+1 day',
                    ],
                ],
            ]);

        // Set "service_start_date" and "service_end_date" fields on the bean
        // relative to today
        $mockBean->service_start_date = (new SugarDateTime($startFromNow))->asDbDate(false);
        $mockBean->service_end_date = (new SugarDateTime($endFromNow))->asDbDate(false);

        // Get the expected string value for the next run datetime
        $expected = new SugarDateTime($mockBean->$expectedField);
        foreach ($expectedMods as $expectedMod) {
            $expected->modify($expectedMod);
        }
        $expected = $expected->asDb(false);

        // Compare the expected next run datetime to the actual next run datetime
        $actual = SugarTestReflection::callProtectedMethod($mockBean, 'getTimeAwareRecalculationNextRun');
        $this->assertEquals($expected, $actual);
    }

    public function providerGetTimeAwareRecalculationNextRun()
    {
        return [
            [
                '+10 days',
                '+90 days',
                'service_start_date',
                [],
            ],
            [
                '-10 days',
                '+90 days',
                'service_end_date',
                [
                    '+1 day',
                ],
            ],
        ];
    }

    /**
     * @covers ::create_list_count_query
     *
     * PHP 8 warnings before it was fixed:
     * Warning: strtolower() expects parameter 1 to be string, array given
     * Warning: strstr() expects parameter 1 to be string, array given
     * Warning: explode() expects parameter 2 to be string, array given
     * Warning: implode(): Invalid arguments passed in ...
     */
    public function testCreateListCountQuery()
    {
        $bean = new BeanMockTestObjectName();

        $results = $bean->create_list_count_query([
            'SELECT distinct x FROM y UNION ALL SELECT a FROM b',
            'SELECT a FROM b',
        ]);

        $expected = [
            'SELECT count(DISTINCT my_table.id) c FROM y UNION ALL SELECT count(*) c FROM b',
            'SELECT count(*) c FROM b',
        ];

        $this->assertEquals($expected, $results);
    }
}

class BeanMockTestObjectName extends SugarBean
{
    public $table_name = 'my_table';
}

class BeanFunctionFieldsMock extends SugarBean
{
    public function processFunctionFields(SugarBean $bean, array $fields)
    {
        parent::processFunctionFields($bean, $fields);
    }

    public static function toUpper($arg)
    {
        return strtoupper($arg);
    }
}
