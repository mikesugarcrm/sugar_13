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

class RetrieveEmailFieldsTest extends SOAPTestCase
{
    public $acc;
    public $email_id;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();
        $this->login();
    }

    public function testGetEmailAddressFields()
    {
        $this->acc = SugarTestAccountUtilities::createAccount();
        $result = $this->soapClient->set_entry($this->sessionId, 'Emails', [['name' => 'assigned_user_id', 'value' => $GLOBALS['current_user']->id], ['name' => 'from_addr_name', 'value' => 'test@test.com'], ['name' => 'parent_type', 'value' => 'Accounts'], ['name' => 'parent_id', 'value' => $this->acc->id], ['name' => 'description', 'value' => 'test'], ['name' => 'name', 'value' => 'Test Subject']]);
        $result = object_to_array_deep($result);
        $this->email_id = $result['id'];

        $result = $this->soapClient->get_entry_list($this->sessionId, 'Emails', "emails.id='" . $this->email_id . "'", '', 0, ['id', 'from_addr_name', 'to_addrs_names'], 10, 0);
        $result = object_to_array_deep($result);

        $this->assertEquals('from_addr_name', $result['entry_list'][0]['name_value_list'][1]['name']);
        $this->assertEquals('test@test.com', $result['entry_list'][0]['name_value_list'][1]['value']);
    }

    public function testGetEmailModuleFields()
    {
        $result = $this->soapClient->get_module_fields($this->sessionId, 'Emails');
        $result = object_to_array_deep($result);
        $foundFromAddrsName = false;
        foreach ($result['module_fields'] as $field) {
            if ($field['name'] == 'from_addr_name') {
                $foundFromAddrsName = true;
            }
        }
        $this->assertTrue($foundFromAddrsName, 'Did not find from_addr_name');
    }
}
