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
$searchdefs['Contacts'] = [
    'templateMeta' => [
        'maxColumns' => '3',
        'widths' => [
            'label' => '10',
            'field' => '30',
        ],
    ],
    'layout' => [
        'basic_search' => [
            0 => [
                'name' => 'first_name',
                'label' => 'LBL_FIRST_NAME',
                'default' => true,
            ],
            1 => [
                'name' => 'last_name',
                'label' => 'LBL_LAST_NAME',
                'default' => true,
            ],
            2 => [
                'name' => 'account_name',
                'label' => 'LBL_ACCOUNT_NAME',
                'default' => true,
            ],
            3 => [
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
            ],
            4 => [
                'width' => '10%',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'default' => true,
                'name' => 'assigned_user_name',
            ],
        ],
        'advanced_search' => [
            0 => [
                'name' => 'first_name',
                'label' => 'LBL_FIRST_NAME',
                'default' => true,
            ],
            1 => [
                'name' => 'address_street',
                'label' => 'LBL_ANY_ADDRESS',
                'type' => 'name',
                'default' => true,
            ],
            2 => [
                'name' => 'phone',
                'label' => 'LBL_ANY_PHONE',
                'type' => 'name',
                'default' => true,
            ],
            3 => [
                'name' => 'last_name',
                'label' => 'LBL_LAST_NAME',
                'default' => true,
            ],
            4 => [
                'name' => 'address_city',
                'label' => 'LBL_CITY',
                'type' => 'name',
                'default' => true,
            ],
            5 => [
                'name' => 'email',
                'label' => 'LBL_ANY_EMAIL',
                'type' => 'name',
                'default' => true,
            ],
            6 => [
                'name' => 'account_name',
                'label' => 'LBL_ACCOUNT_NAME',
                'default' => true,
            ],
            7 => [
                'name' => 'address_state',
                'label' => 'LBL_STATE',
                'type' => 'name',
                'default' => true,
            ],
            8 => [
                'width' => '10%',
                'label' => 'LBL_ASSIGNED_TO_NAME',
                'default' => true,
                'name' => 'assigned_user_name',
            ],
            9 => [
                'width' => '10%',
                'label' => 'LBL_DATE_ENTERED',
                'default' => true,
                'name' => 'date_entered',
            ],
            10 => [
                'name' => 'do_not_call',
                'label' => 'LBL_DO_NOT_CALL',
                'default' => true,
            ],
            11 => [
                'name' => 'assistant',
                'label' => 'LBL_ASSISTANT',
                'default' => true,
            ],
            12 => [
                'name' => 'address_postalcode',
                'label' => 'LBL_POSTAL_CODE',
                'type' => 'name',
                'default' => true,
            ],
            13 => [
                'name' => 'primary_address_country',
                'label' => 'LBL_COUNTRY',
                'type' => 'name',
                'options' => 'countries_dom',
                'default' => true,
            ],
            14 => [
                'name' => 'lead_source',
                'label' => 'LBL_LEAD_SOURCE',
                'default' => true,
            ],
            15 => [
                'name' => 'assigned_user_id',
                'type' => 'enum',
                'label' => 'LBL_ASSIGNED_TO',
                'function' => [
                    'name' => 'get_user_array',
                    'params' => [
                        0 => false,
                    ],
                ],
                'default' => true,
            ],
        ],
    ],
];
