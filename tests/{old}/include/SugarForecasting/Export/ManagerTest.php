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

class SugarForecasting_Export_ManagerTest extends TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    private static $reportee2;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var array
     */
    protected static $managerData2;

    /**
     * @var array
     */
    protected static $repData;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        self::$manager = SugarTestForecastUtilities::createForecastUser();
        //set up another manager, and assign him to the first manager manually so his data is generated
        //correctly.
        self::$manager2 = SugarTestForecastUtilities::createForecastUser();
        self::$manager2['user']->reports_to_id = self::$manager['user']->id;
        self::$manager2['user']->save();

        self::$reportee = SugarTestForecastUtilities::createForecastUser(
            ['user' => ['reports_to' => self::$manager['user']->id]]
        );
        self::$reportee2 = SugarTestForecastUtilities::createForecastUser(
            ['user' => ['reports_to' => self::$manager2['user']->id]]
        );

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        self::$managerData = [
            'amount' => self::$manager['opportunities_total'],
            'quota' => self::$manager['quota']->amount,
            'quota_id' => self::$manager['quota']->id,
            'best_case' => self::$manager['forecast']->best_case,
            'likely_case' => self::$manager['forecast']->likely_case,
            'worst_case' => self::$manager['forecast']->worst_case,
            'best_adjusted' => self::$manager['worksheet']->best_case,
            'likely_adjusted' => self::$manager['worksheet']->likely_case,
            'worst_adjusted' => self::$manager['worksheet']->worst_case,
            'commit_stage' => self::$manager['worksheet']->commit_stage,
            'forecast_id' => self::$manager['forecast']->id,
            'worksheet_id' => self::$manager['worksheet']->id,
            'show_opps' => true,
            'id' => self::$manager['user']->id,
            'name' => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            'user_id' => self::$manager['user']->id,

        ];

        self::$managerData2 = [
            'amount' => self::$manager2['opportunities_total'],
            'quota' => self::$manager2['quota']->amount,
            'quota_id' => self::$manager2['quota']->id,
            'best_case' => self::$manager2['forecast']->best_case,
            'likely_case' => self::$manager2['forecast']->likely_case,
            'worst_case' => self::$manager2['forecast']->worst_case,
            'best_adjusted' => self::$manager2['worksheet']->best_case,
            'likely_adjusted' => self::$manager2['worksheet']->likely_case,
            'worst_adjusted' => self::$manager2['worksheet']->worst_case,
            'commit_stage' => self::$manager2['worksheet']->commit_stage,
            'forecast_id' => self::$manager2['forecast']->id,
            'worksheet_id' => self::$manager2['worksheet']->id,
            'show_opps' => true,
            'id' => self::$manager2['user']->id,
            'name' => 'Opportunities (' . self::$manager2['user']->first_name . ' ' . self::$manager2['user']->last_name . ')',
            'user_id' => self::$manager2['user']->id,

        ];

        self::$repData = [
            'amount' => self::$reportee['opportunities_total'],
            'quota' => self::$reportee['quota']->amount,
            'quota_id' => self::$reportee['quota']->id,
            'best_case' => self::$reportee['forecast']->best_case,
            'likely_case' => self::$reportee['forecast']->likely_case,
            'worst_case' => self::$reportee['forecast']->worst_case,
            'best_adjusted' => self::$reportee['worksheet']->best_case,
            'likely_adjusted' => self::$reportee['worksheet']->likely_case,
            'worst_adjusted' => self::$reportee['worksheet']->worst_case,
            'commit_stage' => self::$reportee['worksheet']->commit_stage,
            'forecast_id' => self::$reportee['forecast']->id,
            'worksheet_id' => self::$reportee['worksheet']->id,
            'show_opps' => true,
            'id' => self::$reportee['user']->id,
            'name' => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            'user_id' => self::$reportee['user']->id,

        ];
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * exportForecastWorksheetsProvider
     *
     * This is the dataProvider function for testExportForecastWorksheets
     */
    /**
     * exportForecastWorksheetsProvider
     *
     * This is the dataProvider function for testExportForecastWorksheets
     */
    public function exportForecastWorksheetProvider()
    {
        return
            [
                ['show_worksheet_best', '1', 'assertMatchesRegularExpression', '/Best/'],
                ['show_worksheet_best', '0', 'assertDoesNotMatchRegularExpression', '/Best/'],
                ['show_worksheet_likely', '1', 'assertMatchesRegularExpression', '/Likely/'],
                ['show_worksheet_likely', '0', 'assertDoesNotMatchRegularExpression', '/Likely/'],
                ['show_worksheet_worst', '1', 'assertMatchesRegularExpression', '/Worst/'],
                ['show_worksheet_worst', '0', 'assertDoesNotMatchRegularExpression', '/Worst/'],
            ];
    }

    /**
     * testExport
     *
     * This is a test to check that we get a response back from the export data call
     *
     * @group forecasts
     * @group export
     *
     * @dataProvider exportForecastWorksheetProvider
     */
    public function testExport($hide, $value, $method, $expectedRegex)
    {
        global $current_user;
        $current_user = self::$manager2['user'];
        $args = [];
        $args['timeperiod_id'] = self::$timeperiod->id;
        $args['user_id'] = self::$manager2['user']->id;

        // hide/show any columns
        SugarTestConfigUtilities::setConfig('Forecasts', $hide, $value);

        $obj = new SugarForecasting_Export_Manager($args);
        $content = $obj->process();

        $this->assertNotEmpty($content, 'content empty. Rep data should have returned csv file contents.');
        $this->$method($expectedRegex, $content);
    }


    /**
     * This is a function to test the getFilename function
     *
     * @group export
     * @group forecasts
     */
    public function testGetFilename()
    {
        $args = [];
        $args['timeperiod_id'] = self::$timeperiod->id;
        $args['user_id'] = self::$manager2['user']->id;
        $obj = new SugarForecasting_Export_Manager($args);

        $this->assertMatchesRegularExpression('/\_manager\_forecast$/', $obj->getFilename());
    }
}
