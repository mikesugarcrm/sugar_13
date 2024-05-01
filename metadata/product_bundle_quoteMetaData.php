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

$dictionary['product_bundle_quote'] = [
    'table' => 'product_bundle_quote',
    'fields' => [
        'id' => [
            'name' => 'id',
            'type' => 'id',
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'type' => 'datetime',
        ],
        'deleted' => [
            'name' => 'deleted',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
            'required' => false,
        ],
        'bundle_id' => [
            'name' => 'bundle_id',
            'type' => 'id',
        ],
        'quote_id' => [
            'name' => 'quote_id',
            'type' => 'id',
        ],
        'bundle_index' => [
            'name' => 'bundle_index',
            'type' => 'int',
            'len' => '11',
            'default' => 0,
            'required' => false,
        ],
    ],
    'indices' => [
        [
            'name' => 'prod_bundl_quotepk',
            'type' => 'primary',
            'fields' => [
                'id',
            ],
        ],
        [
            'name' => 'idx_pbq_bundle',
            'type' => 'index',
            'fields' => [
                'bundle_id',
            ],
        ],
        [
            'name' => 'idx_pbq_bq',
            'type' => 'alternate_key',
            'fields' => [
                'quote_id',
                'bundle_id',
            ],
        ],
        [
            'name' => 'bundle_index_idx',
            'type' => 'index',
            'fields' => [
                'bundle_index',
            ],
        ],
    ],
    'relationships' => [
        'product_bundle_quote' => [
            'lhs_module' => 'Quotes',
            'lhs_table' => 'quotes',
            'lhs_key' => 'id',
            'rhs_module' => 'ProductBundles',
            'rhs_table' => 'product_bundles',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-many',
            'join_table' => 'product_bundle_quote',
            'join_key_lhs' => 'quote_id',
            'join_key_rhs' => 'bundle_id',
            'true_relationship_type' => 'one-to-many',
        ],
    ],
];