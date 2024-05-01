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


require_once 'upgrade/scripts/post/5_FTSHook.php';

class SugarUpgradeFTSHookTest extends UpgradeTestCase
{
    private $oldHookDefs = [
        'application/Ext/LogicHooks/logichooks.ext.php',
        'Extension/application/Ext/LogicHooks/SugarFTSHooks.php',
    ];

    public function fileExistsProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $fileExists string  mainHookFile exists or not
     * @dataProvider fileExistsProvider
     */
    public function testRun($fileExists)
    {
        $upgraderMock = $this->getMockForAbstractClass('UpgradeDriver');

        $mockInstaller = $this->getMockBuilder('SugarUpgradeFTSHook')
            ->setMethods(['removeDuplicates', 'fileExists'])
            ->setConstructorArgs([$upgraderMock])
            ->getMock();

        $mockInstaller->expects($this->once())->method('fileExists')->willReturn($fileExists);

        if ($fileExists) {
            $mockInstaller->expects($this->once())->method('removeDuplicates');
        } else {
            $mockInstaller->expects($this->never())->method('removeDuplicates');
        }

        $mockInstaller->run();
    }

    public function testRemoveDuplicates()
    {
        $upgraderMock =
            $this->getMockForAbstractClass('UpgradeDriver', [], '', true, true, true, ['fileToDelete']);

        $upgraderMock->expects($this->exactly(2))
            ->method('fileToDelete')
            ->with(
                $this->logicalOr(
                    $this->equalTo($this->oldHookDefs[0]),
                    $this->equalTo($this->oldHookDefs[1])
                )
            );

        $mockInstaller = $this->getMockBuilder('SugarUpgradeFTSHook')
            ->setConstructorArgs([$upgraderMock])
            ->getMock();

        SugarTestReflection::setProtectedValue($mockInstaller, 'oldHookDefs', $this->oldHookDefs);

        SugarTestReflection::callProtectedMethod($mockInstaller, 'removeDuplicates');
    }
}
