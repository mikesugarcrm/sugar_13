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

/**
 * Bug44607Test
 *
 * This bug tests the case where a user with a reports to id value that pointed to a user
 * that did not exist in the system or was deleted (record not just deleted flag) would cause
 * the code in Team.php (add_user_to_team) to run in an infinite loop.  Obviously, we do not
 * set the code to run in an infinite loop, but we do test that we get out of it and that no
 * team_membership entries are created for a user that does not exist.
 */
class Bug44607Test extends TestCase
{
    public $testUser;
    public $testUser2;

    protected function setUp(): void
    {
        $this->testUser = SugarTestUserUtilities::createAnonymousUser();
        $this->testUser2 = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->testUser = null;
        $this->testUser2 = null;
    }

    /**
     * testAddUserToTeam
     */
    public function testAddUserToTeam()
    {
        //Create a fake reports_to_id
        $this->testUser->reports_to_id = md5($this->testUser->id);

        $team = BeanFactory::newBean('Teams');
        $team->add_user_to_team($this->testUser->id);

        $results = $GLOBALS['db']->query("SELECT count(*) as total FROM team_memberships WHERE user_id = '{$this->testUser->reports_to_id}'");
        if (!empty($results)) {
            $row = $GLOBALS['db']->fetchByAssoc($results);
            $this->assertEquals($row['total'], 0, 'Assert that no team_membership entries were created');
        }

        $this->testUser->reports_to_id = $this->testUser2->id;
        $team = BeanFactory::newBean('Teams');
        $team->add_user_to_team($this->testUser->id);

        $results = $GLOBALS['db']->query("SELECT count(*) as total FROM team_memberships WHERE user_id = '{$this->testUser->reports_to_id}'");
        if (!empty($results)) {
            $row = $GLOBALS['db']->fetchByAssoc($results);
            $this->assertNotEquals($row['total'], 0, 'Assert that team_membership entries were created');
        }
    }
}
