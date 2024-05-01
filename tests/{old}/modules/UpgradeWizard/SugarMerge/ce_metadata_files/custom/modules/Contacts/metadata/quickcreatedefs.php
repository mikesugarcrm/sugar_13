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

$viewdefs ['Contacts'] =
    [
        'QuickCreate' => [
            'templateMeta' => [
                'form' => [
                    'hidden' => [
                        0 => '<input type="hidden" name="opportunity_id" value="{$smarty.request.opportunity_id}">',
                        1 => '<input type="hidden" name="case_id" value="{$smarty.request.case_id}">',
                        2 => '<input type="hidden" name="bug_id" value="{$smarty.request.bug_id}">',
                        3 => '<input type="hidden" name="email_id" value="{$smarty.request.email_id}">',
                        4 => '<input type="hidden" name="inbound_email_id" value="{$smarty.request.inbound_email_id}">',
                        5 => '<input type="hidden" name="reports_to_id" value="{$smarty.request.contact_id}">',
                        6 => '<input type="hidden" name="report_to_name" value="{$smarty.request.contact_name}">',
                    ],
                ],
                'maxColumns' => '2',
                'widths' => [
                    0 => [
                        'label' => '10',
                        'field' => '30',
                    ],
                    1 => [
                        'label' => '10',
                        'field' => '30',
                    ],
                ],
                'useTabs' => false,
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'first_name',
                        ],
                        1 => [
                            'name' => 'account_name',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'last_name',
                            'displayParams' => [
                                'required' => true,
                            ],
                        ],
                        1 => [
                            'name' => 'phone_work',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'title',
                        ],
                        1 => [
                            'name' => 'phone_mobile',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'phone_fax',
                        ],
                        1 => [
                            'name' => 'do_not_call',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'email1',
                        ],
                        1 => [
                            'name' => 'lead_source',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'assigned_user_name',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'test_c',
                            'label' => 'LBL_TEST',
                        ],
                        1 => [
                            'name' => 'test2_c',
                            'label' => 'LBL_TEST2',
                        ],
                    ],
                ],
            ],
        ],
    ];
