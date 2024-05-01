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

$listViewDefs ['Contacts'] =
    [
        'NAME' => [
            'width' => '20%',
            'label' => 'LBL_LIST_NAME',
            'link' => true,
            'contextMenu' => [
                'objectType' => 'sugarPerson',
                'metaData' => [
                    'contact_id' => '{$ID}',
                    'module' => 'Contacts',
                    'return_action' => 'ListView',
                    'contact_name' => '{$FULL_NAME}',
                    'parent_id' => '{$ACCOUNT_ID}',
                    'parent_name' => '{$ACCOUNT_NAME}',
                    'return_module' => 'Contacts',
                    'parent_type' => 'Account',
                    'notes_parent_type' => 'Account',
                ],
            ],
            'orderBy' => 'name',
            'default' => true,
            'related_fields' => [
                0 => 'first_name',
                1 => 'last_name',
                2 => 'salutation',
                3 => 'account_name',
                4 => 'account_id',
            ],
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
        'TITLE' => [
            'width' => '15%',
            'label' => 'LBL_LIST_TITLE',
            'default' => true,
        ],
        'ACCOUNT_NAME' => [
            'width' => '34%',
            'label' => 'LBL_LIST_ACCOUNT_NAME',
            'module' => 'Accounts',
            'id' => 'ACCOUNT_ID',
            'link' => true,
            'contextMenu' => [
                'objectType' => 'sugarAccount',
                'metaData' => [
                    'return_module' => 'Contacts',
                    'return_action' => 'ListView',
                    'module' => 'Accounts',
                    'parent_id' => '{$ACCOUNT_ID}',
                    'parent_name' => '{$ACCOUNT_NAME}',
                    'account_id' => '{$ACCOUNT_ID}',
                    'account_name' => '{$ACCOUNT_NAME}',
                ],
            ],
            'default' => true,
            'sortable' => true,
            'ACLTag' => 'ACCOUNT',
            'related_fields' => [
                0 => 'account_id',
            ],
        ],
        'EMAIL1' => [
            'width' => '15%',
            'label' => 'LBL_LIST_EMAIL_ADDRESS',
            'sortable' => false,
            'link' => true,
            'customCode' => '{$EMAIL1_LINK}{$EMAIL1}</a>',
            'default' => true,
        ],
        'PHONE_WORK' => [
            'width' => '15%',
            'label' => 'LBL_OFFICE_PHONE',
            'default' => true,
        ],
        'ASSIGNED_USER_NAME' => [
            'width' => '10%',
            'label' => 'LBL_LIST_ASSIGNED_USER',
            'default' => true,
        ],
        'DEPARTMENT' => [
            'width' => '10%',
            'label' => 'LBL_DEPARTMENT',
            'default' => false,
        ],
        'DO_NOT_CALL' => [
            'width' => '10%',
            'label' => 'LBL_DO_NOT_CALL',
            'default' => false,
        ],
        'PHONE_HOME' => [
            'width' => '10%',
            'label' => 'LBL_HOME_PHONE',
            'default' => false,
        ],
        'PHONE_MOBILE' => [
            'width' => '10%',
            'label' => 'LBL_MOBILE_PHONE',
            'default' => false,
        ],
        'PHONE_OTHER' => [
            'width' => '10%',
            'label' => 'LBL_OTHER_PHONE',
            'default' => false,
        ],
        'PHONE_FAX' => [
            'width' => '10%',
            'label' => 'LBL_FAX_PHONE',
            'default' => false,
        ],
        'EMAIL2' => [
            'width' => '15%',
            'label' => 'LBL_LIST_EMAIL_ADDRESS',
            'sortable' => false,
            'customCode' => '{$EMAIL2_LINK}{$EMAIL2}</a>',
            'default' => false,
        ],
        'PRIMARY_ADDRESS_STREET' => [
            'width' => '10%',
            'label' => 'LBL_PRIMARY_ADDRESS_STREET',
            'default' => false,
        ],
        'PRIMARY_ADDRESS_CITY' => [
            'width' => '10%',
            'label' => 'LBL_PRIMARY_ADDRESS_CITY',
            'default' => false,
        ],
        'PRIMARY_ADDRESS_STATE' => [
            'width' => '10%',
            'label' => 'LBL_PRIMARY_ADDRESS_STATE',
            'default' => false,
        ],
        'PRIMARY_ADDRESS_POSTALCODE' => [
            'width' => '10%',
            'label' => 'LBL_PRIMARY_ADDRESS_POSTALCODE',
            'default' => false,
        ],
        'ALT_ADDRESS_COUNTRY' => [
            'width' => '10%',
            'label' => 'LBL_ALT_ADDRESS_COUNTRY',
            'default' => false,
        ],
        'ALT_ADDRESS_STREET' => [
            'width' => '10%',
            'label' => 'LBL_ALT_ADDRESS_STREET',
            'default' => false,
        ],
        'ALT_ADDRESS_CITY' => [
            'width' => '10%',
            'label' => 'LBL_ALT_ADDRESS_CITY',
            'default' => false,
        ],
        'ALT_ADDRESS_STATE' => [
            'width' => '10%',
            'label' => 'LBL_ALT_ADDRESS_STATE',
            'default' => false,
        ],
        'ALT_ADDRESS_POSTALCODE' => [
            'width' => '10%',
            'label' => 'LBL_ALT_ADDRESS_POSTALCODE',
            'default' => false,
        ],
        'DATE_ENTERED' => [
            'width' => '10%',
            'label' => 'LBL_DATE_ENTERED',
            'default' => false,
        ],
        'CREATED_BY_NAME' => [
            'width' => '10%',
            'label' => 'LBL_CREATED',
            'default' => false,
        ],
        'MODIFIED_BY_NAME' => [
            'width' => '10%',
            'label' => 'LBL_MODIFIED',
            'default' => false,
        ],
    ];
