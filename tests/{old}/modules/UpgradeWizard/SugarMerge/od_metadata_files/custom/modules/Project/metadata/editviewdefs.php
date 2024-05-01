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
$viewdefs['Project']['EditView'] = [
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
            'hidden' => '<input type="hidden" name="is_template" value="{$is_template}" />',
            'buttons' => [
                0 => 'SAVE',
                1 => [
                    'customCode' => '{if !empty($smarty.request.return_action) && $smarty.request.return_action == "ProjectTemplatesDetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'ProjectTemplatesDetailView\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> {elseif !empty($smarty.request.return_action) && $smarty.request.return_action == "DetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'DetailView\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> {elseif $is_template}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'ProjectTemplatesListView\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> {else}<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="this.form.action.value=\'index\'; this.form.module.value=\'{$smarty.request.return_module}\'; this.form.record.value=\'{$smarty.request.return_id}\';" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}"> {/if}',
                ],
            ],
        ],
    ],
    'panels' => [
        'default' => [
            0 => [
                0 => [
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                ],
                1 => [
                    'name' => 'status',
                    'label' => 'LBL_STATUS',
                ],
            ],
            1 => [
                0 => [
                    'name' => 'estimated_start_date',
                    'label' => 'LBL_DATE_START',
                ],
                1 => [
                    'name' => 'estimated_end_date',
                    'label' => 'LBL_DATE_END',
                ],
            ],
            2 => [
                0 => [
                    'name' => 'assigned_user_name',
                    'label' => 'LBL_ASSIGNED_USER_NAME',
                ],
                1 => [
                    'name' => 'team_name',
                    'label' => 'LBL_TEAM',
                ],
            ],
            3 => [
                0 => [
                    'name' => 'priority',
                    'label' => 'LBL_PRIORITY',
                ],
            ],
            4 => [
                0 => [
                    'name' => 'description',
                    'displayParams' => [
                        'rows' => '8',
                        'cols' => '60',
                    ],
                    'label' => 'LBL_DESCRIPTION',
                ],
                1 => null,
            ],
            5 => [
                0 => [
                    'name' => 'kiosk_kiosk_project_name',
                ],
            ],
        ],
    ],
];
