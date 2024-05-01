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

class Bug41676Test extends TestCase
{
    public $testUser;
    public $testAccount;
    public $teamSet;

    protected function setUp(): void
    {
        //Make sure we are an admin
        global $current_user;
        $current_user = BeanFactory::getBean('Users', '1');

        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
        $this->testAccount = SugarTestAccountUtilities::createAccount();
        $this->testUser->is_admin = false; // ensure non-admin user

        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams($this->testUser->getPrivateTeamID());


        $this->testAccount->team_id = $this->testUser->getPrivateTeamID();
        $this->testAccount->team_set_id = $this->teamSet->id;
        $this->testAccount->assigned_user_id = $this->testUser->id;
        $this->testAccount->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testAccountWithDeletedUserAndTeam()
    {
        //Simulate deleting the user
        $this->testUser->status = 'Inactive';
        $this->testUser->deleted = 1;
        $this->testUser->employee_status = 'Terminated';
        $this->testUser->save();
        $eapm = BeanFactory::newBean('EAPM');
        $eapm->delete_user_accounts($this->testUser->id);

        //Simulate deleting the team
        $team = BeanFactory::getBean('Teams', $this->testUser->getPrivateTeamID());
        $team->mark_deleted($team->id);

        $account = BeanFactory::getBean('Accounts', $this->testAccount->id);

        $this->assertEquals($account->team_set_id, $this->teamSet->id, 'Assert that team set id value is correctly set');
        $this->assertEquals($account->assigned_user_id, $this->testUser->id, 'Assert that assigned user id value is correctly set');

        $query = "SELECT * FROM teams WHERE id = '{$team->id}'";
        $results = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($results);
        $this->assertEquals($row['deleted'], 1, 'Assert that deleted flag is correctly set');

        $query = "SELECT count(*) as total FROM team_memberships WHERE team_id = '{$team->id}' AND deleted = 0";
        $results = $GLOBALS['db']->query($query);
        $row = $GLOBALS['db']->fetchByAssoc($results);
        $this->assertTrue(is_null($row['total']) || $row['total'] == 0, 'Assert that team_memberships table has been correctly set');
    }
}
