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
$viewdefs['Project']['DetailView'] = [
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
        'includes' => [
            0 => [
                'file' => 'modules/Project/Project.js',
            ],
        ],
        'form' => [
            'buttons' => [
                0 => [
                    'customCode' => '<input title="{$APP.LBL_EDIT_BUTTON_TITLE}" accessKey="{$APP.LBL_EDIT_BUTTON_KEY}" class="button" type="submit" name="Edit" id="edit_button" value="{$APP.LBL_EDIT_BUTTON_LABEL}"{if $IS_TEMPLATE}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesDetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'ProjectTemplatesEditView\';"{else}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$id}\'; this.form.action.value=\'EditView\';" {/if}"/>',
                ],
                1 => [
                    'customCode' => '<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="Delete" id="delete_button" value="{$APP.LBL_DELETE_BUTTON_LABEL}"{if $IS_TEMPLATE}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ProjectTemplatesListView\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');"{else}onclick="this.form.return_module.value=\'Project\'; this.form.return_action.value=\'ListView\'; this.form.action.value=\'Delete\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" {/if}"/>',
                ],
                2 => [
                    'customCode' => '{if $EDIT_RIGHTS_ONLY}<input title="{$MOD.LBL_VIEW_GANTT_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="EditProjectTasks" value="  {$MOD.LBL_VIEW_GANTT_TITLE}  " onclick="prep_edit_project_tasks(this.form);" />{/if}',
                ],
                3 => [
                    'customCode' => '<input title="{$SAVE_AS}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" type="submit" name="SaveAsTemplate" value="{$SAVE_AS}"{if $IS_TEMPLATE}onclick="prep_save_as_project(this.form)"{else}onclick="prep_save_as_template(this.form) {/if}"/>',
                ],
                4 => [
                    'customCode' => '<input title="{$MOD.LBL_EXPORT_TO_MS_PROJECT}" class="button" type="submit" name="ExportToProject" value="  {$MOD.LBL_EXPORT_TO_MS_PROJECT}  " onclick="prep_export_to_project(this.form);"/>',
                ],
            ],
        ],
    ],
    'panels' => [
        'lbl_panel_1' => [
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
                    'label' => 'LBL_ASSIGNED_USER_ID',
                ],
                1 => [
                    'name' => 'team_name',
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
