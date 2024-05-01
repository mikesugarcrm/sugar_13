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

class UpgradeFixClassConstructorTest extends UpgradeTestCase
{
    /**
     * @var array Set of test modules and their properties.
     */
    private $modules = [
        'TestCustomModuleA_0123456789' => [
            'extendsFrom' => 'Basic',
            'importable' => true,
            'team_security' => true,
            'acl' => true,
        ],
        'TestCustomModuleB_0123456789' => [
            'extendsFrom' => 'Company',
            'importable' => true,
            'team_security' => true,
            'acl' => true,
        ],
        'TestCustomModuleC_0123456789' => [
            'extendsFrom' => 'Company',
            'importable' => false,
            'team_security' => false,
            'acl' => false,

        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->modules as $moduleName => $params) {
            sugar_mkdir('modules' . DIRECTORY_SEPARATOR . $moduleName);
            $this->create65CustomClass($moduleName, $params);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->modules as $moduleName => $params) {
            rmdir_recursive('modules' . DIRECTORY_SEPARATOR . $moduleName);
        }
        parent::tearDown();
    }

    private function create65CustomClass($moduleName, $params)
    {
        $class = [
            'name' => $moduleName,
            'extends' => $params['extendsFrom'],
            'table_name' => strtolower($moduleName),
            'importable' => $params['importable'],
            'fields' => [
                'fieldA' => 'fieldA',
                'fieldB' => 'fieldB',
                'fieldC' => 'fieldC',
                'fieldD' => 'fieldD',
                'fieldE' => 'fieldE',
            ],
            'team_security' => $params['team_security'],
            'acl' => $params['acl'],
        ];

        if ($params['extendsFrom'] !== 'Basic') {
            $template = strtolower($params['extendsFrom']);
            $class['requires'] = [
                'include' . DIRECTORY_SEPARATOR .
                'SugarObjects' . DIRECTORY_SEPARATOR .
                'templates' . DIRECTORY_SEPARATOR .
                $template . DIRECTORY_SEPARATOR .
                $params['extendsFrom'] . '.php',
            ];
        }

        $smarty = new Sugar_Smarty();
        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';
        $smarty->assign('class', $class);
        $content = $smarty->fetch(__DIR__ . DIRECTORY_SEPARATOR . '4_FixClassConstructor/6_5_Class.tpl');

        file_put_contents(
            'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '_sugar.php',
            $content
        );
    }

    /**
     * Test for ScanModules
     */
    public function testFixClassConstructor()
    {
        $oldContents = [];
        foreach ($this->modules as $moduleName => $params) {
            $this->assertFileExists(
                'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '_sugar.php'
            );
            $oldContents[$moduleName] = file_get_contents(
                'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '_sugar.php'
            );
        }

        $script = $this->upgrader->getScript('post', '4_FixClassConstructor');
        $script->from_version = 6.7;
        $script->to_version = 7.2;
        $script->run();

        $newContents = [];
        foreach ($this->modules as $moduleName => $params) {
            $newContents[$moduleName] = file_get_contents(
                'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '_sugar.php'
            );

            $this->assertNotEquals(
                $oldContents[$moduleName],
                $newContents[$moduleName],
                'The class ' . $moduleName . '_sugar has the same contents as prior the update.'
            );

            $this->assertNotContains(
                '/**************',
                $newContents[$moduleName],
                'There should be the new license header.'
            );

            $this->assertContains(
                '* Copyright (C) 2004',
                $newContents[$moduleName],
                'There should be the new license header.'
            );

            $this->assertContains(
                'public ',
                $newContents[$moduleName],
                'There should be public keyword.'
            );

            $this->assertContains(
                'function __construct',
                $newContents[$moduleName],
                'There should be the __construct method.'
            );

            $this->assertContains(
                'parent::__construct()',
                $newContents[$moduleName],
                'The construct method should call parent::__construct().'
            );

            $this->assertContains(
                'function __construct',
                $newContents[$moduleName],
                'There should be the __construct method.'
            );

            if ($params['extendsFrom'] === 'Basic') {
                $this->assertNotContains('require_once', $newContents[$moduleName], 'There should be no requires.');
            } else {
                $this->assertContains(
                    'require_once \'include/SugarObjects/templ',
                    $newContents[$moduleName],
                    'There should be a require.'
                );
            }

            $this->assertContains(
                '$importable = ' . $params['importable'] ? 'true' : 'false',
                $newContents[$moduleName],
                'There should be the $importable property.'
            );

            if ($params['team_security']) {
                $this->assertNotContains(
                    '$disable_row_level_security = true',
                    $newContents[$moduleName],
                    'There should not be the $disable_row_level_security property.'
                );
            } else {
                $this->assertContains(
                    '$disable_row_level_security = true',
                    $newContents[$moduleName],
                    'There should not be the $disable_row_level_security property.'
                );
            }

            if ($params['acl']) {
                $this->assertContains(
                    'bean_implements',
                    $newContents[$moduleName],
                    'There should not be the bean_implements function.'
                );
            } else {
                $this->assertNotContains(
                    'bean_implements',
                    $newContents[$moduleName],
                    'There should not be the bean_implements function.'
                );
            }
        }
    }
}
