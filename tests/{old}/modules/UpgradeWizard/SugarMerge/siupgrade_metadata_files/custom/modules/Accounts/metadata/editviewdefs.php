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
$viewdefs ['Accounts'] =
    [
        'EditView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'SAVE',
                        1 => 'CANCEL',
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
                        'file' => 'modules/Accounts/Account.js',
                    ],
                ],
            ],
            'panels' => [
                'lbl_account_information' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'label' => 'LBL_NAME',
                            'displayParams' => [
                                'required' => true,
                            ],
                        ],
                        1 => [
                            'name' => 'phone_office',
                            'label' => 'LBL_PHONE_OFFICE',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'high_prio_c',
                            'label' => 'High_Priority_Account_c',
                        ],
                        1 => null,
                    ],
                    2 => [
                        0 => [
                            'name' => 'website',
                            'type' => 'link',
                            'label' => 'LBL_WEBSITE',
                        ],
                        1 => [
                            'name' => 'phone_fax',
                            'label' => 'LBL_PHONE_FAX',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'ticker_symbol',
                            'label' => 'LBL_TICKER_SYMBOL',
                        ],
                        1 => [
                            'name' => 'phone_alternate',
                            'label' => 'LBL_OTHER_PHONE',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'parent_name',
                            'label' => 'LBL_MEMBER_OF',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'employees',
                            'label' => 'LBL_EMPLOYEES',
                        ],
                        1 => null,
                    ],
                    6 => [
                        0 => [
                            'name' => 'ownership',
                            'label' => 'LBL_OWNERSHIP',
                        ],
                        1 => [
                            'name' => 'rating',
                            'label' => 'LBL_RATING',
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'industry',
                            'label' => 'LBL_INDUSTRY',
                        ],
                        1 => [
                            'name' => 'sic_code',
                            'label' => 'LBL_SIC_CODE',
                        ],
                    ],
                    8 => [
                        0 => [
                            'name' => 'account_type',
                            'label' => 'LBL_TYPE',
                            'customCode' => '
<select name="{$fields.account_type.name}" id="{$fields.account_type.name}" title=\'\' tabindex="0" OnChange=\'checkAccountTypeDependentDropdown({$ref_code_param})\' >
{if isset($fields.account_type.value) && $fields.account_type.value != \'\'}
{html_options options=$fields.account_type.options selected=$fields.account_type.value}
{else}
{html_options options=$fields.account_type.options selected=$fields.account_type.default}
{/if}
</select>
<script src=\'custom/include/javascript/custom_javascript.js\'></script>
',
                        ],
                        1 => [
                            'name' => 'annual_revenue',
                            'label' => 'LBL_ANNUAL_REVENUE',
                        ],
                    ],
                    9 => [
                        0 => [
                            'name' => 'reference_code_c',
                            'label' => 'LBL_REFERENCE_CODE_C',
                        ],
                        1 => [
                            'name' => 'ref_code_expiration_c',
                            'label' => 'LBL_REF_CODE_EXPIRATION',
                        ],
                    ],
                    10 => [
                        null,
                        1 => [
                            'name' => 'code_customized_by_c',
                            'label' => 'LBL_CODE_CUSTOMIZED_BY',
                        ],
                    ],
                    11 => [
                        0 => [
                            'name' => 'resell_discount',
                            'label' => 'LBL_RESELL_DISCOUNT',
                        ],
                        1 => [
                            'name' => 'Support_Service_Level_c',
                            'label' => 'Support Service Level_0',
                        ],
                    ],
                    12 => [
                        0 => [
                            'name' => 'Partner_Type_c',
                            'label' => 'partner_Type__c',
                        ],
                        1 => [
                            'name' => 'deployment_type_c',
                            'label' => 'Deployment_Type__c',
                        ],
                    ],
                    13 => [
                        0 => [
                            'name' => 'team_name',
                            'label' => 'LBL_LIST_TEAM',
                            'displayParams' => [
                                'display' => true,
                            ],
                        ],
                        1 => [
                            'name' => 'renewal_contact_c',
                            'label' => 'LBL_RENEWAL_CONTACT_C',
                            'displayParams' => ['initial_filter' => '&account_name_advanced={$fields.name.value}'],
                        ],
                    ],
                    14 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO',
                        ],
                        1 => [
                            'name' => 'auto_send_renewal_emails_c',
                            'label' => 'LBL_AUTO_SEND_RENEWAL_EMAILS',
                        ],
                    ],
                ],
                'lbl_panel5' => [
                    0 => [
                        0 => [
                            'name' => 'customer_reference_c',
                            'label' => 'LBL_CUSTOMER_REFERENCE',
                        ],
                        1 => [
                            'name' => 'type_of_reference_c',
                            'label' => 'LBL_TYPE_OF_REFERENCE',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'reference_contact_c',
                            'label' => 'LBL_REFERENCE_CONTACT',
                        ],
                        1 => [
                            'name' => 'last_used_as_reference_c',
                            'label' => 'LBL_LAST_USED_AS_REFERENCE',
                        ],
                    ],
                    2 => [
                        0 => null,
                        1 => [
                            'name' => 'reference_status_c',
                            'label' => 'LBL_REFERENCE_STATUS',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'reference_notes_c',
                            'label' => 'LBL_REFERENCE_NOTES',
                        ],
                        1 => [
                            'name' => 'last_used_reference_notes_c',
                            'label' => 'LBL_LAST_USED_REFERENCE_NOTES',
                        ],
                    ],
                ],
                'lbl_panel1' => [
                    0 => [
                        0 => [
                            'name' => 'training_credits_purchased_c',
                            'label' => 'Learning_Credits_Purchased__c',
                        ],
                        1 => [
                            'name' => 'remaining_training_credits_c',
                            'label' => 'Remaining_Learning_Credits__c',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'training_credits_pur_date_c',
                            'label' => 'Most_Recent_Credits_Purchase_Date_c',
                        ],
                        1 => [
                            'name' => 'training_credits_exp_date_c',
                            'label' => 'Upcoming_Credits_Expiration_Date__c',
                        ],
                    ],
                ],
                'LBL_PANEL6' => [
                    0 => [
                        0 => [
                            'name' => 'support_cases_purchased_c',
                            'label' => 'Support_Cases_Purchased__c',
                        ],
                        1 => [
                            'name' => 'remaining_support_cases_c',
                            'label' => 'Remaining_Support_Cases__c',
                        ],
                    ],
                ],
                'lbl_panel4' => [
                    0 => [
                        0 => [
                            'name' => 'dce_auth_user_c',
                            'label' => 'LBL_DCE_AUTH_USER',
                        ],
                        1 => [
                            'name' => 'dce_app_id_c',
                            'label' => 'LBL_DCE_APP_ID',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'dce_auth_pass_c',
                            'label' => 'LBL_DCE_AUTH_PASSWORD',
                        ],
                        1 => null,
                    ],
                ],
                'lbl_address_information' => [
                    0 => [
                        0 => [
                            'name' => 'billing_address_street',
                            'hideLabel' => true,
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'billing',
                                'rows' => 2,
                                'cols' => 30,
                                'maxlength' => 150,
                            ],
                            'label' => 'LBL_BILLING_ADDRESS_STREET',
                        ],
                        1 => [
                            'name' => 'shipping_address_street',
                            'hideLabel' => true,
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'shipping',
                                'copy' => 'billing',
                                'rows' => 2,
                                'cols' => 30,
                                'maxlength' => 150,
                            ],
                            'label' => 'LBL_SHIPPING_ADDRESS_STREET',
                        ],
                    ],
                ],
                'lbl_email_addresses' => [
                    0 => [
                        0 => [
                            'name' => 'email1',
                            'label' => 'LBL_EMAIL',
                        ],
                    ],
                ],
                'lbl_description_information' => [
                    0 => [
                        0 => [
                            'name' => 'description',
                            'displayParams' => [
                                'cols' => 80,
                                'rows' => 6,
                            ],
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
            ],
        ],
    ];
