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

class SugarPortalBrowserTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user', [true, true]);
    }

    protected function tearDown(): void
    {
        $file = 'custom/modules/Accounts/clients/portal/views/list/list.php';
        if (file_exists($file)) {
            unlink($file);
        }

        $dirs = [
            'custom/modules/Accounts/clients/portal/views/list',
            'custom/modules/Accounts/clients/portal/views',
            'custom/modules/Accounts/clients/portal',
        ];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }

        SugarTestHelper::tearDown();
    }

    /**
     * @covers       \SugarPortalBrowser::isPortalModule
     * @dataProvider dataProviderTestHasModulePortalViews
     */
    public function testHasModulePortalViews(string $module, bool $value)
    {
        $spb = new \SugarPortalBrowser();
        $response = $spb->isPortalModule($module);
        $this->assertEquals($response, $value);
    }

    public function dataProviderTestHasModulePortalViews()
    {
        $values = [
            'Contacts' => true,
            'Cases' => true,
            'Bugs' => true,
            'KBContents' => true,
            // Notes might become enabled as soon as we switch layout to the new listviews
            'Notes' => false,
            'Categories' => false,
            'Home' => false,
            'Users' => false,
            'Filters' => false,
            'Accounts' => false,
        ];

        $data = [];
        foreach ($values as $module => $value) {
            $data[] = [
                $module,
                $value,
            ];
        }

        // add all the remaining beans as false
        foreach ($GLOBALS['beanList'] as $bean => $moduleName) {
            if (!isset($values[$bean])) {
                $data[] = [
                    $module,
                    false,
                ];
            }
        }

        return $data;
    }

    /**
     * @covers \SugarPortalBrowser::isPortalModule
     */
    public function testHasAccountsCustomPortalViews()
    {
        mkdir_recursive('custom/modules/Accounts/clients/portal/views/list');
        touch('custom/modules/Accounts/clients/portal/views/list/list.php');

        $spb = new \SugarPortalBrowser();
        $response = $spb->isPortalModule('Accounts');
        $this->assertEquals($response, true);
    }
}
