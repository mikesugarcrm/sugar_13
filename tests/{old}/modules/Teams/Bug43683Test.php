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

class Bug43683Test extends TestCase
{
    /**
     * @var User
     */
    private $user1;
    /**
     * @var User
     */
    private $user2;

    protected function setUp(): void
    {
        $this->user1 = SugarTestUserUtilities::createAnonymousUser();
        $this->user2 = SugarTestUserUtilities::createAnonymousUser(false);
        $this->user2->reports_to_id = $this->user1->id;
        $this->user2->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @group   bug43683
     */
    public function testAddUserToTeamWithManagers()
    {
        $team = BeanFactory::getBean('Teams', $this->user2->getPrivateTeamID());
        $team->add_user_to_team($this->user2->id);
        $team_membership = BeanFactory::newBean('TeamMemberships');
        $this->assertTrue($team_membership->retrieve_by_user_and_team($this->user1->id, $team->id), 'Implicit membership exists');
    }
}
