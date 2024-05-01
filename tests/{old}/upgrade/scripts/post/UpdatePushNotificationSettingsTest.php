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

require_once 'upgrade/scripts/post/9_UpdatePushNotificationSettings.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config as IdmConfig;

class SugarUpgradeUpdatePushNotificationSettingsTest extends TestCase
{
    /** @var IdmConfig|MockObject */
    protected $idmConfig;

    /** @var Configurator|MockObject */
    protected $configurator;

    /** @var SugarUpgradeUpdatePushNotificationSettings|MockObject */
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
            ->setMethods(['isIDMModeEnabled'])
            ->getMock();
        $this->script = $this->getMockBuilder(\SugarUpgradeUpdatePushNotificationSettings::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigurator', 'getIdmConfig', 'fromFlavor', 'toFlavor'])
            ->getMock();
        $this->script->method('getConfigurator')->willReturn($this->configurator);
        $this->script->method('getIdmConfig')->willReturn($this->idmConfig);
        $this->script->method('fromFlavor')->willReturn(true);
        $this->script->method('toFlavor')->willReturn(true);
        $this->script->from_version = '10.0.0';
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
        $this->configurator
            ->expects($this->once())
            ->method('handleOverride');
        $this->script->run();
        $this->assertEquals(true, $this->configurator->config['push_notification']['enabled']);
        $this->assertEquals('SugarPush', $this->configurator->config['push_notification']['service_provider']);
    }
}
