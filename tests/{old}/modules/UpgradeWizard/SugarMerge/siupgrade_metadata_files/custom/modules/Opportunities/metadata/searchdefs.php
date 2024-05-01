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
$searchdefs ['Opportunities'] =
    [
        'layout' => [
            'basic_search' => [
                0 => 'name',
                1 => 'opportunity_type',
                2 => 'account_name',
                3 => [
                    'name' => 'current_user_only',
                    'label' => 'LBL_CURRENT_USER_FILTER',
                    'type' => 'bool',
                ],
            ],
            'advanced_search' => [
                'name' => [
                    'name' => 'name',
                    'label' => 'LBL_OPPORTUNITY_NAME',
                    'default' => true,
                    'width' => '10%',
                ],
                'amount' => [
                    'name' => 'amount',
                    'label' => 'LBL_AMOUNT',
                    'default' => true,
                    'currency_format' => true,
                    'width' => '10%',
                ],
                'account_name' => [
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'default' => true,
                    'width' => '10%',
                ],
                'date_closed' => [
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'default' => true,
                    'width' => '10%',
                ],
                'opportunity_type' => [
                    'width' => '10%',
                    'label' => 'LBL_TYPE',
                    'sortable' => false,
                    'default' => true,
                    'name' => 'opportunity_type',
                ],
                'lead_source' => [
                    'name' => 'lead_source',
                    'label' => 'LBL_LEAD_SOURCE',
                    'default' => true,
                    'sortable' => false,
                    'width' => '10%',
                ],
                'next_step' => [
                    'name' => 'next_step',
                    'label' => 'LBL_NEXT_STEP',
                    'default' => true,
                    'width' => '10%',
                ],
                'sales_stage' => [
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                    'default' => true,
                    'sortable' => false,
                    'width' => '10%',
                ],
                'probability' => [
                    'name' => 'probability',
                    'label' => 'LBL_PROBABILITY',
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
                'partner_assigned_to_c' => [
                    'width' => '10%',
                    'label' => 'Partner_Assigned_To_c',
                    'default' => true,
                    'name' => 'partner_assigned_to_c',
                ],
                'order_number' => [
                    'width' => '10%',
                    'label' => 'LBL_ORDER_NUMBER',
                    'default' => true,
                    'name' => 'order_number',
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
