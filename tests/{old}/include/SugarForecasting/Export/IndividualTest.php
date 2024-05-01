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

class SugarForecasting_Export_IndividualTest extends TestCase
{
    /** @var array
     */
    private $reportee;

    /**
     * @var array
     */
    protected $manager;
    /**
     * @var TimePeriod
     */
    protected $timeperiod;

    /**
     * @var array
     */
    protected $managerData;

    /**
     * @var array
     */
    protected $repData;

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var Current Forecasts Config
     */
    protected static $current_config;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        SugarTestForecastUtilities::setUpForecastConfig([
            'forecast_by' => 'Opportunities',
        ]);
        $this->manager = SugarTestForecastUtilities::createForecastUser();

        $this->reportee = SugarTestForecastUtilities::createForecastUser(
            ['user' => ['reports_to' => $this->manager['user']->id]]
        );

        $this->timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();

        $this->managerData = [
            'amount' => $this->manager['opportunities_total'],
            'quota' => $this->manager['quota']->amount,
            'quota_id' => $this->manager['quota']->id,
            'best_case' => $this->manager['forecast']->best_case,
            'likely_case' => $this->manager['forecast']->likely_case,
            'worst_case' => $this->manager['forecast']->worst_case,
            'best_adjusted' => $this->manager['worksheet']->best_case,
            'likely_adjusted' => $this->manager['worksheet']->likely_case,
            'worst_adjusted' => $this->manager['worksheet']->worst_case,
            'commit_stage' => $this->manager['worksheet']->commit_stage,
            'forecast_id' => $this->manager['forecast']->id,
            'worksheet_id' => $this->manager['worksheet']->id,
            'show_opps' => true,
            'ops' => $this->manager['opportunities'],
            'op_worksheets' => $this->manager['opp_worksheets'],
            'id' => $this->manager['user']->id,
            'name' => 'Opportunities (' . $this->manager['user']->first_name . ' ' . $this->manager['user']->last_name . ')',
            'user_id' => $this->manager['user']->id,
            'timeperiod_id' => $this->timeperiod->id,
        ];

        $this->repData = [
            'amount' => $this->reportee['opportunities_total'],
            'quota' => $this->reportee['quota']->amount,
            'quota_id' => $this->reportee['quota']->id,
            'best_case' => $this->reportee['forecast']->best_case,
            'likely_case' => $this->reportee['forecast']->likely_case,
            'worst_case' => $this->reportee['forecast']->worst_case,
            'best_adjusted' => $this->reportee['worksheet']->best_case,
            'likely_adjusted' => $this->reportee['worksheet']->likely_case,
            'worst_adjusted' => $this->reportee['worksheet']->worst_case,
            'commit_stage' => $this->manager['worksheet']->commit_stage,
            'forecast_id' => $this->reportee['forecast']->id,
            'worksheet_id' => $this->reportee['worksheet']->id,
            'show_opps' => true,
            'ops' => $this->reportee['opportunities'],
            'op_worksheets' => $this->reportee['opp_worksheets'],
            'id' => $this->reportee['user']->id,
            'name' => $this->reportee['user']->first_name . ' ' . $this->reportee['user']->last_name,
            'user_id' => $this->reportee['user']->id,
            'timeperiod_id' => $this->timeperiod->id,
        ];
    }

    protected function tearDown(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
    }

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
        $current_user = $this->reportee['user'];
        $args = [];
        $args['timeperiod_id'] = $this->timeperiod->id;
        $args['user_id'] = $this->repData['id'];
        $args['filters'] = ['include'];

        //hide/show any columns
        SugarTestConfigUtilities::setConfig('Forecasts', $hide, $value);

        $obj = new SugarForecasting_Export_Individual($args);
        $content = $obj->process();
        //echo $content . "\n";

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
        $args['timeperiod_id'] = $this->timeperiod->id;
        $args['user_id'] = $this->repData['id'];
        $obj = new SugarForecasting_Export_Individual($args);

        $this->assertMatchesRegularExpression("/\_rep\_forecast$/", $obj->getFilename());
    }
}
