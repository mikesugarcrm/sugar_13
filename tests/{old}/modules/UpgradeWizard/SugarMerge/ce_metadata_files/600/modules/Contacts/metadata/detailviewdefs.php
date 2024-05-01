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

$viewdefs['Contacts']['DetailView'] = [
    'templateMeta' => [
        'form' => [
            'buttons' => [
                'EDIT',
                'DUPLICATE',
                'DELETE',
                'FIND_DUPLICATES',
                ['customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">'],
            ],
        ],
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
        'includes' => [
            ['file' => 'modules/Leads/Lead.js'],
        ],
    ],
    'panels' => [
        'lbl_contact_information' => [
            [

                [
                    'name' => 'full_name',
                    'label' => 'LBL_NAME',
                ],
            ],

            [
                [
                    'name' => 'title',
                    'comment' => 'The title of the contact',
                    'label' => 'LBL_TITLE',
                ],
                [
                    'name' => 'phone_mobile',
                    'label' => 'LBL_MOBILE_PHONE',
                ],
            ],

            [
                'department',
                [
                    'name' => 'phone_work',
                    'label' => 'LBL_OFFICE_PHONE',
                ],
            ],

            [
                [
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                ],
                [
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX_PHONE',
                ],
            ],

            [
                [
                    'name' => 'primary_address_street',
                    'label' => 'LBL_PRIMARY_ADDRESS',
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'primary',
                    ],
                ],

                [
                    'name' => 'alt_address_street',
                    'label' => 'LBL_ALTERNATE_ADDRESS',
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'alt',
                    ],
                ],
            ],

            [
                [
                    'name' => 'email1',
                    'studio' => 'false',
                    'label' => 'LBL_EMAIL_ADDRESS',
                ],
            ],

            [
                [
                    'name' => 'description',
                    'comment' => 'Full text of the note',
                    'label' => 'LBL_DESCRIPTION',
                ],
            ],
        ],

        'LBL_PANEL_ADVANCED' => [
            [

                [
                    'name' => 'report_to_name',
                    'label' => 'LBL_REPORTS_TO',
                ],

                [
                    'name' => 'sync_contact',
                    'comment' => 'Synch to outlook?  (Meta-Data only)',
                    'label' => 'LBL_SYNC_CONTACT',
                ],
            ],

            [
                [
                    'name' => 'lead_source',
                    'comment' => 'How did the contact come about',
                    'label' => 'LBL_LEAD_SOURCE',
                ],

                [
                    'name' => 'do_not_call',
                    'comment' => 'An indicator of whether contact can be called',
                    'label' => 'LBL_DO_NOT_CALL',
                ],
            ],

            [

                [
                    'name' => 'campaign_name',
                    'label' => 'LBL_CAMPAIGN',
                ],

            ],


        ],
        'LBL_PANEL_ASSIGNMENT' => [
            [

                [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ],

                [
                    'name' => 'date_modified',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                    'label' => 'LBL_DATE_MODIFIED',
                ],
            ],

            [

                [
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ],
            ],
        ],
    ],
];
