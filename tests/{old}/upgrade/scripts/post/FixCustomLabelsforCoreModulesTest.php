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

require_once 'upgrade/scripts/post/7_FixCustomLabelsForCoreModules.php';

/**
 * Class FixCustomModuleLabelsTest test for SugarUpgradeFixCustomModuleLabels upgrader script
 */
class FixCustomLabelsForCoreModulesTest extends UpgradeTestCase
{
    /** @var SugarUpgradeFixCustomLabelsForCoreModules */
    protected $script;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SugarUpgradeFixCustomLabelsForCoreModules */
        $this->script = $this->upgrader->getScript('post', '7_FixCustomLabelsForCoreModules');

        $GLOBALS['sugar_config']['languages'] =
            [
                'en_us' => 'English (US)',
                'test_test' => 'Test',
                'test2_test2' => 'Test 2',
            ];
        SugarConfig::getInstance()->clearCache('languages');

        // Setup subpanels
        $dataViewdefs = <<<END
<?php
\$viewdefs['TestModule1']['base']['view']['subpanel-for-testmodule2'] = array(
'panels' =>
    array(
        0 =>
            array(
                'name' => 'panel_header',
                'label' => 'LBL_PANEL_1',
                'fields' =>
                    array(
                        0 =>
                            array(
                                'type' => 'currency',
                                'default' => true,
                                'sortable' => false,
                                'label' => 'LBL_OLD_1',
                                'enabled' => true,
                                'name' => 'best_case',
                            ),
                    ),
            ),
    ),
);
END;

        $dataSubpanelLayout = <<<END
<?php
\$subpanel_layout = array(
	'top_buttons' => array(
		array('widget_class' => 'SubPanelTopCreateButton'),
		array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'TestModule1'),
	),

	'where' => '',

	'list_fields' => array(
		'name' => array(
 		 	'vname' => 'LBL_OLD_1',
			'widget_class' => 'SubPanelDetailViewLink',
			'width' => '45%',
		),
		'billing_address_city' => array(
 		 	'vname' => 'LBL_OLD_1',
			'width' => '27%',
		),
		'phone_office' => array(
 		 	'vname' => 'LBL_OLD_2',
			'width' => '20%',
		),
		'edit_button' => array(
			'vname' => 'LBL_OLD_3',
			'widget_class' => 'SubPanelEditButton',
			'width' => '4%',
		),
		'remove_button' => array(
			'vname' => 'LBL_OLD_1',
			'widget_class' => 'SubPanelRemoveButton',
			'width' => '4%',
		),
	),
);
END;

        $subpanelPaths = [
            'custom/modules/TestModule1/clients/base/views/subpanel-for-TestModule2/subpanel-for-TestModule2.php' => $dataViewdefs,
            'custom/modules/TestModule1/metadata/subpanels/TestModule1_subpanel_TestModule2.php' => $dataSubpanelLayout,
        ];

        foreach ($subpanelPaths as $subpanelPath => $subpanelContent) {
            mkdir_recursive(dirname($subpanelPath));
            SugarTestHelper::saveFile($subpanelPath);
            sugar_file_put_contents($subpanelPath, $subpanelContent);
        }

        // Prepare ListView data
        $fileListViewDefs = 'custom/modules/TestModule1/clients/base/views/list/list.php';
        $dataListViewDefs = <<<END
