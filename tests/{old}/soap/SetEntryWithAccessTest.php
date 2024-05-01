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
 * @group bug44680
 */
class SetEntryWithAccessTest extends SOAPTestCase
{
    public $testUser;
    public $testAccount;
    public $teamSet;
    public $testTeam;

    protected function setUp(): void
    {
        $this->soapURL = $GLOBALS['sugar_config']['site_url'] . '/soap.php';
        parent::setUp();
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->testUser;
        $this->testAccount = SugarTestAccountUtilities::createAccount();

        $this->testTeam = SugarTestTeamUtilities::createAnonymousTeam();

        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams([$this->testTeam->id, $this->testUser->getPrivateTeamID()]);


        $this->testAccount->team_id = $this->testUser->getPrivateTeamID();
        $this->testAccount->team_set_id = $this->teamSet->id;
        $this->testAccount->assigned_user_id = $this->testUser->id;
        $this->testAccount->save();
        $GLOBALS['db']->commit();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        parent::tearDown();
    }

    public function testSetEntryHasAccess()
    {
        $time = random_int(0, mt_getrandmax());
        $this->loginLegacy();

        $result = get_object_vars($this->soapClient->set_entry($this->sessionId, 'Accounts', [['name' => 'id', 'value' => $this->testAccount->id], ['name' => 'name', 'value' => "$time Account SINGLE"]]));

        $this->assertEquals($this->testAccount->id, $result['id'], 'Did not update the Account as expected.');
    }
}
