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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Administration\clients\base\api;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SugarApiExceptionNotAuthorized;
use SugarTestThemeUtilities;
use SugarTheme;
use SugarThemeRegistry;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class AdministrationApiTest
 * @coversDefaultClass \AdministrationApi
 */
class AdministrationApiTest extends TestCase
{
    /**
     * @var \Configurator|MockObject
     */
    private $configurator;

    /**
     * @var \User|MockObject
     */
    private $currentUser;

    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->currentUser = $this->createPartialMock(\User::class, ['isAdmin', 'isDeveloperForAnyModule']);

        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->apiService->user = $this->currentUser;

        $this->configurator = $this->createPartialMock(\Configurator::class, ['handleOverride']);
        $this->configurator->config = [];

        $this->api = $this->createPartialMock(
            \AdministrationApi::class,
            ['getConfigurator', 'clearCache', 'getSugarConfig', 'getAdminPanelLegacyDefs', 'getImageFromTheme']
        );
        $this->api->method('getConfigurator')->willReturn($this->configurator);

        $GLOBALS['current_user'] = $this->currentUser;
        $GLOBALS['app_strings'] = ['EXCEPTION_NOT_AUTHORIZED' => 'EXCEPTION_NOT_AUTHORIZED'];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($GLOBALS['current_user'], $GLOBALS['app_strings']);