<?php
\$viewdefs['TestModule1']['base']['view']['list'] = array (
  'panels' =>
  array (
    0 =>
    array (
      'label' => 'LBL_PANEL_DEFAULT',
      'fields' =>
      array (
        0 =>
        array (
          'name' => 'name',
          'width' => '30%',
          'link' => true,
          'label' => 'LBL_OLD_1',
          'enabled' => true,
          'default' => true,
        ),
      ),
    ),
  ),
);
END;
        SugarTestHelper::saveFile($fileListViewDefs);
        sugar_file_put_contents($fileListViewDefs, $dataListViewDefs);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        rmdir_recursive('custom/modules/TestModule1');
    }

    /**
     * @dataProvider providerDataForUpgradeLabels
     * @param string $module
     * @param string $language
     * @param array $customLabels
     * @param array $labelsToChange
     * @param array $expected
     */
    public function testUpgradeModuleLabels($module, $language, $customLabels, $labelsToChange, $expected)
    {
        $this->script->upgradeLabels = [$module => $labelsToChange];

        // Prepare language files with customizations
        $path = 'custom/modules/' . $module . '/language/' . $language . '.lang.php';
        mkdir_recursive(dirname($path));
        SugarTestHelper::saveFile($path);
        write_array_to_file('mod_strings', $customLabels, $path);

        $this->script->upgradeModuleLabels($module, $language);
        $mod_strings = [];
        include $path;

        $this->assertEquals($expected, $mod_strings);
    }

    /**
     * Test change old labels to new ones in all $module subpanels
     *
     * @dataProvider providerDataForUpgradeSubpanelLabels
     * @param string $module
     * @param array $labelsToChange
     * @param array $expectedViewdefs
     * @param array $expectedLayout
     * @param string $viewFile
     * @param string $metaFile
     */
    public function testUpgradeSubpanelModuleLabels($module, $labelsToChange, $expectedViewdefs, $expectedLayout, $viewFile, $metaFile)
    {
        $this->script->upgradeLabels = [$module => $labelsToChange];
        $this->script->upgradeSubpanelModuleLabels($module);

        $this->assertFileExists($viewFile);
        $this->assertFileExists($metaFile);

        $viewdefs = [];
        include $viewFile;

        foreach ($expectedViewdefs as $expected) {
            $viewdefsSearch = $viewdefs;
            foreach ((array)$expected['path'] as $pathKey) {
                $this->assertArrayHasKey($pathKey, $viewdefsSearch);
                $viewdefsSearch = $viewdefsSearch[$pathKey];
            }
            $this->assertEquals($expected['value'], $viewdefsSearch);
        }
        unset($viewdefs);

        $subpanel_layout = [];
        include $metaFile;

        foreach ($expectedLayout as $expected) {
            $subpanelLayoutSearch = $subpanel_layout;
            foreach ((array)$expected['path'] as $pathKey) {
                $this->assertArrayHasKey($pathKey, $subpanelLayoutSearch);
                $subpanelLayoutSearch = $subpanelLayoutSearch[$pathKey];
            }
            $this->assertEquals($expected['value'], $subpanelLayoutSearch);
        }
    }

    /**
     * Test upgrade of List Viewdefs
     * @group CRYS557
     */
    public function testUpgradeListViewModuleLabels()
    {
        $this->script->upgradeLabels = ['TestModule1' => ['LBL_OLD_1' => 'LBL_NEW_1']];
        $this->script->upgradeListViewModuleLabels('TestModule1');

        $viewFile = 'custom/modules/TestModule1/clients/base/views/list/list.php';
        $this->assertFileExists($viewFile);

        $viewdefs = [];
        include $viewFile;

        $this->assertNotEmpty($viewdefs);
        $expectedLabel = 'LBL_NEW_1';
        $actualLabel = $viewdefs['TestModule1']['base']['view']['list']['panels'][0]['fields'][0]['label'];
        $this->assertEquals($expectedLabel, $actualLabel);
    }

    /**
     * Dete provider for test change old labels to new ones in all $module subpanels
     *
     * @return array
     */
    public function providerDataForUpgradeSubpanelLabels()
    {
        return [
            [
                'TestModule1',
                [
                    'LBL_OLD_1' => 'LBL_NEW_1',
                    'LBL_OLD_2' => 'LBL_NEW_2',
                    'LBL_OLD_3' => 'LBL_NEW_3',
                    'LBL_PANEL_1' => 'LBL_PANEL_NEW_1',
                ],
                [
                    [
                        'path' => [
                            'TestModule1',
                            'base',
                            'view',
                            'subpanel-for-testmodule2',
                            'panels',
                            0,
                            'label',
                        ],
                        'value' => 'LBL_PANEL_NEW_1',
                    ],
                    [
                        'path' => [
                            'TestModule1',
                            'base',
                            'view',
                            'subpanel-for-testmodule2',
                            'panels',
                            0,
                            'fields',
                            0,
                            'label',
                        ],
                        'value' => 'LBL_NEW_1',
                    ],
                ],
                [
                    [
                        'path' => [
                            'list_fields',
                            'name',
                            'vname',
                        ],
                        'value' => 'LBL_NEW_1',
                    ],
                    [
                        'path' => [
                            'list_fields',
                            'billing_address_city',
                            'vname',
                        ],
                        'value' => 'LBL_NEW_1',
                    ],
                    [
                        'path' => [
                            'list_fields',
                            'phone_office',
                            'vname',
                        ],
                        'value' => 'LBL_NEW_2',
                    ],
                    [
                        'path' => [
                            'list_fields',
                            'edit_button',
                            'vname',
                        ],
                        'value' => 'LBL_NEW_3',
                    ],
                    [
                        'path' => [
                            'list_fields',
                            'remove_button',
                            'vname',
                        ],
                        'value' => 'LBL_NEW_1',
                    ],
                ],
                'custom/modules/TestModule1/clients/base/views/subpanel-for-TestModule2/subpanel-for-TestModule2.php',
                'custom/modules/TestModule1/metadata/subpanels/TestModule1_subpanel_TestModule2.php',
            ],
        ];
    }

    public function providerDataForUpgradeLabels()
    {
        return
            [
                [
                    'TestModule1',
                    'en_us',
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ],
                    [
                        'LBL_OLD_1' => 'LBL_NEW_1',
                        'LBL_OLD_2' => 'LBL_NEW_2',
                    ],
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                        'LBL_NEW_1' => 'OLD_VALUE_1',
                        'LBL_NEW_2' => 'OLD_VALUE_2',
                    ],
                ], // Generic behavior
                [
                    'TestModule1',
                    'test_test',
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ],
                    [
                        'LBL_OLD_1' => 'LBL_NEW_1',
                        'LBL_OLD_2' => 'LBL_NEW_2',
                    ],
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                        'LBL_NEW_1' => 'OLD_VALUE_1',
                        'LBL_NEW_2' => 'OLD_VALUE_2',
                    ],
                ], // Not default language
                [
                    'TestModule1',
                    'test_test',
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ],
                    [
                        'LBL_OLD_ANOTHER_1' => 'LBL_NEW_1',
                        'LBL_OLD_ANOTHER_2' => 'LBL_NEW_2',
                    ],
                    [
                        'LBL_MODULE_NAME' => 'TestModule1',
                        'LBL_OLD_1' => 'OLD_VALUE_1',
                        'LBL_OLD_2' => 'OLD_VALUE_2',
                    ],
                ], // No customizations were done for required labels, so file should not be modified
            ];
    }

    public function testRun()
    {
        $this->script->upgradeLabels = [
            'TestModule1' => [
                'LBL_OLD_1' => 'LBL_NEW_1',
                'LBL_OLD_2' => 'LBL_NEW_2',
            ],
        ];

        // Prepare language files with customizations
        $languages = [
            'en_us' => 'en_us',
            'test_test' => 'test_test',
        ];
        $customLabels = [
            'LBL_MODULE_NAME' => 'TestModule1',
            'LBL_OLD_1' => 'OLD_VALUE_1',
            'LBL_OLD_2' => 'OLD_VALUE_2',
        ];

        foreach ($languages as $key => $value) {
            $path = 'custom/modules/TestModule1/language/' . $key . '.lang.php';
            mkdir_recursive(dirname($path));
            SugarTestHelper::saveFile($path);
            write_array_to_file('mod_strings', $customLabels, $path);
        }

        $moduleInstaller = $this->createPartialMock('ModuleInstaller', ['rebuild_languages']);
        $moduleInstaller->expects($this->once())
            ->method('rebuild_languages')
            ->with($languages, ['TestModule1']);

        $this->script->mi = $moduleInstaller;
        $this->script->run();
    }

    /**
     * Test that no changes were made to label customization
     */
    public function testRunNoCustomization()
    {
        $this->script->upgradeLabels = [
            'TestModule1' => [
                'LBL_OLD_1' => 'LBL_NEW_1',
                'LBL_OLD_2' => 'LBL_NEW_2',
            ],
        ];

        // Prepare language files with customizations
        $languages = [
            'en_us' => 'en_us',
            'test_test' => 'test_test',
        ];
        $customLabels = [
            'LBL_MODULE_NAME' => 'TestModule1',
            'LBL_ANOTHER_KEY_1' => 'VALUE_1',
            'LBL_ANOTHER_KEY_2' => 'VALUE_2',
        ];

        foreach ($languages as $key => $value) {
            $path = 'custom/modules/TestModule1/language/' . $key . '.lang.php';
            mkdir_recursive(dirname($path));
            SugarTestHelper::saveFile($path);
            write_array_to_file('mod_strings', $customLabels, $path);
        }

        // Rebuild should never be called cause there are no customizations for test labels in test module
        $moduleInstaller = $this->createPartialMock('ModuleInstaller', ['rebuild_languages']);
        $moduleInstaller->expects($this->never())
            ->method('rebuild_languages');

        $this->script->mi = $moduleInstaller;
        $this->script->run();
    }
}
