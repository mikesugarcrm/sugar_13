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

class Bug65865Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testGetBeanDeleted()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = 'Test deleted';
        $account->save();
        $account->mark_deleted($account->id);
        $this->assertNotNull(BeanFactory::getBean('Accounts', $account->id, ['deleted' => false, 'strict_retrieve' => true]));
        $this->assertNull(BeanFactory::getBean('Accounts', $account->id, ['strict_retrieve' => true]));
    }

    public function testGetBeanDisableRowLevelSecurity()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $account->name = 'Test disable_row_level_security';
        $user = SugarTestUserUtilities::createAnonymousUser();
        $teamSet = new TeamSet();
        $teamSet->addTeams($user->getPrivateTeamID());
        $account->team_id = $user->getPrivateTeamID();
        $account->team_set_id = $teamSet->id;
        $account->assigned_user_id = $user->id;
        $account->disable_row_level_security = true;
        $account->save();
        $this->assertNotNull(BeanFactory::getBean('Accounts', $account->id, ['disable_row_level_security' => true, 'strict_retrieve' => true]));
        $this->assertNull(BeanFactory::getBean('Accounts', $account->id, ['strict_retrieve' => true]));
    }
}
