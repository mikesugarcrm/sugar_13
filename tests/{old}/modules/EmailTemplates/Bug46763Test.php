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
 * @ticket 46763
 */
class Bug46763Test extends TestCase
{
    /**
     * Language used to perform the test
     *
     * @var string
     */
    protected $language;

    /**
     * Names of singular instances that will be used during testing
     *
     * @var array
     */
    protected $modules = [
        'Accounts' => 'Test1Account',
        'Contacts' => 'Test2Contact',
        'Leads' => 'Test3Lead',
        'Prospects' => 'Test4Target',
    ];

    /**
     * Temporary file path
     *
     * @var string
     */
    protected $file = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * Generates custom module localization file
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        global $mod_strings;
        $mod_strings = return_module_language($GLOBALS['current_language'], 'EmailTemplates');

        global $sugar_config;
        $this->language = $sugar_config['default_language'];

        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, 1);

        // generate module localization data
        $data = ['<?php'];
        $template = '$app_list_strings["moduleListSingular"]["%s"] = "%s";';
        foreach ($this->modules as $moduleName => $singular) {
            $data[] = sprintf($template, $moduleName, $singular);
        }

        // create custom localization file
        $this->file = 'custom/include/language/' . $this->language . '.lang.php';
        if (file_exists($this->file)) {
            rename($this->file, $this->file . '.bak');
        }
        $dirName = dirname($this->file);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        file_put_contents($this->file, implode(PHP_EOL, $data));
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * Removes custom module localization file
     */
    protected function tearDown(): void
    {
        if (file_exists($this->file . '.bak')) {
            rename($this->file . '.bak', $this->file);
        } else {
            unlink($this->file);
        }

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['mod_strings']);
    }

    /**
     * Tests that custom module localization data is used when combining
     * drop down list options
     *
     * @outputBuffering enabled
     */
    public function testCustomModuleLocalizationIsUsed()
    {
        // set global variables in order to create the needed environment
        $_REQUEST['module'] = '';
        $_REQUEST['return_module'] = '';
        $_REQUEST['return_id'] = '';
        $_REQUEST['request_string'] = '';
        $GLOBALS['request_string'] = '';

        // initialize needed local variables
        global $mod_strings, $app_strings, $sugar_config;
        $app_list_strings = return_app_list_strings_language($this->language, false);
        $xtpl = null;

        require_once 'include/utils/layout_utils.php';
        ob_start();
        require 'modules/EmailTemplates/EditView.php';
        ob_get_clean();

        // clean up created global variables
        unset(
            $_REQUEST['module'],
            $_REQUEST['return_module'],
            $_REQUEST['return_id'],
            $_REQUEST['request_string'],
            $GLOBALS['request_string']
        );

        $this->assertInstanceOf('XTemplate', $xtpl);

        /** @var XTemplate $xtpl */
        $vars = $xtpl->VARS;

        // ensure that drop down list is assigned to the template
        $this->assertArrayHasKey('DROPDOWN', $vars);

        // ensure that all localized values are contained within drop down list
        foreach ($this->modules as $singular) {
            $this->assertStringContainsString($singular, $vars['DROPDOWN']);
        }
    }
}
