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

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;
use PHPUnit\Framework\TestCase;

/**
 * Bug 57260 - Panel label of mobile layout in module builder is wrong
 */
class Bug57260Test extends TestCase
{
    public $mbController;
    public $mbPackage;
    public $mbModule;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->is_admin = true;
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', ['ModuleBuilder']);

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
     * @group Bug57260
     *
     * Tests that the default panel label of LBL_PANEL_DEFAULT correctly translates
     * to 'Default' when rendered for undeployed modules in studio
     */
    public function testUndeployedModuleHasDefaultLabelInStudioLayoutEditor()
    {
        // Mock the request
        $req = [
            'module' => 'ModuleBuilder',
            'MB' => '1',
            'action' => 'editLayout',
            'view' => 'wirelessdetailview',
            'view_module' => 'test',
            'view_package' => 'test',
        ];

        // Get the view we need

        $view = new ViewLayoutView(null, null, InputValidation::create($req, $req));

        // Get the output
        ob_start();
        $view->display();
        $output = ob_get_clean();
        $output = json_decode($output);

        // Test that our output is what we wanted
        $this->assertNotEmpty($output->center->content, 'Expected output from parsing layout editor not returned');

        // Test the actual output
        $this->assertMatchesRegularExpression("|<span class='panel_name'?.*>\s*Default\s*</span>|", $output->center->content, "'Default' was not found in the rendered view");
    }
}
