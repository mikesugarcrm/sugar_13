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

class PmseProjectsRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10365]: Missing access control at `pmse_Project #PT16493_6 on SugarCRM On-Demand - March 2023';
    }

    public function run(): void
    {
        $this
            ->login('sarah', 'sarah')
            ->apiCall(
                '/pmse_Project?after_create%5Bcopy_rel_from%5D=e&erased_fields=true&viewed=1&picture_duplicateBeanId=a',
                'POST',
                [
                    'deleted' => false,
                    'prj_status' => 'INACTIVE',
                    'prj_run_order' => 1,
                    'name' => 'test MISSING ACCESS CONTROL 3',
                    'description' => '',
                    'prj_module' => 'Accounts',
                    'following' => false,
                    'my_favorite' => false,
                    'tag' => [
                        [
                            'id' => 'b1dcc4fa-c750-11ed-8997-06de1b3d51c7',
                            'name' => 'k',
                            'tags__name_lower' => 'k',
                            'encodedValue' => 'k',
                        ],
                    ],
                    'assigned_user_id' => '3e1d77ae-dc9e-41ef-a60f-e8f34e6f9a44',
                ],
            )
            ->expectStatusCode(403)
            ->expectSubstring('You are not authorized to perform this action.');
    }
}
