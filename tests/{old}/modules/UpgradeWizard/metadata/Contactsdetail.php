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
// created: 2013-10-25 23:53:09
$viewdefs = [
    'Contacts' => [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => 'FIND_DUPLICATES',
                        4 => [
                            'customCode' => '<input type="submit" class="button" title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" onclick="this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}"/>',
                            'sugar_html' => [
                                'type' => 'submit',
                                'value' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
                                'htmlOptions' => [
                                    'class' => 'button',
                                    'id' => 'manage_subscriptions_button',
                                    'title' => '{$APP.LBL_MANAGE_SUBSCRIPTIONS}',
                                    'onclick' => 'this.form.return_module.value=\'Contacts\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\'; this.form.module_tab.value=\'Contacts\';',
                                    'name' => 'Manage Subscriptions',
                                ],
                            ],
                        ],
                    ],
                ],
                'maxColumns' => '2',
                'useTabs' => true,
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
                'tabDefs' => [
                    'LBL_CONTACT_INFORMATION' => [
                        'newTab' => true,
                        'panelDefault' => 'expanded',
                    ],
                    'LBL_DETAILVIEW_PANEL1' => [
                        'newTab' => true,
                        'panelDefault' => 'expanded',
                    ],
                    'LBL_PANEL_ADVANCED' => [
                        'newTab' => true,
                        'panelDefault' => 'expanded',
                    ],
                    'LBL_PANEL_ASSIGNMENT' => [
                        'newTab' => false,
                        'panelDefault' => 'expanded',
                    ],
                ],
            ],
            'panels' => [
                'lbl_contact_information' => [
                    0 => [
                        0 => [
                            'name' => 'picture',
                            'label' => 'LBL_PICTURE_FILE',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'full_name',
                            'label' => 'LBL_NAME',
                            'displayParams' => [
                                'enableConnectors' => true,
                                'module' => 'Contacts',
                                'connectors' => [
                                    0 => 'ext_rest_twitter',
                                ],
                            ],
                        ],
                        1 => [
                            'name' => 'phone_work',
                            'label' => 'LBL_OFFICE_PHONE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'account_name',
                            'label' => 'LBL_ACCOUNT_NAME',
                            'displayParams' => [
                                'enableConnectors' => true,
                                'module' => 'Contacts',
                                'connectors' => [
                                    0 => 'ext_rest_linkedin',
                                ],
                            ],
                        ],
                        1 => [
                            'name' => 'extension_c',
                            'label' => 'LBL_EXTENSION',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'twitter_handle_c',
                            'label' => 'LBL_TWITTER_HANDLE_C',
                        ],
                        1 => [
                            'name' => 'linkedin_id_c',
                            'label' => 'LBL_LINKEDIN_ID_C',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'title',
                            'comment' => 'The title of the contact',
                            'label' => 'LBL_TITLE',
                        ],
                        1 => [
                            'name' => 'phone_mobile',
                            'label' => 'LBL_MOBILE_PHONE',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'department',
                            'label' => 'LBL_DEPARTMENT',
                        ],
                        1 => [
                            'name' => 'phone_home',
                            'comment' => 'Home phone number of the contact',
                            'label' => 'LBL_HOME_PHONE',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'phone_other',
                            'comment' => 'Other phone number for the contact',
                            'label' => 'LBL_OTHER_PHONE',
                        ],
                        1 => [
                            'name' => 'email1',
                            'studio' => 'false',
                            'label' => 'LBL_EMAIL_ADDRESS',
                        ],
                    ],
                    7 => [
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
                            'name' => 'phone_alternate',
                            'comment' => 'An alternate phone number',
                            'label' => 'LBL_PHONE_ALT',
                        ],
                        1 => '',
                    ],
                ],
                'LBL_PANEL_ADVANCED' => [
                    0 => [
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
                    1 => [
                        0 => [
                            'name' => 'birthdate',
                            'comment' => 'The birthdate of the contact',
                            'label' => 'LBL_BIRTHDATE',
                        ],
                        1 => [
                            'name' => 'report_to_name',
                            'label' => 'LBL_REPORTS_TO',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'lead_source',
                            'comment' => 'How did the contact come about',
                            'label' => 'LBL_LEAD_SOURCE',
                        ],
                        1 => [
                            'name' => 'assistant',
                            'comment' => 'Name of the assistant of the contact',
                            'label' => 'LBL_ASSISTANT',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'campaign_name',
                            'label' => 'LBL_CAMPAIGN',
                        ],
                        1 => [
                            'name' => 'assistant_phone',
                            'comment' => 'Phone number of the assistant of the contact',
                            'label' => 'LBL_ASSISTANT_PHONE',
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
                            'name' => 'sync_contact',
                            'comment' => 'Synch to outlook?  (Meta-Data only)',
                            'label' => 'LBL_SYNC_CONTACT',
                        ],
                    ],
                    1 => [
                        0 => 'team_name',
                        1 => [
                            'name' => 'portal_name',
                            'label' => 'LBL_PORTAL_NAME',
                            'hideIf' => 'empty($PORTAL_ENABLED)',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'portal_active',
                            'label' => 'LBL_PORTAL_ACTIVE',
                            'hideIf' => 'empty($PORTAL_ENABLED)',
                        ],
                        1 => [
                            'name' => 'preferred_language',
                            'label' => 'LBL_PREFERRED_LANGUAGE',
                        ],
                    ],
                    3 => [
                        'id',
                        '',
                    ],
                ],
            ],
        ],
    ],
];
