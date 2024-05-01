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
 * Class ForecastsConfigApiTest
 * @coversDefaultClass \ForecastsConfigApi
 */
class ForecastsConfigApiTest extends TestCase
{
    protected $createdBeans = [];

    protected function setUp(): void
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('moduleList');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        SugarTestForecastUtilities::setUpForecastConfig(
            [
                'worksheet_columns' => [],
            ]
        );

        $GLOBALS['current_user']->is_admin = 1;
    }

    protected function tearDown(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM config where name = 'testSetting' and category = 'Forecasts'");
        $db->commit();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * test the create api
     *
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testCreateConfig()
    {
        // Get the real data that is in the system, not the partial data we have saved

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = [
            'module' => 'Forecasts',
            'testSetting' => 'testValue',
            'worksheet_columns' => [],
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0,
        ];
        /* @var ForecastsConfigApi $apiClass */
        $apiClass = $this->createPartialMock('ForecastsConfigApi', [
            'timePeriodSettingsChanged',
            'refreshForecastByMetadata',
            'rebuildExtensions',
        ]);

        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));

        $apiClass->expects($this->once())
            ->method('refreshForecastByMetadata');

        $apiClass->expects($this->once())
            ->method('rebuildExtensions');

        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], 'testValue');

        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey('testSetting', $results);
        $this->assertEquals($results['testSetting'], 'testValue');
    }

    public function testRefreshForecastByMetadata()
    {
        SugarAutoLoader::load('modules/Opportunities/include/OpportunityWithRevenueLineItem.php');
        $apiClass = $this->createPartialMock('ForecastsConfigApi', ['getOpportunityConfigObject']);
        $oppClass = $this->getMockBuilder('OpportunityWithRevenueLineItem')->getMock();

        $oppClass->expects($this->once())
            ->method('fixForecastFields');

        $apiClass->expects($this->once())
            ->method('getOpportunityConfigObject')
            ->will($this->returnValue($oppClass));

        $apiClass->refreshForecastByMetadata('foo');
    }

    /**
     * @dataProvider getOpportunityConfigObjectProvider
     * @param $forecast_by
     * @param $result
     */
    public function testGetOpportunityConfigObject($forecast_by, $result)
    {
        $apiClass = new ForecastsConfigApi();

        $class = $apiClass->getOpportunityConfigObject($forecast_by);
        $this->assertEquals($result, $class !== null ? get_class($class) : self::class);
    }

    public function getOpportunityConfigObjectProvider()
    {
        return [
            ['RevenueLineItems', 'OpportunityWithRevenueLineItem'],
            ['Opportunities', 'OpportunityWithOutRevenueLineItem'],
        ];
    }

    public function testGetRepairAndClear()
    {
        $apiClass = new ForecastsConfigApi();

        $class = $apiClass->getRepairAndClear();
        $this->assertEquals('RepairAndClear', get_class($class));
    }

    public function testRebuildExtensions()
    {
        $module = 'foo';
        SugarAutoLoader::load('modules/Administration/QuickRepairAndRebuild.php');
        $apiClass = $this->createPartialMock('ForecastsConfigApi', ['getRepairAndClear']);
        $repairClass = $this->getMockBuilder('RepairAndClear')->getMock();

        $apiClass->expects($this->once())
            ->method('getRepairandClear')
            ->will($this->returnValue($repairClass));

        $repairClass->expects($this->once())
            ->method('clearVardefs');

        $repairClass->expects($this->once())
            ->method('rebuildExtensions')
            ->with([$module]);

        $apiClass->rebuildExtensions($module);
    }

    /**
     * test the get config
     * @group forecasts
     * @covers ::config
     */
    public function testReadConfig()
    {
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', 'testValue', 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Forecasts',
        ];
        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->config($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], 'testValue');
    }

    /**
     * test the update config
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testUpdateConfig()
    {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Forecasts',
            'testSetting' => strrev($testSetting),
            'worksheet_columns' => [],
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0,
        ];
        $apiClass = $this->createPartialMock('ForecastsConfigApi', [
            'timePeriodSettingsChanged',
            'refreshForecastByMetadata',
            'rebuildExtensions',
        ]);
        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));
        $result = $apiClass->forecastsConfigSave($api, $args);
        $this->assertArrayHasKey('testSetting', $result);
        $this->assertEquals($result['testSetting'], strrev($testSetting));

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertArrayHasKey('testSetting', $results);
        $this->assertNotEquals($results['testSetting'], $testSetting);
        $this->assertEquals($results['testSetting'], strrev($testSetting));
    }

    /**
     * @covers ::forecastsConfigSave
     */
    public function testSetConfigWithEmptyWorksheetColumns()
    {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Forecasts',
            'worksheet_columns' => [],
            'show_worksheet_best' => 1,
            'show_worksheet_worst' => 0,
        ];
        $apiClass = $this->createPartialMock('ForecastsConfigApi', [
            'timePeriodSettingsChanged',
            'setWorksheetColumns',
            'refreshForecastByMetadata',
            'rebuildExtensions',
        ]);
        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->will($this->returnValue(false));
        $apiClass->expects($this->once())
            ->method('setWorksheetColumns')
            ->will($this->returnValue(true));
        $apiClass->forecastsConfigSave($api, $args);

        $results = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertNotEmpty($results['worksheet_columns']);
    }

    /**
     * test the create api using bad credentials, should receive a failure
     *
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testCreateBadCredentialsConfig()
    {
        $GLOBALS['current_user']->is_admin = 0;

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];


        $args = [
            'module' => 'Forecasts',
            'testSetting' => 'testValue',
        ];
        $apiClass = $this->createPartialMock('ForecastsConfigApi', [
            'refreshForecastByMetadata',
            'rebuildExtensions',
        ]);

        $this->expectException(\SugarApiExceptionNotAuthorized::class);
        $apiClass->forecastsConfigSave($api, $args);
    }

    /**
     * test the save config calls TimePeriodSettingsChanged
     * @group forecasts
     * @covers ::forecastsConfigSave
     */
    public function testSaveConfigTimePeriodSettingsChangedCalled()
    {
        $testSetting = 'testValue';
        /* @var $admin Administration */
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'testSetting', $testSetting, 'base');

        $priorSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentSettings = $admin->getConfigForModule('Forecasts', 'base');

        $currentSettings['worksheet_columns'] = [
            0 => 'commit_stage',
            1 => 'parent_name',
            2 => 'likely_case',
        ];

        $api = new RestService();
        //Fake the security
        $api->user = $GLOBALS['current_user'];

        $args = [
            'module' => 'Forecasts',
        ];

        $args = array_merge($args, $priorSettings);

        $apiClass = $this->createPartialMock('ForecastsConfigApi', [
            'timePeriodSettingsChanged',
            'refreshForecastByMetadata',
            'rebuildExtensions',
        ]);

        if (empty($priorSettings['is_setup'])) {
            $priorSettings['timeperiod_shown_forward'] = 0;
            $priorSettings['timeperiod_shown_backward'] = 0;
        }

        $apiClass->expects($this->once())
            ->method('timePeriodSettingsChanged')
            ->with($priorSettings, $currentSettings);

        $apiClass->forecastsConfigSave($api, $args);
    }

    /**
     * @return array asserting data with the key data points changed to test each conditional
     */
    public function getTimePeriodSettingsData()
    {
        return [
            [
                [],
                false,
            ],
            [
                [
                    'timeperiod_shown_backward' => '3',
                ],
                true,
            ],
            [
                [
                    'timeperiod_shown_forward' => '3',
                ],
                true,
            ],
            [
                [
                    'timeperiod_start_date' => '2013-03-01',
                ],
                true,
            ],
            [
                [
                    'timeperiod_interval' => TimePeriod::QUARTER_TYPE,
                ],
                true,
            ],
            [
                [
                    'timeperiod_leaf_interval' => TimePeriod::MONTH_TYPE,
                ],
                true,
            ],
            [
                [
                    'timeperiod_type' => 'fiscal',
                ],
                true,
            ],
        ];
    }

    /**
     * check the conditionals and that they return expected values for the timePeriodSettingsChanged function
     *
     * @dataProvider getTimePeriodSettingsData
     * @param $changedSettings
     * @param $expectedResult
     * @group forecasts
     * @covers ::timePeriodSettingsChanged
     */
    public function testTimePeriodSettingsChagned($changedSettings, $expectedResult)
    {
        $priorSettings = [
            'timeperiod_shown_backward' => '2',
            'timeperiod_shown_forward' => '2',
            'timeperiod_start_date' => '2013-01-01',
            'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
            'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
            'timeperiod_type' => 'chronological',
        ];

        $currentSettings = array_merge($priorSettings, $changedSettings);

        $apiClass = new ForecastsConfigApi();
        $result = $apiClass->timePeriodSettingsChanged($priorSettings, $currentSettings);

        $this->assertEquals($expectedResult, $result, 'TimePeriod Setting check failed for given parameters. Prior Settings: ' . print_r($priorSettings, 1) . ' Current Settings: ' . print_r($currentSettings, 1) . ' result: ' . print_r($result, 1));
    }
}
