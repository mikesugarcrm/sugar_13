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
use Sugarcrm\Sugarcrm\UserUtils\Constants\CommandType;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\InvokerPayloadFactory;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerDashboardsPayload;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerFiltersPayload;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerUserSettingsPayload;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\UserUtils\Invoker\InvokerPayloadFactory
 */
class InvokerPayloadFactoryTest extends TestCase
{
    /**
     * @var string[]|mixed
     */
    public $types;
    /**
     * @var mixed[]
     */
    public $classesMap;
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
        $this->classesMap = [
            InvokerDashboardsPayload::class,
            InvokerBasePayload::class,
            InvokerBasePayload::class,
            InvokerFiltersPayload::class,
            InvokerBasePayload::class,
            InvokerBasePayload::class,
            InvokerBasePayload::class,
            InvokerBasePayload::class,
            InvokerBasePayload::class,
            InvokerUserSettingsPayload::class,
            InvokerDashboardsPayload::class,
            InvokerFiltersPayload::class,
            InvokerDashboardsPayload::class,
            InvokerFiltersPayload::class,
        ];

        $this->setupPayloadData();
    }

    protected function setupPayloadData()
    {
        $this->payloadData = [];
        foreach ($this->types as $type) {
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
     * @covers ::getInvokerPayload
     */
    public function testGetInvokerPayload()
    {
        foreach ($this->payloadData as $key => $data) {
            $invokerPayload = InvokerPayloadFactory::getInvokerPayload($data);
            $this->assertInstanceOf($this->classesMap[$key], $invokerPayload);
        }
    }
}
