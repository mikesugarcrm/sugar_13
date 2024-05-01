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

class RelationshipRoleTest extends TestCase
{
    protected $createdBeans = [];
    protected $createdFiles = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('timezone', 'America/Los_Angeles');
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y');
        $GLOBALS['current_user']->setPreference('timef', 'h.iA');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTaskUtilities::removeAllCreatedTasks();

        foreach ($this->createdFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
    public function testQuoteAccountsRole()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = 'RoleTestAccount';
        $account->save();

        $quote = SugarTestQuoteUtilities::createQuote();
        $quote->load_relationship('billing_accounts');
        $quote->billing_accounts->add($account);

        //Now check the row in the database
        $quote->set_account();
        $this->assertEquals($account->name, $quote->billing_account_name);
    }
}
