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

class PmseEmailTemplatesRegression extends SugarCRMScenario
{
    public function getRegressionDescription(): string
    {
        return '[BR-10364]: Missing access control at `pmse_Emails_Templates #PT16493_5 on SugarCRM On-Demand - March 2023';
    }

    public function run(): void
    {
        $this
            ->login('sarah', 'sarah')
            ->apiCall(
                '/pmse_Emails_Templates?erased_fields=true&fields=name%2Cbase_module%2Cassigned_user_name%2Cdate_modified%2Cdate_entered%2Cassigned_user_id&max_num=5&order_by=date_modified%3Adesc&filter%5B0%5D%5B%24owner%5D=',
                'POST',
                [
                    'deleted' => false,
                    'name' => 'Missing access Control',
                    'description' => 'test templ idor',
                    'from_name' => '',
                    'from_address' => '',
                    'subject' => 'asd',
                    'body' => '',
                    'body_html' => 'asd',
                    'type' => '',
                    'base_module' => 'Accounts',
                    'published' => '',
                    'following' => true,
                    'my_favorite' => true,
                    'tag' => [
                        [
                            'id' => 'd57386ec-c6e2-11ed-9bbd-06de1b3d51c7',
                            'name' => 'test templ',
                            'tags__name_lower' => 'test templ',
                            'encodedValue' => 'test%20templ',
                        ],
                    ],
                    'team_name' => [
                        [
                            'id' => '1',
                            'name' => 'Global',
                            'name_2' => '',
                            'primary' => true,
                            'selected' => false,
                        ],
                    ],
                    'assigned_user_id' => '3e1d77ae-dc9e-41ef-a60f-e8f34e6f9a44',
                ],
            )
            ->expectStatusCode(403)
            ->expectSubstring('You are not authorized to create Process Email Templates.');
    }
}
