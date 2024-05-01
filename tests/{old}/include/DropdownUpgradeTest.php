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

class DropdownUpgradeTest extends TestCase
{
    private $language = 'en_us'; // Test against English
    private $testCustFile = ['include' => 'tests/{old}/include/DropdownUpgradeTestCustFile.php', 'ext' => 'tests/{old}/include/Bug60008-60607TestCustomFile.php'];
    private $custFile = ['include' => 'custom/include/language/en_us.lang.php', 'ext' => 'custom/application/Ext/Language/en_us.lang.ext.php'];
    private $custDir = ['include' => 'custom/include/language/', 'ext' => 'custom/application/Ext/Language/'];

    protected function setUp(): void
    {
        // Back up existing custom app list strings if they exist
        foreach ($this->custFile as $custFile) {
            if (file_exists($custFile)) {
                rename($custFile, $custFile . '-backup');
            }
        }

        // For cases in which this test runs before the custom include directory
        // is created
        foreach ($this->custDir as $custDir) {
            if (!is_dir($custDir)) {
                mkdir_recursive($custDir);
            }
        }

        foreach ($this->testCustFile as $type => $testCustFile) {
            // Copy our test files into place
            copy($this->testCustFile[$type], $this->custFile[$type]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->custFile as $custFile) {
            // Delete the custom file we just created
            unlink($custFile);

            if (file_exists($custFile . '-backup')) {
                // Move the backup back into place. No need to mess with the file map cache
                rename($custFile . '-backup', $custFile);
            }
        }
    }

    /**
     * Tests that both $app_list_strings and $GLOBALS['app_list_strings'] are picked
     * up when getting app_list_strings
     *
     * @group Bug60008
     */
    public function testAppListStringsParsedEvenWhenInGlobals()
    {
        $als = return_app_list_strings_language($this->language);

        // Assert that the indexes are found
        $this->assertArrayHasKey('aaa_test_list', $als, 'First GLOBALS index not found');
        $this->assertArrayHasKey('bbb_test_list', $als, 'First app_list_strings index not found');
        $this->assertArrayHasKey('ccc_test_list', $als, 'Second GLOBALS index not found');

        // Assert that the indexes actually have elements
        $this->assertArrayHasKey('boop', $als['bbb_test_list'], 'An element of the first app_list_strings array was not found');
        $this->assertArrayHasKey('sam', $als['ccc_test_list'], 'An element of the second GLOBALS array not found');

        // Assert that GLOBALS overriding $app_list_strings work
        $this->assertArrayHasKey('zzz_test_list', $als, 'Bug 60393 - dropdown not picked up');
        $this->assertArrayHasKey('X2', $als['zzz_test_list'], 'Bug 60393 - dropdown values not picked up');
        $this->assertEquals($als['zzz_test_list']['X2'], 'X2 Z', 'Bug 60393 - proper dropdown value not picked up');

        // Assert that app_list_strings overriding GLOBALS work
        $this->assertArrayHasKey('yyy_test_list', $als, 'Bug 60393 - second dropdown not picked up');
        $this->assertArrayHasKey('Y2', $als['yyy_test_list'], 'Bug 60393 - second dropdown values not picked up');
        $this->assertEquals($als['yyy_test_list']['Y2'], 'Y2 Q', 'Bug 60393 - proper dropdown value not picked up for second dropdown');
    }
}
