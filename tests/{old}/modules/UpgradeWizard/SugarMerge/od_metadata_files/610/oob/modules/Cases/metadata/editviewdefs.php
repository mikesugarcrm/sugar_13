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
$viewdefs['Cases']['EditView'] = [
    'templateMeta' => [
        'form' => [
            'footerTpl' => 'modules/Cases/tpls/EditViewFooter.tpl',
        ],
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [

        'lbl_case_information' => [
            [
                ['name' => 'case_number', 'type' => 'readonly'],
            ],

            [
                'priority',
            ],

            [
                'status',
                'account_name',
            ],

            [
                'type',
            ],
            [
                [
                    'name' => 'name',
                    'displayParams' => ['size' => 75],
                ],
            ],

            [
                [
                    'name' => 'description',
                    'nl2br' => true,
                ],
            ],

            [
                [
                    'name' => 'resolution',
                    'nl2br' => true,
                ],
            ],

        ],

        'LBL_PANEL_ASSIGNMENT' => [
            [
                'assigned_user_name',
                ['name' => 'team_name', 'displayParams' => ['required' => true]],
            ],
        ],
    ],


];
