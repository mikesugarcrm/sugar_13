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

/**
 * A simple test to verify that we still have a uid form element even when the ListViewSmarty multiSelect class variable is set to false
 * Other verifications will be needed, but this was a critical variable that was missing
 */
class Bug45566Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }


    public function testListViewDisplayMultiSelect()
    {
        $lv = new ListViewSmarty();
        $lv->multiSelect = false;
        $lv->should_process = true;
        $account = new Account();
        $lv->seed = $account;
        $lv->displayColumns = [];
        $mockData = [];
        $mockData['data'] = [];
        $mockData['pageData'] = ['ordering' => 'ASC', 'offsets' => ['current' => 0, 'next' => 0, 'total' => 0], 'bean' => ['moduleDir' => $account->module_dir]];
        $lv->process('include/ListView/ListViewGeneric.tpl', $mockData, $account->module_dir);
        $this->assertEquals('<textarea style="display: none" name="uid"></textarea>', $lv->ss->getTemplateVars('multiSelectData'), 'Assert that multiSelectData Smarty variable was still assigned');
    }
}
