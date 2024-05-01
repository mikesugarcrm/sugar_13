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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity;

use DomainException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SplObserver;
use SugarConfig;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State\Storage\InMemoryStorage;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State
 */
class StateTest extends TestCase
{
    /**
     * @test
     */
    public function defaultTableIsNull()
    {
        $state = $this->createState(
            $this->createStorage([])
        );

        $this->assertNull($state->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidStateIsIgnored()
    {
        $state = $this->createState(
            $this->createStorage([
                State::STATE_ACTIVE_TABLE => 'team_sets_users_3',
            ])
        );

        $this->assertNull($state->getActiveTable());
    }

    /**
     * @test
     */
    public function validStateIsPreserved()
    {
        $state = $this->createState(
            $this->createStorage([
                State::STATE_ACTIVE_TABLE => 'team_sets_users_1',
            ])
        );

        $this->assertSame('team_sets_users_1', $state->getActiveTable());
    }

    /**
     * @test
     */
    public function validTableCanBeActivated()
    {
        $storage = new InMemoryStorage();
        $state = $this->createState($storage);

        $state->activateTable('team_sets_users_2');

        $this->assertSame('team_sets_users_2', $state->getActiveTable());
    }

    /**
     * @test
     */
    public function invalidTableCanNotBeActivated()
    {
        $state = $this->createState(
            $this->createStorage([])
        );

        $this->expectException(DomainException::class);
        $state->activateTable('team_sets_users_3');
    }

    /**
     * @test
     * @dataProvider targetIsRotatedProvider
     */
    public function tablesAreRotated($activeTable, $expectedStandby)
    {
        $state = $this->createState(
            $this->createStorage([
                State::STATE_ACTIVE_TABLE => $activeTable,
            ])
        );

        $this->assertSame($expectedStandby, $state->getStandbyTable());
    }

    public static function targetIsRotatedProvider()
    {
        return [
            'null-state' => [null, 'team_sets_users_1'],
            'first-to-second' => ['team_sets_users_1', 'team_sets_users_2'],
            'second-to-first' => ['team_sets_users_2', 'team_sets_users_1'],
        ];
    }

    /**
     * @test
     */
    public function deactivation()
    {
        global $sugar_config;
        $sugar_config['perfProfile']['TeamSecurity']['default']['use_denorm'] = false;

        $config = SugarConfig::getInstance();
        $config->clearCache();

        $storage = $this->createStorage([
            State::STATE_ACTIVE_TABLE => 'team_sets_users_1',
        ]);

        $storage->expects($this->once())
            ->method('update')
            ->with(State::STATE_ACTIVE_TABLE, null);

        $state = new State($config, $storage, $this->createLogger());
        $this->assertFalse($state->isEnabled());
        $this->assertFalse($state->isAvailable());
    }

    /**
     * @test
     */
    public function testInvalidation()
    {
        $storage = $this->createStorage([
            State::STATE_ACTIVE_TABLE => 'team_sets_users_1',
            State::STATE_UP_TO_DATE => true,
        ]);
        $storage->expects($this->once())
            ->method('update')
            ->with(State::STATE_UP_TO_DATE, false);

        $state = $this->createState($storage);

        $this->assertTrue($state->isUpToDate());

        $state->markOutOfDate();
    }

    /**
     * @test
     */
    public function rebuildStart()
    {
        $storage = $this->createStorage([
            State::STATE_REBUILD_RUNNING => false,
        ]);
        $storage->expects($this->once())
            ->method('update')
            ->with(State::STATE_REBUILD_RUNNING, true);

        $state = $this->createState($storage);

        $this->assertFalse($state->isRebuildRunning());

        $state->markRebuildRunning();
    }

    /**
     * @test
     */
    public function rebuildStop()
    {
        $storage = $this->createStorage([
            State::STATE_REBUILD_RUNNING => true,
        ]);
        $storage->expects($this->once())
            ->method('update')
            ->with(State::STATE_REBUILD_RUNNING, false);

        $state = $this->createState($storage);

        $this->assertTrue($state->isRebuildRunning());

        $state->markRebuildNotRunning();
    }

    /**
     * @test
     */
    public function notificationOnlyWhenStateChanges()
    {
        $isRunning = false;

        $storage = $this->createMock(Storage::class);
        $storage->expects($this->any())
            ->method('get')
            ->willReturnCallback(function () use (&$isRunning) {
                return $isRunning;
            });

        $state = $this->createState($storage);

        $observer = $this->createMock(SplObserver::class);
        $observer->expects($this->once())
            ->method('update')
            ->with($state);

        $state->attach($observer);
        $state->markRebuildRunning();

        $isRunning = true;
        $state->markRebuildRunning();

        $state->detach($observer);
        $state->markRebuildNotRunning();
    }

    /**
     * @test
     */
    public function unexpectedStateTransition()
    {
        $state = $this->createState(
            $this->createStorage([
                State::STATE_REBUILD_RUNNING => false,
            ])
        );

        $observer = $this->createMock(SplObserver::class);
        $observer->expects($this->never())
            ->method('update')
            ->with($state);

        $state->attach($observer);
        $state->markRebuildNotRunning();
    }

    /**
     * @test
     */
    public function shouldHandleAdminUpdatesInline()
    {
        $state = $this->createState(
            $this->createStorage([])
        );

        $this->assertTrue($state->shouldHandleAdminUpdatesInline());
    }

    private function createStorage(array $params)
    {
        $params = array_merge([
            State::STATE_ACTIVE_TABLE => null,
        ], $params);

        $storage = $this->createMock(Storage::class);
        $storage->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($var) use ($params) {
                return $params[$var];
            });

        return $storage;
    }

    private function createLogger()
    {
        return $this->createMock(LoggerInterface::class);
    }

    private function createState(Storage $storage)
    {
        global $sugar_config;
        $sugar_config['perfProfile']['TeamSecurity']['default']['use_denorm'] = true;
        $sugar_config['perfProfile']['TeamSecurity']['inline_update'] = true;

        $config = SugarConfig::getInstance();
        $config->clearCache();

        return new State($config, $storage, $this->createLogger());
    }
}
