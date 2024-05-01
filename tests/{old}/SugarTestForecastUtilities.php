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

/**
 * This is a test class to create test Forecast instances
 */
class SugarTestForecastUtilities
{
    private static $createdForecasts = [];
    private static $timePeriod;

    /**
     * Utility method to setup Forecasts Data
     *
     * @param array $additional_config A Key Value pair to set Forecast Config Settings
     */
    public static function setUpForecastConfig(array $additional_config = [])
    {
        $defaults = ForecastsDefaults::getDefaults(1);
        foreach ($defaults as $key => $value) {
            SugarTestConfigUtilities::setConfig('Forecasts', $key, $value);
        }

        foreach ($additional_config as $key => $value) {
            SugarTestConfigUtilities::setConfig('Forecasts', $key, $value);
        }
        OpportunityHooks::$settings = [];
        Forecast::$settings = [];
    }

    /**
     * Utility Method to Rest Forecast Config Data
     */
    public static function tearDownForecastConfig()
    {
        SugarTestConfigUtilities::resetConfig();
        OpportunityHooks::$settings = [];
        Forecast::$settings = [];
    }

    /**
     * @static
     * This is a static function to create a test Forecast instance
     * @param $timeperiod TimePeriod instance for Forecast
     * @param $user User assigned to Forecast
     * @return Forecast Mixed Forecast test instance
     */
    public static function createForecast($timeperiod, $user)
    {
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = false;

        $forecast = new Forecast();
        $forecast->timeperiod_id = $timeperiod->id;
        $forecast->best_case = 100;
        $forecast->likely_case = 100;
        $forecast->worst_case = 100;
        $forecast->forecast_type = 'Direct';
        $forecast->user_id = $user->id;
        $forecast->date_modified = db_convert("'" . $timedate->nowDb() . "'", 'datetime');
        $forecast->date_entered = $forecast->date_modified;
        $forecast->pipeline_opp_count = 0;
        $forecast->pipeline_amount = 0;
        $forecast->save();

        self::$createdForecasts[$forecast->id] = $forecast;
        return $forecast;
    }

    /**
     * @static
     * This is a static function to remove all created test Forecast instance
     */
    public static function removeAllCreatedForecasts()
    {
        $forecast_ids = array_keys(self::$createdForecasts);
        $GLOBALS['db']->query('DELETE FROM forecasts WHERE id IN (\'' . implode("', '", $forecast_ids) . '\')');
        self::$createdForecasts = [];
    }

    /**
     * @return TimePeriod
     */
    public static function getCreatedTimePeriod()
    {
        if (empty(self::$timePeriod)) {
            self::$timePeriod = SugarTestTimePeriodUtilities::createTimePeriod();
        }

        return self::$timePeriod;
    }

    public static function setTimePeriod($timeperiod)
    {
        self::$timePeriod = $timeperiod;
    }

