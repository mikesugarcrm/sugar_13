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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass LanguageApi
 * @group ApiTests
 */
class LanguageApiTest extends TestCase
{
    protected $config;
    protected $fieldApi;
    protected $languageApi;
    protected $serviceMock;

    protected function setUp(): void
    {
        global $sugar_config;

        // current user must be admin so a new field can be created
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->fieldApi = new FieldApi();
        $this->languageApi = new LanguageApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();

        $this->config = $sugar_config;
        $sugar_config['default_language'] = 'en_us';
        $sugar_config['disabled_languages'] = '';
        $sugar_config['languages'] = [
            'en_us' => 'English (US)',
            'de_DE' => 'Deutsch',
            'ja_JP' => '日本語',
        ];
    }

    protected function tearDown(): void
    {
        global $sugar_config;

        $data = [
            [
                'module' => 'Leads',
                'name' => 'new_decimal_field_c',
            ],
            [
                'module' => 'Leads',
                'name' => 'ai_conv_score_classification_c',
            ],
        ];
        SugarAutoLoader::requireWithCustom('ModuleInstall/ModuleInstaller.php');
        $moduleInstallerClass = SugarAutoLoader::customClass('ModuleInstaller');
        $moduleInstaller = new $moduleInstallerClass();
        $moduleInstaller->uninstall_custom_fields($data);

        if ($this->config) {
            $sugar_config = $this->config;
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Checks the display labels are updated to the fields in the modules
     *
     * @covers ::updateModules
     * @param array $args argument that contains module/lang/field attributes
     *
     * @dataProvider updateModulesProvider
     */
    public function testUpdateModules(array $args)
    {
        if ($args['fieldArgs']) {
            // Creates a custom field to the system
            $result = $this->fieldApi->createCustomField($this->serviceMock, $args['fieldArgs']);
        }
        $result = $this->languageApi->updateModules($this->serviceMock, $args['langArgs']);
        $this->assertNotEmpty($result);
        foreach ($args['langArgs'] as $key => $block) {
            $bean = BeanFactory::newBean($block['name']);
            foreach ($block['labels'] as $lang => $labels) {
                foreach ($labels as $name => $label) {
                    $labelKey = $bean->field_defs[$name]['vname'] ?? '';
                    $file = "custom/Extension/modules/{$block['name']}/{$lang}.lang.php";
                    if (file_exists($file) && !empty($labelKey)) {
                        include $file;
                        $this->assertEquals($label, $mod_strings[$labelKey]);
                    }
                }
            }
        }
    }

    /**
     * Provider for ::testUpdateModules
     *
     * @return array
     */
    public function updateModulesProvider(): array
    {
        return [
            [
                'args' => [
                    'fieldArgs' => [
                        'module' => 'Leads',
                        'localizations' => [
                            'en_us' => [
                                'LBL_DECIMAL_FIELD' => 'New decimal field',
                            ],
                            'de_DE' => [
                                'LBL_DECIMAL_FIELD' => 'Neues Dezimalfeld',
                            ],
                            'ja_JP' => [
                                'LBL_DECIMAL_FIELD' => '新しい小数フィールド',
                            ],
                        ],
                        'data' => [
                            'name' => 'new_decimal_field',
                            'type' => 'decimal',
                            'label' => 'LBL_DECIMAL_FIELD',
                            'len' => '18',
                            'precision' => '8',
                        ],
                    ],
                    'langArgs' => [
                        [
                            'name' => 'Leads',
                            'labels' => [
                                'en_us' => [
                                    'new_decimal_field_c' => 'Updated decimal field',
                                    'title' => 'Subject',
                                ],
                                'de_DE' => [
                                    'new_decimal_field_c' => 'Dezimalfeld aktualisiert',
                                    'title' => 'Gegenstand',
                                ],
                                'ja_JP' => [
                                    'new_decimal_field_c' => '更新された10進数フィールド',
                                    'title' => '件名',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Opportunities',
                            'labels' => [
                                'de_DE' => [
                                    'amount' => 'Menge',
                                    'amount_usdollar' => 'Betrag US-Dollar',
                                ],
                                'ja_JP' => [
                                    'amount' => '量',
                                    'amount_usdollar' => '金額米ドル',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Checks the display labels are updated to the dropdown items in the dropdowns
     *
     * @covers ::updateDropdowns
     * @param array $args argument that contains dropdown/lang/dropdown item attributes
     *
     * @dataProvider updateDropdownProvider
     */
    public function testUpdateDropdowns(array $args)
    {
        if ($args['fieldArgs']) {
            // Creates a custom field to the system
            $result = $this->fieldApi->createCustomField($this->serviceMock, $args['fieldArgs']);
        }
        $result = $this->languageApi->updateDropdowns($this->serviceMock, $args['langArgs']);
        $this->assertNotEmpty($result);
        $dirName = 'custom/Extension/application/Ext/Language';
        foreach ($args['langArgs'] as $key => $block) {
            foreach ($block['labels'] as $lang => $labels) {
                $file = "{$dirName}/{$lang}.sugar_{$block['name']}.php";
                if (file_exists($file)) {
                    include $file;
                }
                foreach ($labels as $name => $label) {
                    if (!empty($app_list_strings[$block['name']][$name])) {
                        $this->assertEquals($label, $app_list_strings[$block['name']][$name]);
                    }
                }
            }
        }
    }

    /**
     * Provider for ::testUpdateDropdowns
     *
     * @return array
     */
    public function updateDropdownProvider(): array
    {
        return [
            [
                'args' => [
                    'fieldArgs' => [
                        'module' => 'Leads',
                        'localizations' => [
                            'en_us' => [
                                'LBL_AI_CONV_SCORE_CLASSIFICATION' => 'Ai Conv Score Classification',
                                'LBL_LEADS_CONV_NOT_LIKELY' => 'Not Likely',
                                'LBL_LEADS_CONV_LESS_LIKELY' => 'Less Likely',
                                'LBL_LEADS_CONV_SAME' => 'Same',
                            ],
                        ],
                        'data' => [
                            'name' => 'ai_conv_score_classification',
                            'type' => 'enum',
                            'label' => 'LBL_AI_CONV_SCORE_CLASSIFICATION',
                            'options' => [
                                'dropdownName' => 'ai_conv_score_classification_dropdown',
                                'dropdownList' => [
                                    ['value' => 'not_likely', 'label' => 'LBL_LEADS_CONV_NOT_LIKELY'],
                                    ['value' => 'less_likely', 'label' => 'LBL_LEADS_CONV_LESS_LIKELY'],
                                    ['value' => 'same', 'label' => 'LBL_LEADS_CONV_SAME'],
                                ],
                            ],
                            'default_value' => '',
                        ],
                    ],
                    'langArgs' => [
                        [
                            'name' => 'ai_conv_score_classification_dropdown',
                            'labels' => [
                                'de_DE' => [
                                    'not_likely' => 'Unwahrscheinlich',
                                    'less_likely' => 'Weniger wahrscheinlich',
                                ],
                                'ja_JP' => [
                                    'not_likely' => 'ありそうもない',
                                    'less_likely' => '可能性が低い',
                                ],
                            ],
                        ],
                        [
                            'name' => 'account_type_dom',
                            'labels' => [
                                'de_DE' => [
                                    'Analyst' => 'Analytiker',
                                    'Competitor' => 'Wettbewerber',
                                ],
                                'ja_JP' => [
                                    'Analyst' => 'アナリスト',
                                    'Competitor' => '競合他社選手',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testFormatSysDropdownItems()
    {
        // Use Reflection to make the protected method accessible
        $method = new ReflectionMethod(LanguageApi::class, 'formatSysDropdownItems');
        $method->setAccessible(true);

        $formattedDropdownItems = $method->invokeArgs($this->languageApi, ['missingDropdown', 'en_us']);
        static::assertEquals([], $formattedDropdownItems);

        $formattedDropdownItems = $method->invokeArgs($this->languageApi, ['moduleList', 'en_us']);
        $this->assertIsArray($formattedDropdownItems);
    }
}
