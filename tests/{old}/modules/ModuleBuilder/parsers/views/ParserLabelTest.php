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
 * @covers ParserLabel
 */
class ParserLabelTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Administration');
        SugarTestHelper::setUp('files');
    }

    protected function tearDown(): void
    {
        global $current_language;

        SugarTestHelper::tearDown();
        LanguageManager::clearLanguageCache(null, $current_language);
    }

    /**
     * @dataProvider updateModuleListsProvider
     */
    public function testUpdateModuleLists($module, $labelName, $label, $listName)
    {
        global $current_language;

        SugarTestHelper::saveFile([
            'custom/Extension/application/Ext/Language/' . $current_language . '.sugar_moduleList.php',
            'custom/Extension/application/Ext/Language/' . $current_language . '.sugar_moduleListSingular.php',
            'custom/application/Ext/Language/' . $current_language . '.lang.ext.php',
            'custom/include/language/' . $current_language . '.lang.php',
            'custom/Extension/modules/' . $module . '/Ext/Language/' . $current_language . '.lang.php',
        ]);

        $strings = return_app_list_strings_language($current_language);
        $orig = $strings[$listName][$module];
        $this->assertNotEquals($label, $orig);

        $parser = new ParserLabel($module);
        $parser->handleSave([
            'label_' . $labelName => $label,
        ], $current_language);

        $strings = return_app_list_strings_language($current_language);
        $this->assertEquals($label, $strings[$listName][$module]);
    }

    public static function updateModuleListsProvider()
    {
        return [
            'plural' => [
                'Accounts',
                'LBL_MODULE_NAME',
                'Companies',
                'moduleList',
            ],
            'singular' => [
                'Accounts',
                'LBL_MODULE_NAME_SINGULAR',
                'Company',
                'moduleListSingular',
            ],
        ];
    }
}
