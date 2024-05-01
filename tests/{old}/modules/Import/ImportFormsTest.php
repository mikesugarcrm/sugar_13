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

require_once 'modules/Import/Forms.php';

class ImportFormsTest extends TestCase
{
    protected function setUp(): void
    {
        $beanFiles = null;
        $beanList = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $mod_strings = [];
        require 'modules/Import/language/en_us.lang.php';
        $GLOBALS['mod_strings'] = $mod_strings;
        $_SESSION['developerMode'] = true;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($_SESSION['developerMode']);
        unset($GLOBALS['beanFiles']);
        unset($GLOBALS['beanList']);
        unset($GLOBALS['mod_strings']);

        restore_error_handler();
    }

    public function testLoadImportBean()
    {
        $oldisadmin = $GLOBALS['current_user']->is_admin;
        $GLOBALS['current_user']->is_admin = '1';

        $controller = new ImportController();
        $_REQUEST['import_module'] = 'Accounts';
        $controller->loadBean();

        $this->assertEquals($controller->bean->object_name, 'Account');

        $GLOBALS['current_user']->is_admin = $oldisadmin;
    }

    public function testLoadImportBeanNotImportable()
    {
        $controller = new ImportController();
        $_REQUEST['import_module'] = 'vCals';
        $controller->loadBean();

        $this->assertFalse($controller->bean);
    }

    public function testLoadImportBeanUserNotAdmin()
    {
        $controller = new ImportController();
        $_REQUEST['import_module'] = 'Users';
        $controller->loadBean();

        $this->assertFalse($controller->bean);
    }

    public function testGetControlIdField()
    {
        $html = getControl('Contacts', 'assigned_user_id');

        $this->assertMatchesRegularExpression('/name=\'assigned_user_id\'/', $html);
        $this->assertMatchesRegularExpression('/id=\'assigned_user_id\'/', $html);
        $this->assertMatchesRegularExpression('/type=\'text\'/', $html);
    }

    public function testGetControlEmail()
    {
        $html = getControl('Contacts', 'email1');

        $this->assertMatchesRegularExpression('/name=\'email1\'/', $html);
        $this->assertMatchesRegularExpression('/id=\'email1\'/', $html);
        $this->assertMatchesRegularExpression('/type=\'text\'/', $html);
    }

    public function testGetControlCurrencyList()
    {
        global $app_strings;

        $html = getControl('Opportunities', 'currency_id');

        $focus = BeanFactory::newBean('Opportunities');


        $string = str_ireplace('</select>', '<option value="">' . $app_strings['LBL_NONE'] . '</option></select>', getCurrencyDropDown($focus, 'currency_id', '', 'EditView'));
        $this->assertStringContainsString($string, $html, "Failed to find string '$string' in '$html'");

        $string = '<script>function CurrencyConvertAll() { return; }</script>';
        $this->assertStringContainsString($string, $html, "Failed to find string '$string' in '$html'");
    }

    public function testGetControlVardef()
    {
        VardefManager::loadVardef(
            'Contacts',
            'Contact'
        );
        $vardef = $GLOBALS['dictionary']['Contact']['fields']['assigned_user_id'];

        $html = getControl('Contacts', 'assigned_user_id', $vardef);

        $this->assertMatchesRegularExpression('/name=\'assigned_user_id\'/', $html);
        $this->assertMatchesRegularExpression('/id=\'assigned_user_id\'/', $html);
        $this->assertMatchesRegularExpression('/type=\'text\'/', $html);
    }

    public function testGetControlValue()
    {
        $html = getControl('Contacts', 'email1', null, 'poo');

        $this->assertMatchesRegularExpression('/name=\'email1\'/', $html);
        $this->assertMatchesRegularExpression('/id=\'email1\'/', $html);
        $this->assertMatchesRegularExpression('/type=\'text\'/', $html);
        $this->assertMatchesRegularExpression('/value=\'poo\'/', $html);
    }

    /**
     * @group bug41447
     */
    public function testGetControlDatetimecombo()
    {
        $html = getControl('Calls', 'date_start');

        global $timedate;
        $string = '", "' . $timedate->get_user_time_format() . '", "';

        $this->assertStringContainsString($string, $html);
    }
}
