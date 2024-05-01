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
        'QuickCreate' => [
            'templateMeta' => [
                'maxColumns' => '2',
                'form' => [
                    'hidden' => [
                        0 => '<input type="hidden" name="isSaveAndNew" value="false">',
                    ],
                    'buttons' => [
                        0 => [
                            'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button" onclick="SUGAR.meetings.fill_invitees();this.form.action.value=\'Save\'; this.form.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}this.form.return_id.value=\'\'; {/if}return check_form(\'EditView\');" type="submit" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                        ],
                        1 => 'CANCEL',
                        2 => [
                            'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" class="button" onclick="this.form.send_invites.value=\'1\';SUGAR.meetings.fill_invitees();this.form.action.value=\'Save\';this.form.return_action.value=\'EditView\';this.form.return_module.value=\'{$smarty.request.return_module}\';return check_form(\'EditView\');" type="submit" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',
                        ],
                        3 => [
                            'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" accessKey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}" class="button" onclick="SUGAR.meetings.fill_invitees(); this.form.status.value=\'Held\'; this.form.action.value=\'Save\'; this.form.return_module.value=\'Meetings\'; this.form.isDuplicate.value=true; this.form.isSaveAndNew.value=true; this.form.return_action.value=\'EditView\'; this.form.return_id.value=\'{$fields.id.value}\'; return check_form(\'EditView\');" type="submit" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
                        ],
                    ],
                    'headerTpl' => 'modules/Meetings/tpls/header.tpl',
                    'footerTpl' => 'modules/Meetings/tpls/footer.tpl',
                ],
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
                'javascript' => '<script type="text/javascript">{$JSON_CONFIG_JAVASCRIPT}</script>
<script>toggle_portal_flag();function toggle_portal_flag()  {literal} { {/literal} {$TOGGLE_JS} {literal} } {/literal} </script>',
                'useTabs' => false,
            ],
            'panels' => [
                'default' => [
                    0 => [
                        0 => [
                            'name' => 'name',
                            'displayParams' => [
                                'required' => true,
                            ],
                        ],
                        1 => [
                            'name' => 'status',
                            'fields' => [
                                0 => [
                                    'name' => 'status',
                                ],
                            ],
                        ],
                    ],
                    1 => [
                        0 => 'type',
                        1 => 'password',
                    ],
                    2 => [
                        0 => [
                            'name' => 'date_start',
                            'type' => 'datetimecombo',
                            'displayParams' => [
                                'required' => true,
                                'updateCallback' => 'SugarWidgetScheduler.update_time();',
                            ],
                        ],
                        1 => [
                            'name' => 'duration_minutes',
                            'comment' => 'Duration (minutes)',
                            'label' => 'LBL_DURATION_MINUTES',
                        ],
                    ],
                    3 => [
                        0 => [
                            'name' => 'outlook_id',
                            'comment' => 'When the Sugar Plug-in for Microsoft Outlook syncs an Outlook appointment, this is the Outlook appointment item ID',
                            'label' => 'LBL_OUTLOOK_ID',
                        ],
                        1 => [
                            'name' => 'parent_name',
                            'label' => 'LBL_LIST_RELATED_TO',
                        ],
                    ],
                    4 => [
                        0 => [
                            'name' => 'duration_hours',
                            'label' => 'LBL_DURATION',
                            'customCode' => '{literal}<script type="text/javascript">function isValidDuration(formName) { var form = document.getElementById(formName); if ( form.duration_hours.value + form.duration_minutes.value <= 0 ) { return false; } return true; }</script>{/literal}<div class="duration"><input name="duration_hours" id="duration_hours" size="2" maxlength="2" type="text" value="{$fields.duration_hours.value}" onkeyup="SugarWidgetScheduler.update_time();"/>{$fields.duration_minutes.value} {$MOD.LBL_HOURS_MINS}</div>',
                        ],
                    ],
                    5 => [
                        0 => [
                            'name' => 'reminder_time',
                            'customCode' => '{if $fields.reminder_checked.value == "1"}{assign var="REMINDER_TIME_DISPLAY" value="inline"}{assign var="REMINDER_CHECKED" value="checked"}{else}{assign var="REMINDER_TIME_DISPLAY" value="none"}{assign var="REMINDER_CHECKED" value=""}{/if}<input name="reminder_checked" type="hidden" value="0"><input name="reminder_checked" onclick=\'toggleDisplay("should_remind_list");\' type="checkbox" id="reminder_checkbox" class="checkbox" value="1" {$REMINDER_CHECKED}><div id="should_remind_list" style="display:{$REMINDER_TIME_DISPLAY}">{$fields.reminder_time.value}</div>',
                            'label' => 'LBL_REMINDER',
                        ],
                        1 => [
                            'name' => 'location',
                            'comment' => 'Meeting location',
                            'label' => 'LBL_LOCATION',
                        ],
                    ],
                    6 => [
                        0 => [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        1 => [
                            'name' => 'team_name',
                        ],
                    ],
                    7 => [
                        0 => [
                            'name' => 'description',
                            'comment' => 'Full text of the note',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
            ],
        ],
    ];
