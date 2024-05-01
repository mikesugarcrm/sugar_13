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
/**
 * User: rbacon
 * Date: 2012.04.03
 * Time: 16:51
 * just require this file into the unit test. and then use some iterator in the data provider.
 */
$canonicals = [
    // canonical panels
    [
        [
            'fields' => [],
            'name' => 0,
            'label' => 0,
            'columns' => 2,
            'placeholders' => 1,
        ],
    ],

    [
        [
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ],
                [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'comment' => 'Status of the lead',
                ],
                [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'comment' => 'Full text of the note',
                ],
                [],
            ],
            'name' => 0,
            'label' => '0',
            'columns' => 2,
            'placeholders' => 1,
        ],
    ],

    [
        [
            'fields' => [
                [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'span' => 12,
                ],
                [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                    'comment' => 'Status of the lead',
                ],
                [
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                    'comment' => 'Full text of the note',
                ],
            ],
            'name' => 0,
            'label' => '0',
            'columns' => 2,
            'placeholders' => 1,
        ],
    ],
    [
        [
            'fields' => [
                'name',
                'status',
                [
                    'name' => 'description',
                ],
                [],
            ],
            'name' => 0,
            'label' => '0',
            'columns' => 2,
            'placeholders' => 1,
        ],
    ],
    [
        [
            'fields' => [
                [],
                [],
            ],
            'name' => 0,
            'label' => '0',
            'columns' => 2,
            'placeholders' => 1,
        ],
    ],
];
