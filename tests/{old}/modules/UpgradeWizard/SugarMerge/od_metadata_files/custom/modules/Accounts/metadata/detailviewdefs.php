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
$viewdefs['Accounts']['DetailView'] = [
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
        'default' => [
            0 => [
                0 => [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ],
                1 => [
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'account_type',
                    'label' => 'LBL_TYPE',
                ],
                1 => [
                    'name' => 'phone_alternate',
                    'label' => 'LBL_OTHER_PHONE',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'team_name',
                    'label' => 'LBL_TEAM',
                ],
                1 => [
                    'name' => 'phone_fax',
                    'label' => 'LBL_FAX',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_TO',
                ],
                1 => [
                    'name' => 'date_modified',
                    'label' => 'LBL_DATE_MODIFIED',
                    'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
                ],
            ],
            4 => [
                0 => null,
                1 => [
                    'name' => 'date_entered',
                    'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
                    'label' => 'LBL_DATE_ENTERED',
                ],
            ],
            5 => [
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
            6 => [
                0 => [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ],
                1 => null,
            ],
            7 => [
                0 => [
                    'name' => 'email1',
                    'label' => 'LBL_EMAIL',
                ],
                1 => [
                    'name' => 'website',
                    'type' => 'link',
                    'label' => 'LBL_WEBSITE',
                    'displayParams' => [
                        'link_target' => '_blank',
                    ],
                ],
            ],
            8 => [
                0 => null,
                1 => null,
            ],
            11 => [
                0 => 'campaign_name',
            ],
        ],
    ],
];
