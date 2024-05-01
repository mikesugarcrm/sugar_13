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

$dictionary['ShiftException'] = [
    'table' => 'shift_exceptions',
    'audited' => true,
    'activity_enabled' => false,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge' => false,
    'fields' => [
        'timezone' => [
            'name' => 'timezone',
            'vname' => 'LBL_TIMEZONE',
            'type' => 'enum',
            'options' => 'timezone_dom',
            'comment' => 'Time Zone in which this Shift Exception operates',
            'required' => true,
            'audited' => true,
        ],
        'shift_exception_type' => [
            'name' => 'shift_exception_type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'shift_exception_type_dom',
            'len' => 50,
            'duplicate_on_record_copy' => 'always',
            'comment' => 'The Shift Exception is of this type',
            'required' => true,
            'audited' => true,
        ],
        'all_day' => [
            'name' => 'all_day',
            'vname' => 'LBL_ALL_DAY',
            'type' => 'bool',
            'default' => '1',
            'duplicate_on_record_copy' => 'always',
            'comment' => 'The Shift Exception is all day or not',
            'audited' => true,
        ],
        'start_date' => [
            'name' => 'start_date',
            'vname' => 'LBL_START_DATE',
            'type' => 'date',
            'comment' => 'The start date of the shift exception',
            'validation' => ['type' => 'isbefore', 'compareto' => 'end_date', 'blank' => false],
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'start_hour' => [
            'name' => 'start_hour',
            'vname' => 'LBL_START_HOUR',
            'type' => 'enum',
            'function' => 'getHoursDropdown',
            'function_bean' => 'BusinessCenters',
            'len' => 2,
            'group' => 'star_hours',
            'comment' => 'The hour portion of the time this shift exception starts',
            'merge_filter' => 'enabled',
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'start_minutes' => [
            'name' => 'start_minutes',
            'vname' => 'LBL_START_MINUTES',
            'type' => 'enum',
            'function' => 'getMinutesDropdown',
            'function_bean' => 'BusinessCenters',
            'len' => 2,
            'group' => 'start_hours',
            'comment' => 'The minute portion of the time this shift exception starts',
            'merge_filter' => 'enabled',
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'end_date' => [
            'name' => 'end_date',
            'vname' => 'LBL_END_DATE',
            'type' => 'date',
            'comment' => 'The end date of the shift exception',
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'end_hour' => [
            'name' => 'end_hour',
            'vname' => 'LBL_END_HOUR',
            'type' => 'enum',
            'function' => 'getHoursDropdown',
            'function_bean' => 'BusinessCenters',
            'len' => 2,
            'group' => 'end_hours',
            'comment' => 'The hour portion of the time this shift exception ends',
            'merge_filter' => 'enabled',
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'end_minutes' => [
            'name' => 'end_minutes',
            'vname' => 'LBL_END_MINUTES',
            'type' => 'enum',
            'function' => 'getMinutesDropdown',
            'function_bean' => 'BusinessCenters',
            'len' => 2,
            'group' => 'end_hours',
            'comment' => 'The minute portion of the time this shift exception ends',
            'merge_filter' => 'enabled',
            'required' => true,
            'audited' => true,
            'massupdate' => false,
        ],
        'enabled' => [
            'name' => 'enabled',
            'vname' => 'LBL_ENABLED',
            'type' => 'bool',
            'default' => '0',
            'audited' => true,
            'duplicate_on_record_copy' => 'always',
            'comment' => 'An indicator of whether shift exception is enabled',
        ],
        'shift_exceptions_users' => [
            'name' => 'shift_exceptions_users',
            'type' => 'link',
            'relationship' => 'shift_exceptions_users',
            'source' => 'non-db',
            'vname' => 'LBL_SHIFT_EXCEPTION_USERS_TITLE',
            'module' => 'Users',
            'bean_name' => 'User',
        ],
    ],
    'relationships' => [],
    'uses' => ['basic', 'assignable', 'team_security'],
];

VardefManager::createVardef('ShiftExceptions', 'ShiftException');
