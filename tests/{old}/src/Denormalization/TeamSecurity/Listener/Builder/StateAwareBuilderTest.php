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

namespace Sugarcrm\SugarcrmTests\Denormalization\TeamSecurity\Listener\Builder;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Builder\StateAwareBuilder;
use Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\State;

/**
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Builder\StateAwareBuilder
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Composite::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Invalidator::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\NullListener::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Recorder::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\StateAwareListener::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\UserOnly::__toString
 * @covers \Sugarcrm\Sugarcrm\Denormalization\TeamSecurity\Listener\Updater::__toString
 */
class StateAwareBuilderTest extends TestCase
{
    /**
     * @test
     *
     * Normal disabled state.
     */
    public function disabledOutOfDate()
    {
        $this->assertEquals(
            'Null()',
            (string)$this->createListener(false, null, false, null, null)
        );
    }

    /**
     * @test
     *
     * When the usage of denormalized data has been just disabled, we need to invalidate existing data upon the first
     * following change. Otherwise, if re-enabled again, this change will not have been processed but the data will
     * still be marked as up to date.
     */
    public function disabledUpToDate()
    {
        $this->assertEquals(
            'Invalidator()',
            (string)$this->createListener(false, null, true, null, null)
        );
    }

    /**
     * @test
     *
     * The usage of denormalized data has been just enabled on an existing instance.
     */
    public function enabledUnavailableRebuildNotRunning()
    {
        $this->assertEquals(
            'Null()',
            (string)$this->createListener(true, false, null, false, null)
        );
    }

    /**
     * @test
     *
     * The usage of denormalized data has been just enabled on an existing instance and the first rebuild is running.
     */
    public function enabledUnavailableRebuildRunning()
    {
        $this->assertEquals(
            'Recorder()',
            (string)$this->createListener(true, false, null, true, null)
        );
    }

    /**
     * @test
     *
     * Normal operation with admin updates handled offline.
     */
    public function enabledAvailableUpToDateRebuildNotRunningAdminOffline()
    {
        $this->assertEquals(
            'UserOnly(Updater("active_table"), Invalidator())',
            (string)$this->createListener(true, true, true, false, false)
        );
    }

    /**
     * @test
     *
     * An admin update has been already missed, no need to invalidate the data again.
     */
    public function enabledAvailableOutOfDateRebuildNotRunningAdminOffline()
    {
        $this->assertEquals(
            'UserOnly(Updater("active_table"), Null())',
            (string)$this->createListener(true, true, false, false, false)
        );
    }

    /**
     * @test
     *
     * Normal operation with admin updates handled inline. When inline admin updates are available, it's irrelevant
     * if the data is up to date or not, we just apply all new changes.
     */
    public function enabledAvailableRebuildNotRunningAdminInline()
    {
        $this->assertEquals(
            'Updater("active_table")',
            (string)$this->createListener(true, true, null, false, true)
        );
    }

    /**
     * @test
     *
     * In this state, we need to apply user updates immediately and record all of them to replay after the rebuild.
     */
    public function enabledAvailableOutOfDateRebuildRunningAdminOffline()
    {
        $this->assertEquals(
            'Composite(UserOnly(Updater("active_table"), Null()), Recorder())',
            (string)$this->createListener(true, true, false, true, false)
        );
    }

    /**
     * @test
     *
     * In this state, we need to apply user updates immediately and record all of them to replay after the rebuild.
     * When inline admin updates are available, it's irrelevant if the data is up to date or not,
     * we just apply and record all new changes.
     */
    public function enabledAvailableRebuildRunningAdminInline()
    {
        $this->assertEquals(
            'Composite(Updater("active_table"), Recorder())',
            (string)$this->createListener(true, true, null, true, true)
        );
    }

    private function createListener(
        $isEnabled,
        $isAvailable,
        $isUpToDate,
        $isRebuildRunning,
        $shouldHandleAdminUpdatesInline
    ) {

        return (new StateAwareBuilder(
            $this->createMock(Connection::class),
            $this->createState([
                'isEnabled' => $isEnabled,
                'isAvailable' => $isAvailable,
                'isUpToDate' => $isUpToDate,
                'isRebuildRunning' => $isRebuildRunning,
                'shouldHandleAdminUpdatesInline' => $shouldHandleAdminUpdatesInline,
            ])
        ))->createListener();
    }

    private function createState(array $configuration)
    {
        $state = $this->createMock(State::class);

        foreach ($configuration as $method => $value) {
            if ($value !== null) {
                $state->expects($this->atLeastOnce())
                    ->method($method)
                    ->willReturn($value);
            } else {
                $state->expects($this->never())
                    ->method($method);
            }
        }

        $state->method('getActiveTable')
            ->willReturn('active_table');

        return $state;
    }
}
