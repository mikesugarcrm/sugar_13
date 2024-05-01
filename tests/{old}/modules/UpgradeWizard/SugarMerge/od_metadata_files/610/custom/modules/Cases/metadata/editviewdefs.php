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

$viewdefs ['Cases'] =
    [
        'EditView' => [
            'templateMeta' => [
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
                'form' => [
                    'footerTpl' => 'custom/modules/Cases/tpls/EditViewFooter.tpl',
                ],
                'useTabs' => false,
            ],
            'panels' => [
                'lbl_case_information' => [
                    0 => [
                        0 => [
                            'name' => 'case_number',
                            'type' => 'readonly',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'priority',
                            'comment' => 'The priority of the case',
                            'label' => 'LBL_PRIORITY',
                        ],
                        1 => [
                            'name' => 'ticket_due_date_c',
                            'label' => 'LBL_TICKET_DUE_DATE',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'status',
                            'comment' => 'The status of the case',
                            'label' => 'LBL_STATUS',
                        ],
                        1 => [
                            'name' => 'account_name',
                            'comment' => 'The name of the account represented by the account_id field',
                            'label' => 'LBL_ACCOUNT_NAME',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'type',
                            'comment' => 'The type of issue (ex: issue, feature)',
                            'label' => 'LBL_TYPE',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'name',
                            'displayParams' => [
                                'size' => 75,
                            ],
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'description',
                            'nl2br' => true,
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'resolution',
                            'nl2br' => true,
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'portal_viewable',
                            'customLabel' => '{if ($PORTAL_ENABLED)}{sugar_translate label="LBL_SHOW_IN_PORTAL" module="Cases"}{/if}',
                            'customCode' => ' {if ($PORTAL_ENABLED)}
								{if $fields.portal_viewable.value == "1"}
								{assign var="checked" value="CHECKED"}
								{else}
								{assign var="checked" value=""}
									{/if}
								<input type="hidden" name="{$fields.portal_viewable.name}" value="0"> 
								<input type="checkbox" name="{$fields.portal_viewable.name}" value="1" tabindex="1" {$checked}>
		        		        {/if}',
                        ],
                        1 => [
                            'name' => 'tick_email_on_close_c',
                            'label' => 'LBL_TICK_EMAIL_ON_CLOSE',
                        ],
                    ],
                ],
                'LBL_PANEL_ASSIGNMENT' => [
                    0 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'team_name',
                            'displayParams' => [
                                'required' => true,
                            ],
                        ],
                    ],
                ],
                'lbl_editview_panel1' => [
                    0 => [
                        0 => [
                            'name' => 'contact_c',
                            'studio' => 'visible',
                            'label' => 'LBL_CONTACT_C',
                            'displayParams' => [
                                'call_back_function' => 'setAccountInfo',
                            ],
                        ],
                        1 => '',
                    ],
                    1 => [
                        0 => [
                            'name' => 'account_address_street',
                            'hideLabel' => true,
                            'type' => 'address',
                            'displayParams' => [
                                'key' => 'account',
                                'rows' => 2,
                                'cols' => 30,
                                'maxlength' => 150,
                            ],
                        ],
                        1 => '',
                    ],
                ],
            ],
        ],
    ];
