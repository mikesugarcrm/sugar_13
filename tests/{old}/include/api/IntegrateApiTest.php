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
 * @coversDefaultClass \IntegrateApi
 */
class IntegrateApiTest extends TestCase
{
    private const ALTERNATIVE_SYNC_KEY_FIELD_NAME = 'description';

    /**
     * @var RestService
     */
    protected $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;

        $GLOBALS['dictionary']['Contact']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = true;
        $GLOBALS['dictionary']['Call']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = true;
        SugarBean::clearLoadedDef('Contact');
        SugarBean::clearLoadedDef('Call');
    }

    protected function setUp(): void
    {
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['dictionary']['Contact']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key']);
        unset($GLOBALS['dictionary']['Call']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key']);
        SugarBean::clearLoadedDef('Contact');
        SugarBean::clearLoadedDef('Call');

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function providerSyncKeyFieldNames()
    {
        // <field_name, send_sync_key, exception_expected>
        return [
            /* Valid scenarios */
            // CASE #1: "sync_key" sent
            ['sync_key', true, false],

            // CASE #2: "sync_key" didn't send
            ['sync_key', false, false],

            // CASE #3: an alternative sync_key field name sent
            [self::ALTERNATIVE_SYNC_KEY_FIELD_NAME, true, false],

            /* Invalid scenarios */
            // CASE #4: an alternative sync_key field name sent,
            // but the field does not have "is_sync_key" flag set in "true"
            ['date_modified', true, true], // non-sync_key field - invalid scenario
        ];
    }

    /**
     * @group api
     * @covers ::getByField
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testGetByField(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        $bean = $this->getTestBean($syncKeyFieldName);

        $module = $bean->getModuleName();
        $recordId = $bean->id;

        // call the API
        $args = [
            'module' => $module,
            'sync_key_field_value' => $bean->$syncKeyFieldName,
        ];
        if ($sendSyncKeyFieldName) {
            $args['sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateApi();
        $response = $apiClass->getByField($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertEquals($recordId, $response['id']);
    }

    /**
     * @group api
     * @covers ::deleteByField
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testDeleteByField(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        $bean = $this->getTestBean($syncKeyFieldName);

        $module = $bean->getModuleName();
        $recordId = $bean->id;

        // call the API
        $args = [
            'module' => $module,
            'sync_key_field_value' => $bean->$syncKeyFieldName,
        ];
        if ($sendSyncKeyFieldName) {
            $args['sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateApi();
        $response = $apiClass->deleteByField($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertEquals($recordId, $response['id']);

        $bean->retrieve();
        $this->assertEquals(1, $bean->deleted);
    }

    /**
     * @group api
     * @covers ::upsertByField
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testUpsertByFieldCreateScenario(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        $uniqueSyncKey = uniqid('SK_');

        // call the API
        $args = [
            'module' => 'Contacts',
            'sync_key_field_value' => $uniqueSyncKey,
        ];
        if ($sendSyncKeyFieldName) {
            $args['sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateApi();
        $response = $apiClass->upsertByField($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('record', $response);

        // add the created record to the "to-be-removed" list
        SugarTestContactUtilities::setCreatedContact([$response['record']]);

        // assert that the record exists in the DB with correct sync_key
        $contact = BeanFactory::getBean('Contacts', $response['record']);
        $this->assertEquals($uniqueSyncKey, $contact->$syncKeyFieldName);
    }

    /**
     * @group api
     * @covers ::upsertByField
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testUpsertByFieldUpdateScenario(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        $bean = $this->getTestBean($syncKeyFieldName);
        $uniqueSyncKey = $bean->$syncKeyFieldName;

        // call the API
        $args = [
            'module' => $bean->getModuleName(),
            'sync_key_field_value' => $uniqueSyncKey,
            'last_name' => 'UPDATED_TEST_NAME',
        ];
        if ($sendSyncKeyFieldName) {
            $args['sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateApi();
        $response = $apiClass->upsertByField($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('record', $response);
        $this->assertEquals($bean->id, $response['record']);

        $bean->retrieve();
        $this->assertEquals($uniqueSyncKey, $bean->$syncKeyFieldName);
        $this->assertEquals('UPDATED_TEST_NAME', $bean->last_name);
    }

    /**
     * @group api
     * @covers ::setSyncKey
     */
    public function testSetSyncKey()
    {
        $syncKeyFieldName = 'sync_key';
        $bean = $this->getTestBean($syncKeyFieldName);
        $module = $bean->getModuleName();
        $recordId = $bean->id;
        $uniqueSyncKey = uniqid('SK_');

        // call the API
        $args = [
            'module' => $module,
            'record_id' => $recordId,
            'sync_key_field_name' => $syncKeyFieldName,
            'sync_key_field_value' => $uniqueSyncKey,
        ];
        $apiClass = new IntegrateApi();
        $response = $apiClass->setSyncKey($this->serviceMock, $args);

        // check the response
        $this->assertArrayHasKey('success', $response);
        $this->assertTrue($response['success']);

        // check the actual DB value
        $bean = BeanFactory::getBean($module, $recordId, ['use_cache' => false]);
        $this->assertEquals($uniqueSyncKey, $bean->$syncKeyFieldName);
    }

    /**
     * @group api
     * @covers ::setSyncKey
     */
    public function testSetSyncKeyInvalidField()
    {
        // update
        $args = [
            'module' => 'Users',
            'record_id' => '1',
            'sync_key_field_name' => 'is_admin',
            'sync_key_field_value' => '1',
        ];
        $apiClass = new IntegrateApi();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $apiClass->setSyncKey($this->serviceMock, $args);
    }

    private function getTestBean(string $syncFieldName = 'sync_key'): Contact
    {
        return SugarTestContactUtilities::createContact('', [$syncFieldName => uniqid('SK_')]);
    }
}
