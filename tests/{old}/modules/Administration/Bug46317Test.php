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

require_once 'modules/Administration/updater_utils.php';

/**
 * Bug #46317
 * Automatically Check For Updates issue
 * @ticket 46317
 */
class Bug46317Test extends TestCase
{
    public static function versionProvider()
    {
        return [
            ['6.3.1', '6_3_0', true],
            ['6.4', '6.3.1', true],
            ['6_4_0', '6.3.10', true],
            ['6_3_1', '6.3.1', false],
            ['6.3.0', '6_4', false],
            ['6.4.0RC3', '6.3.1', true],
            ['6.4.0RC3', '6.3.1.RC4', true],
            ['goober', 'noober', false],
            ['6.3.5b', 'noob', true],
            ['noob', '6.3.5b', false],
            ['6.5.0beta2', '6.5.0beta1', true],
            ['6.5.5.5.5', '7.5.5.5.5', false],
            ['6.3', '6.2.3.4.5.2.5.2.4superalpha', true],
            ['000000000000.1', '000000000000.1', false],
            ['000000000000.1', '000000000000.05', false],
            ['000000000000.05', '000000000000.1', true],
        ];
    }

    /**
     * @dataProvider versionProvider
     * @group 46317
     */
    public function testCompareVersions($last_version, $current_version, $expectedResult)
    {
        $this->assertEquals($expectedResult, compareVersions($last_version, $current_version), "Current version: $current_version, last available version: $last_version");
    }
}
