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

require_once 'modules/UpgradeWizard/UpgradeDriver.php';
require_once 'upgrade/scripts/post/4_ClearSubpanels.php';

/**
 * Test for clearing bad defs from supbanels definitions..
 */
class ClearSubpanelsTest extends TestCase
{
    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        global $beanList;
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        $beanList = [
            $this->module => 'Account',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param array $def
     * @param string $layout
     * @param string $file
     * @param array $expectedLayout
     * @param array $state
     *
     * @dataProvider provider
     */
    public function testRun($def, $layout, $file, $expectedLayout, $state = [])
    {
        $path = sugar_cached(self::class);
        SugarAutoLoader::ensureDir($path . DIRECTORY_SEPARATOR . 'custom');
        $fpath = $path . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . $file;
        SugarTestHelper::saveFile($fpath);
        sugar_file_put_contents($fpath, $layout);


        $upgradeDriver = $this->getMockForAbstractClass('UpgradeDriver');
        $upgradeDriver->context = [
            'source_dir' => $path,
        ];
        $upgradeDriver->state = $state;

        $script = $this->getMockBuilder('SugarUpgradeClearSubpanels')
            ->setMethods(['getDefFiles', 'updateFile', 'getBeanDefs', 'rebuildExtensions'])
            ->setConstructorArgs([$upgradeDriver])
            ->getMock();

        $script->expects($this->any())
            ->method('getBeanDefs')
            ->will($this->returnValue($def));
        $script->expects($this->any())
            ->method('getDefFiles')
            ->will($this->returnValue([$fpath]));
        $script->expects($this->once())
            ->method('rebuildExtensions');
        $script->expects($this->once())
            ->method('updateFile')
            ->with($fpath, $expectedLayout);
        $script->run();
    }

    public function provider()
    {
        return [
            [
                [
                    'a' => [
                        'name' => 'a',
                        'type' => 'text',
                    ],
                    'b' => [
                        'name' => 'b',
                        'type' => 'text',
                    ],
                ],
                <<<EOL
<?php
\$subpanel_layout = array (
  'top_buttons' => array (),
  'where' => '',
  'list_fields' => array (
    'a' =>
    array (
      'vname' => 'a',
    ),
    'b' => array (
      'vname' => 'b',
    ),
    'c' => array (
      'vname' => 'c',
      'widget_class' => 'SubPanelDetailViewLink',
    ),
    'd' => array (
      'vname' => 'd',
      'usage' => 'SomeUsage'
    ),
    'e' => array (
      'vname' => 'e',
    ),
    'edit_button' => array (
      'vname' => 'edit_button'
    ),
  ),
);
EOL
                ,
                'tst.php',
                [
                    'top_buttons' => [],
                    'where' => '',
                    'list_fields' => [
                        'a' => [
                            'vname' => 'a',
                        ],
                        'b' => [
                            'vname' => 'b',
                        ],
                        'c' => [
                            'vname' => 'c',
                            'widget_class' => 'SubPanelDetailViewLink',
                        ],
                        'd' => [
                            'vname' => 'd',
                            'usage' => 'SomeUsage',
                        ],
                        'edit_button' => [
                            'vname' => 'edit_button',
                            'widget_class' => 'SubPanelEditButton',
                        ],
                    ],
                ],
            ],
            [
                [
                    'a' => [
                        'name' => 'a',
                        'type' => 'link',
                        'relationship' => 'd',
                    ],
                    'b' => [
                        'name' => 'b',
                        'type' => 'relate',
                        'id_name' => 'c',
                        'relationship' => 'd',
                        'link' => 'a',
                    ],
                    'c' => [
                        'name' => 'c',
                        'type' => 'id',
                        'relationship' => 'd',
                    ],
                ],
                <<<EOL
<?php
\$layout_defs["{$this->module}"]["subpanel_setup"]["c"] = array (
  'order' => 100,
  'module' => '{$this->module}',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL',
  'get_subpanel_data' => 'c',
  'top_buttons' =>
  array (),
);
EOL
                ,
                'tst2.php',
                [
                    $this->module => [
                        'subpanel_setup' => [
                            'a' => [
                                'order' => 100,
                                'module' => $this->module,
                                'subpanel_name' => 'default',
                                'sort_order' => 'asc',
                                'sort_by' => 'id',
                                'title_key' => 'LBL',
                                'get_subpanel_data' => 'a',
                                'top_buttons' => [],
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'a' => [
                        'name' => 'a',
                        'type' => 'text',
                    ],
                    'b' => [
                        'name' => 'b',
                        'type' => 'text',
                    ],
                ],
                <<<EOL
<?php
\$subpanel_layout = array(
    'list_fields' => array(
        'a' => array(
            'vname' => 'a',
        ),
        'b' => array(
            'vname' => 'b',
            'widget_class' => 'WrongWidgetClass',
        ),
    ),
);
EOL
                ,
                'tst3.php',
                [
                    'list_fields' => [
                        'a' => [
                            'vname' => 'a',
                        ],
                    ],
                ],
                [
                    'healthcheck' => [
                        [
                            'report' => 'unknownWidgetClass',
                            'params' => [
                                'WrongWidgetClass',
                                'b',
                                $this->module,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
