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
use Sugarcrm\Sugarcrm\UserUtils\Invoker\payloads\InvokerFiltersPayload;
use Sugarcrm\Sugarcrm\UserUtils\Managers\FiltersManager;
use SugarTestFilterUtilities;
use SugarTestHelper;
use SugarTestUserUtilities;

class FiltersManagerTest extends TestCase
{
    /**
     * @var \User|mixed
     */
    public $anonymousUser1;
    /**
     * @var \User|mixed
     */
    public $anonymousUser2;

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
        SugarTestFilterUtilities::removeFilter('filterId');
        SugarTestFilterUtilities::createUserFilter('1', 'some_filter', json_encode([]), 'filterId', 'Accounts');
        $this->anonymousUser1 = SugarTestUserUtilities::createAnonymousUser();
        $this->anonymousUser2 = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestFilterUtilities::removeFiltersByName('some_filter');
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
                    'type' => 'CloneFilters',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
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
     * @covers       clone
     *
     * @param array $args
     *
     * @dataProvider providerClone
     */
    public function testClone(array $args): void
    {
        $payload = new InvokerFiltersPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new FiltersManager($payload);
        $manager->clone();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $filters = $manager->getFilters($userId, 'Accounts');
            $filterNames = array_map(function ($item) {
                return $item['name'];
            }, $filters);
            $this->assertCount(1, $filters);
            $this->assertContains('some_filter', $filterNames);
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
                    'type' => 'CopyFilters',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
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
     * @covers       copy
     *
     * @param array $args
     *
     * @dataProvider providerCopy
     */
    public function testCopy(array $args): void
    {
        $payload = new InvokerFiltersPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new FiltersManager($payload);
        $manager->copy();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $filters = $manager->getFilters($userId, 'Accounts');
            $filterNames = array_map(function ($item) {
                return $item['name'];
            }, $filters);
            $this->assertContains('some_filter', $filterNames);
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
                    'type' => 'DeleteFilters',
                    'sourceUser' => '1',
                    'destinationUsers' => [],
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
     * @covers       delete
     *
     * @param array $args
     *
     * @dataProvider providerDelete
     */
    public function testDelete(array $args): void
    {
        $payload = new InvokerFiltersPayload($args);
        $payload->setDestinationUsers([$this->anonymousUser1->id, $this->anonymousUser2->id,]);
        $manager = new FiltersManager($payload);
        $manager->delete();

        $users = $payload->getDestinationUsers();
        foreach ($users as $userId) {
            $filters = $manager->getFilters($userId, 'Accounts');
            $filterNames = array_map(function ($item) {
                return $item['name'];
            }, $filters);
            $this->assertNotContains('some_filter', $filterNames);
        }
    }
}
