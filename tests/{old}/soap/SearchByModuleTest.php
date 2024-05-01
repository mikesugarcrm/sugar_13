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
 * Bug #41392
 * Wildcard % searching does not return email addresses when searching with outlook plugin
 *
 * @author mgusev@sugarcrm.com
 * @ticket 41392
 */
class SearchByModuleTest extends SOAPTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test creates new account and tries to find the account by wildcard of its email
     *
     * @group 41392
     */
    public function testSearchByModule()
    {
        $account = new Account();
        $account->name = 'Bug4192Test';
        $account->email1 = 'Bug4192Test@example.com';
        $account->save();

        $this->login();

        $actual = $this->soapClient->search_by_module($this->sessionId, '%4192Test%', ['Accounts',], 0, 30);

        $account->mark_deleted($account->id);

        $this->assertGreaterThan(0, safeCount($actual->entry_list), 'Call must return one bean minimum');
        $this->assertEquals('Accounts', $actual->entry_list[0]->name, 'Bean must be account');
        $this->assertEquals(
            $account->id,
            get_object_vars($actual->entry_list[0]->records[0][2])['value'],
            'Bean id must be same as id of created account'
        );
    }
}
