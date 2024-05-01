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

class SugarForecasting_Progress_ManagerTest extends TestCase
{
    /**
     * @var array args to be passed onto methods
     */
    protected static $args = [];

    /**
     * @var array array of users used throughout class
     */
    protected static $users = [];

    /**
     * @var Currency
     */
    protected static $currency;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', ['manager', 'Forecasts']);
        SugarTestHelper::setup('current_user');
        SugarTestForecastUtilities::setUpForecastConfig();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        self::$args['timeperiod_id'] = $timeperiod->id;

        self::$currency = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87);

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$users['top_manager'] = SugarTestForecastUtilities::createForecastUser([
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => ['amount' => 30000],
        ]);

        self::$users['manager'] = SugarTestForecastUtilities::createForecastUser([
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'quota' => ['amount' => 50000],
            'user' => ['manager', 'reports_to' => self::$users['top_manager']['user']->id],
        ]);

        global $current_user;

        $current_user = self::$users['top_manager']['user'];
        self::$args['user_id'] = self::$users['manager']['user']->id;
        $current_user->setPreference('currency', self::$currency->id);

        $current_user = self::$users['manager']['user'];

        $config = [
            'timeperiod_id' => $timeperiod->id,
            'currency_id' => self::$currency->id,
            'user' => ['manager', 'reports_to' => self::$users['manager']['user']->id],
            'quota' => ['amount' => 27000],
        ];
        self::$users['reportee'] = SugarTestForecastUtilities::createForecastUser($config);
    }

    /**
     * reset after each test back to manager id, some tests may have changed to use top manager
     */
    protected function setUp(): void
    {
        self::$args['user_id'] = self::$users['manager']['user']->id;

        global $current_user;
        $current_user = self::$users['manager']['user'];
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = true;
    }

    /**
     * destroy some parts after each test
     */
    protected function tearDown(): void
    {
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
    }

    /**
     * Utility function for calculating pipeline sums
     *
     * @param obj Top level manager
     * @param obj users, passed in as individual arguments ($manager, $user1, $user2, ... $userN)
     * @return array consisting of the amount and closed amount
     */
    protected function calculatePipelineAmount($manager, $user)
    {
        $returnArray = ['amount' => 0, 'closed' => 0];

        $users = [$user];

        $numargs = func_num_args();
        if ($numargs > 2) {
            for ($i = 2; $i < $numargs; $i++) {
                $users[] = func_get_arg($i);
            }
        }

        foreach ($manager['opportunities'] as $opp) {
            if ($opp->sales_stage != Opportunity::STAGE_CLOSED_WON || $opp->sales_stage != Opportunity::STAGE_CLOSED_LOST) {
                $returnArray['amount'] += $opp->amount;
            } elseif ($opp->sales_stage == Opportunity::STAGE_CLOSED_WON) {
                $returnArray['closed'] += $opp->closed;
            }
        }

        foreach ($users as $user) {
            $returnArray['amount'] += ($user['forecast']->pipeline_amount);
            $returnArray['closed'] += ($user['forecast']->closed_amount);
        }

        return $returnArray;
    }

    public function testManagerWithSubManagerAndReps_multisave_withOnlyCloseWon()
    {
        $manager = SugarTestForecastUtilities::createForecastUser([
            'opportunities' => [
                'total' => 1,
                'include_in_forecast' => 0,
            ],
        ]);
        $subManager1 = SugarTestForecastUtilities::createForecastUser([
            'user' => [
                'reports_to' => $manager['user']->id,
            ],
            'opportunities' => [
                'total' => 1,
                'include_in_forecast' => 0,
            ],
        ]);
        $reportee1 = SugarTestForecastUtilities::createForecastUser([
            'user' => [
                'reports_to' => $subManager1['user']->id,
            ],
            'opportunities' => [
                'total' => 1,
                'include_in_forecast' => 1,
            ],
        ]);


        //now we want to change the stage to close lost/close won of a few opps, commit, and make sure they are excluded
        $reportee1['opportunities'][0]->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $reportee1['opportunities'][0]->save();

        //sleep needed so that the new committed forecasts are clearly newer
        sleep(1);
        //recommit
        $reportee1['forecast'] = SugarTestForecastUtilities::createRepDirectForecast($reportee1);
        $subManager1['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($subManager1, $reportee1);
        $manager['forecast'] = SugarTestForecastUtilities::createManagerRollupForecast($manager, $subManager1);

        //calculate what the amount should be
        $totals = $this->calculatePipelineAmount($manager, $subManager1);
        $amount = $totals['amount'];
        $closed = $totals['closed'];

        $obj = new SugarForecasting_Progress_Manager([
            'timeperiod_id' => SugarTestForecastUtilities::getCreatedTimePeriod()->id,
            'user_id' => $manager['user']->id,
        ]);

        $data = $obj->process();

        //Make sure closed amounts match
        $this->assertEquals($closed, $data['closed_amount'], 'Closed Amount Incorrect.');
    }

    /**
     * Dataset Provider
     *
     * @return array
     */
    public function dataProviderDatasets()
    {
        // keys are as follows
        // 1 -> quota amount
        // 2 -> quota currency id

        return [
            [false, false, 0, 0, -99],
            [true, false, 15000, 0, -99],
            [false, true, 0, 30000, -99],
            [true, true, 15000, 30000, -99],
        ];
    }
}
