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


$listViewDefs['Documents'] = [
    'FILE_URL' => [
        'width' => '2%',
        'label' => '&nbsp;',
        'link' => true,
        'default' => true,
        'related_fields' => [
            0 => 'document_revision_id',
        ],
        'sortable' => false,
        'studio' => false,
    ],
    'DOCUMENT_NAME' => [
        'width' => '40%',
        'label' => 'LBL_NAME',
        'link' => true,
        'default' => true,
        'bold' => true,
    ],
    'CATEGORY_ID' => [
        'width' => '40%',
        'label' => 'LBL_LIST_CATEGORY',
        'default' => true,
    ],
    'SUBCATEGORY_ID' => [
        'width' => '40%',
        'label' => 'LBL_LIST_SUBCATEGORY',
        'default' => true,
    ],
    'TEAM_NAME' => [
        'width' => '2',
        'label' => 'LBL_LIST_TEAM',
        'default' => false,
        'sortable' => false,
    ],
    'LAST_REV_CREATE_DATE' => [
        'width' => '10%',
        'label' => 'LBL_LIST_LAST_REV_DATE',
        'default' => true,
        'sortable' => false,
        'related_fields' => [
            0 => 'document_revision_id',
        ],
    ],
    'CREATED_BY_NAME' => [
        'width' => '2%',
        'label' => 'LBL_LIST_LAST_REV_CREATOR',
        'default' => true,
        'sortable' => false,
    ],
    'ACTIVE_DATE' => [
        'width' => '10%',
        'label' => 'LBL_LIST_ACTIVE_DATE',
        'default' => true,
    ],
    'EXP_DATE' => [
        'width' => '10%',
        'label' => 'LBL_LIST_EXP_DATE',
        'default' => true,
    ],
    'MODIFIED_BY_NAME' => [
        'width' => '10%',
        'label' => 'LBL_MODIFIED_USER',
        'module' => 'Users',
        'id' => 'USERS_ID',
        'default' => false,
        'sortable' => false,
        'related_fields' => [
            0 => 'modified_user_id',
        ],
    ],
];
