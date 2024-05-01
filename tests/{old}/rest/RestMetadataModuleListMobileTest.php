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

class RestMetadataModuleListMobileTest extends RestTestBase
{
    public $unitTestFiles = [];

    // Need to set the platform to something else
    protected function restLogin($username = '', $password = '', $platform = 'mobile')
    {
        return parent::restLogin($username, $password, $platform);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->unitTestFiles[] = 'custom/include/MVC/Controller/wireless_module_registry.php';
    }

    protected function tearDown(): void
    {
        foreach ($this->unitTestFiles as $unitTestFile) {
            if (file_exists($unitTestFile)) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                unlink($unitTestFile);
            }
        }
        parent::tearDown();
    }

    /**
     * @group rest
     */
    public function testMetadataGetModuleListMobile()
    {
        $wireless_module_registry = null;
        $this->clearMetadataCache();
        $restReply = $this->restCall('me');

        foreach (SugarAutoLoader::existingCustom('include/MVC/Controller/wireless_module_registry.php') as $file) {
            require $file;
        }


        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);

        $users_key = array_search('Users', $enabledMobile);
        if (!empty($users_key)) {
            unset($enabledMobile[$users_key]);
        }

        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']), 'There is no mobile module list');
        $restModules = $restReply['reply']['current_user']['module_list'];
        unset($restModules['_hash']);
        foreach ($enabledMobile as $module) {
            $this->assertTrue(in_array($module, $restModules), 'Module ' . $module . ' missing from the mobile module list.');
        }
        $this->assertSameSize($enabledMobile, $restModules);

        // Create a custom set of wireless modules to test if it is loading those properly
        SugarAutoLoader::ensureDir('custom/include/MVC/Controller');
        file_put_contents(
            'custom/include/MVC/Controller/wireless_module_registry.php',
            '<' . "?php\n" . '$wireless_module_registry = array("Accounts"=>"Accounts",'
            . '"Contacts"=>"Contacts","Opportunities"=>"Opportunities");'
        );

        $enabledMobile = ['Accounts', 'Contacts', 'Opportunities',];

        $this->clearMetadataCache();
        $restReply = $this->restCall('me');
        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']), 'There is no mobile module list on the second pass');
        $restModules = $restReply['reply']['current_user']['module_list'];
        foreach ($enabledMobile as $module) {
            $this->assertTrue(in_array($module, $restModules), 'Module ' . $module . ' missing from the mobile module list on the second pass');
        }
        $this->assertSameSize($enabledMobile, $restModules);
    }

    public function testMetadataMobileUsers()
    {
        $this->clearMetadataCache();
        $restReply = $this->restCall('metadata');
        $this->assertTrue(!empty($restReply['reply']['modules']['Users']), 'Users does not exist in the metadata list.');
    }
}
