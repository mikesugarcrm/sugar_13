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

/***
 * Test cases for Bug 42379
 */
class Bug42379Test extends TestCase
{
    private $teamSets;
    private $teamIds = [];
    private $teamSetsId = '';


    protected function setUp(): void
    {
        $this->teamIds[] = '8744c7d9-9e4b-2338-cb76-4ab0a3d0a65f';
        $this->teamIds[] = '8749a110-1d85-4562-fa23-4ab0a3c65e16';
        $this->teamIds[] = '874c1242-4645-898d-238a-4ab0a3f7e7c1';

        sort($this->teamIds, SORT_STRING);
    }

    protected function tearDown(): void
    {
        unset($this->teamSets);
        unset($this->teamIds);
        unset($this->teamSetsId);
    }

    public function testGetStatisticsTeamIds()
    {
        $this->teamSets = new TeamSetBug42379Test();

        // we could also call addTeams, which in turn calls getStatistics
        // but this is a more direct test of getStatistics
        $stats = $this->teamSets->getStatistics($this->teamIds);
        $this->assertEquals(
            $this->teamIds,
            $stats['team_ids'],
            'testing to make sure that team IDs are set'
        );
    }

    public function testGetStatisticsWithOneItem()
    {
        $this->teamSets = new TeamSetBug42379Test();

        // add just one item from TeamIDs
        $this->teamSetsId = $this->teamSets->addTeams((array_slice($this->teamIds, 0, 1)));
        $stats = $this->teamSets->getStatistics((array_slice($this->teamIds, 0, 1)));
        $this->assertEquals(
            md5($this->teamIds[0]),
            $stats['team_md5'],
            'testing to make sure that 1 team ID gets added properly'
        );
    }

    public function testGetStatisticsTeamCount()
    {
        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);

        $stats = $this->teamSets->getStatistics($this->teamIds);

        $this->assertEquals(
            count($this->teamIds),
            $stats['team_count'],
            'make sure that all teams get added'
        );
    }

    public function testGetStatisticsWithManyItems()
    {
        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);

        $stats = $this->teamSets->getStatistics($this->teamIds);
        $team_md5 = '';

        foreach ($this->teamIds as $team_id) {
            $team_md5 .= $team_id;
        }
        // run the md5 on the whole string of team_ids
        $team_md5 = md5($team_md5);


        $this->assertEquals(
            $team_md5,
            $stats['team_md5'],
            'make sure that the resulting md5 matches'
        );
    }


    /* This test doesn't actually test the getStatistics method directly
     * The getStatistics is called by addTeams
     * It simply checks that when adding teams in the TeamSet,
     * the primary team gets selected properly.
     */
    public function testGetStatisticsPrimaryTeamID()
    {
        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams($this->teamIds);
        $count = count($this->teamIds);
        $this->assertEquals(
            $this->teamIds[$count - 1],
            $this->teamSets->getPrimaryTeamId(),
            'make sure that primary team ID is correctly set when sending multiple team IDs'
        );
    }

    /* This test doesn't actually test the getStatistics method at all
     * It simply checks that when adding teams in the TeamSet,
     * the primary team gets selected properly.
     * If the proper team is not selected, then it could mess with getStatistics.
     */
    public function testGetStatisticsPrimaryTeamIDWithOneTeam()
    {
        $this->teamSets = new TeamSetBug42379Test();
        $this->teamSetsId = $this->teamSets->addTeams((array_slice($this->teamIds, 0, 1)));
        $this->assertEquals(
            $this->teamIds[0],
            $this->teamSets->getPrimaryTeamId(),
            'make sure that primary team ID is correctly set when sending only 1 team ID'
        );
    }
}

/*
 * Create mock of TeamSet to get access to _getStatistics method for testing
 */

class TeamSetBug42379Test extends TeamSet
{
    public function getStatistics($team_ids)
    {
        return $this->_getStatistics($team_ids);
    }
}
