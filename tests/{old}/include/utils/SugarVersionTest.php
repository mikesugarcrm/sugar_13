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

class SugarVersionTest extends TestCase
{
    /**
     * @dataProvider providerVersionStatus
     */
    public function testVersionStatus(
        $version,
        $expectedResult
    ) {

        $returnedStatus = getVersionStatus($version);
        $this->assertEquals(
            $returnedStatus,
            $expectedResult,
            "{$returnedStatus} status did not match expected status of {$expectedResult}"
        );
    }

    public function providerVersionStatus()
    {
        return [
            ['5.5.0RC1', 'RC'],
            ['5.5.0RC', 'RC'],
            ['5.5.0', 'GA'],
            ['5.5.0Beta', 'BETA'],
            ['5.5.0BEta1', 'BETA'],
            ['5.2', 'GA'],
            ['5.2RC2', 'RC'],
        ];
    }

    /**
     * @dataProvider providerVersionMajorMinor
     */
    public function testVersionMajorMinor(
        $version,
        $expectedResult
    ) {

        $returnedVersion = getMajorMinorVersion($version);
        $this->assertEquals(
            $returnedVersion,
            $expectedResult,
            "{$returnedVersion} MajorMinor version did not match expected version of {$expectedResult}"
        );
    }

    public function providerVersionMajorMinor()
    {
        return [
            ['5.5.0RC1', '5.5'],
            ['5.5.1RC', '5.5.1'],
            ['5.0', '5.0'],
            ['5.0Beta', '5.0'],
            ['5.5.1RC', '5.5.1'],
        ];
    }
}
