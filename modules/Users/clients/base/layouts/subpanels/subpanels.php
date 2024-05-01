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
$viewdefs['Users']['base']['layout']['subpanels'] = [
    'components' => [
        [
            'layout' => 'subpanel',
            'label' => 'LBL_ROLES_SUBPANEL_TITLE',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'aclroles',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_TEAMS',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'team_memberships',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_USER_HOLIDAY_SUBPANEL_TITLE',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'holidays',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_SHIFTS_SUBPANEL_TITLE',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'shifts',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_SHIFTS_EXCEPTIONS_SUBPANEL_TITLE',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'shift_exceptions',
            ],
        ],
        [
            'layout' => 'subpanel',
            'label' => 'LBL_EAPM_SUBPANEL_TITLE',
            'override_paneltop_view' => 'panel-top-for-users',
            'override_subpanel_list_view' => 'subpanel-for-users',
            'context' => [
                'link' => 'eapm',
            ],
        ],
    ],
];
