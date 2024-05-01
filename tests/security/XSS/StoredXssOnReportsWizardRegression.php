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
use Regression\SugarCRMRegression;

class StoredXssOnReportsWizardRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-10527]: Stored XSS on Reports Wizard';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?module=Teams&action=EditView&return_module=Teams&return_action=index',
                [],
                'index.php?module=Teams&action=EditView&return_module=Teams&return_action=index',
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Teams',
                    'action' => 'Save',
                    'name' => '"</script><img src onerror=alert(0)>',
                    'description' => '"</script><img src onerror=alert(0)>',
                ]
            )
            ->extractRegexp('teamId', '/<a href=".*record=([\w]+-[\w]+-[\w]+-[\w]+-[\w]+)">.*[\n\r].*img src onerror=alert/');

        $teamId = $scenario->getVar('teamId');
        $formStamp = time();

        $scenario
            ->submitForm(
                "index.php?module=Teams&offset=1&stamp=$formStamp&return_module=Teams&action=DetailView&record=$teamId",
                [],
                "index.php?module=Teams&offset=1&stamp=$formStamp&return_module=Teams&action=DetailView&record=$teamId",
            )
            ->submitForm(
                "index.php",
                [
                    'module' => 'Teams',
                    'action' => 'Save2',
                    'subpanel_id' => 1,
                    'value' => 'DetailView',
                    'http_method' => 'get',
                    'return_module' => 'Teams',
                    'return_id' => $teamId,
                    'record' => $teamId,
                ]
            );

        $scenario
            ->submitForm(
                'index.php?action=DetailView&module=Users&record=1&type=&return_id=1',
                [],
                'index.php?action=DetailView&module=Users&record=1&type=&return_id=1'
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Users',
                    'action' => 'Save',
                    'record' => '1',
                    'password_change' => 'false',
                    'required_password' => '0',
                    'is_group' => '0',
                    'portal_only' => '0',
                    'is_admin' => '1',
                    'is_current_admin' => '1',
                    'edit_self' => '1',
                    'required_email_address' => '1',
                    'user_name' => 'admin',
                    'status' => 'Active',
                    'last_name' => 'Administrator',
                    'LicenseTypes' => [
                        'SUGAR_SELL_ADVANCED_BUNDLE',
                        'SUGAR_SERVE',
                    ],
                    'employee_status' => 'Active',
                    'show_on_employees' => '1',
                    'title' => 'Administrator',
                    'team_name' => 'team_name',
                    'team_name_field' => 'team_name_table',
                    'arrow_team_name' => 'hide',
                    'team_name_collection_0' => '"</script><img src onerror=alert(0)>',
                    'id_team_name_collection_0' => $teamId,
                    'primary_team_name_collection' => '0',
                    'return_module' => 'Users',
                    'return_id' => '1',
                    'return_action' => 'EditView',
                ]
            );

        $request = new Request(
            'POST',
            'index.php?',
            [
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            ],
            'to_pdf=true&module=Home&action=quicksearchQuery&data=%7B%22form%22%3A%22EditView%22%2C%22method%22%3A%22get_non_private_teams_array%22%2C%22modules%22%3A%5B%22Teams%22%5D%2C%22group%22%3A%22or%22%2C%22field_list%22%3A%5B%22name%22%2C%22id%22%5D%2C%22populate_list%22%3A%5B%22team_name_collection_0%22%2C%22id_team_name_collection_0%22%5D%2C%22required_list%22%3A%5B%22parent_id%22%5D%2C%22conditions%22%3A%5B%7B%22name%22%3A%22name%22%2C%22op%22%3A%22like_custom%22%2C%22end%22%3A%22%25%22%2C%22value%22%3A%22%22%7D%2C%7B%22name%22%3A%22user_id%22%2C%22value%22%3A%221%22%7D%5D%2C%22order%22%3A%22name%22%2C%22limit%22%3A%2230%22%2C%22no_match_text%22%3A%22No%20Match%22%7D&query=%2522%253C%252Fscript%253E%253Cimg%2520src%2520onerror%253Dalert(0)%253E&'
        );

        $scenario
            ->send($request)
            ->expectStatusCode(200)
            ->expectSubstring($teamId)
            ->expectSubstring('\u0022\u003C\/script\u003E\u003Cimg src onerror=alert(0)\u003E');

        $request = new Request('GET', 'cache/include/javascript/sugar_grp1_yui.js');

        $scenario
            ->send($request)
            ->expectSubstring('_populateListItem=function(b,a,c){b.innerText');
    }
}
