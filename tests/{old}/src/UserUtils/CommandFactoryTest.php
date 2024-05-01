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
use Sugarcrm\Sugarcrm\UserUtils\Invoker\InvokerPayloadFactory;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\CommandFactory
 */
class CommandFactoryTest extends TestCase
{
    protected function setUp(): void
    {
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::getCommand
     */
    public function testGetCommand()
    {
        $types = [
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

        $classes = [
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

        foreach ($types as $key => $type) {
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
            $this->assertInstanceOf($classes[$key], $command);
        }
    }
}
