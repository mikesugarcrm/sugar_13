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

class TimePeriodsCurrentApiTest extends TestCase
{
    /**
     * @var TimePeriodsCurrentApi
     */
    protected $api;

    //These are the default forecast configuration settings we will use to test
    private static $forecastConfigSettings = [
        ['name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'timeperiod_start_date', 'value' => '2013-01-01', 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'timeperiod_shown_forward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'],
        ['name' => 'timeperiod_shown_backward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'],
    ];


    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 1');

        //setup forecast admin settings for timeperiods to be able to play nice in the suite
        $admin = BeanFactory::newBean('Administration');

        self::$forecastConfigSettings[3]['timeperiod_start_date']['value'] = TimeDate::getInstance()->getNow()->setDate(date('Y'), 1, 1)->asDbDate(false);
        foreach (self::$forecastConfigSettings as $config) {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }
    }

    protected function setUp(): void
    {
        $this->api = new TimePeriodsCurrentApi();
    }

    public static function tearDownAfterClass(): void
    {
        // delete all current timeperiods
        $db = DBManagerFactory::getInstance();
        $db->query('UPDATE timeperiods SET deleted = 0 where deleted = 1');
    }

    protected function tearDown(): void
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @group timeperiods
     */
    public function testInvalidTimePeriodThrowsException()
    {
        $restService = SugarTestRestUtilities::getRestServiceMock();

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->getCurrentTimePeriod($restService, []);
    }

    /**
     * @group timeperiods
     */
    public function testGetCurrentTimePeriod()
    {
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();

        $restService = SugarTestRestUtilities::getRestServiceMock();
        $return = $this->api->getCurrentTimePeriod($restService, []);

        $this->assertEquals($tp->id, $return['id']);
    }

    /**
     * @group timeperiods
     */
    public function testGetTimePeriodByDate()
    {
        $args = [];
        $tp = SugarTestTimePeriodUtilities::createTimePeriod();
        $args['date'] = $tp->start_date;

        $restService = SugarTestRestUtilities::getRestServiceMock();
        $return = $this->api->getTimePeriodByDate($restService, $args);

        $this->assertEquals($tp->id, $return['id']);
    }

    /**
     * @group timeperiods
     */
    public function testGetTimePeriodByDateNoDate()
    {
        SugarTestTimePeriodUtilities::createTimePeriod();
        $args = [];

        $restService = SugarTestRestUtilities::getRestServiceMock();

        $this->expectException(SugarApiExceptionNotFound::class);
        $this->api->getTimePeriodByDate($restService, $args);
    }
}
