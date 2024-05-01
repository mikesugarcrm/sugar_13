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
 * @group bug45683
 */
class Bug45683Test extends TestCase
{
    public $mbmod;
    public $module_name = 'ThisModule';

    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION['authenticated_user_language'] = 'en_us';
        $this->mbmod = new MBModule($this->module_name, 'custom/modulebuilder/packages/testPkg', 'testPkg', 'testPkg');
        $_REQUEST['view_package'] = 'testPkg';
        $_REQUEST['view_module'] = $this->module_name;
        $_REQUEST['dropdown_name'] = 'testDD';
        $_REQUEST['dropdown_lang'] = 'en_us';
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['dropdown_name']);
        unset($_REQUEST['dropdown_lang']);
        unset($_REQUEST['view_module']);
        unset($_REQUEST['view_package']);
        $this->mbmod->delete();
        unset($_SESSION['authenticated_user_language']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testModuleNameDoesNotGetOverwrittenOnDisplay()
    {
        $view = new ViewDropdown();
        $smarty = $view->generateSmarty($_REQUEST);

        $this->assertSame($this->module_name, $smarty->getTemplateVars('module_name'));
    }
}
