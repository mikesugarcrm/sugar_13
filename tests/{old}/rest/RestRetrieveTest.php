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


class RestRetrieveTest extends RestTestBase
{
    public $account_id;
    /**
     * @var mixed|\Account
     */
    public $account;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (isset($this->account_id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account->id}'");
            }
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testRetrieve()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST - BEFORE';
        $this->account->save();
        $GLOBALS['db']->commit();
        $restReply = $this->restCall("Accounts/{$this->account->id}");

        $this->assertEquals($this->account->id, $restReply['reply']['id'], 'The returned account id was not the same as the requested account.');
        $this->assertEquals('UNIT TEST - BEFORE', $restReply['reply']['name'], 'Did not retrieve the account name.');
    }

    /**
     * @group rest
     */
    // test that the reply is html decoded Story Id: 30925015 Url: https://www.pivotaltracker.com/story/show/30925015
    public function testRetrieveHTMLEntity()
    {
        $this->account = new Account();
        $this->account->name = 'UNIT TEST << >> BEFORE';
        $this->account->save();
        $GLOBALS['db']->commit();
        $restReply = $this->restCall("Accounts/{$this->account->id}");

        $this->assertEquals($this->account->id, $restReply['reply']['id'], 'The returned account id was not the same as the requested account.');
        $this->assertEquals('UNIT TEST << >> BEFORE', $restReply['reply']['name'], 'Did not retrieve the account name.');
    }
}
