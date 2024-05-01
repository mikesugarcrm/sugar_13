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
// created: 2013-08-03 20:21:27
$viewdefs['Contacts']['EditView'] = [
    'templateMeta' => [
        'form' => [
            'hidden' => [
                0 => '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
                1 => '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
                2 => '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
                3 => '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
                4 => '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
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
        'tabDefs' => [
            'LBL_CONTACT_INFORMATION' => [
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
        'syncDetailEditViews' => false,
    ],
    'panels' => [
        'lbl_contact_information' => [
            0 => [
                0 => 'picture',
            ],
            1 => [
                0 => [
                    'name' => 'first_name',
                    'customCode' => '{html_options name="salutation" id="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name"  id="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                ],
                1 => [
                    'name' => 'phone_work',
                    'comment' => 'Work phone number of the contact',
                    'label' => 'LBL_OFFICE_PHONE',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'last_name',
                ],
                1 => [
                    'name' => 'extension_c',
                    'label' => 'LBL_EXTENSION',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'title',
                    'comment' => 'The title of the contact',
                    'label' => 'LBL_TITLE',
                ],
                1 => [
                    'name' => 'phone_mobile',
                    'comment' => 'Mobile phone number of the contact',
                    'label' => 'LBL_MOBILE_PHONE',
                ],
            ],
            4 => [
                0 => 'department',
                1 => [
                    'name' => 'phone_home',
                    'comment' => 'Home phone number of the contact',
                    'label' => 'LBL_HOME_PHONE',
                ],
            ],
            5 => [
                0 => [
                    'name' => 'account_name',
                    'displayParams' => [
                        'key' => 'billing',
                        'copy' => 'primary',
                        'billingKey' => 'primary',
                        'additionalFields' => [
                            'phone_office' => 'phone_work',
                        ],
                    ],
                ],
                1 => [
                    'name' => 'phone_other',
                    'comment' => 'Other phone number for the contact',
                    'label' => 'LBL_OTHER_PHONE',
                ],
            ],
            6 => [
                0 => [
                    'name' => 'twitter_handle_c',
                    'label' => 'LBL_TWITTER_HANDLE_C',
                ],
                1 => [
                    'name' => 'linkedin_id_c',
                    'label' => 'LBL_LINKEDIN_ID_C',
                ],
            ],
            7 => [
                0 => [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ],
                1 => [
                    'name' => 'email1',
                    'studio' => 'false',
                    'label' => 'LBL_EMAIL_ADDRESS',
                ],
            ],
            8 => [
                0 => [
                    'name' => 'primary_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'primary',
                        'rows' => 2,
                        'cols' => 30,
                        'maxlength' => 150,
                    ],
                ],
                1 => [
                    'name' => 'alt_address_street',
                    'hideLabel' => true,
                    'type' => 'address',
                    'displayParams' => [
                        'key' => 'alt',
                        'copy' => 'primary',
                        'rows' => 2,
                        'cols' => 30,
                        'maxlength' => 150,
                    ],
                ],
            ],
        ],
        'LBL_PANEL_ADVANCED' => [
            0 => [
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
            1 => [
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
            2 => [
                0 => 'campaign_name',
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
                    'customCode' => '<table border="0" cellspacing="0" cellpadding="0"><tr><td>
                             <input id="portal_name" name="portal_name" type="text" size="30" maxlength="{$fields.portal_name.len|default:\'30\'}" value="{$fields.portal_name.value}" autocomplete="off">
                             <input type="hidden" id="portal_name_existing" value="{$fields.portal_name.value}" autocomplete="off">
                             </td><tr><tr><td><input type="hidden" id="portal_name_verified" value="true"></td></tr></table>',
                ],
            ],
            2 => [
                0 => 'portal_active',
                1 => [
                    'name' => 'portal_password1',
                    'type' => 'password',
                    'customCode' => '<input id="portal_password1" name="portal_password1" type="password" size="32" maxlength="{$fields.portal_password.len|default:\'32\'}" value="{$fields.portal_password.value}" autocomplete="off">',
                    'label' => 'LBL_PORTAL_PASSWORD',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'portal_password',
                    'customCode' => '<input id="portal_password" name="portal_password" type="password" size="32" maxlength="{$fields.portal_password.len|default:\'32\'}" value="{$fields.portal_password.value}" autocomplete="off"><input name="old_portal_password" type="hidden" value="{$fields.portal_password.value}" autocomplete="off">',
                    'label' => 'LBL_CONFIRM_PORTAL_PASSWORD',
                ],
            ],
            4 => [
                'id',
                'twitter_id',
            ],
        ],
    ],
];
