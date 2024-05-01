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

/**
 * @ticket 41296
 */
class Bug41296Test extends SOAPTestCase
{
    /**
     * @var mixed
     */
    //@codingStandardsIgnoreStart
    public $_resultId;
    //@codingStandardsIgnoreEnd
    public $c = null;
    public $c2 = null;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();

        $unid = uniqid();
        $time = date('Y-m-d H:i:s');

        $contact = new Contact();
        $contact->id = 'c_' . $unid;
        $contact->first_name = 'testfirst';
        $contact->last_name = 'testlast';
        $contact->email1 = 'one@example.com';
        $contact->email2 = 'one_other@example.com';
        $contact->new_with_id = true;
        $contact->disable_custom_fields = true;
        $contact->save();
        $GLOBALS['db']->commit();
        $this->c = $contact;
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->c->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->_resultId}'");
        unset($this->c);
        parent::tearDown();
    }

    public function testCreateNewContactWithNoEmail()
    {
        $this->login();

        $result = $this->soapClient->set_entries(
            $this->sessionId,
            'Contacts',
            [
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
                ],
            ],
        );
        $result = object_to_array_deep($result);
        $this->_resultId = $result['ids'][0];
        $this->assertNotEquals($this->c->id, $result['ids'][0], 'Contacts should not match');
    }
}
