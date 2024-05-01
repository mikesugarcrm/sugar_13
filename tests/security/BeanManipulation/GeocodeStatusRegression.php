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

use Regression\SugarCRMScenario;

class GeocodeStatusRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10211] Bean Manipulation through "/maps/updateGeocodeStatus" REST API endpoint';
    }

    public function run(): void
    {
        $this->login('jim', 'jim')
            ->bwcLogin()
            ->apiCall(
                '/maps/updateGeocodeStatus',
                'POST',
                [
                    'id' => 'not-exists',
                    'module' => 'Accounts',
                    'fieldName' => 'geocode_status',
                    'status' => 'COMPLETED',
                ]
            )
            ->expectStatusCode(404)
            ->apiCall(
                '/maps/updateGeocodeStatus',
                'POST',
                [
                    'id' => 'seed_max_id',
                    'module' => 'Users',
                    'fieldName' => 'is_admin',
                    'status' => 1,
                ]
            )
            ->expectStatusCode(500);
    }
}
