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
 * @group Bug46941
 */
class Bug46941Test extends TestCase
{
    /** @var ModuleBuilderController */
    public $mbController;
    public $mbPackage;
    public $mbModule;
    public $hasCustomSearchFields;
    public $filename = 'custom/modulebuilder/packages/test/modules/test/metadata/SearchFields.php';

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);

        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
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
        $this->mbController->action_SaveModule();
        unset($_REQUEST['key']);
        unset($_REQUEST['label']);
        unset($_REQUEST['readme']);
        unset($_REQUEST['author']);
        unset($_REQUEST['description']);
    }

    protected function tearDown(): void
    {
        if (!$this->mbController) {
            return;
        }

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

        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['current_user']);
    }

    /**
     * Tests that adding a search field to a custom module does not generate errors due to the module name not being
     * in beanList
     */
    public function testActionAdvancedSearchSaveForCustomModule()
    {
        $this->mbController->action_searchViewSave();
        $this->assertFileExists($this->filename);
    }
}
