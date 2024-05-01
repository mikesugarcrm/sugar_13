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

$dictionary['Geocode'] = [
    'table' => 'geocode',
    'archive' => false,
    'audited' => false,
    'activity_enabled' => false,
    'reassignable' => false,
    'duplicate_merge' => false,
    'fields' => [
        'id' => [
            'name' => 'id',
            'vname' => 'LBL_ID',
            'type' => 'id',
            'required' => true,
            'reportable' => false,
            'duplicate_on_record_copy' => 'no',
            'comment' => 'Unique identifier',
            'mandatory_fetch' => true,
        ],
        'date_entered' => [
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_ENTERED',
            'type' => 'datetime',
            'group' => 'created_by_name',
            'comment' => 'Date record created',
            'enable_range_search' => true,
            'options' => 'date_range_search_dom',
            'studio' => [
                'portaleditview' => false, // Bug58408 - hide from Portal edit layout
            ],
            'duplicate_on_record_copy' => 'no',
            'readonly' => true,
            'massupdate' => false,
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
            ],
        ],
        'date_modified' => [
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'type' => 'datetime',
            'group' => 'modified_by_name',
            'comment' => 'Date record last modified',
            'enable_range_search' => true,
            'full_text_search' => [
                'enabled' => true,
                'searchable' => false,
                'sortable' => true,
            ],
            'studio' => [
                'portaleditview' => false, // Bug58408 - hide from Portal edit layout
            ],
            'options' => 'date_range_search_dom',
            'duplicate_on_record_copy' => 'no',
            'readonly' => true,
            'massupdate' => false,
        ],
        'deleted' => [
            'name' => 'deleted',
            'vname' => 'LBL_DELETED',
            'type' => 'bool',
            'default' => '0',
            'reportable' => false,
            'duplicate_on_record_copy' => 'no',
            'comment' => 'Record deletion indicator',
        ],
        'parent_id' => [
            'required' => true,
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_ID',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '36',
            'size' => '36',
        ],
        'parent_type' => [
            'required' => true,
            'name' => 'parent_type',
            'vname' => 'LBL_PARENT_TYPE',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '64',
            'size' => '64',
        ],
        'parent_name' => [
            'required' => true,
            'name' => 'parent_name',
            'vname' => 'LBL_PARENT_NAME',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '256',
            'size' => '256',
        ],
        'parent_user_name' => [
            'required' => true,
            'name' => 'parent_user_name',
            'vname' => 'LBL_PARENT_USER_NAME',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '256',
            'size' => '256',
        ],
        'address' => [
            'name' => 'address',
            'vname' => 'LBL_ADDRESS',
            'type' => 'text',
            'help' => 'Geocode Address',
        ],
        'status' => [
            'duplicate_merge_dom_value' => 0,
            'required' => false,
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'massupdate' => true,
            'mandatory_fetch' => true,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => 100,
            'size' => '20',
            'options' => 'gc_status_list',
            'studio' => 'visible',
            'dependency' => false,
        ],
        'postalcode' => [
            'required' => true,
            'name' => 'postalcode',
            'vname' => 'LBL_POSTALCODE',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '64',
            'size' => '64',
        ],
        'country' => [
            'required' => true,
            'name' => 'country',
            'vname' => 'LBL_COUNTRY',
            'type' => 'varchar',
            'massupdate' => false,
            'default' => '',
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '64',
            'size' => '64',
        ],
        'latitude' => [
            'duplicate_merge_dom_value' => 0,
            'required' => false,
            'name' => 'latitude',
            'vname' => 'LBL_LATITUDE',
            'type' => 'decimal',
            'mandatory_fetch' => true,
            'massupdate' => true,
            'default' => 0.00,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '18',
            'size' => '20',
            'enable_range_search' => false,
            'precision' => '10',
            'readonly' => true,
        ],
        'longitude' => [
            'duplicate_merge_dom_value' => 0,
            'required' => false,
            'name' => 'longitude',
            'vname' => 'LBL_LONGITUDE',
            'type' => 'decimal',
            'mandatory_fetch' => true,
            'massupdate' => true,
            'default' => 0.00,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'audited' => false,
            'reportable' => false,
            'merge_filter' => 'disabled',
            'len' => '18',
            'size' => '20',
            'enable_range_search' => false,
            'precision' => '10',
            'readonly' => true,
        ],
        'geocoded' => [
            'name' => 'geocoded',
            'vname' => 'LBL_GEOCODED',
            'type' => 'bool',
            'default' => '0',
            'reportable' => false,
            'duplicate_on_record_copy' => 'yes',
            'comment' => '',
        ],
        'error_message' => [
            'name' => 'error_message',
            'vname' => 'LBL_ERROR_MESSAGE',
            'type' => 'text',
            'help' => 'Geocode Error Message',
        ],
    ],
    'indices' => [
        'id' => [
            'name' => 'idx_geocode_pk',
            'type' => 'primary',
            'fields' => ['id'],
        ],
        'date_modified' => [
            'name' => 'idx_geocode_del_d_m',
            'type' => 'index',
            'fields' => ['deleted', 'date_modified', 'id'],
        ],
        'deleted' => [
            'name' => 'idx_geocode_id_del',
            'type' => 'index',
            'fields' => ['id', 'deleted'],
        ],
        'date_entered' => [
            'name' => 'idx_geocode_del_d_e',
            'type' => 'index',
            'fields' => ['deleted', 'date_entered', 'id'],
        ],
        'geocoded' => [
            'name' => 'idx_geocode_geocoded',
            'type' => 'index',
            'fields' => ['geocoded', 'id'],
        ],
        'parent_id' => [
            'name' => 'idx_geocode_p_i_id',
            'type' => 'index',
            'fields' => ['parent_id', 'id'],
        ],
        'parent_type' => [
            'name' => 'idx_geocode_p_t_id',
            'type' => 'index',
            'fields' => ['parent_type', 'id'],
        ],
        'country' => [
            'name' => 'idx_geocode_country',
            'type' => 'index',
            'fields' => ['country'],
        ],
        'postalcode' => [
            'name' => 'idx_geocode_postal_code',
            'type' => 'index',
            'fields' => ['postalcode'],
        ],
        'status' => [
            'name' => 'idx_geocode_status',
            'type' => 'index',
            'fields' => ['status'],
        ],
        'coords_from_zip' => [
            'name' => 'idx_coords_from_zip',
            'type' => 'index',
            'fields' => ['postalcode', 'country', 'geocoded', 'status'],
        ],
        'coords_from_record' => [
            'name' => 'idx_coords_from_record',
            'type' => 'index',
            'fields' => ['parent_type', 'parent_id', 'geocoded', 'status'],
        ],
    ],
    'relationships' => [],
    'optimistic_locking' => true,
    'portal_visibility' => [],
    'ignore_templates' => [
        'integrate_fields',
        'default',
    ],
    'uses' => [],
];

VardefManager::createVardef('Geocode', 'Geocode');