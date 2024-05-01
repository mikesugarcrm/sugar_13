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
 * Bug 58463 - Drop Down Lists do not show in studio after save
 */
class Bug58463Test extends TestCase
{
    private $testCustomFile = 'custom/application/Ext/Language/en_us.lang.ext.php';
    private $currentRequest;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');

        // Back up the current file if there is one
        if (file_exists($this->testCustomFile)) {
            rename($this->testCustomFile, $this->testCustomFile . '.testbackup');
        }

        // Create an empty test custom file
        mkdir_recursive(dirname($this->testCustomFile));
        sugar_file_put_contents($this->testCustomFile, '<?php' . "\n");

        // Back up the current request vars
        $this->currentRequest = $_REQUEST;
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();

        // Clean up our file
        unlink($this->testCustomFile);

        if (file_exists($this->testCustomFile . '.testbackup')) {
            rename($this->testCustomFile . '.testbackup', $this->testCustomFile);
        }

        // Reset the request
        $_REQUEST = $this->currentRequest;

        // Clear the cache
        sugar_cache_clear('app_list_strings.en_us');
    }

    /**
     * @group Bug58463
     */
    public function testCustomDropDownListSavesProperly()
    {
        $values = [
            ['bobby', 'Bobby'],
            ['billy', 'Billy'],
            ['benny', 'Benny'],
        ];

        $_REQUEST = [
            'list_value' => json_encode($values),
            'sales_stage_classification' => '',
            'dropdown_lang' => 'en_us',
            'dropdown_name' => 'test_dropdown',
            'view_package' => 'studio',
        ];
        $parser = new ParserDropDown();
        $parser->saveDropDown($_REQUEST);

        $als = $this->getCustomDropDownEntry();
        $this->assertArrayHasKey('test_dropdown', $als, 'The dropdown did not save');
        foreach ($values as $item) {
            $this->assertArrayHasKey($item[0], $als['test_dropdown'], "The dropdown list item {$item[0]} did not save");
        }
    }

    private function getCustomDropDownEntry()
    {
        if (file_exists($this->testCustomFile)) {
            require $this->testCustomFile;
            if (isset($app_list_strings)) {
                return $app_list_strings;
            }
        }

        // This would indicate a failure
        return [];
    }
}
