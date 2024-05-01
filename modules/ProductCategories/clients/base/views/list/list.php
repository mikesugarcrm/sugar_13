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
$viewdefs['ProductCategories']['base']['view']['list'] = [
    'panels' => [
        [
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => [
                [
                    'name' => 'name',
                    'enabled' => true,
                    'default' => true,
                    'link' => true,
                ],
                [
                    'name' => 'parent_name',
                    'enabled' => true,
                    'default' => true,
                    'related_fields' => [
                        'parent_id',
                    ],
                    'id' => 'parent_id',
                    'label' => 'LBL_PARENT_CATEGORY',
                ],
                [
                    'name' => 'description',
                    'enabled' => true,
                    'default' => true,
                ],
                [
                    'name' => 'list_order',
                    'enabled' => true,
                    'default' => true,
                ],
            ],
        ],
    ],
];
