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

require_once 'modules/Campaigns/ProcessBouncedEmails.php';

/**
 * @ticket 38053
 */
class Bug38053Test extends TestCase
{
    private $user;

    protected function setUp(): void
    {
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user'] = $this->user;
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM user_preferences WHERE assigned_user_id='{$this->user->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        unset($GLOBALS['current_user']);
    }

    /**
     * The purpose of this test is to ensure that the user's default team is properly set on the attachment.
     */
    public function testGetNoteBeanForAttachment()
    {
        $GLOBALS['current_user']->team_id = 1;
        $GLOBALS['current_user']->team_set_id = 2;

        $ie = new InboundEmail();
        $attach = $ie->getNoteBeanForAttachment('123');
        $this->assertEquals($GLOBALS['current_user']->team_id, $attach->team_id, "Checking that the attachment team_id is equal to the user's default.");
        $this->assertEquals($GLOBALS['current_user']->team_set_id, $attach->team_set_id, "Checking that the attachment team_set_id is equal to the user's default.");
    }
}
