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

class PmseBusinessRulesRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10363]: Missing access control on /rest/v11_19/pmse_Business_Rules #PT16493_4 on SugarCRM On-Demand - March 2023';
    }

    public function run(): void
    {
        $this
            ->login('sarah', 'sarah')
            ->apiCall(
                '/pmse_Business_Rules?erased_fields=true&viewed=1',
                'POST',
                [
                    'deleted' => false,
                    'rst_type' => 'single',
                    'rst_editable' => '0',
                    'assigned_user_id' => 'seed_sarah_id',
                    'rst_module' => 'Bugs',
                    'name' => 'test 123',
                ],
            )
            ->expectStatusCode(403)
            ->expectSubstring('You are not authorized to create Process Business Rules.');
    }
}
