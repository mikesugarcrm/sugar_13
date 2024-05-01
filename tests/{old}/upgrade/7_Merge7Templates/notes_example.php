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
$viewdefs['Notes']['base']['view']['record'] = [
    'panels' => [
        0 => [
            'name' => 'panel_header',
            'header' => true,
            'fields' => [
                0 => [
                    'name' => 'picture',
                    'type' => 'avatar',
                    'size' => 'large',
                    'dismiss_label' => true,
                    'readonly' => true,
                ],
                1 => 'name',
                2 => [
                    'name' => 'favorite',
                    'label' => 'LBL_FAVORITE',
                    'type' => 'favorite',
                    'dismiss_label' => true,
                ],
                3 => [
                    'name' => 'follow',
                    'label' => 'LBL_FOLLOW',
                    'type' => 'follow',
                    'readonly' => true,
                    'dismiss_label' => true,
                ],
            ],
        ],
        1 => [
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => [
                0 => 'contact_name',
                1 => 'parent_name',
                2 => 'assigned_user_name',
                3 => [
                    'name' => 'filename',
                    'related_fields' => [
                        0 => 'file_mime_type',
                    ],
                ],
                4 => [
                    'name' => 'description',
                    'rows' => 5,
                    'span' => 12,
                ],
            ],
        ],
        2 => [
            'name' => 'panel_hidden',
            'label' => 'LBL_RECORD_SHOWMORE',
            'hide' => true,
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => [
                0 => [
                    'name' => 'date_entered',
                    'comment' => 'Date record created',
                    'studio' => [
                        'portaleditview' => false,
                    ],
                    'readonly' => true,
                    'label' => 'LBL_DATE_ENTERED',
                ],
                1 => [
                    'name' => 'date_modified',
                    'comment' => 'Date record last modified',
                    'studio' => [
                        'portaleditview' => false,
                    ],
                    'readonly' => true,
                    'label' => 'LBL_DATE_MODIFIED',
                ],
                2 => [
                    'name' => 'team_name',
                    'span' => 6,
                ],
                3 => [
                    'span' => 6,
                ],
            ],
        ],
    ],
    'templateMeta' => [
        'useTabs' => false,
    ],
];
