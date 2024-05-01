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

/**
 * @coversDefaultClass CurrentUserApi
 * @group ApiTests
 */
class CurrentUserApiTest extends TestCase
{
    public $currentUserApiMock;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        OutboundEmailConfigurationTestHelper::setUp();
        // load up the unifiedSearchApi for good times ahead

        $apiMock = $this->getMockBuilder('CurrentUserApi')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->currentUserApiMock = $apiMock;
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testCurrentUserLanguage()
    {
        // test from session
        $_SESSION['authenticated_user_language'] = 'en_UK';
        $result = SugarTestReflection::callProtectedMethod($this->currentUserApiMock, 'getBasicUserInfo', ['base']);
        $this->assertEquals('en_UK', $result['preferences']['language']);
        // test from user
        unset($_SESSION['authenticated_user_language']);
        $GLOBALS['current_user']->preferred_language = 'AWESOME';
        $result = SugarTestReflection::callProtectedMethod($this->currentUserApiMock, 'getBasicUserInfo', ['base']);
        $this->assertEquals('AWESOME', $result['preferences']['language']);
        // test from default
        unset($_SESSION['authenticated_user_language']);
        unset($GLOBALS['current_user']->preferred_language);
        $result = SugarTestReflection::callProtectedMethod($this->currentUserApiMock, 'getBasicUserInfo', ['base']);

        $this->assertEquals($GLOBALS['sugar_config']['default_language'], $result['preferences']['language']);
    }

    /**
     * @group wizard
     */
    public function testShowFirstLoginWizard()
    {
        global $current_user;
        $current_user->setPreference('ut', '0');
        $current_user->savePreferencesToDB();
        $result = $this->currentUserApiMock->shouldShowWizard();
        $this->assertTrue($result, "We show Wizard when user's preference 'ut' is falsy");
        $current_user->setPreference('ut', '1');
        $current_user->savePreferencesToDB();
        $result = $this->currentUserApiMock->shouldShowWizard();
        $this->assertFalse($result, "We do NOT show Wizard when user's preference 'ut' is truthy");
    }

    /**
     * Test Field Name Placement preference setting is retrieved from getUserPrefField_name_placement()
     * @param string $placement
     * @param string $expected
     * @dataProvider getUserPrefFieldNamePlacementProvider
     */
    public function testGetUserPrefFieldNamePlacement(string $placement, string $expected)
    {
        $current_user = SugarTestHelper::setUp('current_user', [true, true]);
        if (!empty($placement)) {
            $current_user->setPreference('field_name_placement', $placement, 0, 'global');
        }
        $result = SugarTestReflection::callProtectedMethod($this->currentUserApiMock, 'getUserPrefField_name_placement', [$current_user]);
        $this->assertEquals($expected, $result['field_name_placement']);
    }

    public function getUserPrefFieldNamePlacementProvider()
    {
        return [
            ['field_on_side', 'field_on_side'],
            ['field_on_top', 'field_on_top'],
            // default setting is 'field_on_side'
            ['', 'field_on_side'],
        ];
    }

    /**
     * Test that appearance settings are fetched properly, including default value
     * @param $setting
     * @param $expected
     * @throws Exception
     * @dataProvider dataProviderTestGetUserPrefAppearance
     */
    public function testGetUserPrefAppearance($setting, $expected)
    {
        $current_user = SugarTestHelper::setUp('current_user', [true, true]);
        if (!empty($setting)) {
            $current_user->setPreference('appearance', $setting, 0, 'global');
        }
        $result = SugarTestReflection::callProtectedMethod(
            $this->currentUserApiMock,
            'getUserPrefAppearance',
            [$current_user]
        );
        $this->assertEquals($expected, $result['appearance']);
    }

    public function dataProviderTestGetUserPrefAppearance()
    {
        return [
            ['system_default', 'system_default'],
            ['dark', 'dark'],
            ['light', 'light'],
            ['', 'system_default'],
        ];
    }

    /**
     * @covers ::getPlugins
     * @dataProvider providerTestGetPlugins
     * @param bool $hideOpiWpiFlag whether the "UserDownloadsHideOpiWpiPlugins"
     *                              config toggle is set
     */
    public function testGetPlugins($hideOpiWpiFlag)
    {
        global $app_strings;

        $pluginsMock = $this->getMockBuilder(SugarPlugins::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPluginList', 'getPluginLink'])
            ->getMock();
        $pluginsMock->method('getPluginList')->willReturn([
            [
                'raw_name' => 'excel1',
                'formatted_name' => 'SugarCRM_plugin_for_Excel_v.1',
            ],
            [
                'raw_name' => 'excel2',
                'formatted_name' => 'SugarCRM_plugin_for_Excel_v.2',
            ],
            [
                'raw_name' => 'word1',
                'formatted_name' => 'SugarCRM_plugin_for_Word_v.1',
            ],
            [
                'raw_name' => 'outlook1',
                'formatted_name' => 'SugarCRM_plugin_for_Outlook_v.1',
            ],
        ]);
        $pluginsMock->method('getPluginLink')->willReturnCallback(function ($pluginId) {
            return 'https://www.sugarcrm.com/crm/plugin_service.php/' . $pluginId;
        });

        $currentUserApiMock = $this->getMockBuilder(CurrentUserApi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSugarPluginsInstance', 'shouldHideOpiWpiPlugins'])
            ->getMock();
        $currentUserApiMock->method('getSugarPluginsInstance')->willReturn($pluginsMock);
        $currentUserApiMock->method('shouldHideOpiWpiPlugins')->willReturn($hideOpiWpiFlag);

        $serviceMock = $this->getMockBuilder('ServiceBase')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $expected = [
            'Excel' => [
                'name' => $app_strings['LBL_PLUGIN_EXCEL_NAME'],
                'desc' => $app_strings['LBL_PLUGIN_EXCEL_DESC'],
                'plugins' => [
                    [
                        'link' => 'https://www.sugarcrm.com/crm/plugin_service.php/excel1',
                        'label' => 'SugarCRM plugin for Excel v.1',
                    ],
                    [
                        'link' => 'https://www.sugarcrm.com/crm/plugin_service.php/excel2',
                        'label' => 'SugarCRM plugin for Excel v.2',
                    ],
                ],
            ],
        ];

        if (!$hideOpiWpiFlag) {
            $expected['Word'] = [
                'name' => $app_strings['LBL_PLUGIN_WORD_NAME'],
                'desc' => $app_strings['LBL_PLUGIN_WORD_DESC'],
                'plugins' => [
                    [
                        'link' => 'https://www.sugarcrm.com/crm/plugin_service.php/word1',
                        'label' => 'SugarCRM plugin for Word v.1',
                    ],
                ],
            ];

            $expected['Outlook'] = [
                'name' => $app_strings['LBL_PLUGIN_OUTLOOK_NAME'],
                'desc' => $app_strings['LBL_PLUGIN_OUTLOOK_DESC'],
                'plugins' => [
                    [
                        'link' => 'https://www.sugarcrm.com/crm/plugin_service.php/outlook1',
                        'label' => 'SugarCRM plugin for Outlook v.1',
                    ],
                ],
            ];
        }

        $actual = $currentUserApiMock->getPlugins($serviceMock, []);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Data provider for testGetPlugins
     *
     * @return array[]
     */
    public function providerTestGetPlugins()
    {
        return [[true], [false]];
    }
}
