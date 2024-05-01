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
                    'default' => true,
                    'width' => '10%',
                ],
                'created_by_name' => [
                    'type' => 'relate',
                    'link' => 'created_by_link',
                    'label' => 'LBL_CREATED',
                    'width' => '10%',
                    'default' => true,
                    'name' => 'created_by_name',
                ],
                'current_user_only' => [
                    'name' => 'current_user_only',
                    'label' => 'LBL_CURRENT_USER_FILTER',
                    'type' => 'bool',
                    'default' => true,
                    'width' => '10%',
                ],
            ],
            'advanced_search' => [
                'name' => [
                    'name' => 'name',
                    'default' => true,
                    'width' => '10%',
                ],
                'website' => [
                    'name' => 'website',
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
                'email' => [
                    'name' => 'email',
                    'label' => 'LBL_ANY_EMAIL',
                    'type' => 'name',
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
                'address_city' => [
                    'name' => 'address_city',
                    'label' => 'LBL_CITY',
                    'type' => 'name',
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
                    'type' => 'name',
                    'options' => 'countries_dom',
                    'default' => true,
                    'width' => '10%',
                ],
                'account_type' => [
                    'name' => 'account_type',
                    'default' => true,
                    'width' => '10%',
                ],
                'industry' => [
                    'name' => 'industry',
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
