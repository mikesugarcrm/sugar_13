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

class TeamTest extends TestCase
{
    /**
     * @var $userOne User
     */
    private $userOne;
    /**
     * @var $userTwo User
     */
    private $userTwo;
    /**
     * @var $userThree User
     */
    private $userThree;
    /**
     * @var $teamOne Team
     */
    private $teamOne;
    /**
     * @var $teamTwo Team
     */
    private $teamTwo;

    protected function setUp(): void
    {
        $this->userOne = SugarTestUserUtilities::createAnonymousUser();
        $this->userTwo = SugarTestUserUtilities::createAnonymousUser();
        $this->userThree = SugarTestUserUtilities::createAnonymousUser();

        $this->teamOne = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamTwo = SugarTestTeamUtilities::createAnonymousTeam();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    public function testGetTeamsForUser()
    {
        $this->teamOne->add_user_to_team($this->userOne->id, $this->userOne);
        $this->teamOne->add_user_to_team($this->userTwo->id, $this->userTwo);

        $this->teamTwo->add_user_to_team($this->userOne->id, $this->userOne);
        $this->teamTwo->add_user_to_team($this->userThree->id, $this->userThree);

        $teams = $this->teamOne->get_teams_for_user($this->userOne->id);
        $teamArr = [];
        foreach ($teams as $team) {
            $teamArr[] = $team->id;
        }
        $expectedTeams = [
            $this->teamOne->global_team,
            $this->userOne->getPrivateTeam(),
            $this->teamOne->id,
            $this->teamTwo->id,
        ];
        sort($expectedTeams);
        sort($teamArr);
        $this->assertEquals(
            $expectedTeams,
            $teamArr
        );
    }
}
