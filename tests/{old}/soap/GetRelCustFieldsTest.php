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


require_once 'modules/DynamicFields/FieldCases.php';

/**
 * Bug #58138
 * Web Service get_relationships doesn't work with related_module_query parameter when using custom fields
 *
 * @author mgusev@sugarcrm.com
 * @ticked 58138
 */
class GetRelCustFieldsTest extends SOAPTestCase
{
    /**
     * @var SoapClient
     */
    protected $soap = null;

    /**
     * @var DynamicField
     */
    protected $dynamicField = null;

    /**
     * @var TemplateText
     */
    protected $field = null;

    /**
     * @var Contact
     */
    protected $module = null;

    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Contact
     */
    protected $contact = null;

    /**
     * Creating new field, account, contact with filled custom field, relationship between them
     */
    protected function setUp(): void
    {
        parent::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->field = get_widget('varchar');
        $this->field->id = 'Contactstest_c';
        $this->field->name = 'test_c';
        $this->field->type = 'varchar';
        $this->field->len = 255;
        $this->field->importable = 'true';

        $this->field->label = '';

        $this->module = new Contact();

        $this->dynamicField = new DynamicField('Contacts');

        $this->dynamicField->setup($this->module);
        $this->dynamicField->addFieldObject($this->field);

        SugarTestHelper::setUp('dictionary');
        $GLOBALS['reload_vardefs'] = true;

        $this->account = SugarTestAccountUtilities::createAccount();

        $this->contact = SugarTestContactUtilities::createContact();
        $this->contact->account_id = $this->account->id;
        $this->contact->test_c = 'test value' . $this->account->id;
        $this->contact->load_relationship('accounts');
        $this->contact->accounts->add($this->account->id);
        $this->contact->save();

        $GLOBALS['db']->commit();
    }

    /**
     * Removing field, account, contact
     */
    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();

        $this->dynamicField->deleteField($this->field);

        SugarTestHelper::tearDown();
        $GLOBALS['reload_vardefs'] = false;
    }

    /**
     * Test asserts that contact can be found by custom field
     * @param string $url - Soap service url
     * @group 58138
     * @dataProvider dataProvider
     */
    public function testGetRelationships($url)
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . $url;
        $this->login();
        $actualObj = $this->soapClient->get_relationships(
            $this->sessionId,
            'Accounts',
            $this->account->id,
            'contacts',
            "contacts_cstm.test_c = '" . $this->contact->test_c . "'",
            ['id', 'test_c'],
            [],
            '0'
        );
        $actual = get_object_vars($actualObj);

        $this->assertIsArray($actual, 'Soap call returned incorrect response');
        $this->assertNotEmpty($actual['entry_list'], 'get_relationships did not return any data.');

        $actualById = array_combine(array_column($actual['entry_list'], 'id'), $actual['entry_list']);
        $this->assertArrayHasKey($this->contact->id, $actualById, 'get_relationships returned incorrect Contact.');
    }

    public static function dataProvider()
    {
        return [
            'v2' => ['/service/v2/soap.php'],
            'v2_1' => ['/service/v2_1/soap.php'],
            'v3' => ['/service/v3/soap.php'],
            'v3_1' => ['/service/v3_1/soap.php'],
            'v4' => ['/service/v4/soap.php'],
            'v4_1' => ['/service/v4_1/soap.php'],
        ];
    }
}
