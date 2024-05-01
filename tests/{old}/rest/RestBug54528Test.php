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


class RestBug54528Test extends RestTestPortalBase
{
    /**
     * @var \Account|mixed
     */
    public $account;
    /**
     * @var mixed
     */
    public $bug_id;
    /**
     * @var mixed
     */
    public $case_id;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a portal API user
        // Created in portal base as this->_user

        // create account
        $account = new Account();
        $account->name = create_guid();
        $account->billing_address_postalcode = sprintf('%08d', 1);
        $account->save();
        $this->account = $account;

        // create contact
        $this->contact = BeanFactory::newBean('Contacts');
        $this->contact->id = create_guid();
        $this->contact->new_with_id = true;
        $this->contact->first_name = 'Little';
        $this->contact->last_name = 'Unittest';
        $this->contact->description = 'Little Unittest';
        $this->contact->portal_name = 'liltest@unit.com';
        $this->contact->portal_active = '1';
        $this->contact->portal_password = User::getPasswordHash('unittest');
        $this->contact->assigned_user_id = $this->user->id;
        $this->contact->save();

        $GLOBALS['db']->commit();

        $this->contact->load_relationship('accounts');

        // relate
        $this->contact->accounts->add($account->id);
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        if (isset($this->bug_id)) {
            $GLOBALS['db']->query("DELETE FROM bugs WHERE id = '{$this->bug_id}'");
            if ($GLOBALS['db']->tableExists('bugs_cstm')) {
                $GLOBALS['db']->query("DELETE FROM bugs_cstm WHERE id_c = '{$this->bug_id}'");
            }
        }
        if (isset($this->case_id)) {
            $GLOBALS['db']->query("DELETE FROM cases WHERE id = '{$this->case_id}'");
            if ($GLOBALS['db']->tableExists('cases_cstm')) {
                $GLOBALS['db']->query("DELETE FROM cases_cstm WHERE id_c = '{$this->case_id}'");
            }
        }
        if (isset($this->account->id)) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$this->account->id}'");
            if ($GLOBALS['db']->tableExists('accounts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM accounts_cstm WHERE id_c = '{$this->account->id}'");
            }
        }
        if (isset($this->contact->id)) {
            $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '{$this->contact->id}'");
            if ($GLOBALS['db']->tableExists('contacts_cstm')) {
                $GLOBALS['db']->query("DELETE FROM contacts_cstm WHERE id_c = '{$this->contact->id}'");
            }
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testCreate()
    {
        // we need to be an admin to get at the relationship data
        $GLOBALS['current_user']->is_admin = 1;
        $args = [
            'grant_type' => 'password',
            'username' => $this->contact->portal_name,
            'password' => 'unittest',
            'client_id' => 'support_portal',
            'client_secret' => '',
            'platform' => 'portal',
        ];
        // reload user

        // Prevents _restCall from automatically logging in
        $this->authToken = 'LOGGING_IN';
        $reply = $this->restCall('oauth2/token', json_encode($args));

        // flip to the portal auth
        $this->authToken = $reply['reply']['access_token'];
        // create case
        $restReply = $this->restCall(
            'Cases/',
            json_encode(['name' => 'UNIT TEST Case']),
            'POST'
        );

        $this->assertTrue(
            isset($restReply['reply']['id']),
            'A case was not created (or if it was, the ID was not returned)'
        );

        $this->case_id = $restReply['reply']['id'];

        // load case to check teamset and user id match
        $case = BeanFactory::getBean('Cases', $this->case_id);

        $this->assertEquals($this->user->default_team, $case->team_id, "Team ID doesn't match");
        $this->assertEquals($this->user->default_team, $case->team_set_id, "Team Set ID doesn't match");

        //        $this->assertEquals($this->contact->team_id, $case->team_id, "Team ID doesn't match");
        //        $this->assertEquals($this->contact->team_set_id, $case->team_set_id, "Team Set ID doesn't match");

        $this->assertEquals($this->contact->assigned_user_id, $case->assigned_user_id, "Assigned user id doesn't match.");

        $restReply = null;

        $restReply = $this->restCall(
            'Bugs/',
            json_encode(['name' => 'UNIT TEST Bug']),
            'POST'
        );

        $this->assertTrue(
            isset($restReply['reply']['id']),
            'A bug was not created (or if it was, the ID was not returned)'
        );

        $this->bug_id = $restReply['reply']['id'];

        $bug = BeanFactory::getBean('Bugs', $this->bug_id);

        $this->assertEquals($this->user->default_team, $bug->team_id, "Team ID doesn't match");
        $this->assertEquals($this->user->team_set_id, $bug->team_set_id, "Team Set ID doesn't match");

        //        $this->assertEquals($this->contact->team_id, $bug->team_id, "Team ID doesn't match");
        //        $this->assertEquals($this->contact->team_set_id, $bug->team_set_id, "Team Set ID doesn't match");

        $this->assertEquals($this->contact->assigned_user_id, $bug->assigned_user_id, "Assigned user id doesn't.");
    }
}
