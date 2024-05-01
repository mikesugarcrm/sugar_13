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

require_once 'upgrade/scripts/post/1_ClearVarDefs.php';

/**
 * Test asserts correct removal of wrong field definitions from a bean.
 */
class ClearVarDefsTest extends TestCase
{
    public const MODULE = 'PreScript';

    protected function setUp(): void
    {
        $user = $this->createMock(User::class);

        global $beanList, $dictionary;
        SugarTestHelper::setUp('files');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('dictionary');
        SugarTestHelper::setUp('current_user', [false, true]);
        $beanList = [
            self::MODULE => 'PreScriptBean',
            'Users' => get_class($user),
        ];
        $dictionary[self::MODULE] = [
            'relationships' => [
                //need to be fixed with correct module name
                'b' => [
                    'lhs_module' => 'Users',
                    'rhs_module' => 'prescript',
                    'relationship_type' => 'one-to-many',
                ],
            ],
            'fields' => [
                //bad relationship
                'a' => [
                    'type' => 'link',
                    'name' => 'a',
                    'relationship' => 'b1',
                ],
                //relate type with wrong link
                'c' => [
                    'name' => 'c',
                    'type' => 'relate',
                    'link' => 'a',
                ],
                //fields contains wrong field
                'd' => [
                    'name' => 'd',
                    'type' => 'text',
                    'fields' => ['e', 'g'],
                    'db_concat_fields' => ['e', 'g'],
                ],
                //normal field
                'e' => [
                    'name' => 'e',
                    'type' => 'bool',
                ],
                //field with no type
                'g' => [
                    'name' => 'g',
                ],
                //good relate field
                'h' => [
                    'name' => 'f',
                    'relationship' => 'b',
                    'id_name' => 'i',
                    'type' => 'relate',
                    'link' => 'link',
                ],
                //field type should be `id`
                'i' => [
                    'name' => 'i',
                    'relationship' => 'b',
                    'type' => 'link',
                ],
                //good link
                'link' => [
                    'name' => 'link',
                    'type' => 'link',
                    'relationship' => 'b',
                    'side' => 'RHS',
                ],
            ],
        ];
        SugarRelationshipFactory::deleteCache();
        SugarRelationshipFactory::rebuildCache();
        VardefManager::$linkFields = [];
    }

    protected function tearDown(): void
    {
        global $dictionary;
        unset($dictionary[self::MODULE]);
        SugarRelationshipFactory::deleteCache();
        SugarRelationshipFactory::rebuildCache();
        SugarTestHelper::tearDown();
    }

    /**
     * Test script results.
     */
    public function testRun()
    {
        global $current_user;
        $path = sugar_cached(self::class);
        $upgradeDriver = new TestUpgrader($current_user);
        $upgradeDriver->context = [
            'source_dir' => $path,
        ];

        $script = $this->getMockBuilder('SugarUpgradeClearVarDefs')
            ->setMethods([
                'cleanCache',
                'deleteFieldFile',
                'deleteRelationshipFiles',
                'writeDef',
                'updateLinks',
                'updateRelationshipDefinition',
            ])
            ->setConstructorArgs([$upgradeDriver])
            ->getMock();

        $script->expects($this->once())
            ->method('cleanCache');

        //fields with no type are stripped by vardef manager, so we will only remove 2 with this script.
        $script->expects($this->exactly(2))
            ->method('deleteFieldFile');

        $script->expects($this->once())
            ->method('deleteRelationshipFiles')
            ->with($this->isInstanceOf('PreScriptBean'), 'b1');

        $script->expects($this->once())
            ->method('updateLinks')
            ->with(['i' => 1], $this->isInstanceOf('PreScriptBean'));

        $script->expects($this->once())
            ->method('writeDef')
            ->with(
                [
                    'fields' => ['e'],
                    'db_concat_fields' => ['e'],
                    'name' => 'd',
                    'type' => 'text',
                ],
                ''
            );

        $script->expects($this->once())
            ->method('updateRelationshipDefinition')
            ->with(
                $this->isInstanceOf('PreScriptBean'),
                [
                    'lhs_module' => 'Users',
                    'rhs_module' => 'PreScript',
                    'relationship_type' => 'one-to-many',
                    'name' => 'b',
                ]
            );

        $script->run();

        $bean = SugarTestReflection::callProtectedMethod(
            $script,
            'getBean',
            [strtolower((self::MODULE))]
        );
        $this->assertEquals('PreScriptBean', get_class($bean));
    }
}

/**
 * Mock for SugarBean
 */
class PreScriptBean extends SugarBean
{
    public $object_name = ClearVarDefsTest::MODULE;

    public $module_name = ClearVarDefsTest::MODULE;
}
