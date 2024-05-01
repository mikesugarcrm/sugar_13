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
 * @covers Forecast
 */
class ForecastTest extends TestCase
{
    /**
     * @var Currency
     */
    protected $currency;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider constructProvider
     * @covers       Forecast::__construct
     * @param $customCurrency
     * @param $symbol
     * @param $baseRate
     */
    public function testConstruct($customCurrency, $symbol, $baseRate)
    {
        $user = $GLOBALS['current_user'];

        if ($customCurrency) {
            $this->currency = SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', $symbol, 'MOD', $baseRate);
            $GLOBALS['current_user']->setPreference('currency', $this->currency->id);
        } else {
            unset($GLOBALS['current_user']);
        }

        $forecast = $this->getMockBuilder('Forecast')->getMock();

        $this->assertEquals(true, $forecast->disable_row_level_security);
        $this->assertEquals($symbol, $forecast->currencysymbol);
        $this->assertEquals($baseRate, $forecast->base_rate);
        $GLOBALS['current_user'] = $user;
    }

    public function constructProvider()
    {
        return [
            [false, '$', 1],
            [true, '^', 2],
        ];
    }

    /**
     * @covers Forecast::get_summary_text
     */
    public function testGet_summary_text()
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $forecast->name = 'foo';
        $text = $forecast->get_summary_text();
        $this->assertEquals('foo', $text);
    }

    /**
     * @covers Forecast::retrieve
     */
    public function testRetrieve()
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();
        $forecast = SugarTestForecastUtilities::createForecast($tp, $GLOBALS['current_user']);

        $obj = $forecast->retrieve($forecast->id);

        $this->assertEquals($forecast->id, $obj->id);
    }

    /**
     * @dataProvider calculatePipelineDataProvider
     * @covers       Forecast::calculatePipelineData
     * @param $likely_case
     * @param $opp_count
     * @param $closedAmount
     * @param $closedCount
     * @param $finalPipelineAmount
     * @param $finalOppCount
     */
    public function testCalculatePipelineData(
        $likely_case,
        $opp_count,
        $closedAmount,
        $closedCount,
        $finalPipelineAmount,
        $finalOppCount
    ) {

        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $forecast->likely_case = $likely_case;
        $forecast->opp_count = $opp_count;

        $forecast->calculatePipelineData($closedAmount, $closedCount);
        $this->assertEquals($finalPipelineAmount, $forecast->pipeline_amount);
        $this->assertEquals($finalOppCount, $forecast->pipeline_opp_count);
    }

    public function calculatePipelineDataProvider()
    {
        return [
            [100, 10, 50, 5, 50, 5],
            [100, 10, 500, 20, 0, 0],
        ];
    }

    /**
     * @covers Forecast::is_authenticated
     */
    public function testIs_authenticated()
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $forecast->authenticated = true;

        $isauth = $forecast->is_authenticated();
        $this->assertEquals(true, $isauth);
    }

    /**
     * @covers Forecast::list_view_parse_additional_sections
     */
    public function testList_view_parse_additional_sections()
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $foo = 'foo';

        $retval = $forecast->list_view_parse_additional_sections($foo, null);
        $this->assertEquals($foo, $retval);
    }

    /**
     * @dataProvider create_new_list_queryProvider
     * @covers       Forecast::create_new_list_query
     * @param $orderby
     * @param $where
     * @param $returnArray
     * @param $result
     */
    public function testCreate_new_list_query($orderby, $where, $returnArray, $result)
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(['addVisibilityFrom', 'addVisibilityWhere'])
            ->getMock();

        $forecast->expects($this->once())
            ->method('addVisibilityFrom');

        $forecast->expects($this->once())
            ->method('addVisibilityWhere');

        $query = $forecast->create_new_list_query($orderby, $where, null, null, null, null, $returnArray);

        $this->assertEquals($result, $query);
    }

    public function create_new_list_queryProvider()
    {
        return [
            [
                '',
                '',
                false,
                'SELECT tp.name timeperiod_name, tp.start_date start_date, tp.end_date end_date, forecasts.*  FROM forecasts LEFT JOIN timeperiods tp on forecasts.timeperiod_id = tp.id    ORDER BY forecasts.date_entered desc',
            ],
            [
                'foo desc',
                '',
                false,
                'SELECT tp.name timeperiod_name, tp.start_date start_date, tp.end_date end_date, forecasts.*  FROM forecasts LEFT JOIN timeperiods tp on forecasts.timeperiod_id = tp.id   ORDER BY foo desc',
            ],
            [
                '',
                '1=1',
                false,
                'SELECT tp.name timeperiod_name, tp.start_date start_date, tp.end_date end_date, forecasts.*  FROM forecasts LEFT JOIN timeperiods tp on forecasts.timeperiod_id = tp.id   WHERE 1=1  ORDER BY forecasts.date_entered desc',
            ],
            [
                '',
                '',
                true,
                json_decode('{"select":"SELECT tp.name timeperiod_name, tp.start_date start_date, tp.end_date end_date, forecasts.* ","from":" FROM forecasts LEFT JOIN timeperiods tp on forecasts.timeperiod_id = tp.id  ","where":"","order_by":"  ORDER BY forecasts.date_entered desc"}', true),
            ],
        ];
    }

    /**
     * @dataProvider get_list_view_arrayProvider
     * @covers       Forecast::get_list_view_array
     * @param $likely_case
     * @param $best_case
     * @param $worst_case
     * @param $result
     */
    public function testGet_list_view_array($likely_case, $best_case, $worst_case, $result)
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $forecast->likely_case = $likely_case;
        $forecast->best_case = $best_case;
        $forecast->worst_case = $worst_case;

        $fields = $forecast->get_list_view_array();

        $this->assertEquals($result, $fields);
    }

    public function get_list_view_arrayProvider()
    {
        return [
            [
                100,
                100,
                100,
                json_decode('{"PIPELINE_OPP_COUNT":"0","PIPELINE_AMOUNT":"0","CLOSED_AMOUNT":"0","BEST_CASE":100,"LIKELY_CASE":100,"WORST_CASE":100,"DELETED":0,"CURRENCY_ID":"-99","BASE_RATE":1}', true),
            ],
        ];
    }

    /**
     * @dataProvider getForecastForUserProvider
     * @covers       Forecast::getForecastForUser
     * @param $user
     * @param $where
     * @param $timeperiod
     * @param $rollup
     */
    public function testGetForecastForUser($where, $user, $timeperiod, $rollup)
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(['create_new_list_query'])
            ->getMock();

        $db = $this->getMockBuilder('DBManager')
            ->setMethods([])
            ->getMock();

        $forecast->db = $db;

        $forecast->expects($this->once())
            ->method('create_new_list_query')
            ->with(null, $where, [], [], 0, '', false, null, false)
            ->will($this->returnValue('foo'));

        $db->expects($this->once())
            ->method('query')
            ->with('foo', true, 'Error retrieving user forecast information: ')
            ->will($this->returnValue('bar'));

        $db->expects($this->once())
            ->method('fetchByAssoc')
            ->with('bar');

        $forecast->getForecastForUser($user, $timeperiod, $rollup);
    }

    public function getForecastForUserProvider()
    {
        $timeperiod_id = TimePeriod::getCurrentId();

        return [
            [
                "user_id='user' AND forecast_type='Direct' AND timeperiod_id='{$timeperiod_id}'",
                'user',
                $timeperiod_id,
                false,
            ],
            [
                "user_id='user' AND forecast_type='Rollup' AND timeperiod_id='{$timeperiod_id}'",
                'user',
                $timeperiod_id,
                true,
            ],
        ];
    }

    /**
     * @dataProvider bean_implementsProvider
     * @covers       Forecast::bean_implements
     * @param $implements
     * @param $expected
     */
    public function testBean_implements($implements, $expected)
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $result = $forecast->bean_implements($implements);

        $this->assertEquals($expected, $result);
    }

    public function bean_implementsProvider()
    {
        return [
            ['ACL', true],
            ['Foo', false],
        ];
    }

    /**
     * @covers Forecast::getCommitStageDropdown
     */
    public function testGetCommitStageDropdown()
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $adminBean = BeanFactory::newBean('Administration');
        $config = $adminBean->getConfigForModule($forecast->module_name);

        $result = $forecast->getCommitStageDropdown();

        $this->assertEquals(translate($config['buckets_dom']), $result);
    }

    /**
     * @covers       Forecast::getCommitment
     * @dataProvider getCommitmentDataProvider
     */
    public function testGetCommitment($userId, $isRollup, $intoDatabase, $expectedData)
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();
        $forecast = SugarTestForecastUtilities::createForecast($tp, $GLOBALS['current_user']);

        $forecast->timeperiod_id = $tp->id;
        $forecast->user_id = $userId;
        $forecast->forecast_type = $intoDatabase['forecast_type'];
        $forecast->currency_id = $intoDatabase['currency_id'];
        $forecast->likely_case = $intoDatabase['likely_case'];
        $forecast->save();

        $row = $forecast->getCommitment($forecast->timeperiod_id, $userId, $isRollup);
        $this->assertEquals($row, $expectedData);
    }

    public function getCommitmentDataProvider()
    {
        return [
            ['user_1', true, [
                'forecast_type' => 'Rollup',
                'currency_id' => 1,
                'likely_case' => 11.1,
            ], [
                'currency_id' => 1,
                'likely_case' => 11.1,
            ]],
            ['user_2', false, [
                'forecast_type' => 'Direct',
                'currency_id' => 2,
                'likely_case' => 22.2,
            ], [
                'currency_id' => 2,
                'likely_case' => 22.2,
            ]],
            ['user_3', true, [
                'forecast_type' => 'foo',
                'currency_id' => 3,
                'likely_case' => 33.3,
            ], [
                'currency_id' => -99,
                'likely_case' => 0,
            ]],
        ];
    }

    /**
     * @covers Forecast::getSettings
     */
    public function testGetSettings()
    {
        $forecast = $this->getMockBuilder('Forecast')
            ->setMethods(null)
            ->getMock();

        $admin = BeanFactory::newBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts');

        $result = $forecast->getSettings();

        $this->assertEquals($settings, $result);
    }
}
