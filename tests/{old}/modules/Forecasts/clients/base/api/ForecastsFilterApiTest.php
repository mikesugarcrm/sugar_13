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

/***
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 */
class ForecastsCommittedApiTest extends TestCase
{
    /**
     * @var User
     */
    private static $reportee;

    /**
     * @var User
     */
    protected static $manager;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var ForecastsFilterApi
     */
    protected $api;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$manager = SugarTestForecastUtilities::createForecastUser();

        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager['user']->id;
        self::$reportee->save();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->api = new ForecastsFilterApi();
    }

    protected function tearDown(): void
    {
        unset($this->api);
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilter()
    {
        $GLOBALS['current_user'] = self::$reportee;

        $response = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            ['module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id]
        );

        $this->assertNotEmpty($response['records'], 'Rest reply is empty. Rep data should have been returned.');
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAManagerTryingToViewAnotherUser()
    {
        $GLOBALS['current_user'] = self::$reportee;

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            ['module' => 'Forecasts', 'user_id' => self::$manager['user']->id]
        );
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowExceptionWhenRepViewingHisOwnSheet()
    {
        $GLOBALS['current_user'] = self::$reportee;
        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            ['module' => 'Forecasts', 'user_id' => self::$reportee->id, 'timeperiod_id' => self::$timeperiod->id]
        );

        $this->assertIsArray($return);
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidUserId()
    {
        $GLOBALS['current_user'] = self::$manager['user'];

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            ['module' => 'Forecasts', 'user_id' => 'im_not_valid']
        );
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidTimeperiodId()
    {
        $GLOBALS['current_user'] = self::$reportee;

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            ['module' => 'Forecasts', 'timeperiod_id' => 'im_not_valid']
        );
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterThrowsExceptionWhenNotAValidForecastType()
    {
        $GLOBALS['current_user'] = self::$reportee;

        $stub = $this->createMock('ForecastsFilterApi');
        $stub->expects($this->any())
            ->method('filterList')
            ->will($this->returnValue(
                ['next_offset' => -1, 'records' => []]
            ));

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            ['module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => 'invalid_type']
        );
    }

    /**
     * @dataProvider forecastTypesDataProvider
     * @group forecastapi
     * @group forecasts
     */
    public function testForecastFilterDoesNotThrowsAnExceptionWithAValidForecastType($forecast_type)
    {
        $GLOBALS['current_user'] = self::$reportee;

        $stub = $this->createMock('ForecastsFilterApi');
        $stub->expects($this->any())
            ->method('filterList')
            ->will($this->returnValue(
                ['next_offset' => -1, 'records' => []]
            ));


        $return = $this->api->forecastsCommitted(
            SugarTestRestUtilities::getRestServiceMock(self::$reportee),
            ['module' => 'Forecasts', 'timeperiod_id' => self::$timeperiod->id, 'forecast_type' => $forecast_type]
        );

        $this->assertSame(['next_offset' => -1, 'records' => []], $return);
    }

    public static function forecastTypesDataProvider()
    {
        return [
            ['direct'],
            ['Direct'],
            ['rollup'],
            ['Rollup'],
        ];
    }
}

class ForecastFilterApiServiceMock extends RestService
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
