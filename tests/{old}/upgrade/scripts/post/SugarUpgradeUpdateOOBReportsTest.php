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

require_once 'upgrade/scripts/post/4_UpdateOOBReports.php';

/**
 * Class UpdateOOBReportsTest
 * @package Sugarcrm\SugarcrmTestsUnit\upgrade\scripts\post
 * @coversDefaultClass \SugarUpgradeUpdateOOBReports
 */
class SugarUpgradeUpdateOOBReportsTest extends UpgradeTestCase
{
    /**
     * @covers ::run
     */
    public function testRunOnlyInstallsIfAppropriate()
    {
        $mockScript = $this->getMockBuilder(\SugarUpgradeUpdateOOBReports::class)
            ->setMethods([
                'getReportsToInstall',
                'installReports',
                'log',
                'shouldInstallReports',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $mockScript->expects($this->once())
            ->method('shouldInstallReports')
            ->willReturn(false);
        $mockScript->expects($this->never())
            ->method('installReports');

        $mockScript->run();
    }

    /**
     * @covers ::run
     */
    public function testRunInstallsDesiredReports()
    {
        $mockScript = $this->getMockBuilder(\SugarUpgradeUpdateOOBReports::class)
            ->setMethods([
                'getReportsToInstall',
                'installReports',
                'log',
                'shouldInstallReports',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $mockScript->expects($this->once())
            ->method('shouldInstallReports')
            ->willReturn(true);
        $mockScript->expects($this->once())
            ->method('getReportsToInstall')
            ->willReturn(['New Report 1', 'New Report 2']);
        $mockScript->expects($this->once())
            ->method('installReports')
            ->with($this->equalTo(['New Report 1', 'New Report 2']));

        $mockScript->run();
    }

    /**
     * @covers ::shouldInstallReports
     * @dataProvider providerShouldInstallReports
     * @param array $flavors Configuration for toFlavor and fromFlavor.
     * @param array $versions Version numbers (from and to).
     * @param bool $expected Expected result.
     */
    public function testShouldInstallReports(array $flavors, array $versions, bool $expected)
    {
        $upgradeDriver = $this->getMockForAbstractClass(\UpgradeDriver::class);
        $mockScript = new MockSugarUpgradeUpdateOOBReports($upgradeDriver);
        $upgradeDriver->from_version = $versions['from'];
        $mockScript->flavors = $flavors;
        $this->assertEquals($expected, $mockScript->shouldInstallReports());
        unset($upgradeDriver);
    }

    public function providerShouldInstallReports(): array
    {
        return [
            // 9.2.0 Ent -> 9.3.0 Ent
            [
                ['from' => ['pro' => true, 'ent' => true], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.2.0', 'to' => '9.3.0'],
                true,
            ],
            // 9.3.0 Ent -> 9.3.1 Ent
            [
                ['from' => ['pro' => true, 'ent' => true], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.3.0', 'to' => '9.3.1'],
                false,
            ],
            // 9.3.0 Pro -> 9.3.0 Ent
            [
                ['from' => ['pro' => true, 'ent' => false], 'to' => ['pro' => true, 'ent' => true]],
                ['from' => '9.3.0', 'to' => '9.3.0'],
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

class MockSugarUpgradeUpdateOOBReports extends SugarUpgradeUpdateOOBReports
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
