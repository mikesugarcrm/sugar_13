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
$viewdefs['Calls']['mobile']['view']['edit'] = [
    'templateMeta' => [
        'maxColumns' => '1',
        'widths' => [
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        [
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => [
                [
                    'name' => 'name',
                    'displayParams' => [
                        'required' => true,
                        'wireless_edit_only' => true,
                    ],
                ],
                'date_start',
                'direction',
                'status',
                [
                    'name' => 'duration',
                    'type' => 'fieldset',
                    'related_fields' => ['duration_hours', 'duration_minutes'],
                    'label' => 'LBL_DURATION',
                    'fields' => [
                        [
                            'name' => 'duration_hours',
                        ],
                        [
                            'name' => 'duration_minutes',
                            'type' => 'enum',
                            'options' => 'duration_intervals',
                        ],
                    ],
                ],
                'description',
                'assigned_user_name',
                'team_name',
            ],
        ],
    ],
];
