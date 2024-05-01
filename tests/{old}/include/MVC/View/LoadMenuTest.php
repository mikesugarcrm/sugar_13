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

class LoadMenuTest extends TestCase
{
    protected $moduleName;

    protected function setUp(): void
    {
        global $mod_strings, $app_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Accounts');
        $app_strings = return_application_language($GLOBALS['current_language']);

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        // create a dummy module directory
        $this->moduleName = 'TestModule' . random_int(0, mt_getrandmax());

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        sugar_mkdir("modules/{$this->moduleName}", null, true);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['app_strings']);

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        if (!empty($this->moduleName)) {
            if (is_dir("modules/{$this->moduleName}")) {
                rmdir_recursive("modules/{$this->moduleName}");
            }
            if (is_dir("custom/modules/{$this->moduleName}")) {
                rmdir_recursive("custom/modules/{$this->moduleName}");
            }
        }
        unset($GLOBALS['current_user']);
    }

    public function testMenuDoesNotExists()
    {
        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $this->assertTrue(empty($module_menu), 'Assert the module menu array is empty');
    }

    /**
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleMenu()
    {
        // Create module menu
        if ($fh = @fopen("modules/{$this->moduleName}/Menu.php", 'w+')) {
            $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=bar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $found_menu = false;
        $found_menu_twice = false;
        foreach ($module_menu as $menu_entry) {
            foreach ($menu_entry as $menu_item) {
                if (preg_match('/action=bar/', $menu_item)) {
                    if ($found_menu) {
                        $found_menu_twice = true;
                    }
                    $found_menu = true;
                }
            }
        }

        $this->assertTrue($found_menu, 'Assert that menu was detected');
        $this->assertFalse($found_menu_twice, "Assert that menu item wasn't listed twice");
    }

    /**
     * @ticket 29114
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleExtMenu()
    {
        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->moduleName}/Ext/Menus/", null, true);
        if ($fh = @fopen("custom/modules/{$this->moduleName}/Ext/Menus/menu.ext.php", 'w+')) {
            $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
            foreach ($menu_entry as $id => $menu_item) {
                if (preg_match('/action=foo/', $menu_item)) {
                    if ($found_custom_menu) {
                        $found_custom_menu_twice = true;
                    }
                    $found_custom_menu = true;
                }
            }
        }
        $this->assertTrue($found_custom_menu, 'Assert that custom menu was detected');
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't listed twice");
    }

    /**
     * @ticket 38935
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleExtMenuWhenModuleMenuDefinedGlobal()
    {
        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->moduleName}/Ext/Menus/", null, true);
        if ($fh = @fopen("custom/modules/{$this->moduleName}/Ext/Menus/menu.ext.php", 'w+')) {
            $string = <<<EOQ
<?php
global \$module_menu;
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
            foreach ($menu_entry as $id => $menu_item) {
                if (preg_match('/action=foo/', $menu_item)) {
                    if ($found_custom_menu) {
                        $found_custom_menu_twice = true;
                    }
                    $found_custom_menu = true;
                }
            }
        }

        $this->assertTrue($found_custom_menu, 'Assert that custom menu was detected');
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't listed twice");
    }

    /**
     * @ticket 43497
     */
    public function testMenuExistsCanFindApplicationExtMenu()
    {
        // Create module ext menu
        $backupCustomMenu = false;
        if (!is_dir('custom/application/Ext/Menus/')) {
            sugar_mkdir('custom/application/Ext/Menus/', null, true);
        }
        if (file_exists('custom/application/Ext/Menus/menu.ext.php')) {
            copy('custom/application/Ext/Menus/menu.ext.php', 'custom/application/Ext/Menus/menu.ext.php.backup');
            $backupCustomMenu = true;
        }

        if ($fh = @fopen('custom/application/Ext/Menus/menu.ext.php', 'w+')) {
            $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foobar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $found_application_custom_menu = false;
        $found_application_custom_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
            foreach ($menu_entry as $id => $menu_item) {
                if (preg_match('/action=foobar/', $menu_item)) {
                    if ($found_application_custom_menu) {
                        $found_application_custom_menu_twice = true;
                    }
                    $found_application_custom_menu = true;
                }
            }
        }

        $this->assertTrue($found_application_custom_menu, 'Assert that application custom menu was detected');
        $this->assertFalse($found_application_custom_menu_twice, "Assert that application custom menu item wasn't duplicated");

        if ($backupCustomMenu) {
            copy('custom/application/Ext/Menus/menu.ext.php.backup', 'custom/application/Ext/Menus/menu.ext.php');
            unlink('custom/application/Ext/Menus/menu.ext.php.backup');
        } else {
            unlink('custom/application/Ext/Menus/menu.ext.php');
        }
    }

    /**
     * @ticket 43497
     */
    public function testMenuExistsCanFindModuleMenuAndModuleExtMenu()
    {
        // Create module menu
        if ($fh = @fopen("modules/{$this->moduleName}/Menu.php", 'w+')) {
            $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=foo&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        // Create module ext menu
        sugar_mkdir("custom/modules/{$this->moduleName}/Ext/Menus/", null, true);
        if ($fh = @fopen("custom/modules/{$this->moduleName}/Ext/Menus/menu.ext.php", 'w+')) {
            $string = <<<EOQ
<?php
\$module_menu[]=Array("index.php?module=Import&action=bar&import_module=Accounts&return_module=Accounts&return_action=index","Foo","Foo", 'Accounts');
?>
EOQ;
            fputs($fh, $string);
            fclose($fh);
        }

        $view = new SugarView();
        $module_menu = $view->getMenu($this->moduleName);
        $found_custom_menu = false;
        $found_custom_menu_twice = false;
        $found_menu = false;
        $found_menu_twice = false;
        foreach ($module_menu as $key => $menu_entry) {
            foreach ($menu_entry as $id => $menu_item) {
                if (preg_match('/action=foo/', $menu_item)) {
                    if ($found_menu) {
                        $found_menu_twice = true;
                    }
                    $found_menu = true;
                }
                if (preg_match('/action=bar/', $menu_item)) {
                    if ($found_custom_menu) {
                        $found_custom_menu_twice = true;
                    }
                    $found_custom_menu = true;
                }
            }
        }
        $this->assertTrue($found_menu, 'Assert that menu was detected');
        $this->assertFalse($found_menu_twice, "Assert that menu item wasn't duplicated");
        $this->assertTrue($found_custom_menu, 'Assert that custom menu was detected');
        $this->assertFalse($found_custom_menu_twice, "Assert that custom menu item wasn't duplicated");
    }
}

class ViewLoadMenuTest extends SugarView
{
    public function menuExists(
        $module
    ) {

        return $this->_menuExists($module);
    }
}