        parent::tearDown();
    }

    /**
     * @covers ::enableIdmMigration
     */
    public function testEnableIdmMigrationUserNotAuthorized(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->enableIdmMigration($this->apiService, []);
    }

    /**
     * @covers ::disableIdmMigration
     */
    public function testDisableIdmMigrationUserNotAuthorized(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(false);

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->disableIdmMigration($this->apiService, []);
    }

    /**
     * @covers ::enableIdmMigration
     */
    public function testEnableIdmMigration(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(true);

        $this->configurator->expects($this->once())->method('handleOverride');

        $result = $this->api->enableIdmMigration($this->apiService, []);

        $this->assertTrue($this->configurator->config['maintenanceMode']);
        $this->assertTrue($this->configurator->config['idmMigration']);
        $this->assertEquals(['success' => 'true'], $result);
    }

    /**
     * @covers ::disableIdmMigration
     */
    public function testDisableIdmMigration(): void
    {
        $GLOBALS['current_user']->method('isAdmin')->willReturn(true);

        $this->configurator->expects($this->once())->method('handleOverride');
        $this->api->expects($this->once())->method('clearCache');

        $result = $this->api->disableIdmMigration($this->apiService, []);

        $this->assertFalse($this->configurator->config['maintenanceMode']);
        $this->assertFalse($this->configurator->config['idmMigration']);
        $this->assertEquals(['success' => 'true'], $result);
    }

    /**
     * @covers ::ensureDeveloperUser
     */
    public function testEnsureDeveloperUser(): void
    {
        $this->currentUser->method('isDeveloperForAnyModule')->willReturn(false);
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->ensureDeveloperUser();
    }

    /**
     * @covers ::getValidateIPAddress
     */
    public function testGetValidateIPAddressUserNotAuthorized(): void
    {
        $this->currentUser->method('isAdmin')->willReturn(false);
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->api->getValidateIPAddress($this->apiService, []);
    }

    /**
     * @covers ::getValidateIPAddress
     */
    public function testGetValidateIPAddress(): void
    {
        $this->currentUser->method('isAdmin')->willReturn(true);
        $sugarConfig = $this->createMock(\SugarConfig::class);
        $sugarConfig
            ->method('get')
            ->with('verify_client_ip', $this->anything())
            ->willReturn('1');
        $this->api
            ->method('getSugarConfig')
            ->willReturn($sugarConfig);

        $result = $this->api->getValidateIPAddress($this->apiService, []);
        $this->assertEquals($result, ['validate_ip_address' => true]);
    }

    /**
     * @covers ::getParsedAdminPanelDefsFromLegacyDefs
     */
    public function testGetParsedAdminPanelDefsFromLegacyDefs(): void
    {
        $legacyDef = [
            [
                'LBL_CONTAINER_TITLE',
                '',
                false,
                [
                    'MyModule' => [
                        'section_key_1' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_1_TITLE',
                            'LBL_SECTION_1_DESC',
                            '#link',
                        ],
                    ],
                    'MyModule2' => [
                        'section_key_2' => [
                            'SectionModule',
                            'LBL_SECTION_2_TITLE',
                            'LBL_SECTION_2_DESC',
                            './index.php/example',
                        ],
                    ],
                ],
                'LBL_CONTAINER_DESC',
            ],
            [
                'LBL_CONTAINER_TITLE_2',
                '',
                false,
                [
                    'MyModule' => [
                        'section_key_1' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_1_TITLE',
                            'LBL_SECTION_1_DESC',
                            './index.php/example/2',
                        ],
                        'section_key_2' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_2_TITLE',
                            'LBL_SECTION_2_DESC',
                            './#bwc/index.php/example/2',
                        ],
                    ],
                ],
                'LBL_CONTAINER_DESC_2',
            ],
            [
                'LBL_CONTAINER_TITLE_3',
                '',
                false,
                [
                    'MyModule' => [
                        'section_key_1' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_1_TITLE',
                            'LBL_SECTION_1_DESC',
                            'javascript:void(parent.SUGAR.App.router.navigate("index.php/example/1", {trigger: true}));',
                        ],
                        'section_key_2' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_2_TITLE',
                            'LBL_SECTION_2_DESC',
                            'javascript:void(parent.SUGAR.App.router.navigate("#bwc/index.php/example/2", {trigger: true}));',
                        ],
                    ],
                ],
                'LBL_CONTAINER_DESC_3',
            ],
            [
                'LBL_CONTAINER_TITLE_4',
                '',
                false,
                [
                    'MyModule' => [
                        'section_key_1' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_1_TITLE',
                            'LBL_SECTION_1_DESC',
                            '#link',
                            null,
                            'onclick = "App.alert.show(\'test\', {level: \'info\', messages: \'test\'});"',
                            '_blank',

                        ],
                        'section_key_2' => [
                            'SectionModule',
                            'icon' => 'sicon-link',
                            'LBL_SECTION_2_TITLE',
                            'LBL_SECTION_2_DESC',
                            '#link',
                        ],
                    ],
                ],
                'LBL_CONTAINER_DESC_4',
            ],
        ];

        $expected = [
            [
                'label' => 'LBL_CONTAINER_TITLE',
                'description' => 'LBL_CONTAINER_DESC',
                'options' => [
                    [
                        'label' => 'LBL_SECTION_1_TITLE',
                        'description' => 'LBL_SECTION_1_DESC',
                        'link' => '#link',
                        'customIcon' => '',
                        'icon' => 'sicon-link',
                    ],
                    [
                        'label' => 'LBL_SECTION_2_TITLE',
                        'description' => 'LBL_SECTION_2_DESC',
                        'link' => './#bwc/index.php/example',
                        'icon' => '',
                        'customIcon' => 'SectionModule.gif',
                    ],
                ],
            ],
            [
                'label' => 'LBL_CONTAINER_TITLE_2',
                'description' => 'LBL_CONTAINER_DESC_2',
                'options' => [
                    [
                        'label' => 'LBL_SECTION_1_TITLE',
                        'description' => 'LBL_SECTION_1_DESC',
                        'link' => './#bwc/index.php/example/2',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                    ],
                    [
                        'label' => 'LBL_SECTION_2_TITLE',
                        'description' => 'LBL_SECTION_2_DESC',
                        'link' => './#bwc/index.php/example/2',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                    ],
                ],
            ],
            [
                'label' => 'LBL_CONTAINER_TITLE_3',
                'description' => 'LBL_CONTAINER_DESC_3',
                'options' => [
                    [
                        'label' => 'LBL_SECTION_1_TITLE',
                        'description' => 'LBL_SECTION_1_DESC',
                        'link' => 'javascript:void(parent.SUGAR.App.router.navigate("#bwc/index.php/example/1", {trigger: true}));',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                    ],
                    [
                        'label' => 'LBL_SECTION_2_TITLE',
                        'description' => 'LBL_SECTION_2_DESC',
                        'link' => 'javascript:void(parent.SUGAR.App.router.navigate("#bwc/index.php/example/2", {trigger: true}));',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                    ],
                ],
            ],
            [
                'label' => 'LBL_CONTAINER_TITLE_4',
                'description' => 'LBL_CONTAINER_DESC_4',
                'options' => [
                    [
                        'label' => 'LBL_SECTION_1_TITLE',
                        'description' => 'LBL_SECTION_1_DESC',
                        'link' => '#link',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                        'target' => '_blank',
                        'onclick' => 'onclick = "App.alert.show(\'test\', {level: \'info\', messages: \'test\'});"',
                    ],
                    [
                        'label' => 'LBL_SECTION_2_TITLE',
                        'description' => 'LBL_SECTION_2_DESC',
                        'link' => '#link',
                        'icon' => 'sicon-link',
                        'customIcon' => '',
                    ],
                ],
            ],
        ];

        $this->api->method('getAdminPanelLegacyDefs')->willReturn($legacyDef);
        $this->api->method('getImageFromTheme')->willReturn('SectionModule.gif');

        $actual = $this->api->getParsedAdminPanelDefsFromLegacyDefs();

        $this->assertEquals($actual, $expected);
    }

    /**
     * @covers ::convertBWCLinks
     */
    public function testConvertBWCLinks(): void
    {
        $message = 'server must be configured in <a href="index.php?module=Email">Email Settings</a>';
        $expected = 'server must be configured in <a href="#bwc/index.php?module=Email">Email Settings</a>';
        $actual = TestReflection::callProtectedMethod($this->api, 'convertBWCLinks', [$message]);
        $this->assertEquals($expected, $actual);
    }
}
