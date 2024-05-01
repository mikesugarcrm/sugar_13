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

class Bug25964v2Test extends SOAPTestCase
{
    private $resultId;
    private $c;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/service/v2_1/soap.php';
        parent::setUp();

        $unid = uniqid();

        $contact = new Contact();
        $contact->id = 'c_' . $unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->email1 = 'one@example.com';
        $contact->email2 = 'one_other@example.com';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $this->c = $contact;
        $this->login();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->resultId}'");
        unset($this->c);
        parent::tearDown();
    }

    public function testFindSameContact()
    {
        $contacts_list = [
            [
                [
                    'name' => 'assigned_user_id',
                    'value' => $GLOBALS['current_user']->id,
                ],
                [
                    'name' => 'first_name',
                    'value' => 'testfirst',
                ],
                [
                    'name' => 'last_name',
                    'value' => 'testlast',
                ],
                [
                    'name' => 'email1',
                    'value' => 'one_other@example.com',
                ],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);
        $this->resultId = $result['ids'][0];
        $this->assertEquals($this->c->id, $result['ids'][0], 'did not match contacts');
    }

    public function testDoNotFindSameContact()
    {
        $contacts_list = [
            [
                [
                    'name' => 'assigned_user_id',
                    'value' => $GLOBALS['current_user']->id,
                ],
                [
                    'name' => 'first_name',
                    'value' => 'testfirst',
                ],
                [
                    'name' => 'last_name',
                    'value' => 'testlast',
                ],
                [
                    'name' => 'email1',
                    'value' => 'mytest1@example.com',
                ],
            ],
        ];

        $result = $this->soapClient->set_entries($this->sessionId, 'Contacts', $contacts_list);
        $result = object_to_array_deep($result);
        $this->resultId = $result['ids'][0];
        $this->assertNotEquals($this->c->id, $result['ids'][0], 'did not match contacts');
    }
}
