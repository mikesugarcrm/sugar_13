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

namespace Sugarcrm\SugarcrmTestsUnit\modules\MySettings;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \TabController
 */
class TabControllerTest extends TestCase
{
    /**
     * @covers ::is_system_tabs_in_db
     * @dataProvider providerSystemTabsInDB
     * @param array|null $settings Settings.
     * @param bool $expected Expected result.
     */
    public function testIsSystemTabsInDB(?array $settings, bool $expected)
    {
        $administration = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $administration->settings = $settings;
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getMySettings'])
            ->getMock();
        $controller->method('getMySettings')
            ->willReturn($administration);
        $this->assertEquals($expected, $controller->is_system_tabs_in_db());
    }

    public function providerSystemTabsInDB(): array
    {
        return [
            [null, false],
            [[], false],
            [['RandomSetting' => 'RandomValue'], false],
            [['MySettings_tab' => 'tab settings'], true],
        ];
    }

    /**
     * @covers ::getMySettingsTabHash
     * @dataProvider providerGetMySettingsTabHash
     * @param array|null $settings Settings.
     * @param string $expected Expected result.
     */
    public function testGetMySettingsTabHash(?array $settings, string $expected)
    {
        $administration = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $administration->settings = $settings;
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getMySettings'])
            ->getMock();
        $controller->method('getMySettings')
            ->willReturn($administration);
        $this->assertEquals($expected, $controller->getMySettingsTabHash());
    }

    public function providerGetMySettingsTabHash(): array
    {
        return [
            [null, ''],
            [[], ''],
            [['RandomSetting' => 'RandomValue'], ''],
            [['MySettings_tab' => 'tab settings'], '08a18f1e69fb1cb1561439af236c4d7d'],
        ];
    }

    /**
     * @covers ::set_system_tabs
     * @dataProvider providerSetSystemTabs
     * @param array $tabs Tabs to save.
     * @param array $nonAccessTabs tabs are not accessible
     * @param string $expected Expected serialization.
     */
    public function testSetSystemTabs(array $tabs, array $nonAccessTabs, string $expected)
    {
        $adminMock = $this->getMockBuilder(\Administration::class)
            ->setMethods(['saveSetting'])
            ->disableOriginalConstructor()
            ->getMock();
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getAdministration', 'getNonAccessibleEnabledTabs'])
            ->getMock();
        $controller->method('getAdministration')
            ->willReturn($adminMock);

        $controller->method('getNonAccessibleEnabledTabs')
            ->willReturn($nonAccessTabs);

        $adminMock->expects($this->any())
            ->method('saveSetting')
            ->with($this->equalTo('MySettings'), $this->equalTo('tab'), $this->equalTo($expected));

        $controller->set_system_tabs($tabs);

        $this->assertFalse(TestReflection::getProtectedValue($controller, 'isCacheValid'));
    }

    public function providerSetSystemTabs(): array
    {
        return [
            'no no-access tabs' => [
                ['Accounts' => 'Accounts', 'Contacts' => 'Contacts'],
                [],
                'YToyOntzOjg6IkFjY291bnRzIjtzOjg6IkFjY291bnRzIjtzOjg6IkNvbnRhY3RzIjtzOjg6IkNvbnRhY3RzIjt9',
            ],
            'has no-access tabs' => [
                ['Accounts' => 'Accounts', 'Contacts' => 'Contacts'],
                ['NewTab'],
                'YTozOntzOjg6IkFjY291bnRzIjtzOjg6IkFjY291bnRzIjtzOjg6IkNvbnRhY3RzIjtzOjg6IkNvbnRhY3RzIjtpOjA7czo2OiJOZXdUYWIiO30=',
            ],
        ];
    }

    /**
     * @covers ::get_users_can_edit
     * @dataProvider providerGetUsersCanEdit
     * @param array|null $settings
     * @param bool $expected
     */
    public function testGetUsersCanEdit(?array $settings, bool $expected)
    {
        $administration = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $administration->settings = $settings;
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getMySettings'])
            ->getMock();
        $controller->method('getMySettings')
            ->willReturn($administration);
        $this->assertEquals($expected, $controller->get_users_can_edit());
    }

    public function providerGetUsersCanEdit(): array
    {
        return [
            [null, true],
            [[], true],
            [['Random Setting' => 'Random Value'], true],
            [['Random Setting' => 'Random Value', 'MySettings_disable_useredit' => 'no'], true],
            [['Random Setting' => 'Random Value', 'MySettings_disable_useredit' => 'yes'], false],
        ];
    }

    /**
     * @covers ::get_key_array
     * @dataProvider providerKeyArray
     * @param array $original Original array.
     * @param array $expected Expected result array.
     */
    public function testGetKeyArray(array $original, array $expected)
    {
        $this->assertEquals($expected, \TabController::get_key_array($original));
    }

    public function providerKeyArray(): array
    {
        return [
            [[], []],
            [['Accounts', 'Contacts'], ['Accounts' => 'Accounts', 'Contacts' => 'Contacts']],
        ];
    }

    /**
     * @covers ::get_users_pinned_modules
     * @dataProvider providerGetUsersPinnedModules
     * @param array|null $settings
     * @param bool $expected
     */
    public function testGetUsersPinnedModules(?array $settings, bool $expected)
    {
        $administration = $this->getMockBuilder(\Administration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $administration->settings = $settings;
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getMySettings'])
            ->getMock();
        $controller->method('getMySettings')
            ->willReturn($administration);
        $this->assertEquals($expected, $controller->get_users_pinned_modules());
    }

    public function providerGetUsersPinnedModules(): array
    {
        return [
            [null, true],
            [[], true],
            [['Random Setting' => 'Random Value'], true],
            [['Random Setting' => 'Random Value', 'MySettings_disable_users_pinned_modules' => 'no'], true],
            [['Random Setting' => 'Random Value', 'MySettings_disable_users_pinned_modules' => 'yes'], false],
            [['Random Setting' => 'Random Value', 'MySettings_disable_users_pinned_modules' => 'Random Value'], true],
        ];
    }

    /**
     * @covers ::get_number_pinned_modules
     * @dataProvider providerGetNumberPinnedModules
     * @param array $configs
     * @param int $expected
     */
    public function testGetNumberPinnedModules(array $configs, int $expected)
    {
        $controller = $this->getMockBuilder(\TabController::class)
            ->setMethods(['getSugarConfig'])
            ->getMock();

        $controller->method('getSugarConfig')
            ->willReturnMap([
                ['maxPinnedModules', $configs['maxPinnedModules'] ?? null],
                ['default_max_pinned_modules', $configs['default_max_pinned_modules'] ?? null],
            ]);

        $this->assertEquals($expected, $controller->get_number_pinned_modules());
    }

    public function providerGetNumberPinnedModules(): array
    {
        return [
            [[
                'maxPinnedModules' => 7,
                'default_max_pinned_modules' => 4,
            ], 7],
            [[
                'maxPinnedModules' => 7,
            ], 7],
            [[
                'default_max_pinned_modules' => 4,
            ], 4],
            [[
                'maxPinnedModules' => '7',
                'default_max_pinned_modules' => 4,
            ], 7],
            [[
                'maxPinnedModules' => 'RandomString',
                'default_max_pinned_modules' => 4,
            ], 4],
        ];
    }
}
