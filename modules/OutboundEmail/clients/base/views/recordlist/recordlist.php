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
$viewdefs['OutboundEmail']['base']['view']['recordlist'] = [
    'favorite' => true,
    'following' => false,
    'sticky_resizable_columns' => false,
    'selection' => [],
    'rowactions' => [
        'actions' => [
            [
                'type' => 'rowaction',
                'name' => 'edit_button',
                'dismiss_label' => true,
                'icon' => 'sicon-edit',
                'tooltip' => 'LBL_EDIT_BUTTON',
                'acl_action' => 'edit',
                'route' => [
                    'action' => 'edit',
                    'module' => 'OutboundEmail',
                ],
            ],
            [
                'type' => 'rowaction',
                'name' => 'delete_button',
                'event' => 'list:deleterow:fire',
                'label' => 'LBL_DELETE_BUTTON',
                'acl_action' => 'delete',
            ],
        ],
    ],
    'last_state' => [
        'id' => 'record-list',
    ],
];