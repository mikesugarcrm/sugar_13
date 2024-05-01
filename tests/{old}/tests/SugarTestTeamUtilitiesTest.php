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

class SugarTestTeamUtilitiesTest extends TestCase
{
    private $beforeSnapshot = [];

    protected function setUp(): void
    {
        $this->beforeSnapshot = $this->takeTeamDBSnapshot();
    }

    protected function tearDown(): void
    {
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    private function takeTeamDBSnapshot()
    {
        $snapshot = [];
        $query = 'SELECT * FROM teams';
        $result = $GLOBALS['db']->query($query);
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $snapshot[] = $row;
        }
        return $snapshot;
    }

    public function testCanCreateAnAnonymousTeam()
    {
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $this->assertInstanceOf('Team', $team);

        $after_snapshot = $this->takeTeamDBSnapshot();
        $this->assertNotEquals($this->beforeSnapshot, $after_snapshot, 'Simply insure that something was added');
    }

    public function testAnonymousTeamHasARandomTeamName()
    {
        $first_team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->assertNotEquals($first_team->name, '', 'team name should not be empty');

        $second_team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->assertNotEquals(
            $first_team->name,
            $second_team->name,
            'each team should have a unique name property'
        );
    }

    public function testCanTearDownAllCreatedAnonymousTeams()
    {
        for ($i = 0; $i < 5; $i++) {
            SugarTestTeamUtilities::createAnonymousTeam();
        }
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();

        $this->assertEquals(
            $this->beforeSnapshot,
            $this->takeTeamDBSnapshot(),
            'removeAllCreatedAnonymousTeams() should have removed the team it added'
        );
    }
}
