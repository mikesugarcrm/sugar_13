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
                0 => [
                    'name' => 'full_name',
                    'label' => 'LBL_NAME',
                ],
                1 => [
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'title',
                    'label' => 'LBL_TITLE',
                ],
                1 => [
                    'name' => 'phone_work',
                    'label' => 'LBL_OFFICE_PHONE',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'phone_other',
                    'label' => 'LBL_OTHER_PHONE',
                ],
                1 => [
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX_PHONE',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'lead_source',
                    'label' => 'LBL_LEAD_SOURCE',
                ],
                1 => [
                    'name' => 'lead_source_description',
                    'label' => 'LBL_LEAD_SOURCE_DESCRIPTION',
                ],
            ],
            4 => [
                0 => [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                ],
                1 => [
                    'name' => 'manufacturers_c',
                    'label' => 'LBL_MANUFACTURERS',
                ],
            ],
            5 => [
                0 => [
                    'name' => 'email1',
                    'label' => 'LBL_EMAIL_ADDRESS',
                ],
                1 => [
                    'name' => 'oe_dealer_code_c',
                    'label' => 'LBL_OE_DEALER_CODE',
                ],
            ],
            6 => [
                0 => [
                    'name' => 'refered_by',
                    'label' => 'LBL_REFERED_BY',
                ],
                1 => [
                    'name' => 'regions_c',
                    'label' => 'LBL_REGIONS',
                ],
            ],
            7 => [
                0 => [
                    'name' => 'campaign_name',
                    'label' => 'LBL_CAMPAIGN',
                ],
                1 => [
                    'name' => 'department',
                    'label' => 'LBL_DEPARTMENT',
                ],
            ],
            8 => [
                0 => [
                    'name' => 'do_not_call',
                    'label' => 'LBL_DO_NOT_CALL',
                ],
                1 => [
                    'name' => 'team_name',
                    'label' => 'LBL_TEAM',
                ],
            ],
            9 => [
                0 => [
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                ],
                1 => [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                ],
            ],
            10 => [
                0 => [
                    'name' => 'created_by',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
                    'label' => 'LBL_DATE_ENTERED',
                ],
                1 => 'opportunity_amount',
            ],
            11 => [
                0 => 'birthdate',
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
            ],
            13 => [
                0 => [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ],
            ],
        ],
    ],
];
