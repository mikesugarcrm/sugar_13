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
 * @coversDefaultClass \IntegrateRelateApi
 */
class IntegrateRelateApiTest extends TestCase
{
    private const ALTERNATIVE_SYNC_KEY_FIELD_NAME = 'description';

    /**
     * @var RestService
     */
    private $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;

        $GLOBALS['dictionary']['Contact']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = true;
        $GLOBALS['dictionary']['Call']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = true;
    }

    protected function setUp(): void
    {
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestCallUtilities::removeAllCreatedCalls();
    }

    public static function tearDownAfterClass(): void
    {
        unset($GLOBALS['dictionary']['Contact']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key']);
        unset($GLOBALS['dictionary']['Call']['fields'][self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key']);

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
     * @covers ::relateByFields
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testRelateByFields(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        // create a pair to be related
        $contact = $this->getTestContact($syncKeyFieldName);
        $call = $this->getTestCall($syncKeyFieldName);

        // call the API
        $args = [
            'module' => $contact->getModuleName(),
            'link_name' => 'calls',
            'lhs_sync_key_field_value' => $contact->$syncKeyFieldName,
            'rhs_sync_key_field_value' => $call->$syncKeyFieldName,
        ];
        if ($sendSyncKeyFieldName) {
            $args['lhs_sync_key_field_name'] = $syncKeyFieldName;
            $args['rhs_sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateRelateApi();
        $response = $apiClass->relateByFields($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('record', $response);
        $this->assertArrayHasKey('related_record', $response);
        $this->assertEquals($contact->id, $response['record']);
        $this->assertEquals($call->id, $response['related_record']);

        // check actual DB values
        $contact->load_relationship('calls');
        $calls = $contact->calls->getBeans();
        $this->assertArrayHasKey($call->id, $calls);
    }

    /**
     * @group api
     * @covers ::unrelateByFields
     * @dataProvider providerSyncKeyFieldNames
     */
    public function testUnrelateByFields(string $syncKeyFieldName, bool $sendSyncKeyFieldName, bool $exceptionExpected)
    {
        if ($exceptionExpected) {
            $this->expectException(SugarApiExceptionInvalidParameter::class);
        }

        // create a related pair
        $contact = $this->getTestContact($syncKeyFieldName);
        $call = $this->getTestCall($syncKeyFieldName);
        $contact->load_relationship('calls');
        $contact->calls->add($call);

        // call the API
        $args = [
            'module' => $contact->getModuleName(),
            'link_name' => 'calls',
            'lhs_sync_key_field_value' => $contact->$syncKeyFieldName,
            'rhs_sync_key_field_value' => $call->$syncKeyFieldName,
        ];
        if ($sendSyncKeyFieldName) {
            $args['lhs_sync_key_field_name'] = $syncKeyFieldName;
            $args['rhs_sync_key_field_name'] = $syncKeyFieldName;
        }
        $apiClass = new IntegrateRelateApi();
        $response = $apiClass->unrelateByFields($this->serviceMock, $args);

        // check the response
        $this->assertIsArray($response);
        $this->assertArrayHasKey('record', $response);
        $this->assertArrayHasKey('related_record', $response);
        $this->assertEquals($contact->id, $response['record']);
        $this->assertEquals($call->id, $response['related_record']);

        // check actual DB values
        $contact->load_relationship('calls');
        $calls = $contact->calls->getBeans();
        $this->assertArrayNotHasKey($call->id, $calls);
    }

    private function getTestContact(string $syncFieldName = 'sync_key'): Contact
    {
        $contact = SugarTestContactUtilities::createContact('', [$syncFieldName => uniqid('SK_')]);
        $contact->field_defs[self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = 1;

        return $contact;
    }

    private function getTestCall(string $syncFieldName = 'sync_key'): Call
    {
        $call = SugarTestCallUtilities::createCall();
        $call->field_defs[self::ALTERNATIVE_SYNC_KEY_FIELD_NAME]['is_sync_key'] = 1;
        $call->$syncFieldName = uniqid('SK_');
        $call->save();

        return $call;
    }
}
