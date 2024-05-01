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
 * @group leadconvert
 * @group Studio
 */
class ConvertLayoutMetadataParserTest extends TestCase
{
    /**
     * @var ConvertLayoutMetadataParser&MockObject
     */
    private $parser;

    protected $contactDef = [
        'module' => 'Contacts',
        'required' => true,
        'copyData' => true,
        'duplicateCheckOnStart' => true,
    ];
    protected $accountDef = [
        'module' => 'Accounts',
        'required' => true,
        'copyData' => true,
        'duplicateCheckOnStart' => true,
    ];

    private $originalDefinition = [
        'module' => 'Foo',
    ];

    protected $customFile = null;

    protected $customModule = null;

    protected $customDef = null;

    protected function setUp(): void
    {
        $viewdefs = [];
        $this->parser = $this->createPartialMock(ConvertLayoutMetadataParser::class, [
            'getOriginalViewDefs',
            'isDupeCheckEnabledForModule',
            '_saveToFile',
        ]);
        SugarTestReflection::setProtectedValue($this->parser, '_convertdefs', [
            'modules' => [
                $this->contactDef,
                $this->accountDef,
            ],
        ]);

        // custom def
        $this->customModule = 'TestModule';
        $this->customDef = [
            'module' => $this->customModule,
            'required' => true,
        ];
        $this->customFile = "custom/modules/{$this->customModule}/clients/base/layouts/convert-main-for-leads/convert-main-for-leads.php";
        if (file_exists($this->customFile)) {
            rename($this->customFile, $this->customFile . '.bak');
        }
        $dirName = dirname($this->customFile);
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $viewdefs[$this->customModule]['base']['layout']['convert-main-for-leads'] = $this->customDef;
        write_array_to_file('viewdefs', $viewdefs, $this->customFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->customFile . '.bak')) {
            rename($this->customFile . '.bak', $this->customFile);
        } else {
            unlink($this->customFile);
        }
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithExistingDef_UpdatesDef()
    {
        $this->mockOriginalDefinition();
        $this->parser->updateConvertDef([
            $this->contactDef,
            [
                'module' => 'Accounts',
                'required' => false,
                'copyData' => false,
            ],
        ]);

        $expectedAccountDef = $this->accountDef;
        $expectedAccountDef['required'] = false;
        $expectedAccountDef['copyData'] = false;
        $expectedModules = [
            'modules' => [
                $this->contactDef,
                $expectedAccountDef,
            ],
        ];

        $this->assertEquals(
            $expectedModules,
            SugarTestReflection::getProtectedValue($this->parser, '_convertdefs')
        );
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithNewDef_AddsDef()
    {
        $fooDef = [
            'module' => 'Foo',
            'required' => false,
            'copyData' => false,
        ];

        $this->mockOriginalDefinition();
        $this->parser->updateConvertDef([
            $this->contactDef,
            $this->accountDef,
            $fooDef,
        ]);

        $expectedModules = [
            'modules' => [
                $this->contactDef,
                $this->accountDef,
                $fooDef,
            ],
        ];

        $this->assertEquals(
            $expectedModules,
            SugarTestReflection::getProtectedValue($this->parser, '_convertdefs')
        );
    }

    /**
     * @covers ConvertLayoutMetadataParser::updateConvertDef
     */
    public function testUpdateConvertDef_WithAccountAndOpp_ForcesAccountRequired()
    {
        $oppDef = [
            'module' => 'Opportunities',
            'required' => true,
        ];
        $this->mockOriginalDefinition();
        $this->parser->updateConvertDef([
            $this->contactDef,
            [
                'module' => 'Accounts',
                'required' => false,
            ],
            $oppDef,
        ]);

        $expectedAccountDef = $this->accountDef;
        $expectedAccountDef['required'] = true; //force required
        $expectedModules = [
            'modules' => [
                $this->contactDef,
                $expectedAccountDef,
                $oppDef,
            ],
        ];

        $this->assertEquals(
            $expectedModules,
            SugarTestReflection::getProtectedValue($this->parser, '_convertdefs')
        );
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_DependenciesApply_AddedToDef()
    {
        $dependency = ['Bar' => []];
        $this->mockOriginalDefinition(['dependentModules' => $dependency]);
        $def = [
            'module' => 'Foo',
            'required' => true,
        ];
        $includedModules = ['Foo', 'Bar'];
        $resultDef = SugarTestReflection::callProtectedMethod(
            $this->parser,
            'applyDependenciesAndHiddenFields',
            [$def, $includedModules]
        );
        $this->assertEquals($dependency, $resultDef['dependentModules'], 'Dependency should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_DependencyDoesNotApply_NotAddedToDef()
    {
        $dependency = ['Bar' => []];
        $this->mockOriginalDefinition(['dependentModules' => $dependency]);
        $def = [
            'module' => 'Foo',
            'required' => true,
        ];
        $includedModules = ['Foo']; //Bar not included
        $resultDef = SugarTestReflection::callProtectedMethod(
            $this->parser,
            'applyDependenciesAndHiddenFields',
            [$def, $includedModules]
        );
        $this->assertFalse(isset($resultDef['dependentModules']), 'Dependency should not be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_HiddenFieldsApply_AddedToDef()
    {
        $hiddenFields = ['baz' => 'Bar'];
        $this->mockOriginalDefinition(['hiddenFields' => $hiddenFields]);
        $def = [
            'module' => 'Foo',
            'required' => true,
        ];
        $includedModules = ['Foo', 'Bar'];
        $resultDef = SugarTestReflection::callProtectedMethod(
            $this->parser,
            'applyDependenciesAndHiddenFields',
            [$def, $includedModules]
        );
        $this->assertEquals($hiddenFields, $resultDef['hiddenFields'], 'Hidden fields should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::applyDependenciesAndHiddenFields
     */
    public function testApplyDependenciesAndHiddenFields_ExcludedFieldsApply_AddedToDef()
    {
        $hiddenFields = ['baz' => 'Bar'];
        SugarTestReflection::setProtectedValue($this->parser, 'excludedFields', [
            'Foo' => [
                'repeat_type' => 'Bar',
            ],
        ]);
        $this->mockOriginalDefinition(['hiddenFields' => $hiddenFields]);
        $def = [
            'module' => 'Foo',
            'required' => true,
        ];
        $includedModules = ['Foo', 'Bar'];
        $resultDef = SugarTestReflection::callProtectedMethod(
            $this->parser,
            'applyDependenciesAndHiddenFields',
            [$def, $includedModules]
        );
        $this->assertArrayHasKey('baz', $resultDef['hiddenFields'], 'Hidden fields should be set');
        $this->assertArrayHasKey('repeat_type', $resultDef['hiddenFields'], 'Hidden fields should be set');
    }

    /**
     * @covers ConvertLayoutMetadataParser::removeLayout
     */
    public function testRemoveLayout()
    {
        $this->parser->removeLayout('Accounts');
        $expectedModules = [
            'modules' => [
                $this->contactDef,
            ],
        ];
        $this->assertEquals(
            $expectedModules,
            SugarTestReflection::getProtectedValue($this->parser, '_convertdefs')
        );
    }

    /**
     * @covers ConvertLayoutMetadataParser::deploy
     */
    public function testDeploy()
    {
        $this->parser->expects($this->once())
            ->method('_saveToFile');
        SugarTestReflection::callProtectedMethod($this->parser, 'deploy');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModule()
    {
        $customDef = $this->parser->getDefaultDefForModule($this->customModule);
        $this->assertEquals($this->customDef, $customDef, 'Custom def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefForModule
     */
    public function testGetDefForModule_WithConvertDefsPassed_ReturnsCorrectModuleDef()
    {
        $fooDef = ['module' => 'Foo'];
        $testModules = [
            'modules' => [
                $fooDef,
            ],
        ];
        $actualDef = $this->parser->getDefForModule('Foo', $testModules);
        $this->assertEquals($fooDef, $actualDef, 'Foo def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefForModule
     */
    public function testGetDefForModules_WithNoConvertDefsPassed_ReturnsCorrectModuleDef()
    {
        $actualDef = $this->parser->getDefForModule('Accounts');
        $this->assertEquals($this->accountDef, $actualDef, 'Accounts def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleInOriginalViewDefs_ReturnsOriginalValues()
    {
        $this->mockOriginalDefinition();
        $actualDef = $this->parser->getDefaultDefForModule('Foo');
        $this->assertEquals($this->originalDefinition, $actualDef, 'Original Foo def should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleNotInOriginalViewDefs_ReturnsDefaultValues()
    {
        $this->mockOriginalDefinition();
        $actualDef = $this->parser->getDefaultDefForModule('Bar');
        $defaultSettings = SugarTestReflection::getProtectedValue($this->parser, 'defaultModuleDefSettings');
        $expectedDef = array_merge(['module' => 'Bar'], $defaultSettings);
        $this->assertEquals($expectedDef, $actualDef, 'Default settings should be returned');
    }

    /**
     * @covers ConvertLayoutMetadataParser::getDefaultDefForModule
     */
    public function testGetDefaultDefForModules_ForModuleNotInOriginalAndDupeCheckEnabled_ReturnsDefaultWithDupeOnStart()
    {
        $this->mockOriginalDefinition();
        $this->parser->method('isDupeCheckEnabledForModule')
            ->willReturn(true);

        $actualDef = $this->parser->getDefaultDefForModule('Bar');
        $defaultSettings = SugarTestReflection::getProtectedValue($this->parser, 'defaultModuleDefSettings');
        $defaultSettings['duplicateCheckOnStart'] = true;
        $expectedDef = array_merge(['module' => 'Bar'], $defaultSettings);
        $this->assertEquals($expectedDef, $actualDef, 'Default settings should be returned');
    }

    private function mockOriginalDefinition(array $properties = []): void
    {
        $this->parser->method('getOriginalViewDefs')
            ->willReturn([
                'Leads' => [
                    'base' => [
                        'layout' => [
                            'convert-main' => [
                                'modules' => [
                                    array_merge($this->originalDefinition, $properties),
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }
}
