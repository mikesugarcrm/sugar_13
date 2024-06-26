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

$viewdefs['Home']['base']['view']['tutorial'] = [
    'record' => [
        'version' => 1,
        'intro' => 'LBL_TOUR_INTRO',
        'content' => [
            [
                'name' => '[href=#Home]',
                'text' => 'LBL_TOUR_CUBE',
                'full' => true,
                'horizAdj' => -11,
                'vertAdj' => -5,
            ],
            [
                'name' => '[data-route="#Accounts"]',
                'text' => 'LBL_TOUR_NAV_BAR',
                'full' => true,
                'vertAdj' => -13,
            ],
            [
                'name' => '.search-query',
                'text' => 'LBL_TOUR_SEARCH',
                'full' => true,
                'vertAdj' => -8,
            ],
            [
                'name' => '.notification-list',
                'text' => 'LBL_TOUR_NOTIFICATIONS',
                'full' => true,
                'horizAdj' => -8,
            ],
            [
                'name' => '#userActions',
                'text' => 'LBL_TOUR_AVATAR',
                'full' => true,
                'horizAdj' => -3,
            ],
            [
                'name' => '#createList',
                'text' => 'LBL_TOUR_ADD',
                'full' => true,
                'horizAdj' => -3,
            ],
        ],
    ],
];
