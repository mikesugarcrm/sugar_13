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

require_once 'modules/ModuleBuilder/MB/MBModule.php';

/**
 * @coversDefaultClass MBModule
 */
class MBModuleTest extends TestCase
{
    protected $moduleName = 'superAwesomeModule';
    protected $packageKey = 'sap';
    protected $mbModuleName;
    protected $target;
    protected $path;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('files');
        $this->mbModuleName = "{$this->packageKey}_{$this->moduleName}";
        $this->path = "modules/{$this->moduleName}";
        $this->target = "$this->path/clients/base/menus/header/header.php";
        SugarTestHelper::saveFile($this->target);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenu()
    {
        $viewdefs = [];
        $expectedArray = $this->getExpectedActionItems();

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = false;
        $mb->createMenu($this->path);

        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenuWithImport()
    {
        $viewdefs = [];
        $expectedArray = $this->getExpectedActionItems(true);

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = true;
        $mb->createMenu($this->path);

        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }

    protected function getExpectedActionItems($import = false)
    {
        $expectedArray = [
            [
                'route' => "#{$this->mbModuleName}/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $this->mbModuleName,
                'icon' => 'sicon-plus',
            ],
            [
                'route' => "#{$this->mbModuleName}",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $this->mbModuleName,
                'icon' => 'sicon-list-view',
            ],
        ];

        if ($import) {
            $importRoute = http_build_query(
                [
                    'module' => 'Import',
                    'action' => 'Step1',
                    'import_module' => $this->mbModuleName,
                    'return_module' => $this->mbModuleName,
                    'return_action' => 'index',
                ]
            );

            $expectedArray[] = [
                'route' => "#bwc/index.php?{$importRoute}",
                'label' => 'LBL_IMPORT',
                'acl_action' => 'import',
                'acl_module' => $this->mbModuleName,
                'icon' => 'sicon-upload',
            ];
        }

        return $expectedArray;
    }


    public function vardefProvider()
    {
        return [
            [
                ['name' => 'testvardef', 'label' => 'testvardef'],
                'testvardef',
            ],
            [
                ['name' => 'range', 'label' => 'testvardef'],
                'range_field',
            ],
            [
                ['name' => 'hipopotomounstruosesquipedaliofobia_pentakismyriahexakisquilioletracosiohexacontapentagono', 'label' => 'testvardef'],
                $GLOBALS['db']->getValidDBName('hipopotomounstruosesquipedaliofobia_pentakismyriahexakisquilioletracosiohexacontapentagono', true, 'column'),
            ],
        ];
    }

    /**
     * @dataProvider vardefProvider
     * @param array $vardef
     * @param string $exp_name
     */
    public function testVardefValidation($vardef, $exp_name)
    {
        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->addField($vardef);
        $defs = $mb->mbvardefs->getVardef();
        $this->assertArrayHasKey('fields', $defs);
        $this->assertArrayHasKey($exp_name, $defs['fields']);
        $this->assertEquals($exp_name, $defs['fields'][$exp_name]['name']);
    }

    /**
     * @covers ::getModuleAbbreviatedLabel
     * @dataProvider providerTestGetModuleAbbreviatedLabel
     * @param $moduleLabel
     * @param $expected
     */
    public function testGetModuleAbbreviatedLabel($moduleLabel, $expected)
    {
        $actual = MBModule::getModuleAbbreviatedLabel($moduleLabel);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provider for testGetModuleAbbreviatedLabel
     *
     * @return array The set of label abbreviation test cases
     */
    public function providerTestGetModuleAbbreviatedLabel()
    {
        return [
            ['Accounts', 'Ac'],
            ['mOdUlE', 'mO'],
            ['Test Module', 'TM'],
            ['Revenue Line Items', 'RL'],
        ];
    }
}
