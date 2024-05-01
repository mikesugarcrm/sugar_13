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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Users\upgrade\scripts\post;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once 'modules/Users/upgrade/scripts/post/1_UpgradeAuthenticationClass.php';

/**
 * @coversDefaultClass \SugarUpgradeUpgradeAuthenticationClass
 */
final class SugarUpgradeUpgradeAuthenticationClassTest extends TestCase
{
    /**
     * @var \UpgradeDriver|MockObject
     */
    private $upgradeDriver;

    /**
     * @var \SugarConfig|MockObject
     */
    private $sugarConfig;

    /**
     * @var \Configurator|MockObject
     */
    private $configurator;

    /**
     * @var \SugarUpgradeUpgradeAuthenticationClass|MockObject
     */
    private $upgrader;

    /**
     * @return array
     * @see testUpgrade
     */
    public function upgradeDataProvider(): array
    {
        return [
            'SugarAuthenticate' => [
                'oldClass' => 'SugarAuthenticate',
                'expects' => $expects = [
                    'authenticationClass' => 'IdMSugarAuthenticate',
                    'log' => 'AuthenticationClass upgraded form SugarAuthenticate to IdMSugarAuthenticate',
                ],
            ],
            'LDAPAuthenticate' => [
                'oldClass' => 'LDAPAuthenticate',
                'expects' => $expects = [
                    'authenticationClass' => 'IdMLDAPAuthenticate',
                    'log' => 'AuthenticationClass upgraded form LDAPAuthenticate to IdMLDAPAuthenticate',
                ],
            ],
            'SAMLAuthenticate' => [
                'oldClass' => 'SAMLAuthenticate',
                'expects' => $expects = [
                    'authenticationClass' => 'IdMSAMLAuthenticate',
                    'log' => 'AuthenticationClass upgraded form SAMLAuthenticate to IdMSAMLAuthenticate',
                ],
            ],
        ];
    }

    /**
     * @dataProvider upgradeDataProvider
     * @covers ::run
     * @param string $oldClass
     * @param array $expects
     */
    public function testUpgrade(string $oldClass, array $expects): void
    {
        $this->upgrader->from_version = '8.0.0';

        $this->upgradeDriver->config = ['authenticationClass' => $oldClass];

        $this->upgrader
            ->expects($this->once())
            ->method('log')
            ->with($expects['log']);

        $this->configurator
            ->expects($this->once())
            ->method('handleOverride');
        $this->configurator
            ->expects($this->once())
            ->method('clearCache');
        $this->sugarConfig
            ->expects($this->once())
            ->method('clearCache');

        $this->upgrader->run();

        $this->assertEquals($expects['authenticationClass'], $this->configurator->config['authenticationClass']);
    }

    /**
     * @covers ::run
     */
    public function testEmptyCLass(): void
    {
        $this->upgrader->from_version = '8.0.0';

        $this->upgrader
            ->expects($this->once())
            ->method('log')
            ->with('AuthenticationClass was empty leave as is');


        $this->upgrader->run();
    }

    /**
     * @covers ::run
     */
    public function testUnexpectedClass(): void
    {
        $this->upgrader->from_version = '8.0.0';

        $this->upgradeDriver->config = ['authenticationClass' => 'SomeUnexpectedCalss'];

        $this->upgrader
            ->expects($this->once())
            ->method('error')
            ->with('Unexpected authenticationClass:SomeUnexpectedCalss', true);

        $this->upgrader
            ->expects($this->never())
            ->method('log');
        $this->configurator
            ->expects($this->never())
            ->method('handleOverride');
        $this->configurator
            ->expects($this->never())
            ->method('clearCache');
        $this->sugarConfig
            ->expects($this->never())
            ->method('clearCache');

        $this->upgrader->run();
    }

    /**
     * @covers ::run
     */
    public function testToNewVersion(): void
    {
        $this->upgrader->from_version = '8.1.0';

        $this->upgradeDriver->config = ['authenticationClass' => 'SAMLAuthenticate'];

        $this->upgrader
            ->expects($this->never())
            ->method('error');
        $this->upgrader
            ->expects($this->never())
            ->method('log');
        $this->configurator
            ->expects($this->never())
            ->method('handleOverride');
        $this->configurator
            ->expects($this->never())
            ->method('clearCache');
        $this->sugarConfig
            ->expects($this->never())
            ->method('clearCache');

        $this->upgrader->run();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->sugarConfig = $this->createMock(\SugarConfig::class);
        $this->configurator = $this->createMock(\Configurator::class);
        $this->configurator->config = [];

        $this->upgradeDriver = $this->createMock(\UpgradeDriver::class);

        $this->upgrader = $this->getMockBuilder(\SugarUpgradeUpgradeAuthenticationClass::class)
            ->setMethods(['getConfigurator', 'getConfigInstance', 'log', 'error'])
            ->setConstructorArgs([$this->upgradeDriver])
            ->getMock();

        $this->upgrader->method('getConfigurator')->willReturn($this->configurator);
        $this->upgrader->method('getConfigInstance')->willReturn($this->sugarConfig);
    }
}
