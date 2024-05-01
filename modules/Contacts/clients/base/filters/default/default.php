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

$viewdefs['Contacts']['base']['filter']['default'] = [
    'default_filter' => 'all_records',
    'quicksearch_field' => [
        [
            'first_name',
            'last_name',
        ],
        'email',
        'phone_work',
        'phone_mobile',
        'phone_other',
        'assistant_phone',
    ],
    'quicksearch_priority' => 2,
    'fields' => [
        'first_name' => [
            'type' => 'name',
        ],
        'last_name' => [
            'type' => 'name',
        ],
        'title' => [
            'type' => 'name',
        ],
        'account_name' => [],
        'lead_source' => [],
        'do_not_call' => [],
        'phone' => [
            'dbFields' => [
                'phone_mobile',
                'phone_work',
                'phone_other',
                'phone_fax',
                'assistant_phone',
            ],
            'type' => 'phone',
            'vname' => 'LBL_PHONE',
        ],
        'assistant' => [],
        'address_street' => [
            'dbFields' => [
                'primary_address_street',
                'alt_address_street',
            ],
            'vname' => 'LBL_STREET',
            'type' => 'text',
        ],
        'address_city' => [
            'dbFields' => [
                'primary_address_city',
                'alt_address_city',
            ],
            'vname' => 'LBL_CITY',
            'type' => 'text',
        ],
        'address_state' => [
            'dbFields' => [
                'primary_address_state',
                'alt_address_state',
            ],
            'vname' => 'LBL_STATE',
            'type' => 'text',
        ],
        'address_postalcode' => [
            'dbFields' => [
                'primary_address_postalcode',
                'alt_address_postalcode',
            ],
            'vname' => 'LBL_POSTAL_CODE',
            'type' => 'text',
        ],
        'address_country' => [
            'dbFields' => [
                'primary_address_country',
                'alt_address_country',
            ],
            'vname' => 'LBL_COUNTRY',
            'type' => 'text',
        ],
        'campaign_name' => [],
        'date_entered' => [],
        'date_modified' => [],
        'tag' => [],
        '$owner' => [
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        ],
        'assigned_user_name' => [],
        '$favorite' => [
            'predefined_filter' => true,
            'vname' => 'LBL_FAVORITES_FILTER',
        ],
        '$distance' => [
            'name' => '$distance',
            'vname' => 'LBL_MAPS_DISTANCE',
            'type' => 'maps-distance',
            'source' => 'non-db',
            'merge_filter' => 'enabled',
            'licenseFilter' => ['MAPS'],
        ],
    ],
];
