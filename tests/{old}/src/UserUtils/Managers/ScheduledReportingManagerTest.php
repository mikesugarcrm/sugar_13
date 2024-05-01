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

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerBasePayload;
use Sugarcrm\Sugarcrm\UserUtils\Managers\ScheduledReportingManager;
use SugarTestHelper;
use SugarTestScheduledReportsUtilities;

class ScheduledReportingManagerTest extends TestCase
{
    /**
     * setUpBeforeClass function
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        global $current_user;
        /**
         * @var User
         */
        $current_user = BeanFactory::newBean('Users');
        $current_user->getSystemUser();
    }

    protected function setUp(): void
    {
        SugarTestScheduledReportsUtilities::removeReportSchedule('sreport_id');
        SugarTestScheduledReportsUtilities::createScheduledReport('sreport_id', [
            'name' => 'sreport_name',
            'assigned_user_id' => '1',
            'report_id' => 'report_id',
        ]);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestScheduledReportsUtilities::removeAllCreatedScheduledReports();
        SugarTestScheduledReportsUtilities::removeReportScheduleByName('sreport_name');
    }

    /**
     * provider for cloning scheduled reports;
     *
     * @return array
     */
    public function providerCloneScheduledReporting(): array
    {
        return [
            [
                'args' => [
                    'type' => 'CloneScheduledReporting',
                    'sourceUser' => '1',
                    'destinationUsers' => ['seed_jim_id', 'seed_will_id'],
                    'destinationTeams' => ['West'],
                    'destinationRoles' => [],
                    'modules' => ['Accounts'],
                    'dashboards' => ['dashboardId'],
                    'filters' => ['filterId'],
                ],
            ],
        ];
    }

    /**
     * test for cloning scheduled reporting
     *
     * @param array $args
     * @return void
     * @dataProvider providerCloneScheduledReporting
     */
    public function testCloneScheduledReporting($args): void
    {
        $payload = new InvokerBasePayload($args);
        $manager = new ScheduledReportingManager($payload);
        $manager->cloneScheduledReporting();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $reportSchedules = $manager->getScheduledReports($userId);
            $names = array_map(function ($item) {
                return $item['name'];
            }, $reportSchedules);
            $this->assertContains('sreport_name', $names);
        }
    }
}
