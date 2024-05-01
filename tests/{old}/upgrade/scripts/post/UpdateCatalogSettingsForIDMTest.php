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

require_once 'upgrade/scripts/post/9_UpdateCatalogSettingsForIDM.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config as IdmConfig;

class SugarUpgradeUpdateCatalogSettingsForIDMTest extends TestCase
{
    /** @var IdmConfig|MockObject */
    protected $idmConfig;

    /** @var Configurator|MockObject */
    protected $configurator;

    /** @var SugarUpgradeUpdateCatalogSettingsForIDM|MockObject */
    protected $script;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configurator = $this->getMockBuilder('Configurator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->idmConfig = $this->getMockBuilder('IdmConfig')
            ->disableOriginalConstructor()
            ->setMethods(['isIDMModeEnabled', 'getCatalogURL'])
            ->getMock();
        $this->script = $this->getMockBuilder(\SugarUpgradeUpdateCatalogSettingsForIDM::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurator', 'getIdmConfig'])
            ->getMock();
        $this->script->from_version = '10.0.0';
        $this->script->method('getConfigurator')->willReturn($this->configurator);
        $this->script->method('getIdmConfig')->willReturn($this->idmConfig);
    }

    /**
     * Test run() when idm is not enabled
     */
    public function testRunWhenIdmNotEnabled()
    {
        $this->idmConfig->method('isIDMModeEnabled')->willReturn(false);
        $this->configurator->expects($this->never())->method('handleOverride');
        $this->script->run();
    }

    /**
     * Test run() when idm is enabled
     */
    public function testRunWhenIdmEnabled()
    {
        $this->configurator->config = [];
        $this->idmConfig->method('isIDMModeEnabled')->willReturn(true);
        $this->idmConfig->method('getCatalogURL')->willReturn('TEST-URL');
        $this->configurator
            ->expects($this->once())
            ->method('handleOverride');
        $this->script->run();
        $this->assertEquals('TEST-URL', $this->configurator->config['catalog_url']);
        $this->assertEquals(true, $this->configurator->config['catalog_enabled']);
    }
}
