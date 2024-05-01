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

$viewdefs ['Contacts'] =
    [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => 'FIND_DUPLICATES',
                        4 => [
                            'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}">',
                        ],
                    ],
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
                'useTabs' => false,
            ],
            'panels' => [
                'lbl_contact_information' => [
                    0 => [
                        0 => [
                            'name' => 'full_name',
                            'label' => 'LBL_NAME',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'phone_mobile',
                            'label' => 'LBL_MOBILE_PHONE',
                        ],
                        1 => [
                            'name' => 'title',
                            'comment' => 'The title of the contact',
                            'label' => 'LBL_TITLE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'department',
                            'comment' => 'The department of the contact',
                            'label' => 'LBL_DEPARTMENT',
                        ],
                        1 => [
                            'name' => 'phone_work',
                            'label' => 'LBL_OFFICE_PHONE',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'account_name',
                            'label' => 'LBL_ACCOUNT_NAME',
                            'displayParams' => [],
                        ],
                        1 => [
                            'name' => 'phone_fax',
                            'label' => 'LBL_FAX_PHONE',
                        ],
                    ],
                    4 => [
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
                    5 => [
                        0 => [
                            'name' => 'email1',
                            'studio' => 'false',
                            'label' => 'LBL_EMAIL_ADDRESS',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'description',
                            'comment' => 'Full text of the note',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
                'lbl_detailview_panel1' => [
                    0 => [
                        0 => [
                            'name' => 'test_c',
                            'label' => 'LBL_TEST',
                        ],
                        1 => [
                            'name' => 'test2_c',
                            'label' => 'LBL_TEST2',
                        ],
                    ],
                ],
                'LBL_PANEL_ADVANCED' => [
                    0 => [
                        0 => [
                            'name' => 'report_to_name',
                            'label' => 'LBL_REPORTS_TO',
                        ],
                        1 => [
                            'name' => 'sync_contact',
                            'comment' => 'Synch to outlook?  (Meta-Data only)',
                            'label' => 'LBL_SYNC_CONTACT',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'lead_source',
                            'comment' => 'How did the contact come about',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => [
                            'name' => 'do_not_call',
                            'comment' => 'An indicator of whether contact can be called',
                            'label' => 'LBL_DO_NOT_CALL',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'campaign_name',
                            'label' => 'LBL_CAMPAIGN',
                        ],
                    ],
                ],
                'LBL_PANEL_ASSIGNMENT' => [
                    0 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'date_modified',
                            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                            'label' => 'LBL_DATE_MODIFIED',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                ],
            ],
        ],
    ];
