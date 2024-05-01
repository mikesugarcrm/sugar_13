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
$listViewDefs ['Opportunities'] =
    [
        'NAME' => [
            'width' => '30%',
            'label' => 'LBL_LIST_OPPORTUNITY_NAME',
            'link' => true,
            'default' => true,
        ],
        'SALES_STAGE' => [
            'width' => '10%',
            'label' => 'LBL_LIST_SALES_STAGE',
            'default' => true,
        ],
        'LEAD_SOURCE' => [
            'width' => '15%',
            'label' => 'LBL_LEAD_SOURCE',
            'default' => true,
        ],
        'REQREVBAND_C' => [
            'width' => '10%',
            'label' => 'LBL_REQREVBAND',
            'default' => true,
        ],
        'AMOUNT_USDOLLAR' => [
            'width' => '50%',
            'label' => 'LBL_LIST_AMOUNT + {$MY CUSTOMIZATION}',
            'align' => 'right',
            'default' => true,
            'currency_format' => true,
        ],
        'AIRVOLUMEDAY_C' => [
            'width' => '10%',
            'label' => 'LBL_AIRVOLUMEDAY',
            'default' => true,
        ],
        'TERRITORY_C' => [
            'width' => '10%',
            'label' => 'LBL_TERRITORY',
            'default' => true,
        ],
        'HOTACCOUNT_C' => [
            'width' => '10%',
            'label' => 'LBL_HOTACCOUNT',
            'default' => true,
        ],
        'DATE_CLOSED' => [
            'width' => '10%',
            'label' => 'LBL_LIST_DATE_CLOSED',
            'default' => true,
        ],
        'ROE_DENIED_C' => [
            'width' => '10%',
            'label' => 'LBL_ROE_DENIED',
            'default' => true,
        ],
        'SSRLEAD_C' => [
            'type' => 'bool',
            'default' => true,
            'label' => 'LBL_SSRLEAD',
            'width' => '10%',
        ],
        'ASSIGNED_USER_NAME' => [
            'width' => '5%',
            'label' => 'LBL_LIST_ASSIGNED_USER',
            'default' => true,
        ],
        'ACCOUNT_NAME' => [
            'width' => '20%',
            'label' => 'LBL_LIST_ACCOUNT_NAME',
            'id' => 'ACCOUNT_ID',
            'module' => 'Accounts',
            'link' => true,
            'default' => false,
            'sortable' => true,
            'ACLTag' => 'ACCOUNT',
            'contextMenu' => [
                'objectType' => 'sugarAccount',
                'metaData' => [
                    'return_module' => 'Contacts',
                    'return_action' => 'ListView',
                    'module' => 'Accounts',
                    'parent_id' => '{$ACCOUNT_ID}',
                    'parent_name' => '{$ACCOUNT_NAME}',
                    'account_id' => '{$ACCOUNT_ID}',
                    'account_name' => '{$ACCOUNT_NAME}',
                ],
            ],
            'related_fields' => [
                0 => 'account_id',
            ],
        ],
        'NEXT_STEP' => [
            'width' => '10%',
            'label' => 'LBL_NEXT_STEP',
            'default' => false,
        ],
        'OPPORTUNITY_TYPE' => [
            'width' => '10%',
            'label' => 'LBL_TYPE',
            'default' => false,
        ],
        'TEAM_NAME' => [
            'width' => '5%',
            'label' => 'LBL_LIST_TEAM',
            'default' => false,
        ],
        'PROBABILITY' => [
            'width' => '10%',
            'label' => 'LBL_PROBABILITY',
            'default' => false,
        ],
        'DATEREQUESTED_C' => [
            'width' => '10%',
            'label' => 'LBL_DATEREQUESTED',
            'default' => false,
        ],
        'AMOUNT' => [
            'width' => '10%',
            'label' => 'LBL_AMOUNT',
            'currency_format' => true,
            'default' => false,
        ],
        'DATE_ENTERED' => [
            'width' => '10%',
            'label' => 'LBL_DATE_ENTERED',
            'default' => false,
        ],
        'CREATED_BY_NAME' => [
            'width' => '10%',
            'label' => 'LBL_CREATED',
            'default' => false,
        ],
        'MODIFIED_BY_NAME' => [
            'width' => '5%',
            'label' => 'LBL_MODIFIED',
            'default' => false,
        ],
    ];
