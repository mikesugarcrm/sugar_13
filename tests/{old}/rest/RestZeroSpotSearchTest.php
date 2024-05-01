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


class RestZeroSpotSearchTest extends RestTestBase
{
    /**
     * @var mixed|mixed[]
     */
    public $account_id;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        if (isset($this->account_id)) {
            foreach ($this->account_id as $account_id) {
                $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account_id}'");
                if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                    $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id = '{$account_id}'");
                }
            }
            $GLOBALS['db']->commit();
        }
        parent::tearDown();
    }

    public function testZeroSpotSearch()
    {
        $restReply = $this->restCall(
            'Accounts/',
            json_encode(['name' => '0 - UNIT TEST - AFTER &nbsp;']),
            'POST'
        );

        $this->account_id[] = $restReply['reply']['id'];

        $this->assertTrue(
            isset($restReply['reply']['id']),
            'An account was not created (or if it was, the ID was not returned)'
        );


        $restReply = $this->restCall(
            'Accounts/',
            json_encode(['name' => '1 - UNIT TEST - AFTER &nbsp;']),
            'POST'
        );

        $this->assertTrue(
            isset($restReply['reply']['id']),
            'An account was not created (or if it was, the ID was not returned)'
        );


        $this->account_id[] = $restReply['reply']['id'];

        $restReply = $this->restCall('Accounts/?q=0');

        $this->assertEquals($this->account_id[0], $restReply['reply']['records'][0]['id'], 'The record returned does not match the 0 record');

        $this->assertCount(1, $restReply['reply']['records']);
    }
}
