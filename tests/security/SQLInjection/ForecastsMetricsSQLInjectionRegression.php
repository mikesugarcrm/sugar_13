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
declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use Regression\SugarCRMScenario;

class ForecastsMetricsSQLInjectionRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10209] [Bug Bounty] SQL Injection through "/Forecasts/metrics" REST API endpoint';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $injection = "1), CONCAT(id,'||',user_name,'||',user_hash) metric_count FROM users WHERE user_name NOT IN ('') UNION SELECT 1,2,3 FROM purchased_line_items WHERE 1=1 OR id IN (?, ?, ?, ?) UNION SELECT 1,2,3 FROM purchased_line_items WHERE ((purchased_line_items.team_set_id IN (SELECT tst.team_set_id FROM team_sets_teams tst)))#";

        $request = new Request(
            'POST',
            'rest/v11_19/Forecasts/metrics',
            ['Content-Type' => 'application/json'],
            '{"module":"PurchasedLineItems","filter":[],"user_id":"","type":"","time_period":"","metrics":[{"filter":[],"sum_fields":"' . $injection . '"}]}'
        );

        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->send($request)
            ->expectStatusCode(500)
            ->expectSubstring('{"error":"db_error","error_message":"Database failure. Please refer to sugarcrm.log for details."}');
    }
}
