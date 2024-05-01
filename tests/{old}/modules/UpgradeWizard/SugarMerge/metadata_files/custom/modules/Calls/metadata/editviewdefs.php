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

$viewdefs ['Calls'] =
    [
        'EditView' => [
            'templateMeta' => [
                'maxColumns' => '2',
                'form' => [
                    'hidden' => [
                        0 => '<input type="hidden" name="isSaveAndNew" value="false">',
                        1 => '<input type="hidden" name="send_invites">',
                        2 => '<input type="hidden" name="user_invitees">',
                        3 => '<input type="hidden" name="lead_invitees">',
                        4 => '<input type="hidden" name="contact_invitees">',
                    ],
                    'buttons' => [
                        0 => [
                            'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}" class="button primary" onclick="fill_invitees();document.forms[0].action.value=\'Save\'; document.forms[0].return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.forms[0].return_id.value=\'\'; {/if}formSubmitCheck();;" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                        ],
                        1 => 'CANCEL',
                        2 => [
                            'customCode' => '<input title="{$MOD.LBL_SEND_BUTTON_TITLE}" class="button" onclick="document.forms[0].send_invites.value=\'1\';fill_invitees();document.forms[0].action.value=\'Save\';document.forms[0].return_action.value=\'EditView\';document.forms[0].return_module.value=\'{$smarty.request.return_module}\';formSubmitCheck();;" type="button" name="button" value="{$MOD.LBL_SEND_BUTTON_LABEL}">',
                        ],
                        3 => [
                            'customCode' => '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" accessKey="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_KEY}" class="button" onclick="fill_invitees(); document.forms[0].status.value=\'Held\'; document.forms[0].action.value=\'Save\'; document.forms[0].return_module.value=\'Calls\'; document.forms[0].isDuplicate.value=true; document.forms[0].isSaveAndNew.value=true; document.forms[0].return_action.value=\'EditView\'; document.forms[0].return_id.value=\'{$fields.id.value}\'; formSubmitCheck();" type="button" name="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_LABEL}">{/if}',
                        ],
                    ],
                    'footerTpl' => 'modules/Calls/tpls/footer.tpl',
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
                'javascript' => '<script type="text/javascript" src="include/JSON.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/jsolait/init.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/jsolait/lib/urllib.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript">{$JSON_CONFIG_JAVASCRIPT}</script>
<script type="text/javascript" src="include/javascript/jsclass_base.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="include/javascript/jsclass_async.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script type="text/javascript" src="modules/Meetings/jsclass_scheduler.js?s=94932f0dc915603816562a2cc59dbcd0&c=1"></script>
<script>toggle_portal_flag();function toggle_portal_flag()  {ldelim} {$TOGGLE_JS} {rdelim} 
function formSubmitCheck(){ldelim}if(check_form(\'EditView\') && isValidDuration()){ldelim}document.forms[0].submit();{rdelim}{rdelim}</script>',
                'useTabs' => false,
            ],
            'panels' => [
                'lbl_call_information' => [
                    [
                        'name' => 'name',
                        [
                            'name' => 'status',
                            'fields' => [
                                [
                                    'name' => 'direction',
                                ],
                                [
                                    'name' => 'status',
                                ],
                            ],
                        ],
                    ],
                    [
                        [
                            'name' => 'date_start',
                            'displayParams' => [
                                'updateCallback' => 'SugarWidgetScheduler.update_time();',
                            ],
                            'label' => 'LBL_DATE_TIME',
                        ],
                        [
                            'name' => 'parent_name',
                            'label' => 'LBL_LIST_RELATED_TO',
                        ],
                    ],
                    [
                        [
                            'name' => 'reminder_time',
                            'customCode' => '{if $fields.reminder_checked.value == "1"}{assign var="REMINDER_TIME_DISPLAY" value="inline"}{assign var="REMINDER_CHECKED" value="checked"}{else}{assign var="REMINDER_TIME_DISPLAY" value="none"}{assign var="REMINDER_CHECKED" value=""}{/if}<input name="reminder_checked" type="hidden" value="0"><input name="reminder_checked" onclick=\'toggleDisplay("should_remind_list");\' type="checkbox" class="checkbox" value="1" {$REMINDER_CHECKED}><div id="should_remind_list" style="display:{$REMINDER_TIME_DISPLAY}">{$fields.reminder_time.value}</div>',
                            'label' => 'LBL_REMINDER',
                        ],
                        [
                            'name' => 'duration_hours',
                            'label' => 'LBL_DURATION',
                            'customCode' => '{literal}<script type="text/javascript">function isValidDuration() { form = document.getElementById(\'EditView\'); if ( form.duration_hours.value + form.duration_minutes.value <= 0 ) { alert(\'{/literal}{$MOD.NOTICE_DURATION_TIME}{literal}\'); return false; } return true; }</script>{/literal}<input id="duration_hours" name="duration_hours" tabindex="1" size="2" maxlength="2" type="text" value="{$fields.duration_hours.value}" onkeyup="SugarWidgetScheduler.update_time();"/>{$fields.duration_minutes.value}&nbsp;<span class="dateFormat">{$MOD.LBL_HOURS_MINUTES}</span>',

                        ],
                    ],
                    [
                        [
                            'name' => 'description',
                            'comment' => 'Full text of the note',
                            'label' => 'LBL_DESCRIPTION',
                        ],
                    ],
                ],
                'LBL_PANEL_ASSIGNMENT' => [
                    [
                        [
                            'name' => 'assigned_user_name',
                            'label' => 'LBL_ASSIGNED_TO_NAME',
                        ],
                        [
                            'name' => 'team_name',
                            'displayParams' => ['display' => true],
                        ],
                    ],
                ],
            ],
        ],
    ];