    /**
     * This method will create a new user with opportunities with a variable number of items based on an array passed in
     */
    public static function createForecastUser(array $config = [])
    {
        $default_config = [
            'timeperiod_id' => null,
            'user' => [
                'reports_to' => null,
            ],
            'createOpportunities' => true,
            'opportunities' => [
                'total' => 5,
                'include_in_forecast' => 3,
            ],
            'createForecast' => true,
            'createWorksheet' => true,
            'createQuota' => true,
            'quota' => [
                'amount' => 2000,
            ],
            'currency_id' => '-99',
        ];

        $config = array_merge($default_config, $config);

        $return = [
            'opportunities' => [],
            'opportunities_total' => 0,
            'included_opps_totals' => [
                'likely' => 0,
                'best' => 0,
                'worst' => 0,
            ],
            'opp_worksheets' => [],
        ];


        if (empty($config['timeperiod_id'])) {
            $config['timeperiod_id'] = self::getCreatedTimePeriod()->id;
        }

        $user = SugarTestUserUtilities::createAnonymousUser();
        if (!empty($config['user']['reports_to'])) {
            $user->reports_to_id = $config['user']['reports_to'];
            $user->save();
        }

        $return['user'] = $user;

        if ($config['createOpportunities'] === true) {
            // create opportunities
            $included = 0;
            $opportunities = [];

            $forecast_likely_total = 0;
            $forecast_best_total = 0;
            $forecast_worst_total = 0;
            for ($x = 0; $config['opportunities']['total'] > $x; $x++) {
                $opp_amount = random_int(1000, 2500);

                $include = 0;
                if ($included < $config['opportunities']['include_in_forecast']) {
                    $included++;
                    $include = 1;
                }

                // random date
                $int_date_closed = random_int(strtotime(self::$timePeriod->start_date), strtotime(self::$timePeriod->end_date));
                $date_closed = date('Y-m-d', $int_date_closed);

                $opp = SugarTestOpportunityUtilities::createOpportunity();
                $opp->assigned_user_id = $user->id;
                $opp->amount = $opp_amount;
                $opp->best_case = ($opp_amount + 200);
                $opp->worst_case = ($opp_amount - 400);
                $opp->probability = random_int(50, 90);
                $opp->sales_stage = 'Prospecting';
                $opp->commit_stage = ($include == 1) ? 'include' : 'exclude';
                $opp->date_closed = $date_closed;
                $opp->team_id = '1';
                $opp->team_set_id = '1';
                $opp->currency_id = $config['currency_id'];
                $opp->save();

                /*
                 * Since the products for opps are being autogenerated, they are being done when the $opp is
                 * initially created, and thus missing stuff. We need to grab the product that is incomplete and finish
                 * setting it up.
                 */
                /* @var $rli RevenueLineItem */
                $rli = BeanFactory::newBean('RevenueLineItems');
                $rli->retrieve_by_string_fields(['opportunity_id' => $opp->id]);
                $rli->name = $opp->name;
                $rli->best_case = $opp->best_case;
                $rli->likely_case = $opp->amount;
                $rli->worst_case = $opp->worst_case;
                $rli->cost_price = $opp->amount;
                $rli->quantity = 1;
                $rli->currency_id = $opp->currency_id;
                $rli->base_rate = $opp->base_rate;
                $rli->probability = $opp->probability;
                $rli->date_closed = $opp->date_closed;
                $rli->date_closed_timestamp = $opp->date_closed_timestamp;
                $rli->assigned_user_id = $opp->assigned_user_id;
                $rli->opportunity_id = $opp->id;
                $rli->commit_stage = $opp->commit_stage;
                $rli->save();

                if ($include == 1) {
                    $forecast_likely_total += $opp->amount;
                    $forecast_best_total += $opp->best_case;
                    $forecast_worst_total += $opp->worst_case;

                    $return['included_opps_totals']['likely'] += $opp->amount;
                    $return['included_opps_totals']['best'] += $opp->best_case;
                    $return['included_opps_totals']['worst'] += $opp->worst_case;
                    $return['included_opps_totals']['base_rate'] = $opp->base_rate;

                    if ($config['createWorksheet'] === true) {
                        $worksheet = BeanFactory::newBean('ForecastWorksheets');
                        $worksheet->retrieve_by_string_fields(
                            [
                                'parent_id' => $opp->id,
                                'parent_type' => $opp->module_name,
                                'deleted' => 0,
                                'draft' => 1,
                            ]
                        );

                        $return['opp_worksheets'][] = $worksheet;
                    }
                }

                $return['opportunities_total'] += $opp_amount;

                $opportunities[] = $opp;

                $return['opportunities'][] = $opp;
            }

            if ($config['createQuota'] === true) {
                //create rollup quota too
                if (!empty($user->reports_to_id)) {
                    $quota = SugarTestQuotaUtilities::createQuota($config['quota']['amount']);
                    $quota->user_id = $user->id;
                    $quota->created_by = '1';
                    $quota->modified_user_id = '1';
                    $quota->quota_type = 'Rollup';
                    $quota->timeperiod_id = $config['timeperiod_id'];
                    $quota->team_set_id = '1';
                    $quota->currency_id = $config['currency_id'];
                    $quota->committed = 1;
                    $quota->save();
                }
                $quota = SugarTestQuotaUtilities::createQuota($config['quota']['amount']);
                $quota->user_id = $user->id;
                $quota->created_by = '1';
                $quota->modified_user_id = '1';
                $quota->quota_type = 'Direct';
                $quota->timeperiod_id = $config['timeperiod_id'];
                $quota->team_set_id = 1;
                $quota->currency_id = $config['currency_id'];
                $quota->committed = 1;
                $quota->save();

                $return['quota'] = $quota;
            }

            if ($config['createForecast'] === true) {
                $forecast = self::createForecast(self::$timePeriod, $user);

                $forecast->best_case = $forecast_best_total;
                $forecast->worst_case = $forecast_worst_total;
                $forecast->likely_case = $forecast_likely_total;
                $forecast->forecast_type = 'Direct';
                $forecast->opp_count = $config['opportunities']['include_in_forecast'];
                $forecast->pipeline_amount = $forecast->likely_case;
                $forecast->pipeline_opp_count = $forecast->opp_count;
                $forecast->currency_id = $config['currency_id'];
                $forecast->save();

                // roll forecast up to manager
                /* @var $mgr_worksheet ForecastManagerWorksheet */
                $mgr_worksheet = BeanFactory::newBean('ForecastManagerWorksheets');
                $mgr_worksheet->reporteeForecastRollUp($user, $forecast->toArray());

                $return['forecast'] = $forecast;
            }
            if ($config['createWorksheet'] === true) {
                $worksheet = SugarTestManagerWorksheetUtilities::createWorksheet();
                $worksheet->assigned_user_id = (empty($user->reports_to_id)) ? $user->id : $user->reports_to_id;
                $worksheet->timeperiod_id = $config['timeperiod_id'];
                $worksheet->best_case = $forecast_best_total;
                $worksheet->likely_case = $forecast_likely_total;
                $worksheet->worst_case = $forecast_worst_total;
                $worksheet->best_case_adjusted = $forecast_best_total + 100;
                $worksheet->likely_case_adjusted = $forecast_likely_total + 100;
                $worksheet->worst_case_adjusted = $forecast_worst_total + 100;
                $worksheet->currency_id = $config['currency_id'];
                $worksheet->quota = $config['quota']['amount'];
                $worksheet->save();

                $return['worksheet'] = $worksheet;
            }
        }

        return $return;
    }

