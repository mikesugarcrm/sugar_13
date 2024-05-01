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

$popupMeta = [
    'moduleMain' => 'Contact',
    'varName' => 'CONTACT',
    'orderBy' => 'contacts.first_name, contacts.last_name',
    'whereClauses' => [
        'first_name' => 'contacts.first_name',
        'last_name' => 'contacts.last_name',
        'account_name' => 'accounts.name',
        'test_c' => 'contacts_cstm.test_c',
        'test2_c' => 'contacts_cstm.test2_c',
        'title' => 'contacts.title',
        'lead_source' => 'contacts.lead_source',
        'campaign_name' => 'contacts.campaign_name',
        'assigned_user_id' => 'contacts.assigned_user_id',
    ],
    'searchInputs' => [
        0 => 'first_name',
        1 => 'last_name',
        2 => 'account_name',
        3 => 'test_c',
        4 => 'test2_c',
        5 => 'title',
        6 => 'lead_source',
        7 => 'campaign_name',
        8 => 'assigned_user_id',
    ],
    'create' => [
        'formBase' => 'ContactFormBase.php',
        'formBaseClass' => 'ContactFormBase',
        'getFormBodyParams' => [
            0 => '',
            1 => '',
            2 => 'ContactSave',
        ],
        'createButton' => 'Create Contact',
    ],
    'searchdefs' => [
        'first_name' => [
            'name' => 'first_name',
            'width' => '10%',
        ],
        'test_c' => [
            'type' => 'varchar',
            'label' => 'LBL_TEST',
            'width' => '10%',
            'name' => 'test_c',
        ],
        'test2_c' => [
            'type' => 'varchar',
            'label' => 'LBL_TEST2',
            'width' => '10%',
            'name' => 'test2_c',
        ],
        'last_name' => [
            'name' => 'last_name',
            'width' => '10%',
        ],
        'account_name' => [
            'name' => 'account_name',
            'displayParams' => [
                'hideButtons' => 'true',
                'size' => 30,
                'class' => 'sqsEnabled sqsNoAutofill',
            ],
            'width' => '10%',
        ],
        'title' => [
            'name' => 'title',
            'width' => '10%',
        ],
        'lead_source' => [
            'name' => 'lead_source',
            'width' => '10%',
        ],
        'campaign_name' => [
            'name' => 'campaign_name',
            'displayParams' => [
                'hideButtons' => 'true',
                'size' => 30,
                'class' => 'sqsEnabled sqsNoAutofill',
            ],
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
            'width' => '10%',
        ],
    ],
    'listviewdefs' => [
        'NAME' => [
            'width' => '20%',
            'label' => 'LBL_LIST_NAME',
            'link' => true,
            'default' => true,
            'related_fields' => [
                0 => 'first_name',
                1 => 'last_name',
                2 => 'salutation',
                3 => 'account_name',
                4 => 'account_id',
            ],
            'name' => 'name',
        ],
        'TEST_C' => [
            'type' => 'varchar',
            'default' => true,
            'label' => 'LBL_TEST',
            'width' => '10%',
        ],
        'TEST2_C' => [
            'type' => 'varchar',
            'default' => true,
            'label' => 'LBL_TEST2',
            'width' => '10%',
        ],
        'ACCOUNT_NAME' => [
            'width' => '25%',
            'label' => 'LBL_LIST_ACCOUNT_NAME',
            'module' => 'Accounts',
            'id' => 'ACCOUNT_ID',
            'default' => true,
            'sortable' => true,
            'ACLTag' => 'ACCOUNT',
            'related_fields' => [
                0 => 'account_id',
            ],
            'name' => 'account_name',
        ],
        'TITLE' => [
            'width' => '15%',
            'label' => 'LBL_LIST_TITLE',
            'default' => true,
            'name' => 'title',
        ],
        'LEAD_SOURCE' => [
            'width' => '15%',
            'label' => 'LBL_LEAD_SOURCE',
            'default' => true,
            'name' => 'lead_source',
        ],
    ],
];
