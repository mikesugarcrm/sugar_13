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

class Bug41050Test extends TestCase
{
    public $quote;
    public $account;

    protected function setUp(): void
    {
        global $current_user, $currentModule;

        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $GLOBALS['current_user']->save();

        $time = date('Y-m-d H:i:s');
        //for the purpose of this test, we need to create an account and quote object and relate them

        //create account
        $account = new Account();
        $account->name = 'quote test account name';
        $account->assigned_user_id = $current_user->id;
        $account->disable_custom_fields = true;
        $account->save();
        $this->account = $account;

        //create quote
        $timeDate = new TimeDate();
        $quote = new Quote();
        $quote->name = 'quote test ' . time();
        $quote->quote_stage = 'Draft';
        $quote->date_quote_expected_closed = $timeDate->to_display_date(date('Y') + 1 . '-01-01');
        ;
        $quote->assigned_id = $current_user->id;
        $quote->save();
        $this->quote = $quote;

        //relate the two with different roles on relationship
        $GLOBALS['db']->query("insert into quotes_accounts ( id, quote_id, account_id, account_role, date_modified, deleted) values ( 'quo_acc_" . uniqid() . "', '{$quote->id}', '{$account->id}', 'Bill To', '$time', 0)");
        $GLOBALS['db']->query("insert into quotes_accounts ( id, quote_id, account_id, account_role, date_modified, deleted) values ( 'quo_acc_" . uniqid() . "', '{$quote->id}', '{$account->id}', 'Ship To', '$time', 0)");
    }

    protected function tearDown(): void
    {
        //delete the account, quote and relationship table
        $GLOBALS['db']->query('DELETE FROM quotes WHERE id = \'' . $this->quote->id . '\' ');
        $GLOBALS['db']->query('DELETE FROM accounts WHERE id = \'' . $this->account->id . '\' ');
        $GLOBALS['db']->query('DELETE FROM quotes_accounts WHERE account_id = \'' . $this->account->id . '\' ');
        unset($this->account);
        unset($this->quote);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }


    public function testRetrieveQuoteByAccountName()
    {
        global $current_user;

        //We are going to mimic searching for the quotes by account name using the same api the list views do.
        $lvd = new ListViewData();

        //create a fake post/request object used by listview
        $_REQUEST = $_POST = [
            'module' => 'Quotes',
            'action' => 'index',
            'sugar_user_theme' => 'Sugar',
            'query' => 'true',
            'searchFormTab' => 'advanced_search',
            'name_advanced' => '',
            'quote_num_advanced' => '',
            'account_name_advanced' => $this->account->name,
            'total_usdollar_advanced' => '',
            'date_quote_expected_closed_advanced' => '',
            'favorites_only_advanced' => '0',
            'button' => 'Search',
        ];

        //create a list of fields passed in to create the query in sugarbean, we are staying as close to the Out of Box list view as possible
        $filter = [
            'quote_num' => 1,
            'name' => 1,
            'billing_account_name' => 1,
            'quote_stage' => 1,
            'total_usdollar' => 1,
            'currency_id' => 1,
            'date_quote_expected_closed' => 1,
            'assigned_user_name' => 1,
            'account_name' => 1,
            'favorites_only' => 1,
        ];

        //mimic querying for the listview
        $listResults = $lvd->getListViewData(new Quote(), "(jt0.name like '" . $this->account->name . "%')", -1, -1, $filter);

        //if there is no data returned, then an error occurred
        $this->assertFalse(empty($listResults['data']), 'List view query failed to retrieve the quote by the account name.');
    }
}
