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
 * @covers TeamSetLink
 */
class TeamSetLinkTest extends TestCase
{
    private static $previousTeamAccessCheck;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        self::$previousTeamAccessCheck = $GLOBALS['sugar_config']['disable_team_access_check'] ?? null;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        $GLOBALS['sugar_config']['disable_team_access_check'] = self::$previousTeamAccessCheck;
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function testTeamSetLinkEmailNotAdminSave()
    {
        $createdBy = SugarTestUserUtilities::createAnonymousUser()->id;

        $email = BeanFactory::newBean('Emails');
        $email->team_id = null;
        $email->created_by = $createdBy;
        $email->in_workflow = false;
        $email->assigned_user_id = null;

        $GLOBALS['sugar_config']['disable_team_access_check'] = false;
        $teamSetLink = new TeamSetLink('contacts', $email);
        $teamSetLink->save(false);
        $this->assertEquals(false, empty($email->team_set_id));
    }

    public function testTeamSetLinkEmailAdminSave()
    {
        $email = BeanFactory::newBean('Emails');
        $email->team_id = null;
        $email->created_by = 1;
        $email->in_workflow = false;
        $email->assigned_user_id = null;

        $GLOBALS['sugar_config']['disable_team_access_check'] = false;
        $teamSetLink = new TeamSetLink('contacts', $email);
        $teamSetLink->save(false);
        $this->assertEquals(true, empty($email->team_set_id));
    }

    public function testTeamsSetLinkNotEmailNotAdminSave()
    {
        $createdBy = SugarTestUserUtilities::createAnonymousUser()->id;

        $task = BeanFactory::newBean('Tasks');
        $task->team_id = null;
        $task->created_by = $createdBy;
        $task->in_workflow = false;
        $task->assigned_user_id = null;

        $GLOBALS['sugar_config']['disable_team_access_check'] = false;
        $teamSetLink = new TeamSetLink('contacts', $task);
        $teamSetLink->save(false);
        $this->assertEquals(false, empty($task->team_set_id));
    }

    public function testTeamsSetLinkNotEmailAdminSave()
    {
        $task = BeanFactory::newBean('Tasks');
        $task->team_id = null;
        $task->created_by = 1;
        $task->in_workflow = false;
        $task->assigned_user_id = null;

        $GLOBALS['sugar_config']['disable_team_access_check'] = false;
        $teamSetLink = new TeamSetLink('contacts', $task);
        $teamSetLink->save(false);
        $this->assertEquals(false, empty($task->team_set_id));
    }
}
