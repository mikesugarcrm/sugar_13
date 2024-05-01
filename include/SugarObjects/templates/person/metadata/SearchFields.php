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
$module_name = '<module_name>';
$searchFields[$module_name] =
    [
        'first_name' => ['query_type' => 'default'],
        'last_name' => ['query_type' => 'default'],
        'search_name' => ['query_type' => 'default', 'db_field' => ['first_name', 'last_name'], 'force_unifiedsearch' => true],
        'do_not_call' => ['query_type' => 'default', 'input_type' => 'checkbox', 'operator' => '='],
        'phone' => ['query_type' => 'default', 'db_field' => ['phone_mobile', 'phone_work', 'phone_other', 'phone_fax', 'assistant_phone']],
        'email' => [
            'query_type' => 'default',
            'operator' => 'subquery',
            'subquery' => 'SELECT eabr.bean_id FROM email_addr_bean_rel eabr JOIN email_addresses ea ON (ea.id = eabr.email_address_id) WHERE eabr.deleted=0 AND ea.email_address LIKE',
            'db_field' => [
                'id',
            ],
            'vname' => 'LBL_ANY_EMAIL',
        ],
        'address_street' => ['query_type' => 'default', 'db_field' => ['primary_address_street', 'alt_address_street']],
        'address_city' => ['query_type' => 'default', 'db_field' => ['primary_address_city', 'alt_address_city']],
        'address_state' => ['query_type' => 'default', 'db_field' => ['primary_address_state', 'alt_address_state']],
        'address_postalcode' => ['query_type' => 'default', 'db_field' => ['primary_address_postalcode', 'alt_address_postalcode']],
        'address_country' => ['query_type' => 'default', 'db_field' => ['primary_address_country', 'alt_address_country']],
        'current_user_only' => ['query_type' => 'default', 'db_field' => ['assigned_user_id'], 'my_items' => true, 'vname' => 'LBL_CURRENT_USER_FILTER', 'type' => 'bool'],
        'favorites_only' => [
            'query_type' => 'format',
            'operator' => 'subquery',
            'subquery' => 'SELECT sugarfavorites.record_id FROM sugarfavorites 
			                    WHERE sugarfavorites.deleted=0 
			                        and sugarfavorites.module = \'' . $module_name . '\' 
			                        and sugarfavorites.assigned_user_id = \'{0}\'',
            'db_field' => ['id']],

        //Range Search Support
        'range_date_entered' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        'start_range_date_entered' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        'end_range_date_entered' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        'range_date_modified' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        'start_range_date_modified' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        'end_range_date_modified' => ['query_type' => 'default', 'enable_range_search' => true, 'is_date_field' => true],
        //Range Search Support
    ];
