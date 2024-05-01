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
    ],
    'panels' => [
        'lbl_contact_information' => [
            0 => [
                0 => [
                    'name' => 'first_name',
                    'customCode' => '{html_options name="salutation" options=$fields.salutation.options selected=$fields.salutation.value}&nbsp;<input name="first_name" size="25" maxlength="25" type="text" value="{$fields.first_name.value}">',
                    'label' => 'LBL_FIRST_NAME',
                ],
                1 => [
                    'name' => 'last_name',
                    'displayParams' => [
                        'required' => true,
                    ],
                    'label' => 'LBL_LAST_NAME',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'personeels_id_c',
                    'label' => 'LBL_PERSONEELS_ID',
                ],
                1 => [
                    'name' => 'account_name',
                    'displayParams' => [
                        'key' => 'billing',
                        'copy' => 'primary',
                        'billingKey' => 'primary',
                        'additionalFields' => [
                            'phone_office' => 'phone_work',
                        ],
                    ],
                    'label' => 'LBL_ACCOUNT_NAME',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'phone_work',
                    'label' => 'LBL_OFFICE_PHONE',
                ],
                1 => [
                    'name' => 'phone_mobile',
                    'label' => 'LBL_MOBILE_PHONE',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'phone_other',
                    'label' => 'LBL_OTHER_PHONE',
                ],
                1 => [
                    'name' => 'lead_source',
                    'label' => 'LBL_LEAD_SOURCE',
                ],
            ],
            4 => [
                0 => [
                    'name' => 'phone_home',
                    'label' => 'LBL_HOME_PHONE',
                ],
                1 => [
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX_PHONE',
                ],
            ],
            5 => [
                0 => [
                    'name' => 'department',
                    'label' => 'LBL_DEPARTMENT',
                ],
                1 => [
                    'name' => 'title',
                    'label' => 'LBL_TITLE',
                ],
            ],
            6 => [
                0 => [
                    'name' => 'report_to_name',
                    'label' => 'LBL_REPORTS_TO',
                ],
                1 => [
                    'name' => 'contactpersoon_c',
                    'label' => 'LBL_CONTACTPERSOON',
                ],
            ],
            7 => [
                0 => [
                    'name' => 'birthdate',
                    'label' => 'LBL_BIRTHDATE',
                ],
                1 => [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO_NAME',
                ],
            ],
            8 => [
                0 => [
                    'name' => 'sync_contact',
                    'label' => 'LBL_SYNC_CONTACT',
                ],
                1 => [
                    'name' => 'team_name',
                    'displayParams' => [
                        'display' => true,
                    ],
                    'label' => 'LBL_TEAM',
                ],
            ],
        ],
        'lbl_address_information' => [
            0 => [
                0 => [
                    'name' => 'nulmeting_sturen_c',
                    'studio' => 'visible',
                    'label' => 'LBL_NULMETING_STUREN',
                ],
                1 => [
                    'name' => 'datum_nulmeeting_verzonden_c',
                    'label' => 'LBL_DATUM_NULMEETING_VERZONDEN',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'deelnemende_bedrijven_c',
                    'studio' => 'visible',
                    'label' => 'LBL_DEELNEMENDE_BEDRIJVEN',
                ],
            ],
        ],
        'lbl_panel1' => [
            0 => [
                0 => [
                    'name' => 'primary_address_street',
                    'label' => 'LBL_PRIMARY_ADDRESS_STREET',
                ],
                1 => [
                    'name' => 'alt_address_street',
                    'label' => 'LBL_ALT_ADDRESS_STREET',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'primary_address_postalcode',
                    'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
                ],
                1 => [
                    'name' => 'alt_address_postalcode',
                    'label' => 'LBL_ALT_ADDRESS_POSTALCODE',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'primary_address_city',
                    'label' => 'LBL_PRIMARY_ADDRESS_CITY',
                ],
                1 => [
                    'name' => 'alt_address_city',
                    'label' => 'LBL_ALT_ADDRESS_CITY',
                ],
            ],
        ],
        'lbl_email_addresses' => [
            0 => [
                0 => [
                    'name' => 'email1',
                    'label' => 'LBL_EMAIL_ADDRESS',
                ],
            ],
        ],
        'lbl_description_information' => [
            0 => [
                0 => [
                    'name' => 'description',
                    'displayParams' => [
                        'rows' => 6,
                        'cols' => 80,
                    ],
                    'label' => 'LBL_DESCRIPTION',
                ],
            ],
        ],
    ],
];
