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
 * RS-144
 * Prepare ForecastManagerWorksheetsFilter Api
 * @coversDefaultClass ForecastManagerWorksheetsFilterApi
 */
class ForecastManagerWorksheetsFilterApiTest extends TestCase
{
    /** @var RestService */
    protected $service = null;

    /** @var TimePeriod */
    protected $timeperiod = null;

    /** @var Quota */
    protected $quota = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        $this->quota = SugarTestQuotaUtilities::createQuota();
        $this->quota->user_id = $GLOBALS['current_user']->id;
        $this->quota->quota_type = 'Rollup';
        $this->quota->timeperiod_id = $this->timeperiod->id;
        $this->quota->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts that createFilter method will receive right parameters after forecastManagerWorksheetsGet method call
     *
     * @dataProvider getDataForForecastManagerWorksheetsGet
     *
     * @param array $args
     * @param mixed $expectedUserId
     * @param mixed $expectedTimePeriodId
     * @param mixed $expectedType
     * @covers ::forecastManagerWorksheetsGet
     */
    public function testForecastManagerWorksheetsGet($args, $expectedUserId, $expectedTimePeriodId, $expectedType)
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', ['createFilter', 'filterList']);
        $api->expects($this->once())->method('createFilter')->with($this->equalTo($this->service), $this->equalTo($expectedUserId), $this->equalTo($expectedTimePeriodId));
        $api->forecastManagerWorksheetsGet($this->service, $args);
    }

    /**
     * Data Provider for testForecastManagerWorksheetsGet
     *
     * @return array
     */
    public function getDataForForecastManagerWorksheetsGet()
    {
        return [
            [
                [
                    'module' => 'ForecastManagerWorksheets',
                ],
                false,
                false,
                false,
            ],
            [
                [
                    'module' => 'ForecastManagerWorksheets',
                    'user_id' => 1,
                    'timeperiod_id' => 2,
                ],
                1,
                2,
                3,
            ],
        ];
    }

    /**
     * Test asserts that chart data has right structure if we pass user & time period
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGet()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', ['ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers']);
        $api->expects($this->once())->method('ForecastManagerWorksheetsGet')->will($this->returnValue(['records' => []]));
        $api->expects($this->once())->method('getDirectHierarchyUsers')->will($this->returnValue(['records' => [
            [
                'id' => $GLOBALS['current_user']->id,
                'full_name' => $GLOBALS['current_user']->full_name,
            ],
        ],
        ]));
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, [
            'timeperiod_id' => $this->timeperiod->id,
            'user_id' => $GLOBALS['current_user']->id,
        ]);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that chart data has right structure if we don't pass user & time period
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGetNoData()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', ['ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers']);
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, [
            'no_data' => 1,
        ]);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('data', $actual);
        $this->assertArrayHasKey('quota', $actual);
    }

    /**
     * Test asserts that we have target_quota if we need that
     * @covers ::forecastManagerWorksheetsChartGet
     */
    public function testForecastManagerWorksheetsChartGetTargetQuota()
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', ['ForecastManagerWorksheetsGet', 'getDirectHierarchyUsers']);
        $api->expects($this->never())->method('ForecastManagerWorksheetsGet');
        $api->expects($this->never())->method('getDirectHierarchyUsers');
        $actual = $api->forecastManagerWorksheetsChartGet($this->service, [
            'no_data' => 1,
            'target_quota' => 1,
            'timeperiod_id' => $this->timeperiod->id,
            'user_id' => $GLOBALS['current_user']->id,
        ]);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('target_quota', $actual);
        $this->assertEquals(500, $actual['target_quota']);
    }

    /**
     * We should get current_user if there are no parameters
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsers()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);

        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', [
            $this->service,
            [],
        ]);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $result = [];
        foreach ($actual['records'] as $value) {
            array_push($result, $value['id']);
        }

        $this->assertEqualsCanonicalizing([$GLOBALS['current_user']->id, $user->id], $result);
    }

    /**
     * We should get custom user if current_user is manager and request him
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsersCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', [
            $this->service,
            [
                'user_id' => $user->id,
            ],
        ]);
        $this->assertNotEmpty($actual);
        $this->assertArrayHasKey('records', $actual);
        $record = reset($actual['records']);
        $this->assertNotEmpty($record);
        $this->assertEquals($user->id, $record['id']);
    }

    /**
     * We should get exception if custom user doesn't present in system
     *
     * @covers ::getDirectHierarchyUsers
     */
    public function testGetDirectHierarchyUsersCustomUserInvalidParameter()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod($api, 'getDirectHierarchyUsers', [
            $this->service,
            [
                'user_id' => '-1',
            ],
        ]);
    }

    /**
     * Test asserts that createFilter method will receive right parameters after filterList method call
     *
     * @dataProvider getDataForFilterList
     * @param array $args
     * @param mixed $expectedAssignedUser
     * @param mixed $expectedTimePeriod
     * @param mixed $expectedType
     * @covers ::filterList
     */
    public function testFilterList($args, $expectedAssignedUser, $expectedTimePeriod, $expectedType)
    {
        $api = $this->createPartialMock('ForecastManagerWorksheetsFilterApi', ['createFilter']);
        $api->expects($this->once())->method('createFilter')->with(
            $this->equalTo($this->service),
            $this->equalTo($expectedAssignedUser),
            $this->equalTo($expectedTimePeriod)
        );
        $api->filterList($this->service, $args);
    }

    /**
     * Data Provider for testFilterList
     *
     * @return array
     */
    public function getDataForFilterList()
    {
        return [
            [
                [
                    'module' => 'ForecastManagerWorksheets',
                ],
                false,
                false,
                false,
            ],
            [
                [
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => 15,
                ],
                false,
                false,
                false,
            ],
            [
                [
                    'module' => 'ForecastManagerWorksheets',
                    'filter' => [
                        [
                            'assigned_user_id' => 1,
                        ],
                        [
                            'timeperiod_id' => 2,
                        ],
                    ],
                ],
                1,
                2,
                3,
            ],
        ];
    }

    /**
     * Test asserts that we have correct filter if there are no parameters
     * @covers::createFilter
     */
    public function testCreateFilter()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', [
            $this->service,
            false,
            false,
        ]);
        $this->assertNotEmpty($actual);
        $result = [];
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($this->service->user->id, $result['assigned_user_id']);
        $this->assertArrayHasKey('draft', $result);
        $this->assertEquals(1, $result['draft']);
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals(TimePeriod::getCurrentId(), $result['timeperiod_id']);
    }

    /**
     * We should get customer user if current_user is manager
     * @covers ::createFilter
     */
    public function testCreateFilterCustomUser()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $user->reports_to_id = $GLOBALS['current_user']->id;
        $user->save();
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', [
            $this->service,
            $user->id,
            false,
        ]);
        $this->assertNotEmpty($actual);
        $result = [];
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('assigned_user_id', $result);
        $this->assertEquals($user->id, $result['assigned_user_id']);
    }

    /**
     * We should get exception if custom user isn't present in system
     *
     * @covers ::createFilter
     */
    public function testCreateFilterCustomUserInvalidParameter()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod($api, 'createFilter', [
            $this->service,
            '-1',
            false,
        ]);
    }

    /**
     * We should get correct time period if it's passed
     * @covers ::createFilter
     */
    public function testCreateFilterTimePeriod()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();
        $actual = SugarTestReflection::callProtectedMethod($api, 'createFilter', [
            $this->service,
            false,
            $this->timeperiod->id,
        ]);
        $this->assertNotEmpty($actual);
        $result = [];
        foreach ($actual as $value) {
            $result[key($value)] = current($value);
        }
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertEquals($this->timeperiod->id, $result['timeperiod_id']);
    }

    /**
     * We should get exception if time period isn't present in system
     *
     * @covers ::createFilter
     */
    public function testCreateFilterTimePeriodInvalidParameter()
    {
        $fields = [
            'reports_to_id' => $GLOBALS['current_user']->id,
        ];
        $user = SugarTestUserUtilities::createAnonymousUser(true, 0, $fields);
        $api = new ForecastManagerWorksheetsFilterApi();

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod($api, 'createFilter', [
            $this->service,
            false,
            create_guid(),
        ]);
    }
}
