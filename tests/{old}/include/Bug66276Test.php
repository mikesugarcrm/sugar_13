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
 * @ticket 66276
 */
class Bug66276Test extends TestCase
{
    protected $testFiles = [
        ['dir' => 'custom/include', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsCustom {}'],
        ['dir' => 'custom/modules/Test', 'name' => 'QuickSearchDefaults.php', 'content' => '<?php class QuickSearchDefaultsModule {}'],
    ];

    protected function setUp(): void
    {
        foreach ($this->testFiles as $testFile) {
            if (!file_exists($testFile['dir'])) {
                sugar_mkdir($testFile['dir'], 0777, true);
            }

            file_put_contents($testFile['dir'] . '/' . $testFile['name'], $testFile['content']);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->testFiles as $testFile) {
            if (file_exists($testFile['dir'] . '/' . $testFile['name'])) {
                unlink($testFile['dir'] . '/' . $testFile['name']);
            }
        }
    }

    /**
     * Tests function QuickSearchDefaults::getQuickSearchDefaults()
     */
    public function testGetQuickSearchDefaults()
    {
        $this->assertInstanceOf('QuickSearchDefaultsModule', QuickSearchDefaults::getQuickSearchDefaults(['custom/modules/Test/QuickSearchDefaults.php' => 'QuickSearchDefaultsModule']));
        $this->assertInstanceOf('QuickSearchDefaultsCustom', QuickSearchDefaults::getQuickSearchDefaults());
        unlink('custom/include/QuickSearchDefaults.php');
        $this->assertInstanceOf('QuickSearchDefaults', QuickSearchDefaults::getQuickSearchDefaults());
    }
}
