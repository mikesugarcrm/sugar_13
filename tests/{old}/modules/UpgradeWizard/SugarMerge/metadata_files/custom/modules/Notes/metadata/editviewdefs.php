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

$viewdefs['Notes']['EditView'] = [
    'templateMeta' => [
        'form' => [
            'enctype' => 'multipart/form-data',
            'headerTpl' => 'modules/Notes/tpls/EditViewHeader.tpl',
        ],
        'buttons' => ['DELETE'],
        'useTabs' => true,
        'tabDefs' => [],
        'syncDetailEditViews' => true,
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
        'javascript' => '<script type="text/javascript" src="include/javascript/dashlets.js?s={$SUGAR_VERSION}&c={$JS_CUSTOM_VERSION}"></script>
<script>
function deleteAttachmentCallBack(text) 
	{literal} { {/literal} 
	if(text == \'true\') {literal} { {/literal} 
		document.getElementById(\'new_attachment\').style.display = \'\';
		ajaxStatus.hideStatus();
		document.getElementById(\'old_attachment\').innerHTML = \'\'; 
	{literal} } {/literal} else {literal} { {/literal} 
		document.getElementById(\'new_attachment\').style.display = \'none\';
		ajaxStatus.flashStatus(SUGAR.language.get(\'Notes\', \'ERR_REMOVING_ATTACHMENT\'), 2000); 
	{literal} } {/literal}  
{literal} } {/literal} 
</script>
<script>toggle_portal_flag(); function toggle_portal_flag()  {literal} { {/literal} {$TOGGLE_JS} {literal} } {/literal} </script>',
    ],
    'panels' => [
        'lbl_note_information' => [
            ['contact_name', 'parent_name'],
            [
                ['name' => 'name', 'displayParams' => ['size' => 60]], '',
            ],
            [
                [
                    'name' => 'filename',
                    'customCode' => '<span id=\'new_attachment\' style=\'display:{if !empty($fields.filename.value)}none{/if}\'>
        									 <input name="uploadfile" tabindex="3" type="file" size="60"/>
        									 </span>
											 <span id=\'old_attachment\' style=\'display:{if empty($fields.filename.value)}none{/if}\'>
		 									 <input type=\'hidden\' name=\'deleteAttachment\' value=\'0\'>
		 									 {$fields.filename.value}<input type=\'hidden\' name=\'old_filename\' value=\'{$fields.filename.value}\'/><input type=\'hidden\' name=\'old_id\' value=\'{$fields.id.value}\'/>
											 <input type=\'button\' class=\'button\' value=\'{$APP.LBL_REMOVE}\' onclick=\'ajaxStatus.showStatus(SUGAR.language.get("Notes", "LBL_REMOVING_ATTACHMENT"));this.form.deleteAttachment.value=1;this.form.action.value="EditView";SUGAR.dashlets.postForm(this.form, deleteAttachmentCallBack);this.form.deleteAttachment.value=0;this.form.action.value="";\' >       
											 </span>',
                ],
                ['name' => 'portal_flag',
                    'displayParams' => ['required' => false],
                    'customLabel' => '{if ($PORTAL_ENABLED)}{sugar_translate label="LBL_PORTAL_FLAG" module="Notes"}{/if}',
                    'customCode' => ' {if ($PORTAL_ENABLED)}
											  {if $fields.portal_flag.value == "1"}
											  {assign var="checked" value="CHECKED"}
											  {else}
											  {assign var="checked" value=""}
											  {/if}
											  <input type="hidden" name="{$fields.portal_flag.name}" value="0"> 
											  <input type="checkbox" name="{$fields.portal_flag.name}" value="1" tabindex="1" {$checked}>
					        		          {/if}',
                ],
            ],
            [
                ['name' => 'description', 'label' => 'LBL_NOTE_STATUS'],
            ],

        ],


        'LBL_PANEL_ASSIGNMENT' => [
            [
                ['name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO'],
                ['name' => 'team_name'],
            ],
        ],
    ],
];
