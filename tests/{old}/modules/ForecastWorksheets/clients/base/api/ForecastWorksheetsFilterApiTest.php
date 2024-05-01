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
 * Used to test Forecast Module endpoints from ForecastModuleApi.php
 */
class ForecastWorksheetsFilterApiTest extends TestCase
{
    /** @var array
     */
    protected static $reportee;

    /**
     * @var array
     */
    protected static $manager;
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
    protected static $repData;

    /**
     * @var ForecastWorksheetsFilterApi
     */
    protected $filterApi;

    /**
     * @var ForecastsWorksheetApi
     */
    protected $putApi;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        // get current settings

        SugarTestForecastUtilities::setUpForecastConfig([
            'show_worksheet_worst' => 1,
        ]);

        // setup the test users
        self::$manager = SugarTestForecastUtilities::createForecastUser();

        self::$reportee = SugarTestForecastUtilities::createForecastUser([
            'user' => [
                'reports_to' => self::$manager['user']->id,
            ],
            'opportunities' => [
                'total' => 5,
                'include_in_forecast' => 5,
            ],
        ]);

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
            'ops' => self::$manager['opportunities'],
            'op_worksheets' => self::$manager['opp_worksheets'],
            'id' => self::$manager['user']->id,
            'name' => 'Opportunities (' . self::$manager['user']->first_name . ' ' . self::$manager['user']->last_name . ')',
            'user_id' => self::$manager['user']->id,
            'timeperiod_id' => self::$timeperiod->id,
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
            'commit_stage' => self::$manager['worksheet']->commit_stage,
            'forecast_id' => self::$reportee['forecast']->id,
            'worksheet_id' => self::$reportee['worksheet']->id,
            'show_opps' => true,
            'ops' => self::$reportee['opportunities'],
            'op_worksheets' => self::$reportee['opp_worksheets'],
            'id' => self::$reportee['user']->id,
            'name' => self::$reportee['user']->first_name . ' ' . self::$reportee['user']->last_name,
            'user_id' => self::$reportee['user']->id,
            'timeperiod_id' => self::$timeperiod->id,
        ];
    }

    protected function setUp(): void
    {
        $this->filterApi = new ForecastWorksheetsFilterApi();
        $this->putApi = new ForecastWorksheetsApi();
    }

    protected function tearDown(): void
    {
        $this->filterApi = null;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    /**
     * @group forecastapi
     * @group forecasts
     */
    public function testNoResultsForManagerOnDraftSaveOfNewUser()
    {
        // set the current user to Manager
        $GLOBALS['current_user'] = self::$manager['user'];

        $newUser = SugarTestForecastUtilities::createForecastUser(
            ['user' => ['reports_to' => self::$manager['user']->id]]
        );

        //remove any created worksheets for this user so we can test the edge case
        $worksheetIds = [];
        foreach ($newUser['opp_worksheets'] as $worksheet) {
            $worksheetIds[] = $worksheet->id;
        }
        SugarTestWorksheetUtilities::removeSpecificCreatedWorksheets($worksheetIds);

        $response = $this->filterApi->forecastWorksheetsGet(
            SugarTestRestUtilities::getRestServiceMock(self::$manager['user']),
            ['user_id' => $newUser['user']->id, 'timeperiod_id' => self::$timeperiod->id, 'module' => 'ForecastWorksheets']
        );

        $this->assertEmpty($response['records'], 'Data was returned, this edge case should return no data');
    }
}