    /**
     * @param array $manager A manager created from createForecastUser
     * @param $user                 N+ number of users that report to $manager to create in the forecast
     * @return Forecast
     */
    public static function createManagerRollupForecast($manager, $user)
    {
        $users = [$user];

        $numargs = func_num_args();
        if ($numargs > 2) {
            for ($i = 2; $i < $numargs; $i++) {
                $users[] = func_get_arg($i);
            }
        }
        $tmpForecast = SugarTestForecastUtilities::createForecast(self::$timePeriod, $manager['user']);
        $tmpForecast->best_case = $manager['forecast']->best_case;
        $tmpForecast->worst_case = $manager['forecast']->worst_case;
        $tmpForecast->likely_case = $manager['forecast']->likely_case;
        $tmpForecast->forecast_type = 'Rollup';
        $pipelineAmount = 0;
        $pipelineCount = 0;

        //grab the users
        foreach ($users as $user) {
            if ($user['user']->reports_to_id == $manager['user']->id) {
                $tmpForecast->best_case += $user['forecast']->best_case;
                $tmpForecast->worst_case += $user['forecast']->worst_case;
                $tmpForecast->likely_case += $user['forecast']->likely_case;
                $tmpForecast->opp_count += $user['forecast']->opp_count;
                $pipelineAmount += $user['forecast']->pipeline_amount;
                $pipelineCount += $user['forecast']->pipeline_opp_count;
            }
        }
        //finish off with the manager
        $tmpForecast->best_case += $manager['forecast']->best_case;
        $tmpForecast->worst_case += $manager['forecast']->worst_case;
        $tmpForecast->likely_case += $manager['forecast']->likely_case;
        $tmpForecast->opp_count += $manager['forecast']->opp_count;

        $tmpForecast->pipeline_amount = $pipelineAmount + $manager['forecast']->pipeline_amount;
        $tmpForecast->pipeline_opp_count = $pipelineCount + $manager['forecast']->pipeline_opp_count;

        $tmpForecast->save();

        // roll forecast up to manager
        /* @var $mgr_worksheet ForecastManagerWorksheet */
        $mgr_worksheet = BeanFactory::newBean('ForecastManagerWorksheets');
        $mgr_worksheet->reporteeForecastRollUp($manager['user'], $tmpForecast->toArray());

        return $tmpForecast;
    }

    /**
     * Creates direct rep forecasts given a user that has opportunities
     *
     * @param array User array defined above.
     * @return object Updated forecast.
     */
    public static function createRepDirectForecast($user)
    {
        $tmpForecast = SugarTestForecastUtilities::createForecast(self::$timePeriod, $user['user']);
        $tmpForecast->best_case = 0;
        $tmpForecast->worst_case = 0;
        $tmpForecast->likely_case = 0;
        $tmpForecast->opp_count = 0;
        $closedAmount = 0;
        $closedCount = 0;

        //loop over opportunities
        foreach ($user['opportunities'] as $opp) {
            $tmpForecast->best_case += $opp->best_case;
            $tmpForecast->worst_case += $opp->worst_case;
            $tmpForecast->likely_case += $opp->amount;
            $tmpForecast->opp_count++;

            if ($opp->sales_stage == Opportunity::STAGE_CLOSED_WON || $opp->sales_stage == Opportunity::STAGE_CLOSED_LOST) {
                $closedCount++;
                $closedAmount += $opp->amount;
            }
        }

        $tmpForecast->calculatePipelineData($closedAmount, $closedCount);
        $tmpForecast->save();
        return $tmpForecast;
    }

    public static function cleanUpCreatedForecastUsers()
    {
        if (!empty(self::$timePeriod)) {
            SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
            self::$timePeriod = null;
        }
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestQuotaUtilities::setCreatedUserIds(SugarTestUserUtilities::getCreatedUserIds());
        SugarTestQuotaUtilities::removeAllCreatedQuotas();
        SugarTestWorksheetUtilities::removeAllCreatedWorksheets();
        SugarTestManagerWorksheetUtilities::removeAllCreatedWorksheets();
    }


    /**
     * This is a helper function for tests so that we convert values to the expected amount returned from the API
     *
     * @static
     * @param $amount The amount to format to the test format
     * @param null $user The user to use for currency id and formatting; defaults to using system locale settings
     */
    public static function formatTestNumber($amount, $user = null)
    {
        if (is_null($user)) {
            return number_format($amount, 6, '.', '');
        }

        return SugarCurrency::formatAmount(
            $amount,
            $user->getPreference('currency'),
            $user->getPreference('default_currency_significant_digits'),
            $user->getPreference('default_number_grouping_seperator')
        );
    }
}
