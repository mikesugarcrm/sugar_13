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

class SugarViewTest extends TestCase
{
    /**
     * @var string|bool|mixed
     */
    public $dir;
    /**
     * @var SugarViewTestMock
     */
    private $view;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('mod_strings', ['Users']);
        $this->view = new SugarViewTestMock();
        $this->dir = getcwd();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        chdir($this->dir);
    }

    public function testGetModuleTab()
    {
        $_REQUEST['module_tab'] = 'ADMIN';
        $moduleTab = $this->view->getModuleTab();
        $this->assertEquals('ADMIN', $moduleTab, 'Module Tab names are not equal from request');
    }

    public function testGetMetaDataFile()
    {
        // backup custom file if it already exists
        if (file_exists('custom/modules/Contacts/metadata/listviewdefs.php')) {
            copy('custom/modules/Contacts/metadata/listviewdefs.php', 'custom/modules/Contacts/metadata/listviewdefs.php.bak');
            unlink('custom/modules/Contacts/metadata/listviewdefs.php');
        }
        $this->view->module = 'Contacts';
        $this->view->type = 'list';
        $metaDataFile = $this->view->getMetaDataFile();
        $this->assertEquals('modules/Contacts/metadata/listviewdefs.php', $metaDataFile, 'Did not load the correct metadata file');

        //test custom file
        if (!file_exists('custom/modules/Contacts/metadata/')) {
            sugar_mkdir('custom/modules/Contacts/metadata/', null, true);
        }
        $customFile = 'custom/modules/Contacts/metadata/listviewdefs.php';
        if (!file_exists($customFile)) {
            sugar_touch($customFile);
            $customMetaDataFile = $this->view->getMetaDataFile();
            $this->assertEquals($customFile, $customMetaDataFile, 'Did not load the correct custom metadata file');
            unlink($customFile);
        }
        // Restore custom file if we backed it up
        if (file_exists('custom/modules/Contacts/metadata/listviewdefs.php.bak')) {
            rename('custom/modules/Contacts/metadata/listviewdefs.php.bak', 'custom/modules/Contacts/metadata/listviewdefs.php');
        }
    }

    public function testInit()
    {
        $bean = new SugarBean();
        $view_object_map = ['foo' => 'bar'];
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->view->init($bean, $view_object_map);

        $this->assertInstanceOf('SugarBean', $this->view->bean);
        $this->assertEquals($view_object_map, $this->view->view_object_map);
        $this->assertEquals($GLOBALS['action'], $this->view->action);
        $this->assertEquals($GLOBALS['module'], $this->view->module);
        $this->assertInstanceOf('Sugar_Smarty', $this->view->ss);
    }

    public function testInitNoParameters()
    {
        $GLOBALS['action'] = 'barbar';
        $GLOBALS['module'] = 'foofoo';

        $this->view->init();

        $this->assertNull($this->view->bean);
        $this->assertEquals([], $this->view->view_object_map);
        $this->assertEquals($GLOBALS['action'], $this->view->action);
        $this->assertEquals($GLOBALS['module'], $this->view->module);
        $this->assertInstanceOf('Sugar_Smarty', $this->view->ss);
    }

    public function testInitSmarty()
    {
        $this->view->initSmarty();

        $this->assertInstanceOf('Sugar_Smarty', $this->view->ss);
        $this->assertEquals($this->view->ss->getTemplateVars('MOD'), $GLOBALS['mod_strings']);
        $this->assertEquals($this->view->ss->getTemplateVars('APP'), $GLOBALS['app_strings']);
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrors()
    {
        $this->view->errors = ['error1', 'error2'];
        $this->view->suppressDisplayErrors = true;

        $this->assertEquals(
            '<span class="error">error1</span><br><span class="error">error2</span><br>',
            $this->view->displayErrors()
        );
    }

    /**
     * @outputBuffering enabled
     */
    public function testDisplayErrorsDoNotSupressOutput()
    {
        $this->view->errors = ['error1', 'error2'];
        $this->view->suppressDisplayErrors = false;

        $this->expectOutputString('<span class="error">error1</span><br><span class="error">error2</span><br>');
        $this->view->displayErrors();
    }

    public function testGetBrowserTitle()
    {
        $viewMock = $this->createPartialMock('SugarViewTestMock', ['_getModuleTitleParams']);
        $viewMock->expects($this->any())
            ->method('_getModuleTitleParams')
            ->will($this->returnValue(['foo', 'bar']));

        $this->assertEquals(
            "bar &raquo; foo &raquo; {$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $viewMock->getBrowserTitle()
        );
    }

    public function testGetBrowserTitleUserLogin()
    {
        $this->view->module = 'Users';
        $this->view->action = 'Login';

        $this->assertEquals(
            "{$GLOBALS['app_strings']['LBL_BROWSER_TITLE']}",
            $this->view->getBrowserTitle()
        );
    }

    public function testGetBreadCrumbSymbolForLTRTheme()
    {
        SugarTestHelper::setUp('theme');
        $theme = SugarTestThemeUtilities::createAnonymousTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='breadCrumbSymbol'>&raquo;</span>",
            $this->view->getBreadCrumbSymbol()
        );
    }

    public function testGetBreadCrumbSymbolForRTLTheme()
    {
        SugarTestHelper::setUp('theme');
        $theme = SugarTestThemeUtilities::createAnonymousRTLTheme();
        SugarThemeRegistry::set($theme);

        $this->assertEquals(
            "<span class='breadCrumbSymbol'>&laquo;</span>",
            $this->view->getBreadCrumbSymbol()
        );
    }

    public function testGetSugarConfigJS()
    {
        global $sugar_config;

        $sugar_config['js_available'] = ['default_action'];

        $js_array = $this->view->getSugarConfigJS();
        $this->assertContains('SUGAR.config.default_action = "index";', $js_array);
    }
}

class SugarViewTestMock extends SugarView
{
    public function getModuleTab()
    {
        return parent::_getModuleTab();
    }

    public function initSmarty()
    {
        return parent::_initSmarty();
    }

    public function getSugarConfigJS()
    {
        return parent::getSugarConfigJS();
    }
}
