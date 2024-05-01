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

require_once 'include/utils.php';


/**
 * @ticket 33806
 */
class Bug33806Test extends TestCase
{
    public static function moduleNameProvider()
    {
        return [
            ['singular' => 'Account', 'module' => 'Accounts'],
            ['singular' => 'Contact', 'module' => 'Contacts'],
        ];
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider moduleNameProvider
     */
    public function testGetModuleFromSingular($singular, $expectedName)
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        $module = get_module_from_singular($singular);

        $this->assertEquals($expectedName, $module);
    }

    public static function moduleNameProvider2()
    {
        return [
            ['renamed' => 'Acct', 'module' => 'Accounts'],
        ];
    }

    /**
     * Test the getMime function for the use case where the mime type is already provided.
     *
     * @dataProvider moduleNameProvider2
     */
    public function testGetModuleFromRenamed($renamed, $expectedName)
    {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);

        // manually rename the module name to 'Acct'
        $GLOBALS['app_list_strings']['moduleList']['Accounts'] = 'Acct';

        $module = get_module_from_singular($renamed);

        $this->assertEquals($expectedName, $module);
    }
}
