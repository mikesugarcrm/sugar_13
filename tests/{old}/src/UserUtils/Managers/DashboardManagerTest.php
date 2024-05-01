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

namespace Sugarcrm\SugarcrmTests\UserUtils\Managers;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerDashboardsPayload;
use Sugarcrm\Sugarcrm\UserUtils\Managers\DashboardManager;
use SugarTestDashboardUtilities;
use SugarTestHelper;
use SugarTestUserUtilities;

class DashboardManagerTest extends TestCase
{
    /**
     * @var \User|mixed
     */
    public $anonymousUser1;
    /**
     * @var \User|mixed
     */
    public $anonymousUser2;

    protected function setUp(): void
    {
        SugarTestDashboardUtilities::removeDashboard('dashboardId');
        SugarTestDashboardUtilities::createDashboard('dashboardId', [
            'name' => 'testName',
            'assigned_user_id' => '1',
            'team_id' => '1',
            'default_dashboard' => 0,
            'dashboard_module' => 'Home',
        ]);
        $this->anonymousUser1 = SugarTestUserUtilities::createAnonymousUser();
        $this->anonymousUser2 = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestDashboardUtilities::removeAllCreatedDashboards();
        SugarTestDashboardUtilities::removeDashboardsByName('testName');
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Data provider for the clone test
     *
     * @return array
     */
    public function providerClone(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneDashboards',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Home'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * @covers       clone
     *
     * @param array $args
     *
     * @dataProvider providerClone
     */
    public function testClone(array $args): void
    {
        $payload = new InvokerDashboardsPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new DashboardManager($payload);
        $manager->clone();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $dashboards = $manager->getDashboards($userId, 'Home');
            $dashboardNames = array_map(function ($item) {
                return $item['name'];
            }, $dashboards);

            $this->assertCount(1, $dashboards);
            $this->assertContains('testName', $dashboardNames);
        }
    }

    /**
     * Data provider for the clone test
     *
     * @return array
     */
    public function providerCopy(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CopyDashboards',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Home'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * @covers       copy
     *
     * @param array $args
     *
     * @dataProvider providerCopy
     */
    public function testCopy(array $args): void
    {
        $payload = new InvokerDashboardsPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new DashboardManager($payload);
        $manager->copy();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $dashboards = $manager->getDashboards($userId, 'Home');
            $dashboardNames = array_map(function ($item) {
                return $item['name'];
            }, $dashboards);

            $this->assertContains('testName', $dashboardNames);
        }
    }

    /**
     * Data provider for the delete test
     *
     * @return array
     */
    public function providerDelete(): array
    {
        return [
            [
                'args' => [
                    'type' => 'DeleteDashboards',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Home'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * @covers       delete
     *
     * @param array $args
     *
     * @dataProvider providerDelete
     */
    public function testDelete(array $args): void
    {
        $payload = new InvokerDashboardsPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new DashboardManager($payload);
        $manager->delete();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $dashboards = $manager->getDashboards($userId, 'Home');
            $dashboardNames = array_map(function ($item) {
                return $item['name'];
            }, $dashboards);
            $this->assertNotContains('testName', $dashboardNames);
        }
    }
}
