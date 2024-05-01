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

class Bug40699Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user', [true, true]);
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testGetListViewDataForAccounts()
    {
        $testAccount = SugarTestAccountUtilities::createAccount();

        $emailMan = new EmailMan();
        $emailMan->related_id = $testAccount->id;
        $emailMan->related_type = 'Accounts';

        $filter = [];
        $filter['campaign_name'] = 1;
        $filter['recipient_name'] = 1;
        $filter['recipient_email'] = 1;
        $filter['message_name'] = 1;
        $filter['send_date_time'] = 1;
        $filter['send_attempts'] = 1;
        $filter['in_queue'] = 1;

        $params = [];
        $params['massupdate'] = 1;

        $data = $emailMan->get_list_view_data();
        $this->assertEquals($testAccount->name, $data['RECIPIENT_NAME'], 'Assert that account name was correctly set');
    }


    public function testGetListViewDataForContacts()
    {
        $testContact = SugarTestContactUtilities::createContact();

        $emailMan = new EmailMan();
        $emailMan->related_id = $testContact->id;
        $emailMan->related_type = 'Contacts';

        $filter = [];
        $filter['campaign_name'] = 1;
        $filter['recipient_name'] = 1;
        $filter['recipient_email'] = 1;
        $filter['message_name'] = 1;
        $filter['send_date_time'] = 1;
        $filter['send_attempts'] = 1;
        $filter['in_queue'] = 1;

        $params = [];
        $params['massupdate'] = 1;
        $GLOBALS['current_user']->setPreference('default_locale_name_format', 'f l');

        $contact_name_expected = $testContact->first_name . ' ' . $testContact->last_name;

        $data = $emailMan->get_list_view_data();
        $this->assertEquals($contact_name_expected, $data['RECIPIENT_NAME'], 'Assert that contact name was correctly set');
    }
}
