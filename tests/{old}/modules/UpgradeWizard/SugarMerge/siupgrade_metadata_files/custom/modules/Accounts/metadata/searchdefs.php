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
$searchdefs ['Accounts'] =
    [
        'layout' => [
            'basic_search' => [
                'name' => [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'default' => true,
                ],
                'billing_address_city' => [
                    'name' => 'billing_address_city',
                    'label' => 'LBL_BILLING_ADDRESS_CITY',
                    'default' => true,
                ],
                'phone_office' => [
                    'name' => 'phone_office',
                    'label' => 'LBL_PHONE_OFFICE',
                    'default' => true,
                ],
                'address_street' => [
                    'name' => 'address_street',
                    'label' => 'LBL_BILLING_ADDRESS',
                    'type' => 'name',
                    'group' => 'billing_address_street',
                    'default' => true,
                ],
                'website' => [
                    'width' => '10%',
                    'label' => 'LBL_WEBSITE',
                    'default' => true,
                    'name' => 'website',
                ],
                'current_user_only' => [
                    'name' => 'current_user_only',
                    'label' => 'LBL_CURRENT_USER_FILTER',
                    'type' => 'bool',
                    'default' => true,
                ],
            ],
            'advanced_search' => [
                'name' => [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'default' => true,
                    'width' => '10%',
                ],
                'address_street' => [
                    'name' => 'address_street',
                    'label' => 'LBL_ANY_ADDRESS',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'phone' => [
                    'name' => 'phone',
                    'label' => 'LBL_ANY_PHONE',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'website' => [
                    'name' => 'website',
                    'label' => 'LBL_WEBSITE',
                    'default' => true,
                    'width' => '10%',
                ],
                'address_city' => [
                    'name' => 'address_city',
                    'label' => 'LBL_CITY',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'email' => [
                    'name' => 'email',
                    'label' => 'LBL_ANY_EMAIL',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'annual_revenue' => [
                    'name' => 'annual_revenue',
                    'label' => 'LBL_ANNUAL_REVENUE',
                    'default' => true,
                    'width' => '10%',
                ],
                'address_state' => [
                    'name' => 'address_state',
                    'label' => 'LBL_STATE',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'employees' => [
                    'name' => 'employees',
                    'label' => 'LBL_EMPLOYEES',
                    'default' => true,
                    'width' => '10%',
                ],
                'address_postalcode' => [
                    'name' => 'address_postalcode',
                    'label' => 'LBL_POSTAL_CODE',
                    'type' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'billing_address_country' => [
                    'name' => 'billing_address_country',
                    'label' => 'LBL_COUNTRY',
                    'type' => 'enum',
                    'options' => 'countries_dom',
                    'default' => true,
                    'width' => '10%',
                ],
                'ticker_symbol' => [
                    'name' => 'ticker_symbol',
                    'label' => 'LBL_TICKER_SYMBOL',
                    'default' => true,
                    'width' => '10%',
                ],
                'sic_code' => [
                    'name' => 'sic_code',
                    'label' => 'LBL_SIC_CODE',
                    'default' => true,
                    'width' => '10%',
                ],
                'rating' => [
                    'name' => 'rating',
                    'label' => 'LBL_RATING',
                    'default' => true,
                    'width' => '10%',
                ],
                'ownership' => [
                    'name' => 'ownership',
                    'label' => 'LBL_OWNERSHIP',
                    'default' => true,
                    'width' => '10%',
                ],
                'assigned_user_id' => [
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
                    'sortable' => false,
                    'width' => '10%',
                ],
                'account_type' => [
                    'name' => 'account_type',
                    'label' => 'LBL_TYPE',
                    'default' => true,
                    'sortable' => false,
                    'width' => '10%',
                ],
                'industry' => [
                    'name' => 'industry',
                    'label' => 'LBL_INDUSTRY',
                    'default' => true,
                    'sortable' => false,
                    'width' => '10%',
                ],
            ],
        ],
        'templateMeta' => [
            'maxColumns' => '3',
            'widths' => [
                'label' => '10',
                'field' => '30',
            ],
        ],
    ];
