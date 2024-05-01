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

$viewdefs['KBContents']['base']['view']['create'] = [
    'template' => 'record',
    'buttons' => [
        [
            'name' => 'cancel_button',
            'type' => 'button',
            'label' => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'events' => [
                'click' => 'button:cancel_button:click',
            ],
        ],
        [
            'name' => 'restore_button',
            'type' => 'button',
            'label' => 'LBL_RESTORE',
            'css_class' => 'btn-invisible btn-link',
            'showOn' => 'select',
            'events' => [
                'click' => 'button:restore_button:click',
            ],
        ],
        [
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'switch_on_click' => true,
            'showOn' => 'create',
            'buttons' => [
                [
                    'type' => 'rowaction',
                    'name' => 'save_button',
                    'label' => 'LBL_SAVE_BUTTON_LABEL',
                    'events' => [
                        'click' => 'button:save_button:click',
                    ],
                ],
            ],
        ],
        [
            'type' => 'actiondropdown',
            'name' => 'duplicate_dropdown',
            'primary' => true,
            'showOn' => 'duplicate',
            'buttons' => [
                [
                    'type' => 'rowaction',
                    'name' => 'duplicate_button',
                    'label' => 'LBL_IGNORE_DUPLICATE_AND_SAVE',
                    'events' => [
                        'click' => 'button:save_button:click',
                    ],
                ],
            ],
        ],
        [
            'type' => 'actiondropdown',
            'name' => 'select_dropdown',
            'primary' => true,
            'showOn' => 'select',
            'buttons' => [
                [
                    'type' => 'rowaction',
                    'name' => 'select_button',
                    'label' => 'LBL_SAVE_BUTTON_LABEL',
                    'events' => [
                        'click' => 'button:save_button:click',
                    ],
                ],
            ],
        ],
        [
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ],
    ],
];