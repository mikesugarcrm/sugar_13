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
 * Tests that translations happen properly and does not modify global mod_strings
 */
class Bug60664Test extends TestCase
{
    /**
     * Holder for the current mod_strings if there are any
     *
     * @var array
     */
    private static $modStrings;

    public static function setUpBeforeClass(): void
    {
        // We are working directly on mod_strings, so we won't set it up but rather
        // back it up if necessary
        if (isset($GLOBALS['mod_strings'])) {
            self::$modStrings = $GLOBALS['mod_strings'];
        }

        // Create our own test mod strings for this test
        $GLOBALS['mod_strings'] = [
            'LBL_TEST1' => 'Test Label',
            'LBL_TEST2' => 'Second Label',
        ];
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$modStrings) {
            $GLOBALS['mod_strings'] = self::$modStrings;
        }
    }

    /**
     * Tests that a translation occurred properly
     *
     * @dataProvider labelProvider
     * @group Bug60664
     * @param string $label The label to translate
     * @param string $expects The expected translation
     * @param string $module The module to use for mod_strings fetching
     */
    public function testTranslateDoesNotUseVName($label, $expects, $module)
    {
        $actual = translate($label, $module);
        $this->assertEquals($expects, $actual, "Translated value of $label in $module module was not $expects: $actual");
    }

    /**
     * Tests that the GLOBALS['mod_strings'] did not get manipulated
     *
     * @group Bug60664
     */
    public function testGlobalModStringsWasNotMutated()
    {
        $this->assertEquals(2, safeCount($GLOBALS['mod_strings']), 'Global mod_strings was manipulated');
    }

    public function labelProvider()
    {
        return [
            ['label' => 'LBL_TEST1', 'expects' => 'Test Label', 'module' => '',],
            ['label' => 'LBL_TEST2', 'expects' => 'Second Label', 'module' => '',],
            ['label' => 'LBL_ACCOUNT_INFORMATION', 'expects' => 'Overview', 'module' => 'Accounts',],
            ['label' => 'LBL_CONVERTLEAD_BUTTON_KEY', 'expects' => 'V', 'module' => 'Leads',],
            ['label' => 'LBL_HIDEOPTIONS', 'expects' => 'Hide Options', 'module' => 'ModuleBuilder',],
        ];
    }
}
