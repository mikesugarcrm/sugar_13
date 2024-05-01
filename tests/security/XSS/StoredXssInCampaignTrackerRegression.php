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

use Regression\SugarCRMRegression;

class StoredXssInCampaignTrackerRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return "[BR-10450]: Stored XSS via campaign name allows to attack all the user's in the organization.";
    }

    public function run(): void
    {
        $name = 'test' . time();

        $this
            ->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php?module=Campaigns&action=WizardHome',
                [],
                'index.php?module=Campaigns&action=WizardHome',
            )
            ->submitForm(
                'index.php',
                [
                    'module' => 'Campaigns',
                    'record' => '',
                    'action' => 'WizardNewsletterSave',
                    'return_module' => 'Campaigns',
                    'return_id' => '',
                    'return_action' => 'index',
                    'campaign_type' => 'NewsLetter',
                    'totalsteps' => '4',
                    'currentstep' => '4',
                    'wiz_direction' => 'exit',
                    'wiz_step1_name' => $name,
                    'wiz_step1_assigned_user_name' => 'admin',
                    'wiz_step1_assigned_user_id' => '1',
                    'wiz_step1_status' => 'Planning',
                    'team_name_new_on_update' => false,
                    'team_name_allow_new' => true,
                    'team_name' => 'team_name',
                    'team_name_field' => 'team_name_table',
                    'arrow_team_name' => 'hide',
                    'team_name_collection_0' => 'Global',
                    'id_team_name_collection_0' => '1',
                    'primary_team_name_collection' => '0',
                    'wiz_step1_campaign_type' => 'Telesales',
                    'wiz_step1_end_date' => '2023-09-13',
                    'wiz_step2_currency_id' => '-99',
                    'wiz_step2_impressions' => '0',
                    'existing_tracker_count' => '0',
                    'added_tracker_count' => '1',
                    'wiz_list_of_trackers' => '<img src=x onerror=alert()>@@0@@http://<img src=x onerror=alert()>',
                    'tracker_url' => 'http://',
                    'wiz_step3_tracker_name1' => '<img src=x onerror=alert()>',
                    'wiz_step3_tracker_url1' => 'http://<img src=x onerror=alert()>',
                    'target_list_type' => 'default',
                ],
            )
            ->expectSubstring('&lt;img src=x onerror=alert()&gt;');
    }
}
