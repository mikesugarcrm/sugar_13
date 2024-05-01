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
$viewdefs = [
    'Accounts' => [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => 'FIND_DUPLICATES',
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
                'DEFAULT' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'label' => 'LBL_NAME',
                            'displayParams' => [],
                        ],
                        1 => [
                            'name' => 'subscription_expiration_c',
                            'label' => 'Subscription_Expiration__c',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'high_prio_c',
                            'label' => 'High_Priority_Account_c',
                        ],
                        1 => [
                            'name' => 'phone_office',
                            'label' => 'LBL_PHONE_OFFICE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'website',
                            'type' => 'link',
                            'label' => 'LBL_WEBSITE',
                            'customCode' => '<a href="http://{$fields.website.value}" target="_blank">{$fields.website.value}</a>',
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
                        1 => [
                            'name' => 'email1',
                            'label' => 'LBL_EMAIL',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'employees',
                            'label' => 'LBL_EMPLOYEES',
                        ],
                        1 => [
                            'name' => 'LBL_FILLER',
                            'label' => 'LBL_FILLER',
                        ],
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
                        ],
                        1 => [
                            'name' => 'annual_revenue',
                            'label' => 'LBL_ANNUAL_REVENUE',
                        ],
                    ],
                    9 => [
                        0 => [
                            'name' => 'reference_code_c',
                            'label' => 'LBL_REFERENCE_CODE',
                        ],
                        1 => [
                            'name' => 'ref_code_expiration_c',
                            'label' => 'LBL_REF_CODE_EXPIRATION',
                        ],
                    ],
                    10 => [
                        0 => [
                            'name' => 'contract_version',
                            'label' => 'LBL_CONTRACT_VERSION',
                        ],
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
                        0 => null,
                        1 => [
                            'name' => 'deployment_type_c',
                            'label' => 'Deployment_Type__c',
                        ],
                    ],
                    13 => [
                        0 => [
                            'name' => 'Partner_Type_c',
                            'label' => 'partner_Type__c',
                        ],
                        1 => [
                            'default' => 'false',
                            'customCode' => '{ if $fields.deployment_type_c.value == "ondemand" || $fields.deployment_type_c.value == "ondemand_ded" || $fields.deployment_type_c.value == ""}<a href="http://www.sugarcrm.com/sugarshop/ion3-tools/display.php?arid={$id}" onclick="window.open(this.href,\'window\',\'width=350,height=90,resizable,menubar\'); return false;">On-Demand Account URL</a>{/if}',
                        ],
                    ],
                    14 => [
                        0 => [
                            'name' => 'auto_send_renewal_emails_c',
                            'label' => 'LBL_AUTO_SEND_RENEWAL_EMAILS',
                        ],
                        1 => [
                            'name' => 'renewal_contact_c',
                            'label' => 'LBL_RENEWAL_CONTACT_C',
                        ],
                    ],
                    15 => [
                        0 => [
                            'name' => 'team_name',
                            'label' => 'LBL_LIST_TEAM',
                        ],
                        1 => [
                            'name' => 'date_modified',
                            'label' => 'LBL_DATE_MODIFIED',
                            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                        ],
                    ],
                    16 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO',
                        ],
                        1 => [
                            'name' => 'date_entered',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                    17 => [
                        0 => [
                            'name' => 'billing_address_street',
                            'label' => 'LBL_BILLING_ADDRESS',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'billing',
                            ],
                        ],
                        1 => [
                            'name' => 'shipping_address_street',
                            'label' => 'LBL_SHIPPING_ADDRESS',
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'shipping',
                            ],
                        ],
                    ],
                    18 => [
                        0 => [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                    19 => [
                        0 => [
                            'name' => 'id',
                            'type' => 'link',
                            'label' => 'LBL_USAGE_GRAPH',
                            'customCode' => '<a href="https://sugarinternal.sugarondemand.com/index.php?action=SubscriptionUsageReport&module=Accounts&record={$fields.id.value}">Usage Graph</a>',
                        ],
                    ],
                ],
                'lbl_panel7' => [
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
                'LBL_PANEL1' => [
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
                'LBL_PANEL4' => [
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
                ],
            ],
        ],
    ],
];
