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

require_once 'upgrade/scripts/post/9_RemoveIdmSettingsFromConfigFile.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SugarUpgradeRemoveIdmSettingsFromConfigFileTest extends TestCase
{
    /** @var UpgradeDriver|MockObject */
    protected $upgradeDriver;

    /** @var Configurator|MockObject */
    protected $configurator;

    /** @var SugarUpgradeRemoveIdmSettingsFromConfigFile|MockObject */
    protected $script;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->upgradeDriver = $this->getMockBuilder('UpgradeDriver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurator = $this->getMockBuilder('Configurator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->script = $this->getMockBuilder(\SugarUpgradeRemoveIdmSettingsFromConfigFile::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurator', 'isIdmDataInDb'])
            ->getMock();
        $this->script->method('getConfigurator')->willReturn($this->configurator);
        $this->script->upgrader = $this->upgradeDriver;
    }

    public function testSkipRunWhenNoIdmInFile()
    {
        $this->configurator->config = ['foo' => 'bar'];

        $this->configurator->expects($this->never())->method('saveOverride');
        $this->script->run();
    }

    public function testSkipRunWhenNoIdmDataInDB()
    {
        $this->configurator->config = ['foo' => 'bar', 'idm_mode' => ['a' => 'b']];
        $this->script->method('isIdmDataInDb')->willReturn(false);

        $this->configurator->expects($this->never())->method('saveOverride');
        $this->script->run();
    }

    public function testRun()
    {
        $expectConfig = <<<DATA
<?php
/***CONFIGURATOR***/
\$sugar_config['foo'] = 'bar';
/***CONFIGURATOR***/
DATA;
        $this->configurator->config = ['c' => 'd', 'foo' => 'bar', 'idm_mode' => ['a' => 'b']];
        $this->script->method('isIdmDataInDb')->willReturn(true);
        $this->upgradeDriver->method('readConfigFiles')->willReturn([
            ['c' => 'd'],
            ['foo' => 'bar', 'idm_mode' => ['a' => 'b']],
        ]);

        $this->configurator
            ->expects($this->once())
            ->method('saveOverride')
            ->with($expectConfig);
        $this->script->run();
    }
}
