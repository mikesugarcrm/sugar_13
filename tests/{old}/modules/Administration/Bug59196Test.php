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

class Bug59196Test extends TestCase
{
    private $request;
    private $customFile = 'custom/include/MVC/Controller/wireless_module_registry.php';
    private $backedUp;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', [true, true]); // Admin

        // Backup the custom file if there is one
        if (file_exists($this->customFile)) {
            $this->backedUp = true;
            rename($this->customFile, $this->customFile . '.backup');
        }

        // Backup the request
        if (!empty($_REQUEST)) {
            $this->request = $_REQUEST;
        }
    }

    protected function tearDown(): void
    {
        $_REQUEST = $this->request;

        @unlink($this->customFile);
        if ($this->backedUp) {
            rename($this->customFile . '.backup', $this->customFile);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug59196
     */
    public function testChangingMobileModuleListMaintainsSelectedOrder()
    {
        // Add Documents module to the list
        $_REQUEST['enabled_modules'] = 'Accounts,Documents,Contacts,Leads';
        $admin = new AdministrationController();

        // Capturing the output since that could affect the suite
        ob_start();
        $admin->action_updatewirelessenabledmodules();
        $out = ob_get_clean();

        // Begin assertions
        $this->assertFileExists($this->customFile, 'Custom wireless module registry file was not written');

        include $this->customFile;

        $this->assertTrue(isset($wireless_module_registry), 'Wireless module registry not found in the custom file');
        $this->assertIsArray($wireless_module_registry, 'Wireless module registry is not an array');
        $this->assertEquals(4, count($wireless_module_registry), 'Expected wireless module registry to contain 4 modules');

        // Grab the keys and compare
        $modules = array_keys($wireless_module_registry);
        $this->assertEquals('Documents', $modules[1], "Second module in wireless module list should be 'Documents'");
    }
}
