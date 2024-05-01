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


class RestCreateTest extends RestTestBase
{
    /**
     * @var mixed
     */
    public $account_id;

    protected function tearDown(): void
    {
        if (isset($this->account_id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account_id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account_id}'");
            }
        }
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        $restReply = $this->restCall(
            'Accounts/',
            json_encode(['name' => 'UNIT TEST - AFTER', 'my_favorite' => true]),
            'POST'
        );
        $this->assertTrue(
            isset($restReply['reply']['id']),
            'An account was not created (or if it was, the ID was not returned)'
        );

        $this->assertTrue(isset($restReply['reply']['team_name'][0]['name']), 'A team name was not set.');

        $this->account_id = $restReply['reply']['id'];

        $account = new Account();
        $account->retrieve($this->account_id);

        $this->assertEquals(
            'UNIT TEST - AFTER',
            $account->name,
            'Did not set the account name.'
        );

        $this->assertEquals(
            $restReply['reply']['name'],
            $account->name,
            'Rest Reply and Bean Do Not Match.'
        );

        $this->assertEquals(
            $restReply['reply']['team_name'][0]['name'],
            'Global',
            'Rest Reply Does Not Match Team Name Global.'
        );

        $this->assertEquals(
            $restReply['reply']['team_name'][0]['name'],
            $account->team_name,
            'Rest Reply and Bean Do Not Match Team Name.'
        );

        $is_fav = SugarFavorites::isUserFavorite('Accounts', $account->id, $this->user->id);

        $this->assertEquals($is_fav, (bool)$restReply['reply']['my_favorite'], 'The returned favorite was not the same.');
    }
}
