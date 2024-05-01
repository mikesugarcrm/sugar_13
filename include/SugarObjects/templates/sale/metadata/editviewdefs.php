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
/*********************************************************************************
 * Description:  Defines the English language pack for the base application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['EditView'] = [
    'templateMeta' => ['maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
        'javascript' => '{$PROBABILITY_SCRIPT}',
    ],
    'panels' => [
        'lbl_sale_information' => [
            [
                'name',
                'currency_id',
            ],
            [
                $_object_name . '_type',
                'amount',
            ],
            ['lead_source', 'date_closed'],
            [
                ['name' => 'team_name', 'displayParams' => ['required' => true]],
                'sales_stage',
            ],

            [
                ['name' => 'assigned_user_name', 'displayParams' => ['required' => true]],
                'next_step',
            ],
            [
                'probability',
                '',
            ],

            [
                'description',
            ],
        ],
    ],


];