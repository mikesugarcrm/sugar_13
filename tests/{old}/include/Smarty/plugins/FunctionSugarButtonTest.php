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

use PHPUnit\Framework\TestCase;

require_once 'include/SugarSmarty/plugins/function.sugar_button.php';
require_once 'include/SugarSmarty/plugins/function.sugar_menu.php';

class FunctionSugarButtonTest extends TestCase
{
    /**
     * @var \Sugar_Smarty|mixed
     */
    //@codingStandardsIgnoreStart
    public $_smarty;

    //@codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        $this->_smarty = new Sugar_Smarty();
    }

    public function providerCustomCode()
    {
        $onclick = 'this.form.module.value=\'Contacts\';';
        $expected_onclick = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Contacts\';_form.submit();';
        $onclick2 = 'this.form.module.value=\'Projects\';';
        $expected_onclick2 = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Projects\';_form.submit();';
        $onclick3 = 'this.form.module.value=\'Meeting\';';
        $expected_onclick3 = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Meeting\';_form.submit();';
        $onclick4 = 'this.form.module.value=\'Notes\';';
        $expected_onclick4 = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Notes\';_form.submit();';
        return [

            //set #0: simple input code
            [
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="' . $onclick . '">',
                [
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => $onclick,
                    'value' => '{$APP.BUTTON_LABEL}',
                ],
                '<input type="button" value="{$APP.BUTTON_LABEL}" onclick="' . $expected_onclick . '"/>',
            ],
            //set #1:
            [
                '<input type="submit" disabled value="{$APP.BUTTON_LABEL}" onclick="' . $onclick . '">',
                [
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => $onclick,
                    'value' => '{$APP.BUTTON_LABEL}',
                    'disabled' => '',
                ],
                '<input type="button" disabled value="{$APP.BUTTON_LABEL}" onclick="' . $expected_onclick . '"/>',
            ],

            //set #2: custom code contains smarty conditional statement
            [
                '<input type="submit" value="{$APP.BUTTON_LABEL}" onclick="{if $bean->access(\'edit\')}' . $onclick . '{/if}">',
                [
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'onclick' => '{if $bean->access(\'edit\')}' . $onclick . '{/if}',
                    'value' => '{$APP.BUTTON_LABEL}',
                ],
                '<input type="button" value="{$APP.BUTTON_LABEL}" onclick="var _form = document.getElementById(\'DetailView\');{if $bean->access(\'edit\')}_form.module.value=\'Contacts\';{/if};_form.submit();"/>',
            ],
            //set #3: attributes wrapped with smarty
            [
                //custom code
                '<input {if $bean->access(\'edit\')}type="submit"{else}type="hidden"{/if} value="{$APP.BUTTON_LABEL}" {if $bean->access(\'edit\')}onclick="' . $onclick . '"{else}onclick="alert(\'nope\');"{/if} id="button_submit">',
                //parsed array
                [
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'self_closing' => true,
                    'smarty' => [
                        [
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => [
                                'type' => 'submit',
                            ],
                            '[CONTENT1]' => [
                                'type' => 'hidden',
                            ],
                        ],
                        [
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => [
                                'onclick' => $onclick,
                            ],
                            '[CONTENT1]' => [
                                'onclick' => 'alert(\'nope\');',
                            ],
                        ],
                    ],

                    'value' => '{$APP.BUTTON_LABEL}',
                ],
                '<input {if $bean->access(\'edit\')}type="submit"{else}type="hidden"{/if} {if $bean->access(\'edit\')}onclick="' . $expected_onclick . '"{else}onclick="alert(\'nope\');"{/if} value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ],

            //set #4: attributes wrapped with smarty
            [
                //custom code
                '<input type="submit"{if $bean->access(\'edit\')}onclick="' . $onclick . '"{else}onclick="this.form.module.value=\'{$APP.MODULE}\';this.form.action.value=\'{$APP.ACTION}\';"{/if} value="{$APP.BUTTON_LABEL}" id="button_submit">',
                //parsed array
                [
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'smarty' => [
                        [
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else}[CONTENT1]{/if}',
                            '[CONTENT0]' => [
                                'onclick' => $onclick,
                            ],
                            '[CONTENT1]' => [
                                'onclick' => 'this.form.module.value=\'{$APP.MODULE}\';this.form.action.value=\'{$APP.ACTION}\';',
                            ],
                        ],
                    ],

                    'value' => '{$APP.BUTTON_LABEL}',
                ],
                '<input {if $bean->access(\'edit\')}onclick="' . $expected_onclick . '"{else}onclick="var _form = document.getElementById(\'DetailView\');_form.module.value=\'{$APP.MODULE}\';_form.action.value=\'{$APP.ACTION}\';_form.submit();"{/if} type="button" value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ],

            //set #5: recursive smarty wrapper within the attributes
            [
                '<input type="submit" value="{$APP.BUTTON_LABEL}" {$APP.DISABLED} {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="' . $onclick . '"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} id="button_submit">',
                [
                    'id' => 'button_submit',
                    'tag' => 'input',
                    'type' => 'submit',
                    'self_closing' => true,
                    'value' => '{$APP.BUTTON_LABEL}',
                    'smarty' => [
                        [
                            'template' => '{$APP.DISABLED}',
                        ],
                        [
                            'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else if $bean->access(\'delete\') }[CONTENT1]{else}[CONTENT2]{/if}',
                            '[CONTENT0]' => [
                                'smarty' => [
                                    [
                                        'template' => '{if $APP.CONTAINER = true}[CONTENT0]{/if}',
                                        '[CONTENT0]' => [
                                            'onclick' => $onclick,
                                        ],
                                    ],
                                ],
                            ],
                            '[CONTENT1]' => [
                                'onclick' => 'del();',
                            ],
                            '[CONTENT2]' => [
                                'onclick' => 'alert(\'nope\');',
                            ],
                        ],
                    ],
                ],
                '<input {$APP.DISABLED} {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="' . $expected_onclick . '"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} type="button" value="{$APP.BUTTON_LABEL}" id="button_submit"/>',
            ],
            //set #6: Begins with smarty conditional statement
            [
                '{if $fields.status.value != "Held"} <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="' . $onclick . '" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                [
                    'smarty' => [
                        [
                            'template' => '{if $fields.status.value != "Held"}[CONTENT0]{/if}',
                            '[CONTENT0]' => [
                                'tag' => 'input',
                                'type' => 'submit',
                                'self_closing' => true,
                                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'class' => 'button',
                                'onclick' => $onclick,
                                'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                            ],
                        ],
                    ],
                ],
                '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="' . $expected_onclick . '" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" type="button"/>{/if}',
            ],
            //set #7: Begins with smarty conditional statement and contains recursive conditional statement inside the context
            [
                '{ if($fields.status.value != "Held") } <input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="' . $onclick . '"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if}  class="button" onclick="' . $onclick . '" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"  type="submit">{/if}',
                [
                    'smarty' => [
                        [
                            'template' => '{ if($fields.status.value != "Held") }[CONTENT0]{/if}',
                            '[CONTENT0]' => [
                                'tag' => 'input',
                                'type' => 'submit',
                                'class' => 'button',
                                'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'onclick' => $onclick,
                                'self_closing' => true,
                                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                'smarty' => [
                                    [
                                        'template' => '{if $bean->access(\'edit\')}[CONTENT0]{else if $bean->access(\'delete\') }[CONTENT1]{else}[CONTENT2]{/if}',
                                        '[CONTENT0]' => [
                                            'smarty' => [
                                                [
                                                    'template' => '{if $APP.CONTAINER = true}[CONTENT0]{/if}',
                                                    '[CONTENT0]' => [
                                                        'onclick' => $onclick,
                                                    ],
                                                ],
                                            ],
                                        ],
                                        '[CONTENT1]' => [
                                            'onclick' => 'del();',
                                        ],
                                        '[CONTENT2]' => [
                                            'onclick' => 'alert(\'nope\');',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '{ if($fields.status.value != "Held") }<input {if $bean->access(\'edit\')}{if $APP.CONTAINER = true}onclick="' . $expected_onclick . '"{/if}{else if $bean->access(\'delete\') }onclick="del();"{else}onclick="alert(\'nope\');"{/if} title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" class="button" onclick="' . $expected_onclick . '" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" type="button"/>{/if}',
            ],
            //set #8: The submit button is encapsulated with another form
            [
                '<form name="blah">   <input type="hidden" name="id1">    <input type="hidden" name="id2">     <input type="submit" onclick="' . $onclick . '"></form>',
                [
                    'tag' => 'form',
                    'name' => 'blah',
                    'self_closing' => false,
                    'container' => [
                        [
                            'tag' => 'input',
                            'type' => 'hidden',
                            'name' => 'id1',
                            'self_closing' => true,
                        ],
                        [
                            'tag' => 'input',
                            'type' => 'hidden',
                            'name' => 'id2',
                            'self_closing' => true,
                        ],
                        [
                            'tag' => 'input',
                            'type' => 'submit',
                            'onclick' => $onclick,
                            'self_closing' => true,
                        ],
                    ],
                ],
                '<form name="blah"><input type="hidden" name="id1"/><input type="hidden" name="id2"/><input type="submit" onclick="' . $onclick . '"/></form>',
            ],

            //set #9: custom code encapsulated smarty conditional statement, and contains additional hidden fields
            [
                '{if $fields.status.value != "Held"} <input type="hidden" name="id1" value="true">    <input type="hidden" name="id2">     <input type="submit" {if $APP.CONTAINER = true}onclick="' . $onclick . '"{else}onclick="stop();"{/if} value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">{/if}',
                [
                    'smarty' => [
                        [
                            'template' => '{if $fields.status.value != "Held"}[CONTENT0]{/if}',
                            '[CONTENT0]' => [
                                [
                                    'tag' => 'input',
                                    'type' => 'hidden',
                                    'name' => 'id1',
                                    'self_closing' => true,
                                    'value' => 'true',
                                ],
                                [
                                    'tag' => 'input',
                                    'type' => 'hidden',
                                    'name' => 'id2',
                                    'self_closing' => true,
                                ],
                                [
                                    'tag' => 'input',
                                    'type' => 'submit',
                                    'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                                    'self_closing' => true,
                                    'smarty' => [
                                        [
                                            'template' => '{if $APP.CONTAINER = true}[CONTENT0]{else}[CONTENT1]{/if}',
                                            '[CONTENT0]' => [
                                                'onclick' => $onclick,
                                            ],
                                            '[CONTENT1]' => [
                                                'onclick' => 'stop();',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input {if $APP.CONTAINER = true}onclick="' . $expected_onclick . '"{else}onclick="stop();"{/if} type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}',
            ],
            //set #10: empty spaces after the equal sign
            [
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id= "SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();"type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                [
                    'tag' => 'input',
                    'title' => '{$APP.LBL_SAVE_BUTTON_TITLE}',
                    'id' => 'SAVE',
                    'disabled' => '',
                    'onclick' => 'SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();',
                    'type' => 'button',
                    'name' => 'button',
                    'value' => '{$APP.LBL_SAVE_BUTTON_LABEL}',
                    'self_closing' => true,
                ],
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>',
            ],
            //set #11: empty spaces before the equal sign
            [
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id    = "SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();"type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}">',
                [
                    'tag' => 'input',
                    'title' => '{$APP.LBL_SAVE_BUTTON_TITLE}',
                    'id' => 'SAVE',
                    'disabled' => '',
                    'onclick' => 'SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();',
                    'type' => 'button',
                    'name' => 'button',
                    'value' => '{$APP.LBL_SAVE_BUTTON_LABEL}',
                    'self_closing' => true,
                ],
                '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" id="SAVE" disabled onclick="SUGAR.meetings.fill_invitees();document.EditView.action.value=\'Save\'; document.EditView.return_action.value=\'DetailView\'; {if isset($smarty.request.isDuplicate) && $smarty.request.isDuplicate eq "true"}document.EditView.return_id.value=\'\'; {/if} formSubmitCheck();" type="button" name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}"/>',
            ],
            //set #12: Contains smarty syntax "ldelim, rdelim"
            [
                '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Users\'; this.form.return_action.value=\'EditView\'; this.form.action.value=\'Delete\'; this.form.return_id.value=\'{$return_id}\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');" type="submit" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}">{/if} ',
                [
                    'smarty' => [
                        [
                            'template' => '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}[CONTENT0]{/if}',
                            '[CONTENT0]' => [
                                'tag' => 'input',
                                'self_closing' => true,
                                'title' => '{$APP.LBL_DELETE_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_DELETE_BUTTON_KEY}',
                                'class' => 'button',
                                'onclick' => 'this.form.return_module.value=\'Users\'; this.form.return_action.value=\'EditView\'; this.form.action.value=\'Delete\'; this.form.return_id.value=\'{$return_id}\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');',
                                'type' => 'submit',
                                'name' => 'Delete',
                                'value' => '{$APP.LBL_DELETE_BUTTON_LABEL}',
                            ],
                        ],
                    ],
                ],
                '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" onclick="var _form = document.getElementById(\'DetailView\'); var _onclick=(function(){ldelim}_form.return_module.value=\'Users\'; _form.return_action.value=\'EditView\'; _form.action.value=\'Delete\'; _form.return_id.value=\'{$return_id}\'; return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');{rdelim}()); if(_onclick!==false) _form.submit();" type="button" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}"/>{/if}',
            ],

            //set #13: Contains smarty syntax "literal"
            [
                '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Users\'; this.form.return_action.value=\'EditView\'; this.form.action.value=\'Delete\'; this.form.return_id.value=\'{$return_id}\'; {literal}return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');{/literal}" type="submit" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}">{/if} ',
                [
                    'smarty' => [
                        [
                            'template' => '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}[CONTENT0]{/if}',
                            '[CONTENT0]' => [
                                'tag' => 'input',
                                'self_closing' => true,
                                'title' => '{$APP.LBL_DELETE_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_DELETE_BUTTON_KEY}',
                                'class' => 'button',
                                'onclick' => 'this.form.return_module.value=\'Users\'; this.form.return_action.value=\'EditView\'; this.form.action.value=\'Delete\'; this.form.return_id.value=\'{$return_id}\'; {literal}return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');{/literal}',
                                'type' => 'submit',
                                'name' => 'Delete',
                                'value' => '{$APP.LBL_DELETE_BUTTON_LABEL}',
                            ],
                        ],
                    ],
                ],
                '{if $bean->aclAccess("delete") && !empty($smarty.request.record)}<input title="{$APP.LBL_DELETE_BUTTON_TITLE}" accessKey="{$APP.LBL_DELETE_BUTTON_KEY}" class="button" onclick="var _form = document.getElementById(\'DetailView\'); var _onclick=(function(){ldelim}_form.return_module.value=\'Users\'; _form.return_action.value=\'EditView\'; _form.action.value=\'Delete\'; _form.return_id.value=\'{$return_id}\'; {literal}return confirm(\'{$APP.NTC_DELETE_CONFIRMATION}\');{/literal};{rdelim}()); if(_onclick!==false) _form.submit();" type="button" name="Delete" value="{$APP.LBL_DELETE_BUTTON_LABEL}"/>{/if}',
            ],

            //set #14: Multiple conditional statement
            [
                '{if !empty($smarty.request.return_action) && $smarty.request.return_action == "ProjectTemplatesDetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $onclick . '" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"> ' .
                '{elseif !empty($smarty.request.return_action) && $smarty.request.return_action == "DetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $onclick2 . '" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"> ' .
                '{elseif $is_template}' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $onclick3 . '" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"> ' .
                '{else}' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $onclick4 . '" type="submit" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"> ' .
                '{/if}',
                [
                    'smarty' => [
                        [
                            'template' => '{if !empty($smarty.request.return_action) && $smarty.request.return_action == "ProjectTemplatesDetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }[CONTENT0]{elseif !empty($smarty.request.return_action) && $smarty.request.return_action == "DetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }[CONTENT1]{elseif $is_template}[CONTENT2]{else}[CONTENT3]{/if}',
                            '[CONTENT0]' => [
                                'tag' => 'input',
                                'title' => '{$APP.LBL_CANCEL_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_CANCEL_BUTTON_KEY}',
                                'class' => 'button',
                                'type' => 'submit',
                                'name' => 'button',
                                'value' => '{$APP.LBL_CANCEL_BUTTON_LABEL}',
                                'id' => 'Cancel',
                                'onclick' => $onclick,
                                'self_closing' => true,
                            ],
                            '[CONTENT1]' => [
                                'tag' => 'input',
                                'title' => '{$APP.LBL_CANCEL_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_CANCEL_BUTTON_KEY}',
                                'class' => 'button',
                                'type' => 'submit',
                                'name' => 'button',
                                'value' => '{$APP.LBL_CANCEL_BUTTON_LABEL}',
                                'id' => 'Cancel',
                                'onclick' => $onclick2,
                                'self_closing' => true,
                            ],
                            '[CONTENT2]' => [
                                'tag' => 'input',
                                'title' => '{$APP.LBL_CANCEL_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_CANCEL_BUTTON_KEY}',
                                'class' => 'button',
                                'type' => 'submit',
                                'name' => 'button',
                                'value' => '{$APP.LBL_CANCEL_BUTTON_LABEL}',
                                'id' => 'Cancel',
                                'onclick' => $onclick3,
                                'self_closing' => true,
                            ],
                            '[CONTENT3]' => [
                                'tag' => 'input',
                                'title' => '{$APP.LBL_CANCEL_BUTTON_TITLE}',
                                'accessKey' => '{$APP.LBL_CANCEL_BUTTON_KEY}',
                                'class' => 'button',
                                'type' => 'submit',
                                'name' => 'button',
                                'value' => '{$APP.LBL_CANCEL_BUTTON_LABEL}',
                                'id' => 'Cancel',
                                'onclick' => $onclick4,
                                'self_closing' => true,
                            ],
                        ],
                    ],
                ],
                '{if !empty($smarty.request.return_action) && $smarty.request.return_action == "ProjectTemplatesDetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $expected_onclick . '" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"/>' .
                '{elseif !empty($smarty.request.return_action) && $smarty.request.return_action == "DetailView" && (!empty($fields.id.value) || !empty($smarty.request.return_id)) }' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $expected_onclick2 . '" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"/>' .
                '{elseif $is_template}' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $expected_onclick3 . '" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"/>' .
                '{else}' .
                '<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}" class="button" onclick="' . $expected_onclick4 . '" type="button" name="button" value="{$APP.LBL_CANCEL_BUTTON_LABEL}" id="Cancel"/>' .
                '{/if}',
            ],

            //set #15: Parallel smarty strings
            [
                '{$APP.VALUE1} {$HIDDEN_FIELD} <input type="submit" value="{$APP.BUTTON_LABEL}" onclick="' . $onclick . '">',
                [
                    [
                        'smarty' => [
                            [
                                'template' => '{$APP.VALUE1}',
                            ],
                        ],
                    ],
                    [
                        'smarty' => [
                            [
                                'template' => '{$HIDDEN_FIELD}',
                            ],
                        ],
                    ],
                    [
                        'tag' => 'input',
                        'type' => 'submit',
                        'self_closing' => true,
                        'onclick' => $onclick,
                        'value' => '{$APP.BUTTON_LABEL}',
                    ],
                ],
                '{$APP.VALUE1}{$HIDDEN_FIELD}<input type="button" value="{$APP.BUTTON_LABEL}" onclick="' . $expected_onclick . '"/>',
            ],
            //set #16: Contains smarty syntax "nocache"
            [
                '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form" onsubmit="this.sugarpdf.value =(document.getElementById(\'sugarpdf\'))? document.getElementById(\'sugarpdf\').value: \'\';"><input type="hidden" name="module" value="Quotes">'
                . '{nocache}'
                . '{sugar_email_btn}'
                . '{/nocache}'
                . '</form>',
                [
                    'tag' => 'form',
                    'action' => 'index.php',
                    'method' => '{$PDFMETHOD}',
                    'name' => 'ViewPDF',
                    'id' => 'form',
                    'onsubmit' => 'this.sugarpdf.value =(document.getElementById(\'sugarpdf\'))? document.getElementById(\'sugarpdf\').value: \'\';',
                    'container' => [
                        [
                            'tag' => 'input',
                            'type' => 'hidden',
                            'name' => 'module',
                            'value' => 'Quotes',
                            'self_closing' => true,
                        ],
                        [
                            'smarty' => [
                                [
                                    'template' => '{nocache}{sugar_email_btn}{/nocache}',
                                ],
                            ],
                        ],
                    ],
                    'self_closing' => false,
                ],
                '<form action="index.php" method="{$PDFMETHOD}" name="ViewPDF" id="form" onsubmit="this.sugarpdf.value =(document.getElementById(\'sugarpdf\'))? document.getElementById(\'sugarpdf\').value: \'\';"><input type="hidden" name="module" value="Quotes"/>'
                . '{nocache}'
                . '{sugar_email_btn}'
                . '{/nocache}'
                . '</form>',
            ],
        ];
    }

    /**
     * @dataProvider providerCustomCode
     */
    public function testCustomCode($customCode, $expected_parsed_array, $expected_customCode)
    {
        //Test for parseHtmlTag
        $this->assertEquals($expected_parsed_array, SugarHtml::parseHtmlTag($customCode));
        $params = [
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => [
                'customCode' => $customCode,
            ],
            'form_id' => 'DetailView',
        ];


        //Test for smarty_function_sugar_button for customCode
        $this->assertEquals($expected_customCode, smarty_function_sugar_button($params, $this->_smarty));
    }

    public function providerCustomCodeWithHidden()
    {
        $onclick = 'this.form.module.value=\'Contacts\';this.form.action.value=\'DetailView\';';
        $expected_onclick = 'var _form = document.getElementById(\'DetailView\');_form.module.value=\'Contacts\';_form.action.value=\'DetailView\';_form.submit();';

        return [
            //set #0: Button with hidden field
            [
                '<input type="hidden" name="id2" value="2">     <input type="submit"onclick="' . $onclick . '"value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">',
                '<input type="hidden" name="id2" value="2"/><input type="button" onclick="' . $expected_onclick . '" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>',
                [
                    '<input type="hidden" name="id2" value="2"/>',
                ],
            ],
            //set #1: Button with hidden field wrapping with conditional smarty statement
            [
                '{if $fields.status.value != "Held"} <input type="hidden" name="id1" value="true">    <input type="hidden" name="id2">     <input type="submit" {if $APP.CONTAINER = true}onclick="' . $onclick . '"{else}onclick="stop();"{/if} value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}">{/if}',
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input {if $APP.CONTAINER = true}onclick="' . $expected_onclick . '"{else}onclick="stop();"{/if} type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}',
                [
                    '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/>{/if}',
                ],
            ],
            //set #2: wrapping with conditional smarty statement great equal than two phases
            [
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"><input type="hidden" name="id2"><input type="submit" onclick="' . $onclick . '">{else}<input type="hidden" name="id3" value="true"><input type="submit" onclick="' . $onclick . '">{/if}',
                '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/><input type="button" onclick="' . $expected_onclick . '"/>{else}<input type="hidden" name="id3" value="true"/><input type="button" onclick="' . $expected_onclick . '"/>{/if}',
                [
                    '{if $fields.status.value != "Held"}<input type="hidden" name="id1" value="true"/><input type="hidden" name="id2"/>{else}<input type="hidden" name="id3" value="true"/>{/if}',
                ],
            ],

            //set #3: hidden fields wrapped with the additional form element
            [
                '<form name="blah">   <input type="hidden" name="id1">    <input type="hidden" name="id2">     <input type="submit" onclick="' . $onclick . '"></form>',
                '<form name="blah"><input type="hidden" name="id1"/><input type="hidden" name="id2"/><input type="submit" onclick="' . $onclick . '"/></form>',
                null,
            ],
        ];
    }

    /**
     * @dataProvider providerCustomCodeWithHidden
     */
    public function testCustomCodeWithHidden($customCode, $expected_customCode, $expected_hidden_array)
    {
        $params = [
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => [
                'customCode' => $customCode,
            ],
            'form_id' => 'DetailView',
        ];
        $this->assertEquals($expected_customCode, smarty_function_sugar_button($params, $this->_smarty));
        $form = $this->_smarty->get_template_vars('form');

        if ($expected_hidden_array !== null) {
            $this->assertEquals($expected_hidden_array, $form['hidden']);
        } else {
            $this->assertNull($form);
        }
    }

    public function testBuildSugarHtml()
    {
        $sugar_html = [
            'type' => 'submit',
            'value' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
            'htmlOptions' => [
                'title' => '{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}',
                'name' => 'button',
                'class' => 'button',
                'onclick' => 'this.form.isSaveFromDetailView.value=true; this.form.status.value=\'Held\'; this.form.action.value=\'Save\';this.form.return_module.value=\'Meetings\';this.form.isDuplicate.value=true;this.form.isSaveAndNew.value=true;this.form.return_action.value=\'EditView\'; this.form.isDuplicate.value=true;this.form.return_id.value=\'{$fields.id.value}\';',

            ],
            'template' => '{if $fields.status.value != "Held"}[CONTENT]{/if}',
        ];
        $expected_html = '{if $fields.status.value != "Held"}<input title="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}" name="button" class="button" onclick="var _form = document.getElementById(\'DetailView\');_form.isSaveFromDetailView.value=true; _form.status.value=\'Held\'; _form.action.value=\'Save\';_form.return_module.value=\'Meetings\';_form.isDuplicate.value=true;_form.isSaveAndNew.value=true;_form.return_action.value=\'EditView\'; _form.isDuplicate.value=true;_form.return_id.value=\'{$fields.id.value}\';_form.submit();" type="button" value="{$APP.LBL_CLOSE_AND_CREATE_BUTTON_TITLE}"/>{/if}';

        $params = [
            'module' => 'Accounts',
            'view' => 'DetailView',
            'id' => [
                'sugar_html' => $sugar_html,
            ],
            'form_id' => 'DetailView',
        ];
        //Test for smarty_function_sugar_button for sugar_html
        $this->assertEquals($expected_html, smarty_function_sugar_button($params, $this->_smarty));
    }
}
