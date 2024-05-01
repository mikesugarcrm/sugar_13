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

class Bug56675Test extends TestCase
{
    public $mbController;
    public $mbPackage;
    public $mbModule;
    public $dirname = 'custom/modulebuilder/packages/test/modules/test/clients/';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->is_admin = true;
        SugarTestHelper::setUp('app_list_strings');
        // Cannot use the SugarTestHelper because it requires a module name
        $GLOBALS['mod_strings'] = [];

        $_REQUEST['name'] = 'test';
        $_REQUEST['view'] = 'advanced_search';
        $_REQUEST['view_package'] = 'test';
        $_REQUEST['view_module'] = 'test';

        $this->mbController = new ModuleBuilderController();
        $_REQUEST['description'] = '';
        $_REQUEST['author'] = '';
        $_REQUEST['readme'] = '';
        $_REQUEST['label'] = 'test';
        $_REQUEST['key'] = 'test';
        $this->mbController->action_SavePackage();

        $_REQUEST['type'] = 'basic';
        $this->mbController->action_SaveModule();
        unset($_REQUEST['key']);
        unset($_REQUEST['label']);
        unset($_REQUEST['readme']);
        unset($_REQUEST['author']);
        unset($_REQUEST['description']);
    }

    protected function tearDown(): void
    {
        $_REQUEST['package'] = 'test';
        $_REQUEST['module'] = 'test';
        $_REQUEST['view_module'] = 'test';
        $_REQUEST['view_package'] = 'test';
        $this->mbController->action_DeleteModule();
        unset($_REQUEST['view_module']);
        unset($_REQUEST['module']);
        $this->mbController->action_DeletePackage();
        unset($_REQUEST['view_package']);
        unset($_REQUEST['package']);
        unset($this->mbController);

        unset($_REQUEST['view_module']);
        unset($_REQUEST['view_package']);
        unset($_REQUEST['view']);
        unset($_REQUEST['name']);

        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug56675
     *
     * Tests that a clients directory and metadata files for clients exist after
     * creating a custom module
     */
    public function testClientsDirectoryCreatedWhenCustomModuleSaved()
    {
        // Make sure the clients directory is there
        $this->assertFileExists($this->dirname, "$this->dirname was not created when the custom module was saved.");

        // Make sure the child directories and files are there for mobile
        $types = ['list', 'edit', 'detail'];
        foreach ($types as $type) {
            $dir = $this->dirname . 'mobile/views/' . $type;
            $this->assertFileExists($dir, "$dir directory was not created when the module was saved");

            $file = $dir . '/' . $type . '.php';
            $this->assertFileExists($file, "$file was not created when module was saved");
        }

        // Modified this test for Bug 57259 to test NOT exists for portal viewdefs
        $dir = $this->dirname . 'portal';
        $this->assertFileDoesNotExist($dir, "$dir directory was created when the module was saved but should not have been");
    }

    /**
     * @group Bug56675
     */
    public function testUndeployedMobileListViewsHavePanelDefs()
    {
        $parser = ParserFactory::getParser(MB_WIRELESSLISTVIEW, 'test', 'test', null, MB_WIRELESS);
        $paneldefs = $parser->getPanelDefs();
        $this->assertNotEmpty($paneldefs, 'Undeployed Module list view defs have no panel defs');
        $this->assertTrue(is_array($paneldefs), 'Undeployed Module List view panel defs are not of type ARRAY');
        $this->assertTrue(isset($paneldefs[0]['label']), 'Undeployed Module List view panel defs do not have a label');
    }
}
