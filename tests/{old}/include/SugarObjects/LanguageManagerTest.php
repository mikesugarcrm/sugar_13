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

require_once 'include/SugarObjects/LanguageManager.php';

class LanguageManagerTest extends TestCase
{
    protected $testModule = 'do_not_change';
    protected $testLanguage = 'ever';

    /**
     * Tests that the language file load order is correct always.
     *
     * IF THIS TEST FAILS THEN A CHANGE WAS MADE THAT SHOULD NOT HAVE BEEN MADE.
     *
     * @param int $index The numeric index of this path in the list
     * @param string $path The path to match to this index
     * @dataProvider languageFilePathProvider
     */
    public function testGetModuleLanguageFilePaths($index, $path)
    {
        $list = LanguageManager::getModuleLanguageFilePaths($this->testModule, $this->testLanguage);
        $this->assertArrayHasKey($index, $list);
        $this->assertEquals($path, $list[$index], 'PLEASE DO NOT CHANGE THE ORDER OR VALUES OF THE LANGUAGE FILE LOAD LIST');
    }

    public function languageFilePathProvider()
    {
        return [
            ['index' => 0, 'path' => 'modules/do_not_change/language/ever.lang.php'],
            ['index' => 1, 'path' => 'modules/do_not_change/language/ever.lang.override.php'],
            ['index' => 2, 'path' => 'custom/modules/do_not_change/language/ever.lang.php'],
            ['index' => 3, 'path' => 'custom/modules/do_not_change/Ext/Language/ever.lang.ext.php'],
        ];
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('files');
    }

    /**
     * @ticket BR-1467
     * Test app list strings, e.g `account_type_dom`, are loaded if default is
     * non-english and we load English.
     */
    public function testLanguageOrder()
    {
        $GLOBALS['sugar_config']['default_language'] = 'tlh_QON';
        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::saveFile('include/language/tlh_QON.lang.php');
        file_put_contents(
            'include/language/tlh_QON.lang.php',
            '<?php $app_list_strings = array ("language_pack_name" => "tlhIngan Hol");'
        );
        $strings = return_app_list_strings_language('en_us');
        $this->assertArrayHasKey('account_type_dom', $strings);
        $this->assertNotEmpty($strings['account_type_dom'], 'account_type_dom is empty');
    }
}
