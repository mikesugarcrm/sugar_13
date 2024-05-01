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


require_once 'modules/Cases/upgrade/scripts/post/7_InstallAgentWorkbench.php';

/**
 * @coversDefaultClass SugarUpgradeInstallAgentWorkbench
 */
class SugarUpgradeInstallAgentWorkbenchTest extends UpgradeTestCase
{
    public $upgradeDriver;

    /**
     * @covers ::shouldInstallWorkbench
     * @dataProvider providerShouldInstallWorkbench
     * @param array $flavors Configuration for toFlavor and fromFlavor.
     * @param array $versions Version numbers (from and to).
     * @param bool $expected Expected result.
     */
    public function testShouldInstallReports(array $flavors, array $versions, bool $expected)
    {
        $upgradeDriver = $this->getMockForAbstractClass(\UpgradeDriver::class);
        $upgradeDriver->from_version = $versions['from'];
        $mockScript = new MockSugarUpgradeInstallAgentWorkbench($upgradeDriver);
        $mockScript->flavors = $flavors;
        $this->assertEquals($expected, $mockScript->shouldInstallWorkbench());
        unset($this->upgradeDriver);
    }

    public function providerShouldInstallWorkbench(): array
    {
        return [
            // 9.0.0 Ent -> 9.1.0 Ent
            [
                ['from' => ['pro' => true, 'ent' => true], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.0.0', 'to' => '9.1.0'],
                true,
            ],
            // 9.1.0 Ent -> 9.1.1 Ent
            [
                ['from' => ['pro' => true, 'ent' => true], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.1.0', 'to' => '9.1.1'],
                false,
            ],
            // 9.1.0 Pro -> 9.1.0 Ent
            [
                ['from' => ['pro' => true, 'ent' => false], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.1.0', 'to' => '9.1.0'],
                true,
            ],
            // 9.0.0 Ent -> 10.0.0 Ent (roll-up)
            [
                ['from' => ['pro' => true, 'ent' => true], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.0.0', 'to' => '10.0.0'],
                true,
            ],
        ];
    }
}

class MockSugarUpgradeInstallAgentWorkbench extends SugarUpgradeInstallAgentWorkbench
{
    /**
     * @var mixed[]
     */
    public $flavors;

    public function fromFlavor($flavor)
    {
        return $this->flavors['from'][$flavor];
    }

    public function toFlavor($flavor)
    {
        return $this->flavors['to'][$flavor];
    }
}
