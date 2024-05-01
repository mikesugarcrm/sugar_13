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

use PHPUnit\Framework\TestCase;

/**
 * RS-278: Fix Dashboards regression caused by RussianStandard PR #17355
 * Because of rename `view` field to `view_name` test checks that `view` argument has the same behavior as `view_name`
 */
class RS278Test extends TestCase
{
    /** @var RestService */
    protected static $service = null;

    /** @var array */
    protected $beans = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        foreach ($this->beans as $module => $ids) {
            foreach ($ids as $id) {
                $bean = BeanFactory::getBean($module, $id);
                if ($bean instanceof SugarBean) {
                    $bean->mark_deleted($bean->id);
                }
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::$service = null;
        SugarTestHelper::tearDown();
    }

    /**
     * Creating dashboard through DashboardAPI without 'view'
     *
     * @return array
     */
    public function testCreateDashboardWithoutView()
    {
        $args = [
            'metadata' => [
                'components' => [
                    [
                        'rows' => [
                            [
                                [
                                    'view' => [
                                        'label' => 'LBL_DASHLET_PIPLINE_NAME',
                                        'type' => 'sales-pipeline',
                                        'visibility' => 'user',
                                    ],
                                ],
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
        ];

        $api = new DashboardApi();
        $actual = $api->createDashboard(self::$service, $args);
        $this->assertNotEmpty($actual);
        $this->assertEquals('', $actual['view']);
        return $actual;
    }

    /**
     * Creating dashboard through DashboardAPI with 'view' only
     *
     * @return array
     */
    public function testCreateDashboardWithView()
    {
        $args = [
            'metadata' => [
                'components' => [
                    [
                        'rows' => [
                            [
                                [
                                    'context' => [
                                        'module' => 'Accounts',
                                    ],
                                    'view' => [
                                        'display_columns' => [
                                            'name',
                                            'billing_address_country',
                                            'billing_address_city',
                                        ],
                                        'label' => 'LBL_DASHLET_MY_MODULE',
                                        'type' => 'dashablelist',
                                    ],
                                    'width' => 12,
                                ],
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
            'module' => 'Leads',
            'name' => 'LBL_DEFAULT_DASHBOARD_TITLE',
            'view' => 'records',
        ];

        $api = new DashboardApi();
        $actual = $api->createDashboard(self::$service, $args);
        $this->assertNotEmpty($actual);
        $this->assertEquals($args['view'], $actual['view']);
        return $actual;
    }

    /**
     * Creating dashboard through DashboardAPI with 'view' and 'view_name'
     *
     * @return array
     */
    public function testCreateDashboardWithBoth()
    {
        $args = [
            'metadata' => [
                'components' => [
                    [
                        'rows' => [
                            [
                                [
                                    'context' => [
                                        'module' => 'Accounts',
                                    ],
                                    'view' => [
                                        'display_columns' => [
                                            'name',
                                            'billing_address_country',
                                            'billing_address_city',
                                        ],
                                        'label' => 'LBL_DASHLET_MY_MODULE',
                                        'type' => 'dashablelist',
                                    ],
                                    'width' => 12,
                                ],
                            ],
                        ],
                        'width' => 12,
                    ],
                ],
            ],
            'module' => 'Contacts',
            'name' => 'LBL_DEFAULT_DASHBOARD_TITLE',
            'view' => '',
            'view_name' => 'records',
        ];

        $api = new DashboardApi();
        $actual = $api->createDashboard(self::$service, $args);
        $this->assertNotEmpty($actual);
        $this->assertEquals($args['view_name'], $actual['view']);
        $this->assertEquals($args['view_name'], $actual['view_name']);
        return $actual;
    }

    /**
     * Fetching created dashboard without 'view'
     *
     * @depends testCreateDashboardWithoutView
     * @param $expected
     */
    public function testGetDashboardsWithoutView($expected)
    {
        $args = [
            'max_num' => '20',
        ];

        $api = new DashboardListApi();
        $actual = $api->getDashboards(self::$service, $args);
        $this->assertNotEmpty($actual);
        $this->assertNotEmpty($actual['records']);
        $actual = reset($actual['records']);
        $this->beans['Dashboard'][] = $expected['id'];
        $this->assertEquals($expected['id'], $actual['id']);
        $this->assertEquals($expected['view'], $actual['view']);
    }

    /**
     * Fetching created dashboard with 'view'
     *
     * @depends testCreateDashboardWithView
     * @param $expected
     */
    public function testGetDashboardsWithView($expected)
    {
        $args = [
            'fields' => '',
            'max_num' => '20',
            'module' => 'Leads',
            'view' => 'records',
        ];

        $api = new DashboardListApi();
        $actual = $api->getDashboards(self::$service, $args);
        $this->assertNotEmpty($actual);
        $actual = reset($actual['records']);
        $this->beans['Dashboard'][] = $expected['id'];
        $this->assertEquals($expected['id'], $actual['id']);
        $this->assertEquals($expected['view'], $actual['view']);
    }

    /**
     * Fetching created dashboard with 'view' and 'view_name'
     *
     * @depends testCreateDashboardWithBoth
     * @param $expected
     */
    public function testGetDashboardsWithBoth($expected)
    {
        $args = [
            'fields' => '',
            'max_num' => '20',
            'module' => 'Contacts',
            'view' => '',
            'view_name' => 'records',
        ];

        $api = new DashboardListApi();
        $actual = $api->getDashboards(self::$service, $args);
        $this->assertNotEmpty($actual);
        $actual = reset($actual['records']);
        $this->beans['Dashboard'][] = $expected['id'];
        $this->assertEquals($expected['id'], $actual['id']);
        $this->assertEquals($expected['view_name'], $actual['view']);
        $this->assertEquals($expected['view_name'], $actual['view_name']);
    }
}
