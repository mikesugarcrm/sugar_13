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
$viewdefs ['Meetings'] =
    [
        'DetailView' => [
            'templateMeta' => [
                'form' => [
                    'buttons' => [
                        0 => 'EDIT',
                        1 => 'DUPLICATE',
                        2 => 'DELETE',
                        3 => [
                            'customCode' => '{if $fields.status.value != "Held"} <input type="hidden" name="isSaveAndNew" value="false">  <input type="hidden" name="status" value="">  <input type="hidden" name="isSaveFromDetailView" value="true">  <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  accesskey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}"  class="button"  onclick="this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Meetings\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.isDuplicate.value=true;this.form.return_id.value=\'{$fields.id.value}\';"  name="button"  value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                        ],
                        4 => [
                            'customCode' => '{if $fields.status.value != "Held"} <input type="hidden" name="isSave" value="false">  <input title="{$APP.LBL_CLOSE_BUTTON_TITLE}"  accesskey="{$APP.LBL_CLOSE_BUTTON_KEY}"  class="button"  onclick="this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Meetings\';this.form.isSave.value=true;this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'"  name="button1"  value="{$APP.LBL_CLOSE_BUTTON_TITLE}"  type="submit">{/if}',
                        ],
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
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'label' => 'LBL_SUBJECT',
                        ],
                        1 => [
                            'name' => 'status',
                            'label' => 'LBL_STATUS',
                        ],
                    ],
                    1 => [
                        0 => [
                            'name' => 'location',
                            'label' => 'LBL_LOCATION',
                        ],
                        1 => [
                            'name' => 'parent_name',
                            'customLabel' => '{sugar_translate label=\'LBL_MODULE_NAME\' module=$fields.parent_type.value}',
                            'label' => 'LBL_LIST_RELATED_TO',
                        ],
                    ],
                    2 => [
                        0 => [
                            'name' => 'date_start',
                            'label' => 'LBL_DATE_TIME',
                        ],
                        1 => [
                            'name' => 'duration_hours',
                            'customCode' => '{$fields.duration_hours.value}{$MOD.LBL_HOURS_ABBREV} {$fields.duration_minutes.value}{$MOD.LBL_MINSS_ABBREV}&nbsp;',
                            'label' => 'LBL_DURATION',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO',
                        ],
                        1 => [
                            'name' => 'created_by_name',
                            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
                            'label' => 'LBL_DATE_ENTERED',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'reminder_checked',
                            'fields' => [
                                0 => 'reminder_checked',
                                1 => 'reminder_time',
                            ],
                            'label' => 'LBL_REMINDER',
                        ],
                        1 => [
                            'name' => 'description',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'meetings_opportunities_name',
                        ],
                    ],
                ],
            ],
        ],
    ];
