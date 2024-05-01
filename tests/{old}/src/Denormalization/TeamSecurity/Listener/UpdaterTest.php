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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\Listener;

use BeanFactory;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\StateAwareRebuild;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;
use SugarTestTeamUtilities;
use SugarTestUserUtilities;
use Team;
use TeamSet;
use TeamSetManager;
use User;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Updater
 */
class UpdaterTest extends TestCase
{
    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var string
     */
    private $table;

    public static function setUpBeforeClass(): void
    {
        global $sugar_config;
        $sugar_config['perfProfile']['TeamSecurity']['default']['use_denorm'] = true;
        $sugar_config['perfProfile']['TeamSecurity']['inline_update'] = true;

        $container = Container::getInstance();

        $config = $container->get(SugarConfig::class);
        $config->clearCache();

        $command = $container->get(StateAwareRebuild::class);
        $command();
    }

    protected function setUp(): void
    {
        $container = Container::getInstance();
        $state = $container->get(State::class);

        $this->conn = $container->get(Connection::class);
        $this->table = $state->getActiveTable();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
    }

    /**
     * @test
     */
    public function userDeleted()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        $team = $this->createTeam();
        $teamSet = $this->createTeamSet($team);

        $this->addUserToTeams($user1, $team);
        $this->addUserToTeams($user2, $team);

        $this->deleteUser($user1);

        $this->assertUserNotBelongsToTeamSet($user1, $teamSet);
        $this->assertUserBelongsToTeamSet($user2, $teamSet);

