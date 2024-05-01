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

class Bug42097Test extends TestCase
{
    public $testUser;

    protected function setUp(): void
    {
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->testUser = null;
    }


    /**
     * testRemoveUserFromTeam
     *
     * This test checks the case where a user is removed from his own private team.
     * We are expecting an exception to be thrown.
     */
    public function testRemoveUserFromTeam()
    {
        $team = BeanFactory::getBean('Teams', $this->testUser->getPrivateTeamID());
        $exceptionThrown = false;
        try {
            $team->remove_user_from_team($this->testUser->id);
        } catch (Exception $ex) {
            $exceptionThrown = true;
        }

        $this->assertTrue($exceptionThrown, 'Assert that an exception was thrown for attempting to remove user off own private team');
    }
}
