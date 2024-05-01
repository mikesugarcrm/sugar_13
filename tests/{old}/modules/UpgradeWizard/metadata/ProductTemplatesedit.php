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
// created: 2014-01-23 18:54:55
$viewdefs['ProductTemplates']['EditView'] = [
    'templateMeta' => [
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
        'useTabs' => false,
        'syncDetailEditViews' => true,
    ],
    'panels' => [
        'default' => [
            0 => [
                0 => [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'displayParams' => [
                        'required' => true,
                    ],
                ],
                1 => [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'category_name',
                    'label' => 'LBL_CATEGORY_NAME',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'website',
                    'label' => 'LBL_URL',
                ],
                1 => [
                    'name' => 'date_available',
                    'label' => 'LBL_DATE_AVAILABLE',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'tax_class',
                    'label' => 'LBL_TAX_CLASS',
                ],
                1 => [
                    'name' => 'qty_in_stock',
                    'label' => 'LBL_QUANTITY',
                ],
            ],
            4 => [
                0 => [
                    'name' => 'manufacturer_id',
                    'label' => 'LBL_LIST_MANUFACTURER_ID',
                ],
                1 => [
                    'name' => 'weight',
                    'label' => 'LBL_WEIGHT',
                ],
            ],
            5 => [
                0 => [
                    'name' => 'mft_part_num',
                    'label' => 'LBL_MFT_PART_NUM',
                ],
            ],
            6 => [
                0 => [
                    'name' => 'vendor_part_num',
                    'label' => 'LBL_VENDOR_PART_NUM',
                ],
                1 => [
                    'name' => 'type_id',
                    'label' => 'LBL_TYPE',
                ],
            ],
            7 => [
                0 => [
                    'name' => 'currency_id',
                    'label' => 'LBL_CURRENCY',
                ],
                1 => [
                    'name' => 'support_name',
                    'label' => 'LBL_SUPPORT_NAME',
                ],
            ],
            8 => [
                0 => [
                    'name' => 'cost_price',
                    'label' => 'LBL_COST_PRICE',
                ],
                1 => [
                    'name' => 'support_contact',
                    'label' => 'LBL_SUPPORT_CONTACT',
                ],
            ],
            9 => [
                0 => [
                    'name' => 'list_price',
                    'label' => 'LBL_LIST_PRICE',
                ],
                1 => [
                    'name' => 'support_description',
                    'label' => 'LBL_SUPPORT_DESCRIPTION',
                ],
            ],
            10 => [
                0 => [
                    'name' => 'discount_price',
                    'label' => 'LBL_DISCOUNT_PRICE',
                ],
                1 => [
                    'name' => 'support_term',
                    'label' => 'LBL_SUPPORT_TERM',
                ],
            ],
            11 => [
                0 => [
                    'name' => 'pricing_formula',
                    'label' => 'LBL_PRICING_FORMULA',
                ],
            ],
            12 => [
                0 => [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'unit_test_c',
                ],
                1 => [
                    'name' => 'id',
                ],
            ],
        ],
    ],
];
