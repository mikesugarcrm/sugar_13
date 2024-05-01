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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\DataPrivacy\Erasure\FieldList;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use Sugarcrm\Sugarcrm\Security\Context;
use Sugarcrm\Sugarcrm\Security\Subject\ApiClient\Rest as RestApiClient;
use Sugarcrm\Sugarcrm\Security\Subject\User;

/**
 * @coversDefaultClass ModuleApi
 * @group ApiTests
 */
class ModuleApiTest extends TestCase
{
    public $accounts;

    /**
     * @var ModuleApi
     */
    public $moduleApi;
    public $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        // load up the unifiedSearchApi for good times ahead
        $this->moduleApi = new ModuleApi();
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'ModulaApiTest setUp Account';
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->billing_address_city = 'Cupertino';
        $account->billing_address_country = 'USA';
        $account->googleplus = 'info@sugarcrm.com';
        $account->save();
        $this->accounts[] = $account;
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarACL::resetACLs();
    }

    public static function tearDownAfterClass(): void
    {
        // delete the bunch of accounts crated
        $GLOBALS['db']->query("DELETE FROM accounts WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'");

        SugarTestAccountUtilities::deleteM2MRelationships('contacts');
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();

        SugarTestHelper::tearDown();
    }

    public function testGetPiiFields()
    {
        $args = [];
        $container = Container::getInstance();
        $context = $container->get(Context::class);
        $subject = new User($GLOBALS['current_user'], new RestApiClient());
        $context->activateSubject($subject);
        $context->setAttribute('platform', 'base');
        $contact = SugarTestContactUtilities::createContact(null, ['phone_mobile' => '(111)222-2222']);

        $args['module'] = 'Contacts';
        $args['record'] = $contact->id;

        $moduleApi = new ModuleApi();

        $result = $moduleApi->getPiiFields($this->serviceMock, $args);
        $this->assertArrayHasKey('fields', $result, 'Does not have expected key - fields.');
        $this->assertArrayHasKey('_acl', $result, 'Does not have expected key -  _acl.');

        $fieldsByName = array_combine(array_column($result['fields'], 'field_name'), $result['fields']);
        $this->assertArrayHasKey('phone_mobile', $fieldsByName, 'phone_mobile field not returned.');
        $this->assertEquals(
            $GLOBALS['current_user']->last_name,
            $fieldsByName['phone_mobile']['source']['subject']['last_name'],
            'Expected formatted subject not returned for phone_mobile.'
        );
        $this->assertEquals(
            $contact->phone_mobile,
            $fieldsByName['phone_mobile']['value'],
            'Expected phone_mobile value not returned.'
        );
        $this->assertArrayHasKey('email', $fieldsByName, 'email field not returned.');
        $this->assertEquals(
            $GLOBALS['current_user']->last_name,
            $fieldsByName['email']['source']['subject']['last_name'],
            'Expected formatted subject not returned for email.'
        );
        $this->assertEquals(
            $contact->emailAddress->addresses[0]['email_address'],
            $fieldsByName['email']['value']['email_address'],
            'Expected email address not returned.'
        );
    }

    public function testGetPiiFieldsErasedFields()
    {
        $args = [];
        $contact = SugarTestContactUtilities::createContact();

        $list = FieldList::fromArray(['phone_mobile']);

        $contact->erase($list, false);

        // this shouldn't be needed after BR-5932 is resolved
        BeanFactory::clearCache();
        $args['module'] = 'Contacts';
        $args['record'] = $contact->id;
        $args['erased_fields'] = true;

        $moduleApi = new ModuleApi();
        $result = $moduleApi->getPiiFields($this->serviceMock, $args);
        $this->assertArrayHasKey('_erased_fields', $result, 'Does not have expected key -  _erased_fields.');
        $this->assertContains('phone_mobile', $result['_erased_fields'], 'Expected erased field not returned');
    }

    public function testGetPiiFieldsEmailWithNoValue()
    {
        $args = [];
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'ModulaApiTest setUp Account';
        $account->save();
        $args['module'] = 'Accounts';
        $args['record'] = $account->id;

        $moduleApi = new ModuleApi();
        $result = $moduleApi->getPiiFields($this->serviceMock, $args);

        $this->assertNotEmpty($result, 'Did not fetch any Pii fields.');
        $fieldsByName = array_combine(array_column($result['fields'], 'field_name'), $result['fields']);
        $this->assertArrayHasKey('email', $fieldsByName, 'Expected email entry not returned.');
        $this->assertNull($fieldsByName['email']['value'], 'Expected email entry with null value.');
    }

    public function testGetPiiFieldsEmailWithNoPermission()
    {
        $args = [];
        $contact = SugarTestContactUtilities::createContact();

        $args['module'] = 'Contacts';
        $args['record'] = $contact->id;

        SugarACL::$acls['Contacts'][] = new TestSugarACLEmailAddress();

        $moduleApi = new ModuleApi();
        $result = $moduleApi->getPiiFields($this->serviceMock, $args);

        $this->assertNotEmpty($result, 'Did not fetch any Pii fields.');
        $fieldsByName = array_combine(array_column($result['fields'], 'field_name'), $result['fields']);
        $this->assertArrayHasKey('email', $fieldsByName, 'Expected email entry not returned.');
        $this->assertNull($fieldsByName['email']['value'], 'Expected email entry with null value.');
    }

    // test set favorite
    public function testSetFavorite()
    {
        $result = $this->moduleApi->setFavorite(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id]
        );
        $this->assertTrue((bool)$result['my_favorite'], 'Was not set to true');

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was not auto-followed when marked as favorite');

        return $this->accounts[0];
    }

    /**
     * @depends testSetFavorite
     */
    public function testRemoveFavorite(Account $account)
    {
        $result = $this->moduleApi->unsetFavorite(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $account->id]
        );

        $this->assertArrayHasKey('my_favorite', $result, 'API response does not contain "my_favorite" key');
        $this->assertEmpty($result['my_favorite'], 'Bean was not removed from favorites');

        $this->assertArrayHasKey('following', $result, 'API response does not contain "following" key');
        $this->assertNotEmpty($result['following'], 'Bean was auto-unfollowed when removed from favorites');
    }

    // test set favorite of deleted record
    public function testSetFavoriteDeleted()
    {
        $this->accounts[0]->mark_deleted($this->accounts[0]->id);
        $this->expectException(SugarApiExceptionNotFound::class);
        $this->expectExceptionMessage("Could not find record: {$this->accounts[0]->id} in module: Accounts");
        $result = $this->moduleApi->setFavorite(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id]
        );
    }

    // test remove favorite of deleted record
    public function testRemoveFavoriteDeleted()
    {
        $result = $this->moduleApi->setFavorite(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id]
        );
        $this->assertTrue((bool)$result['my_favorite'], 'Was not set to true');

        $this->accounts[0]->deleted = 1;
        $this->accounts[0]->save();
        $this->expectException(SugarApiExceptionNotFound::class);
        $this->expectExceptionMessage("Could not find record: {$this->accounts[0]->id} in module: Accounts");

        $result = $this->moduleApi->setFavorite(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id]
        );
    }

    // test set my_favorite on bean
    public function testSetFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id, 'my_favorite' => true]
        );
        $this->assertTrue((bool)$result['my_favorite'], 'Was not set to true');
    }

    // test remove my_favorite on bean
    public function testRemoveFavoriteOnBean()
    {
        $result = $this->moduleApi->updateRecord(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $this->accounts[0]->id, 'my_favorite' => true]
        );
        $this->assertTrue((bool)$result['my_favorite'], 'Was not set to true');

        $result = $this->moduleApi->updateRecord(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'record' => $this->accounts[0]->id,
                'my_favorite' => false,
            ]
        );
        $this->assertFalse((bool)$result['my_favorite'], 'Was not set to False');
    }

    public function testCreate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id]);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Test Account', $result['name']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertEquals('Test Account', $account->name);
    }

    public function testprocessAfterCreateOperations_afterSaveOperationSpecified_copiesRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = [
            'copy_rel_from' => [
                'contacts',
            ],
        ];

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            [
                'module' => 'Accounts',
                'after_create' => [
                    'copy_rel_from' => $accountBean->id,
                ],
            ],
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(1, safeCount($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromVarDefNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            [
                'module' => 'Accounts',
                'after_create' => [
                    'copy_rel_from' => $accountBean->id,
                ],
            ],
            $newAccountBean
        );

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, safeCount($newAccountBean->contacts->beans));
    }

    public function testprocessAfterCreateOperations_copyRelFromUrlParameterNotSpecified_doesNotCopyRelationships()
    {
        $accountBean = SugarTestAccountUtilities::createAccount();
        $contactBean = SugarTestContactUtilities::createContact();

        $accountBean->load_relationship('contacts');
        $accountBean->contacts->add($contactBean);

        $newAccountBean = SugarTestAccountUtilities::createAccount();

        $GLOBALS['dictionary']['Account']['after_create'] = [
            'copy_rel_from' => [
                'contacts',
            ],
        ];

        $moduleApi = new ModuleApiTestMock();
        $moduleApi->processAfterCreateOperationsMock(
            [
                'module' => 'Accounts',
            ],
            $newAccountBean
        );

        unset($GLOBALS['dictionary']['Account']['after_create']);

        $newAccountBean->load_relationship('contacts');
        $newAccountBean->contacts->getBeans();

        $this->assertEquals(0, safeCount($newAccountBean->contacts->beans));
    }

    public function testUpdate()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id]);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $id = $result['id'];

        $result = $this->moduleApi->updateRecord(
            $this->serviceMock,
            ['module' => 'Accounts', 'record' => $id, 'name' => 'Changed Account']
        );
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($id, $result['id']);

        $account = BeanFactory::newBean('Accounts');
        $account->retrieve($result['id']);
        $this->assertEquals('Changed Account', $account->name);
    }

    public function testUpdateNonConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        $dm = $timedate->fromIso($result['date_modified']);

        $result = $this->moduleApi->updateRecord(
            $this->serviceMock,
            [
                'module' => 'Accounts',
                'record' => $id,
                'name' => 'Changed Account',
                '_headers' => ['X_TIMESTAMP' => $timedate->asIso($dm)],
            ]
        );
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals($id, $result['id']);
    }

    public function testUpdateConflict()
    {
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account',
            'assigned_user_id' => $GLOBALS['current_user']->id,
        ]);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $id = $result['id'];
        $timedate = TimeDate::getInstance();
        // change modified data to not match the record
        $dm = $timedate->fromIso($result['date_modified'])->get('-1 minute');

        try {
            $this->moduleApi->updateRecord($this->serviceMock, [
                'module' => 'Accounts',
                'record' => $id,
                'name' => 'Changed Account',
                '_headers' => ['X_TIMESTAMP' => $timedate->asIso($dm)],
            ]);
        } catch (SugarApiExceptionEditConflict $e) {
            $this->assertNotEmpty($e->extraData);
            $this->arrayHasKey('record');
            $this->assertEquals('Test Account', $e->extraData['record']['name']);

            return;
        }

        $this->fail('Expected a SugarApiExceptionEditConflict to be thrown');
    }

    public function testViewNoneCreate()
    {
        // setup ACL
        $rejectacl = $this->createPartialMock('SugarACLStatic', ['checkAccess']);
        $rejectacl->expects($this->any())->method('checkAccess')->will($this->returnCallback(function ($module, $view, $context) {
            if ($module == 'Accounts' && $view == 'view') {
                return false;
            }
            return true;
        }));
        SugarACL::setACL('Accounts', [$rejectacl]);
        // create a record
        $result = $this->moduleApi->createRecord($this->serviceMock, ['module' => 'Accounts', 'name' => 'Test Account', 'assigned_user_id' => $GLOBALS['current_user']->id]);
        // verify only id returns
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayNotHasKey('name', $result);

        // cleanup ACL mock
        SugarACL::resetACLs('Accounts');
    }

    /**
     * Integration test returned fields based on fields list and/or view parameter
     * @dataProvider providerTestModuleReturnedField
     */
    public function testModuleReturnedFields($args, $expected, $suppressed)
    {
        $args['module'] = 'Accounts';
        $args['record'] = $this->accounts[0]->id;

        $reply = $this->moduleApi->retrieveRecord($this->serviceMock, $args);

        foreach ($expected as $field) {
            $this->assertArrayHasKey(
                $field,
                $reply,
                "Expected field $field not present"
            );
        }

        foreach ($suppressed as $field) {
            $this->assertArrayNotHasKey(
                $field,
                $reply,
                "Field $field should not be present"
            );
        }
    }

    public function providerTestModuleReturnedField()
    {
        return [

            // field list only
            [
                [
                    'fields' => 'name,billing_address_country',
                ],
                [
                    'name',
                    'billing_address_country',
                ],
                [
                    'googleplus',
                ],
            ],

            // view only
            [
                [
                    'view' => 'record',
                ],
                [
                    'name',
                    'billing_address_country',
                ],
                [
                    'googleplus',
                ],
            ],

            // field list and view combined
            [
                [
                    'fields' => 'name,billing_address_country',
                    'view' => 'record',
                ],
                [
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                ],
                [
                    'googleplus',
                ],
            ],

            // nothing specified - expecting all fields
            [
                [],
                [
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                    'googleplus',
                ],
                [],
            ],
        ];
    }

    public function testGetLoadedAndFormattedBean()
    {
        $account = $this->getMockBuilder('Account')
            ->setMethods(['ACLAccess'])
            ->getMock();
        $account->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(false));
        $account->id = $this->accounts[0]->id;
        $account->date_modified = $this->accounts[0]->date_modified;

        $api = $this->getMockBuilder('ModuleApi')
            ->setMethods(['loadBean'])
            ->getMock();
        $api->expects($this->any())
            ->method('loadBean')
            ->will($this->returnValue($account));

        $data = SugarTestReflection::callProtectedMethod(
            $api,
            'getLoadedAndFormattedBean',
            [
                $this->serviceMock,
                [
                    'module' => 'Accounts',
                    'record' => $this->accounts[0]->id,
                ],
            ]
        );

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data, 'API response does not contain ID');
        $this->assertEquals($data['id'], $account->id, 'API has returned wrong ID');
        $this->assertArrayHasKey('date_modified', $data, 'API response does not contain last modification date');
        $this->assertArrayHasKey('_acl', $data, 'API response does not contain ACL data');
        $this->assertArrayNotHasKey('name', $data, 'API response contains should not contain "name" field');
    }

    /**
     * @dataProvider getRelatedRecordArgumentsSuccessProvider
     */
    public function testGetRelatedRecordArgumentsSuccess(array $fieldDefs, array $args, $action, array $expected)
    {
        $actual = $this->getRelatedRecordArguments($fieldDefs, $args, $action);
        $this->assertEquals($expected, $actual);
    }

    public static function getRelatedRecordArgumentsSuccessProvider()
    {
        $fieldDefs = [
            'name' => [
                'type' => 'varchar',
            ],
            'contacts' => [
                'type' => 'link',
            ],
        ];
        $action = 'some-action';

        return [
            'some-data' => [
                $fieldDefs,
                [
                    'name' => [
                        'some-action' => ['a28c2304-f9b6-97ca-f2e6'],
                        'some-other-action' => [
                            [
                                'first_name' => 'Max',
                                'last_name' => 'Jensen',
                            ],
                        ],
                    ],
                    'contacts' => [
                        'some-action' => [
                            '89179177-41d7-311f-90ef',
                            [
                                'id' => '473d122f-0a45-fef6-0138',
                                'role' => 'owner',
                            ],
                        ],
                        'some-other-action' => [
                            [
                                'first_name' => 'Chris',
                                'last_name' => 'Oliver',
                            ],
                        ],
                    ],
                ],
                $action,
                [
                    'contacts' => [
                        '89179177-41d7-311f-90ef',
                        [
                            'id' => '473d122f-0a45-fef6-0138',
                            'role' => 'owner',
                        ],
                    ],
                ],
            ],
            'no-data-for-action' => [
                $fieldDefs,
                [
                    'contacts' => [
                        'some-other-action' => [],
                    ],
                ],
                $action,
                [],
            ],
        ];
    }

    /**
     * @dataProvider getRelatedRecordArgumentsFailureProvider
     */
    public function testGetRelatedRecordArgumentsFailure(array $fieldDefs, array $args, $action)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->getRelatedRecordArguments($fieldDefs, $args, $action);
    }

    public static function getRelatedRecordArgumentsFailureProvider()
    {
        $fieldDefs = [
            'contacts' => [
                'type' => 'link',
            ],
        ];
        $action = 'the-action';

        return [
            'link-data-non-array' => [
                $fieldDefs,
                [
                    'contacts' => 1,
                ],
                $action,
            ],
            'action-data-non-array' => [
                $fieldDefs,
                [
                    'contacts' => [
                        'the-action' => 2,
                    ],
                ],
                $action,
            ],
        ];
    }

    private function getRelatedRecordArguments(array $fieldDefs, array $args, $action)
    {
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldDefinitions'])
            ->getMock();
        $bean->expects($this->once())
            ->method('getFieldDefinitions')
            ->willReturn($fieldDefs);

        return SugarTestReflection::callProtectedMethod(
            $this->moduleApi,
            'getRelatedRecordArguments',
            [$bean, $args, $action]
        );
    }

    public function testLinkRelatedRecords()
    {
        /** @var MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('createRelatedLinks', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->exactly(2))
            ->method('createRelatedLinks')
            ->withConsecutive(
                [
                    $this->serviceMock,
                    [
                        'module' => 'primary-module',
                        'record' => 'primary-id',
                        'link_name' => 'link1',
                        'ids' => [
                            'id11',
                            [
                                'id' => 'id12',
                                'field12' => 'value12',
                            ],
                        ],
                    ],
                ],
                [
                    $this->serviceMock, [
                    'module' => 'primary-module',
                    'record' => 'primary-id',
                    'link_name' => 'link2',
                    'ids' => ['id21'],
                    ],
                ]
            );

        SugarTestReflection::callProtectedMethod(
            $api,
            'linkRelatedRecords',
            [
                $this->serviceMock,
                $bean,
                [
                    'link1' => [
                        'id11',
                        [
                            'id' => 'id12',
                            'field12' => 'value12',
                        ],
                    ],
                    'link2' => ['id21'],
                ],
            ]
        );
    }

    /**
     * @covers ::linkRelatedRecords
     */
    public function testLinkRelatedRecordsWithAnEmptySetOfRecords()
    {
        /** @var MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('createRelatedLinks', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->never())->method('createRelatedLinks');

        SugarTestReflection::callProtectedMethod($api, 'linkRelatedRecords', [
            $this->serviceMock,
            $bean,
            [
                'link1' => [],
                'link2' => [],
            ],
        ]);
    }

    public function testUnlinkRelatedRecords()
    {
        /** @var MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('deleteRelatedLink', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->exactly(2))
            ->method('deleteRelatedLink')
            ->withConsecutive(
                [
                    $this->serviceMock,
                    [
                        'module' => 'primary-module',
                        'record' => 'primary-id',
                        'link_name' => 'link1',
                        'remote_id' => 'id1',
                    ],
                ],
                [
                    $this->serviceMock,
                    [
                        'module' => 'primary-module',
                        'record' => 'primary-id',
                        'link_name' => 'link2',
                        'remote_id' => 'id2',
                    ],
                ]
            );

        SugarTestReflection::callProtectedMethod(
            $api,
            'unlinkRelatedRecords',
            [
                $this->serviceMock,
                $bean,
                [
                    'link1' => ['id1'],
                    'link2' => ['id2'],
                ],
            ]
        );
    }

    public function testCreateRelatedRecords()
    {
        /** @var MockObject $relateRecordApi */
        $api = $this->getApiWithMockedRelateRecordApi('createRelatedBean', $relateRecordApi);
        $bean = $this->getPrimaryBean('primary-module', 'primary-id');

        $relateRecordApi->expects($this->exactly(2))
            ->method('createRelatedBean')
            ->withConsecutive(
                [
                    $this->serviceMock,
                    [
                        'module' => 'primary-module',
                        'record' => 'primary-id',
                        'link_name' => 'link1',
                        'name' => 'Underwater Mining Inc.',
                    ],
                ],
                [
                    $this->serviceMock,
                    [
                        'module' => 'primary-module',
                        'record' => 'primary-id',
                        'link_name' => 'link2',
                        'first_name' => 'Latanya',
                        'last_name' => 'Ollie',
                    ],
                ]
            );

        SugarTestReflection::callProtectedMethod(
            $api,
            'createRelatedRecords',
            [
                $this->serviceMock,
                $bean,
                [
                    'link1' => [
                        ['name' => 'Underwater Mining Inc.'],
                    ],
                    'link2' => [
                        [
                            'first_name' => 'Latanya',
                            'last_name' => 'Ollie',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @param string $method
     * @param MockObject $relateRecordApi
     * @return ModuleApi|MockObject
     */
    private function getApiWithMockedRelateRecordApi($method, &$relateRecordApi)
    {
        $relateRecordApi = $this->getMockBuilder('RelateRecordApi')
            ->setMethods([$method])
            ->getMock();

        $moduleApi = $this->getMockBuilder('ModuleApi')
            ->setMethods(['getRelateRecordApi'])
            ->getMock();
        $moduleApi->expects($this->any())
            ->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        return $moduleApi;
    }

    /**
     * @param string $module
     * @param string $id
     *
     * @return SugarBean
     */
    private function getPrimaryBean($module, $id)
    {
        $bean = new SugarBean();
        $bean->module_name = $module;
        $bean->id = $id;

        return $bean;
    }
}

class ModuleApiTestMock extends ModuleApi
{
    public function processAfterCreateOperationsMock($args, SugarBean $bean)
    {
        $this->processAfterCreateOperations($args, $bean);
    }
}

class TestSugarACLEmailAddress extends SugarACLEmailAddress
{
    /**
     * Return access to the email field as false.
     */
    public function checkAccess($module, $view, $context)
    {
        if ($view != 'field') {
            return true;
        }
        if ($context['field'] != 'email') {
            return true;
        }

        return false;
    }
}
