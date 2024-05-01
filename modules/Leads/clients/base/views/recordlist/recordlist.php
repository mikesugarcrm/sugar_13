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

$viewdefs['Leads']['base']['view']['recordlist'] = [
    'selection' => [
        'type' => 'multi',
        'actions' => [
            [
                'name' => 'mass_email_button',
                'type' => 'mass-email-button',
                'label' => 'LBL_EMAIL_COMPOSE',
                'primary' => true,
                'events' => [
                    'click' => 'list:massaction:hide',
                ],
                'acl_module' => 'Emails',
                'acl_action' => 'edit',
                'related_fields' => ['name', 'email'],
            ],
            [
                'name' => 'massupdate_button',
                'type' => 'button',
                'label' => 'LBL_MASS_UPDATE',
                'primary' => true,
                'events' => [
                    'click' => 'list:massupdate:fire',
                ],
                'acl_action' => 'massupdate',
            ],
            [
                'name' => 'merge_button',
                'type' => 'button',
                'label' => 'LBL_MERGE',
                'primary' => true,
                'events' => [
                    'click' => 'list:mergeduplicates:fire',
                ],
                'acl_action' => 'edit',
            ],
            [
                'name' => 'calc_field_button',
                'type' => 'button',
                'label' => 'LBL_UPDATE_CALC_FIELDS',
                'events' => [
                    'click' => 'list:updatecalcfields:fire',
                ],
                'acl_action' => 'massupdate',
            ],
            [
                'name' => 'addtolist_button',
                'type' => 'button',
                'label' => 'LBL_ADD_TO_PROSPECT_LIST_BUTTON_LABEL',
                'primary' => true,
                'events' => [
                    'click' => 'list:massaddtolist:fire',
                ],
                'acl_module' => 'ProspectLists',
                'acl_action' => 'edit',
            ],
            [
                'name' => 'massdelete_button',
                'type' => 'button',
                'label' => 'LBL_DELETE',
                'acl_action' => 'delete',
                'primary' => true,
                'events' => [
                    'click' => 'list:massdelete:fire',
                ],
            ],
            [
                'name' => 'export_button',
                'type' => 'button',
                'label' => 'LBL_EXPORT',
                'acl_action' => 'export',
                'primary' => true,
                'events' => [
                    'click' => 'list:massexport:fire',
                ],
            ],
        ],
    ],
    'rowactions' => [
        'actions' => [
            [
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'sicon-preview',
                'acl_action' => 'view',
            ],
            [
                'type' => 'rowaction',
                'name' => 'edit_button',
                'icon' => 'sicon-edit',
                'label' => 'LBL_EDIT_BUTTON',
                'event' => 'list:editrow:fire',
                'acl_action' => 'edit',
            ],
            [
                'type' => 'follow',
                'name' => 'follow_button',
                'event' => 'list:follow:fire',
                'acl_action' => 'view',
            ],
            [
                'type' => 'rowaction',
                'name' => 'delete_button',
                'icon' => 'sicon-trash',
                'event' => 'list:deleterow:fire',
                'label' => 'LBL_DELETE_BUTTON',
                'acl_action' => 'delete',
            ],
            [
                'type' => 'convertbutton',
                'name' => 'lead_convert_button',
                'label' => 'LBL_CONVERT_BUTTON_LABEL',
                'acl_action' => 'edit',
            ],
        ],
    ],
];
