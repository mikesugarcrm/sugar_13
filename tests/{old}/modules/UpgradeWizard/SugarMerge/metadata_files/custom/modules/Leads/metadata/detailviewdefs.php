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
$viewdefs['Leads']['DetailView'] = [
    'templateMeta' => [
        'form' => [
            'buttons' => [
                0 => 'EDIT',
                1 => 'DUPLICATE',
                2 => 'DELETE',
                3 => [
                    'customCode' => '<input title="{$MOD.LBL_CONVERTLEAD_TITLE}" accessKey="{$MOD.LBL_CONVERTLEAD_BUTTON_KEY}" type="button" class="button" onClick="document.location=\'index.php?module=Leads&action=ConvertLead&record={$fields.id.value}\'" name="convert" value="{$MOD.LBL_CONVERTLEAD}">',
                ],
                4 => [
                    'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Leads\'; this.form.return_action.value=\'DetailView\';this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Leads\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
                ],
            ],
            'headerTpl' => 'modules/Leads/tpls/DetailViewHeader.tpl',
        ],
        'maxColumns' => '2',
        'widths' => [
            0 => [
                'label' => '10',
                'field' => '30',
            ],
            1 => [
                'label' => '10',
                'field' => '30',
            ],
        ],
        'includes' => [
            0 => [
                'file' => 'modules/Leads/Lead.js',
            ],
        ],
    ],
    'panels' => [
        'default' => [
            0 => [
                0 => 'lead_source',
                1 => 'status',
            ],
            1 => [
                0 => 'lead_source_description',
                1 => 'status_description',
            ],
            2 => [
                0 => [
                    'name' => 'campaign_name',
                    'label' => 'LBL_CAMPAIGN',
                ],
            ],
            3 => [
                0 => 'refered_by',
                1 => 'phone_work',
            ],
            4 => [
                0 => [
                    'name' => 'full_name',
                    'label' => 'LBL_NAME',
                ],
                1 => 'phone_mobile',
            ],
            6 => [
                0 => [
                    'name' => 'account_name',
                    'displayParams' => [],
                ],
            ],
            7 => [
                0 => 'title',
                1 => 'phone_fax',
            ],
            8 => [
                0 => 'department',
                1 => 'do_not_call',
            ],
            9 => [
                0 => 'team_name',
                1 => [
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                ],
            ],
            11 => [
                0 => [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                ],
            ],
            12 => [
                0 => [
                    'name' => 'primary_address_street',
                    'label' => 'LBL_PRIMARY_ADDRESS',
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'primary',
                    ],
                ],
                1 => [
                    'name' => 'alt_address_street',
                    'label' => 'LBL_ALTERNATE_ADDRESS',
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'alt',
                    ],
                ],
            ],
            13 => [
                0 => 'description',
                1 => '',
            ],
            14 => [
                0 => 'email1',
            ],
            15 => [
                0 => 'field1_c',
                1 => 'field2_c',
            ],
        ],
        'lbl_panel_assignment' => [
            1 => [
                1 => [
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                ],
            ],
        ],
    ],
];
