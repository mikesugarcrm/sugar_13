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
 * @coversDefaultClass OpportunitiesFilterApi
 */
class OpportunitiesFilterApiTest extends TestCase
{
    /**
     * @var \RestService
     */
    public $service;
    /**
     * @var OpportunitiesFilterApi
     */
    protected $api;

    public const TOP_LEVEL_MANAGER = 'fb9de8cc-1111-1111-1111-acde48001122';
    public const MID_LEVEL_MANAGER = 'fb9de8cc-2222-2222-2222-acde48001122';
    public const SALES_REP = 'fb9de8cc-3333-3333-3333-acde48001122';
    public const TIME_PERIOD = 'fb9de8cc-4444-4444-4444-acde48001122';

    protected function setUp(): void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OpportunitiesFilterApi();
    }

    protected function tearDown(): void
    {
        unset($this->api);
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        self::createTestUsers();
        self::createTestTimePeriods();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @param $filterDef
     * @param $forecastTimePeriodId
     * @param $expected
     * @dataProvider dataProviderTestAddForecastDateClosedFilter
     * @covers ::addForecastDateClosedFilter
     */
    public function testAddForecastDateClosedFilter($filterDef, $forecastTimePeriodId, $expected)
    {
        $actualResult = SugarTestReflection::callProtectedMethod($this->api, 'addForecastDateClosedFilter', [
            $filterDef, $forecastTimePeriodId,
        ]);

        $this->assertSame($expected, $actualResult);
    }

    public function dataProviderTestAddForecastDateClosedFilter()
    {
        return [
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'forecastTimePeriodId' => self::TIME_PERIOD,
                'expected' => [
                    ['test' => 'test'],
                    [
                        'date_closed' => [
                            '$dateBetween' => [
                                '2022-01-01',
                                '2022-04-01',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $filterDef
     * @param $forecastUserType
     * @param $forecastUserId
     * @param $expected
     * @dataProvider dataProviderTestAddForecastAssignedUserFilter
     * @covers ::addForecastAssignedUserFilter
     */
    public function testAddForecastAssignedUserFilter($filterDef, $forecastUserType, $forecastUserId, $expected)
    {
        $actualResult = SugarTestReflection::callProtectedMethod($this->api, 'addForecastAssignedUserFilter', [
            $filterDef, $forecastUserType, $forecastUserId,
        ]);

        $this->assertSame($expected, $actualResult);
    }

    public function dataProviderTestAddForecastAssignedUserFilter()
    {
        return [
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'forecastUserType' => 'Rollup',
                'forecastUserId' => self::TOP_LEVEL_MANAGER,
                'expected' => [
                    ['test' => 'test'],
                    [
                        'assigned_user_id' => [
                            '$in' => [
                                self::MID_LEVEL_MANAGER,
                                self::SALES_REP,
                                self::TOP_LEVEL_MANAGER,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'forecastUserType' => 'Rollup',
                'forecastUserId' => self::MID_LEVEL_MANAGER,
                'expected' => [
                    ['test' => 'test'],
                    [
                        'assigned_user_id' => [
                            '$in' => [
                                self::SALES_REP,
                                self::MID_LEVEL_MANAGER,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'forecastUserType' => 'Direct',
                'forecastUserId' => self::MID_LEVEL_MANAGER,
                'expected' => [
                    ['test' => 'test'],
                    [
                        'assigned_user_id' => [
                            '$in' => [
                                self::MID_LEVEL_MANAGER,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'forecastUserType' => 'Direct',
                'forecastUserId' => self::SALES_REP,
                'expected' => [
                    ['test' => 'test'],
                    [
                        'assigned_user_id' => [
                            '$in' => [
                                self::SALES_REP,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param $filterDef
     * @param $metrics
     * @param $expected
     * @dataProvider dataProviderTestAddForecastMetricsFilter
     * @covers ::addForecastMetricsFilter
     */
    public function testAddForecastMetricsFilter($filterDef, $metrics, $expected)
    {
        $actualResult = SugarTestReflection::callProtectedMethod($this->api, 'addForecastMetricsFilter', [
            $filterDef, $metrics,
        ]);

        $this->assertEquals($expected, $actualResult);
    }

    public function dataProviderTestAddForecastMetricsFilter()
    {
        return [
            [
                'filterDef' => [
                    ['test' => 'test'],
                ],
                'metrics' => [
                    'test' => [
                        'filter' => [
                            [
                                'testField' => 'testValue',
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    ['test' => 'test'],
                    ['testField' => 'testValue'],
                ],
            ],
            [
                'filterDef' => [
                    ['testA' => 'testA'],
                    ['testB' => 'testB'],
                ],
                'metrics' => [
                    'test1' => [
                        'filter' => [
                            [
                                'testField1' => 'testValue1',
                            ],
                        ],
                    ],
                    'test2' => [
                        'filter' => [
                            [
                                'testField2' => 'testValue2',
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    ['testA' => 'testA'],
                    ['testB' => 'testB'],
                    ['testField1' => 'testValue1'],
                    ['testField2' => 'testValue2'],
                ],
            ],
        ];
    }

    /**
     * Creates User hierarchy
     */
    protected static function createTestUsers()
    {
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::TOP_LEVEL_MANAGER,
        ]);
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::MID_LEVEL_MANAGER,
            'reports_to_id' => self::TOP_LEVEL_MANAGER,
        ]);
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::SALES_REP,
            'reports_to_id' => self::MID_LEVEL_MANAGER,
        ]);
    }

    /**
     * Creates time periods
     */
    protected static function createTestTimePeriods()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2022-01-01', '2022-04-01', null, null, [
            'id' => self::TIME_PERIOD,
        ]);
    }
}
