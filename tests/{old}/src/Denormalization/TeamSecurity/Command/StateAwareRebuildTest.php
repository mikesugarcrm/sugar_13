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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\Command;

use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use SugarConfig;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\StateAwareRebuild;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage\InMemoryStorage;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Command\StateAwareRebuild
 */
class StateAwareRebuildTest extends TestCase
{
    /**
     * @test
     */
    public function noRebuildBecauseDisabled()
    {
        $state = $this->createState(false);

        $this->assertRebuildNotHappened($state);
        $this->assertFalse($state->isRebuildRunning());
    }

    /**
     * @test
     */
    public function noRebuildBecauseRebuildRunning()
    {
        $state = $this->createState(true);
        $state->markRebuildRunning();

        $this->assertRebuildNotHappened($state);
    }

    /**
     * @test
     */
    public function noRebuildBecauseUpToDate()
    {
        $state = $this->createState(true);
        $state->activateTable('team_sets_users_1');

        $this->assertRebuildNotHappened($state);
        $this->assertFalse($state->isRebuildRunning());
    }

    /**
     * @test
     * @dataProvider ignoreUpToDateProvider
     */
    public function upToDateDataIsRebuildOnlyIfForced($ignoreUpToDate, InvocationOrder $invocationRule)
    {
        $state = $this->createState(true);
        $state->activateTable('team_sets_users_1');

        $rebuild = $this->createMock(Invokable::class);
        $rebuild->expects($invocationRule)
            ->method('__invoke')
            ->with('team_sets_users_2');

        $stateAwareRebuild = $this->createStateAwareRebuild($state, $rebuild);
        $stateAwareRebuild($ignoreUpToDate);
    }

    public static function ignoreUpToDateProvider()
    {
        return [
            'not-forced' => [
                false,
                self::never(),
            ],
            'forced' => [
                true,
                self::once(),
            ],
        ];
    }

    private function assertRebuildNotHappened(State $state)
    {
        $rebuild = $this->createMock(Invokable::class);
        $rebuild->expects($this->never())
            ->method('__invoke');

        $stateAwareRebuild = $this->createStateAwareRebuild($state, $rebuild);

        [$result] = $stateAwareRebuild();
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function rebuildIsMarkedRunningDuringRebuild()
    {
        $state = $this->createState(true);
        $stateAwareRebuild = $this->expectRebuild(
            $state,
            $this->returnCallback(function () use ($state, &$wasRebuildRunning) {
                $wasRebuildRunning = $state->isRebuildRunning();
            })
        );

        $stateAwareRebuild();

        $this->assertTrue($wasRebuildRunning);
    }

    /**
     * @test
     */
    public function rebuildSuccess()
    {
        $state = $this->createState(true);
        $stateAwareRebuild = $this->expectRebuild($state, $this->returnValue(null));

        [$result] = $stateAwareRebuild();

        $this->assertTrue($result);
        $this->assertEquals('team_sets_users_2', $state->getActiveTable());
        $this->assertFalse($state->isRebuildRunning());
    }

    /**
     * @test
     */
    public function rebuildFailure()
    {
        $state = $this->createState(true);
        $stateAwareRebuild = $this->expectRebuild(
            $state,
            $this->throwException(new \Exception('Something went wrong'))
        );

        [$result, $message] = $stateAwareRebuild();

        $this->assertFalse($result);
        $this->assertStringContainsString('Something went wrong', $message);
        $this->assertEquals('team_sets_users_1', $state->getActiveTable());
        $this->assertFalse($state->isRebuildRunning());
    }

    private function expectRebuild(State $state, $will)
    {
        $state->activateTable('team_sets_users_1');
        $state->markOutOfDate();

        $rebuild = $this->createMock(Invokable::class);
        $rebuild->expects($this->once())
            ->method('__invoke')
            ->with('team_sets_users_2')
            ->will($will);

        return $this->createStateAwareRebuild($state, $rebuild);
    }

    private function createState($isEnabled)
    {
        global $sugar_config;
        $sugar_config['perfProfile']['TeamSecurity']['default']['use_denorm'] = $isEnabled;

        $config = SugarConfig::getInstance();
        $config->clearCache();

        $storage = new InMemoryStorage();

        $state = new State(
            $config,
            $storage,
            $this->createMock(LoggerInterface::class)
        );

        return $state;
    }

    private function createStateAwareRebuild(State $state, $command)
    {
        return new StateAwareRebuild(
            $state,
            $command,
            $this->createMock(LoggerInterface::class)
        );
    }
}
