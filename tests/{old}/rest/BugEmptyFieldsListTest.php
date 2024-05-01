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


class BugEmptyFieldsListTest extends RestTestBase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->accounts = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->accounts as $account) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account->id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$account->id}'");
            }
        }

        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testList()
    {
        // Make sure there is at least one page of accounts
        for ($i = 0; $i < 40; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . safeCount($this->accounts) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', safeCount($this->accounts));
            $account->save();
            $this->accounts[] = $account;
        }
        $GLOBALS['db']->commit();
        $restReply = $this->restCall('Accounts?fields=');
        $this->assertNotEquals($restReply['replyRaw'], 'ERROR: No access to view field:  in module: Accounts');
    }
}
