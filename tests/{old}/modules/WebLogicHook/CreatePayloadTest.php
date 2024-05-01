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
 * @coversDefaultClass CreatePayload
 */
class CreatePayloadTest extends TestCase
{
    /** @var WebLogicHook */
    private $hookMock;
    private $afterRelAdd = [
        'id' => '10b22156-aaaa-11ec-8412-0242ac120006',
        'related_id' => '606b71de-aaaa-11ec-adb6-0242ac120006',
        'name' => 'aaa',
        'related_name' => 'qwe',
        'module' => 'Accounts',
        'related_module' => 'Tags',
        'link' => 'tag_link',
        'relationship' => 'accounts_tags',
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function setUp(): void
    {
        $this->hookMock = $this->createMock(WebLogicHook::class);
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterSave()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $arguments = [
            'isUpdate' => true,
            'dataChanges' => [
                'name' => [
                    'field_name' => 'name',
                    'data_type' => 'varchar',
                    'before' => 'aaa',
                    'after' => 'aaa2',
                ],
                'date_modified' => [
                    'field_name' => 'date_modified',
                    'data_type' => 'datetime',
                    'before' => '2022-03-23 13:08:47',
                    'after' => '2022-03-23 13:21:51',
                ],
                'modified_by_name' => [
                    'field_name' => 'modified_by_name',
                    'data_type' => 'relate',
                    'before' => 'Administrator',
                    'after' => 'admin',
                ],
            ],
            'stateChanges' => [
                'name' => [
                    'field_name' => 'name',
                    'data_type' => 'varchar',
                    'before' => 'aaa',
                    'after' => 'aaa2',
                ],
                'date_modified' => [
                    'field_name' => 'date_modified',
                    'data_type' => 'datetime',
                    'before' => '2022-03-23 13:08:47',
                    'after' => '2022-03-23 13:21:51',
                ],
                'modified_by_name' => [
                    'field_name' => 'modified_by_name',
                    'data_type' => 'relate',
                    'before' => 'Administrator',
                    'after' => 'admin',
                ],
            ],
        ];
        $result = (new CreatePayload($this->hookMock))->getPayload($account, 'after_save', $arguments);
        $this->assertEquals('Account', $result['bean']);
        $this->assertEquals('after_save', $result['event']);
        $this->assertEquals($account->id, $result['data']['id']);

        $this->assertEquals($arguments['isUpdate'], $result['isUpdate']);
        $this->assertEqualsCanonicalizing($arguments['dataChanges'], $result['dataChanges']);
        $this->assertEquals($arguments['stateChanges'], $result['stateChanges']);

        $fieldsReference = array_merge(['bean' => true, 'data' => true, 'event' => true], $arguments);
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterDelete()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $result = (new CreatePayload($this->hookMock))->getPayload($account, 'after_delete', ['id' => $account->id]);
        $this->assertEquals('Account', $result['bean']);
        $this->assertEquals('after_delete', $result['event']);
        $this->assertEquals($account->id, $result['data']['id']);
        $this->assertEquals($account->id, $result['id']);
        $fieldsReference = ['bean' => true, 'data' => true, 'event' => true, 'id' => true];
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterRelationshipAdd()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $arguments = [
            'id' => $account->id,
            'related_id' => 'fake_id',
            'name' => $account->name,
            'related_name' => 'fake_tag_name',
            'module' => 'Accounts',
            'related_module' => 'Tags',
            'link' => 'tag_link',
            'relationship' => 'accounts_tags',
        ];
        $result = (new CreatePayload($this->hookMock))->getPayload($account, 'after_relationship_add', $arguments);
        $this->assertEquals('Account', $result['bean']);
        $this->assertEquals('after_relationship_add', $result['event']);
        $this->assertEquals($account->id, $result['data']['id']);

        foreach ($arguments as $name => $value) {
            $this->assertEquals($value, $result[$name]);
        }

        $fieldsReference = array_merge(['bean' => true, 'data' => true, 'event' => true], $arguments);
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterRelationshipDelete()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $arguments = [
            'id' => $account->id,
            'related_id' => 'fake_id',
            'name' => $account->name,
            'related_name' => 'fake_name',
            'module' => 'Accounts',
            'related_module' => 'Users',
            'link' => 'created_by_link',
            'relationship' => 'accounts_created_by',
        ];
        $result = (new CreatePayload($this->hookMock))->getPayload($account, 'after_relationship_delete', $arguments);
        $this->assertEquals('Account', $result['bean']);
        $this->assertEquals('after_relationship_delete', $result['event']);
        $this->assertEquals($account->id, $result['data']['id']);

        foreach ($arguments as $name => $value) {
            $this->assertEquals($value, $result[$name]);
        }

        $fieldsReference = array_merge(['bean' => true, 'data' => true, 'event' => true], $arguments);
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterLogin()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $result = (new CreatePayload($this->hookMock))->getPayload($user, 'after_login', []);
        $this->assertEquals('User', $result['bean']);
        $this->assertEquals('after_login', $result['event']);
        $this->assertEquals($user->id, $result['data']['id']);
        $fieldsReference = ['bean' => true, 'data' => true, 'event' => true];
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterLogout()
    {
        $result = (new CreatePayload($this->hookMock))->getPayload(BeanFactory::newBean('Users'), 'after_logout', []);
        $this->assertEquals('User', $result['bean']);
        $this->assertEquals('after_logout', $result['event']);
        $this->assertArrayNotHasKey('id', $result['data']);
        $fieldsReference = ['bean' => true, 'data' => true, 'event' => true];
        $keyDiff = array_diff_key($result, $fieldsReference);
        $this->assertEmpty($keyDiff, 'payload has extra fields: ' . implode(',', array_keys($keyDiff)));
    }

    /**
     * @covers ::getPayload
     * @return void
     */
    public function testAfterLoginFailed()
    {
        $this->markTestIncomplete('Hook is not triggered, implement test when it is fixed');
    }
}