        $this->assertUserRemoved($user1);
    }

    /**
     * @test
     */
    public function teamDeleted()
    {
        $user = $this->createUser();

        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $teamSet1 = $this->createTeamSet($team1);
        $teamSet2 = $this->createTeamSet($team2);
        $teamSet12 = $this->createTeamSet($team1, $team2);

        $this->addUserToTeams($user, $team1, $team2);

        $this->deleteTeam($team1);

        $this->assertUserNotBelongsToTeamSet($user, $teamSet1);
        $this->assertUserNotBelongsToTeamSet($user, $teamSet12);
        $this->assertUserBelongsToTeamSet($user, $teamSet2);

        $this->assertTeamSetRemoved($teamSet1);
        $this->assertTeamSetRemoved($teamSet12);
    }

    /**
     * @test
     */
    public function teamSetCreated()
    {
        $user = $this->createUser();

        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $team3 = $this->createTeam();

        $this->addUserToTeams($user, $team1, $team2);

        $teamSet1 = $this->createTeamSet($team1);
        $teamSet12 = $this->createTeamSet($team1, $team2);
        $teamSet3 = $this->createTeamSet($team3);

        $this->assertUserBelongsToTeamSet($user, $teamSet1);
        $this->assertUserBelongsToTeamSet($user, $teamSet12);
        $this->assertUserNotBelongsToTeamSet($user, $teamSet3);
    }

    /**
     * @test
     */
    public function userAddedToTeam()
    {
        $user = $this->createUser();

        $team1 = $this->createTeam();
        $team2 = $this->createTeam();
        $team3 = $this->createTeam();
        $team4 = $this->createTeam();

        $teamSet12 = $this->createTeamSet($team1, $team2);
        $teamSet23 = $this->createTeamSet($team2, $team3);
        $teamSet4 = $this->createTeamSet($team4);

        $this->addUserToTeams($user, $team1, $team2);

        $this->assertUserBelongsToTeamSet($user, $teamSet12);
        $this->assertUserBelongsToTeamSet($user, $teamSet23);
        $this->assertUserNotBelongsToTeamSet($user, $teamSet4);
    }

    /**
     * @test
     */
    public function userRemovedFromTeam()
    {
        $user = $this->createUser();

        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $this->addUserToTeams($user, $team1, $team2);

        $teamSet1 = $this->createTeamSet($team1);
        $teamSet12 = $this->createTeamSet($team1, $team2);

        $this->removeUserFromTeams($user, $team1);

        $this->assertUserNotBelongsToTeamSet($user, $teamSet1);
        $this->assertUserBelongsToTeamSet($user, $teamSet12);
    }

    /**
     * @test
     */
    public function teamRecordsReassigned()
    {
        $user = $this->createUser();

        $team1 = $this->createTeam();
        $team2 = $this->createTeam();

        $this->addUserToTeams($user, $team1, $team2);

        $teamSet1 = $this->createTeamSet($team1);
        $teamSet2 = $this->createTeamSet($team2);
        $teamSet12 = $this->createTeamSet($team1, $team2);

        TeamSetManager::reassignRecords([$team2], $team1);

        $this->assertUserBelongsToTeamSet($user, $teamSet1);
        $this->assertUserNotBelongsToTeamSet($user, $teamSet2);
        $this->assertUserNotBelongsToTeamSet($user, $teamSet12);
    }

    /**
     * @test
     */
    public function userManagerChanged()
    {
        $user = $this->createUser();
        $manager = $this->createUser();

        $team = $this->getPrivateTeam($user);
        $teamSet = $this->createTeamSet($team);

        // manager added
        $team->user_manager_changed($user->id, '', $manager->id);
        $this->assertUserBelongsToTeamSet($user, $teamSet);
        $this->assertUserBelongsToTeamSet($manager, $teamSet);

        // manager removed
        $team->user_manager_changed($user->id, $manager->id, '');
        $this->assertUserBelongsToTeamSet($user, $teamSet);
        $this->assertUserNotBelongsToTeamSet($manager, $teamSet);
    }

    /**
     * @return User
     */
    private function createUser()
    {
        return SugarTestUserUtilities::createAnonymousUser();
    }

    /**
     * @param User $user
     */
    private function deleteUser(User $user)
    {
        $user->mark_deleted($user->id);
    }

    /**
     * @return Team
     */
    private function createTeam()
    {
        return SugarTestTeamUtilities::createAnonymousTeam();
    }

    /**
     * @param Team $team
     */
    private function deleteTeam(Team $team)
    {
        $team->mark_deleted($team->id);
    }

    /**
     * @param Team[] $teams
     * @return TeamSet
     */
    private function createTeamSet(Team ...$teams)
    {
        /** @var TeamSet $teamSet */
        $teamSet = BeanFactory::newBean('TeamSets');
        $id = $teamSet->addTeams(array_map(function (Team $team) {
            return $team->id;
        }, $teams));

        return BeanFactory::retrieveBean('TeamSets', $id);
    }

    /**
     * @param User $user
     * @param Team[] ...$teams
     *
     * @return void
     */
    private function addUserToTeams(User $user, Team ...$teams)
    {
        foreach ($teams as $team) {
            $team->add_user_to_team($user->id);
        }
    }

    /**
     * @param User $user
     * @param Team[] ...$teams
     *
     * @return void
     */
    private function removeUserFromTeams(User $user, Team ...$teams)
    {
        foreach ($teams as $team) {
            $team->remove_user_from_team($user->id);
        }
    }

    /**
     * @param User $user
     * @return Team|null
     */
    private function getPrivateTeam(User $user)
    {
        return BeanFactory::retrieveBean('Teams', $user->getPrivateTeamID(), ['use_cache' => false]);
    }

    private function assertUserBelongsToTeamSet(User $user, TeamSet $teamSet)
    {
        $this->assertTrue(
            $this->relationshipExists($user, $teamSet),
            sprintf(
                'Failed asserting that user %s belongs to team set %s.',
                $user->name,
                $teamSet->team_md5
            )
        );
    }

    private function assertUserNotBelongsToTeamSet(User $user, TeamSet $teamSet)
    {
        $this->assertFalse(
            $this->relationshipExists($user, $teamSet),
            sprintf(
                'Failed asserting that user %s does not belong to team set %s.',
                $user->name,
                $teamSet->team_md5
            )
        );
    }

    /**
     * Checks if all user records have bean entirely removed from the table
     *
     * @param User $user
     */
    private function assertUserRemoved(User $user)
    {
        $this->assertFalse(
            $this->keyExists('user_id', $user->id),
            sprintf(
                'Failed asserting that user %s is removed.',
                $user->name
            )
        );
    }

    /**
     * Checks if all team set records have bean entirely removed from the table
     *
     * @param TeamSet $teamSet
     */
    private function assertTeamSetRemoved(TeamSet $teamSet)
    {
        $this->assertFalse(
            $this->keyExists('team_set_id', $teamSet->id),
            sprintf(
                'Failed asserting that team set %s is removed.',
                $teamSet->team_md5
            )
        );
    }

    /**
     * Checks whether relationship between user and team set exists
     *
     * @param User $user
     * @param TeamSet $teamSet
     * @return bool
     */
    private function relationshipExists(User $user, TeamSet $teamSet)
    {
        $query = sprintf(
            <<<SQL
SELECT NULL
  FROM %s
 WHERE team_set_id = ?
   AND user_id = ?
SQL
            ,
            $this->table
        );

        return $this->conn->executeQuery($query, [
                $teamSet->id,
                $user->id,
            ])->fetchAssociative() !== false;
    }

    /**
     * Checks whether rows identified by the given field and ID exist
     *
     * @param string $field
     * @param string $id
     * @return bool
     */
    private function keyExists($field, $id)
    {
        $query = sprintf(
            <<<SQL
SELECT NULL
  FROM %s
 WHERE $field = ?
SQL
            ,
            $this->table
        );

        return $this->conn->executeQuery($query, [$id])->fetchAssociative() !== false;
    }
}
