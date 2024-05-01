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

namespace Sugarcrm\SugarcrmTests\UserUtils;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\CommandFactory;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneDefaultTeams;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFavoriteReports;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneFilters;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneNavigationBar;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneNotifyOnAssignment;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneRemindersOptions;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneScheduledReporting;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneSugarEmailClient;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CloneUserSettings;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CopyDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Commands\CopyFilters;
use Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteDashboards;
use Sugarcrm\Sugarcrm\UserUtils\Commands\DeleteFilters;
use Sugarcrm\Sugarcrm\UserUtils\Constants\CommandType;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\Invoker;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\InvokerPayloadFactory;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\Invoker\Invoker
 */
class InvokerTest extends TestCase
{
    /**
     * @var string[]|mixed
     */
    public $types;
    /**
     * @var mixed[]
     */
    public $classes;
    /**
     * @var mixed[]|array<mixed, array<string, mixed>>|mixed
     */
    public $payloadData;

    protected function setUp(): void
    {
        $this->types = [
            CommandType::CloneDashboards,
            CommandType::CloneDefaultTeams,
            CommandType::CloneFavoriteReports,
            CommandType::CloneFilters,
            CommandType::CloneNavigationBar,
            CommandType::CloneNotifyOnAssignment,
            CommandType::CloneRemindersOptions,
            CommandType::CloneScheduledReporting,
            CommandType::CloneSugarEmailClient,
            CommandType::CloneUserSettings,
            CommandType::CopyDashboards,
            CommandType::CopyFilters,
            CommandType::DeleteDashboards,
            CommandType::DeleteFilters,
        ];

        $this->classes = [
            CloneDashboards::class,
            CloneDefaultTeams::class,
            CloneFavoriteReports::class,
            CloneFilters::class,
            CloneNavigationBar::class,
            CloneNotifyOnAssignment::class,
            CloneRemindersOptions::class,
            CloneScheduledReporting::class,
            CloneSugarEmailClient::class,
            CloneUserSettings::class,
            CopyDashboards::class,
            CopyFilters::class,
            DeleteDashboards::class,
            DeleteFilters::class,
        ];

        $this->setupPayloadData();
    }

    protected function setupPayloadData()
    {
        $this->payloadData = [];
        foreach ($this->types as $key => $type) {
            $this->payloadData [] = [
                'type' => $type,
                'sourceUser' => '1',
                'destinationUsers' => ['user1', 'user2'],
                'destinationTeams' => ['1'],
                'destinationRoles' => [],
                'modules' => ['Home'],
                'dashboards' => ['dashboardId'],
                'filters' => ['filterId'],
            ];
        }
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::getCommands
     */
    public function testGetCommands()
    {
        $invoker = new Invoker($this->payloadData);
        $commands = $invoker->getCommands();
        $this->assertCount(safeCount($this->types), $commands);

        foreach ($commands as $key => $command) {
            $this->assertInstanceOf($this->classes[$key], $command);
        }
    }

    /**
     * @covers ::setCommands
     */
    public function testSetCommands()
    {
        $commands = [];
        foreach ($this->types as $key => $type) {
            $payloadData = [
                'type' => $type,
                'sourceUser' => '1',
                'destinationUsers' => ['user1', 'user2'],
                'destinationTeams' => ['1'],
                'destinationRoles' => [],
                'modules' => ['Home'],
                'dashboards' => ['dashboardId'],
                'filters' => ['filterId'],
            ];
            $payload = InvokerPayloadFactory::getInvokerPayload($payloadData);
            $command = CommandFactory::getCommand($type, $payload);
            $commands [] = $command;
        }

        $invoker = new Invoker([]);
        $invoker->setCommands($commands);
        $invokerCommands = $invoker->getCommands();
        $this->assertEquals($commands, $invokerCommands);
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $commandMocks = [];
        $invoker = new Invoker($this->payloadData);
        $commands = $invoker->getCommands();
        foreach ($commands as $key => $command) {
            $class = $this->classes[$key];
            $mock = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->onlyMethods(['execute'])->getMock();
            $mock->expects($this->once())->method('execute');
            $commandMocks [] = $mock;
        }
        $invoker->setCommands($commandMocks);
        $invoker->execute();
    }
}
