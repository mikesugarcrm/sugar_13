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


class RestBug54864Test extends RestTestPortalBase
{
    public $portalGuy;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testMeEndpoint()
    {
        // Build three accounts, we'll associate to two of them.
        for ($i = 0; $i < 3; $i++) {
            $account = new Account();
            $account->name = 'UNIT TEST ' . ($i + 1) . ' - ' . create_guid();
            $account->billing_address_postalcode = sprintf('%08d', ($i + 1));
            $account->save();
            $this->accounts[] = $account;
        }
        for ($i = 0; $i < 3; $i++) {
            $contact = new Contact();
            $contact->first_name = 'UNIT' . ($i + 1);
            $contact->last_name = create_guid();
            $contact->title = sprintf('%08d', ($i + 1));
            $contact->save();
            $this->contacts[$i] = $contact;

            $contact->load_relationship('accounts');
            $accountNum = $i;
            $contact->accounts->add([$this->accounts[$accountNum]]);

            $contact->save();
        }
        $this->portalGuy->load_relationship('accounts');
        $this->portalGuy->accounts->add([$this->accounts[1], $this->accounts[2]]);
        $GLOBALS['db']->commit();


        $restReply = $this->restCall('me');
        $this->assertTrue(in_array($this->accounts[1]->id, $restReply['reply']['current_user']['account_ids']), 'The first account id is missing from the list #1');
        $this->assertTrue(in_array($this->accounts[2]->id, $restReply['reply']['current_user']['account_ids']), 'The second account id is missing from the list #1');


        $this->portalGuy->accounts->delete($this->portalGuy->id, $this->accounts[1]);

        $GLOBALS['db']->commit();

        $restReply = $this->restCall('me');
        $this->assertFalse(in_array($this->accounts[1]->id, $restReply['reply']['current_user']['account_ids']), 'The first account id is not missing from the list when it should be #2');
        $this->assertTrue(in_array($this->accounts[2]->id, $restReply['reply']['current_user']['account_ids']), 'The second account id is missing from the list #2');

        $this->portalGuy->accounts->delete($this->portalGuy->id, $this->accounts[2]);

        $GLOBALS['db']->commit();

        $restReply = $this->restCall('me');
        $this->assertFalse(in_array($this->accounts[1]->id, $restReply['reply']['current_user']['account_ids']), 'The first account id is not missing from the list when it should be #3');
        $this->assertFalse(in_array($this->accounts[2]->id, $restReply['reply']['current_user']['account_ids']), 'The second account id is not missing from the list when it should be #3');
    }
}
