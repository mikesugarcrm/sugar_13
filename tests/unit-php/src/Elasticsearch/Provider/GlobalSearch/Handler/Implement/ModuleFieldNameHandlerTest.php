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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Provider\GlobalSearch\Handler\Implement;

use PHPUnit\Framework\TestCase;
use SugarBean;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Document;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ModuleFieldNameHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MappingHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\ProcessDocumentHandlerInterface;
use PHPUnit\Framework\Assert;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ModuleFieldNameHandler
 */
class ModuleFieldNameHandlerTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $implements = class_implements(ModuleFieldNameHandler::class);
        Assert::assertContains(MappingHandlerInterface::class, $implements);
        Assert::assertContains(ProcessDocumentHandlerInterface::class, $implements);
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, array $fields, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $moduleNameHandlerMock = $this->getModuleNameHandlerMock();

        // make sure only 1 es field has been created
        foreach ($fields as $field) {
            $moduleNameHandlerMock->buildMapping($mapping, $field, $defs);
        }
        $this->assertEquals($expected, $mapping->compile());
    }

    public function providerTestBuildMapping()
    {
        return [
            [
                'anyModule',
                ['test_field_1', 'test_field_2', 'test_field_3'],
                ['type' => 'not_used_type'],
                [
                    Mapping::MODULE_NAME_FIELD => [
                        'type' => 'keyword',
                        'index' => true,
                        'fields' => [
                            Mapping::MODULE_NAME_FIELD => ['type' => 'keyword'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::processDocumentPreIndex
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex(string $module, $isOneIndexAndOverV6, $expected)
    {
        $bean = $this->getSugarBeanMock($module);
        $moduleNameHandlerMock = $this->getModuleNameHandlerMock(['isOneIndexEnabledAndEsV6Above']);

        $moduleNameHandlerMock->expects($this->any())
            ->method('isOneIndexEnabledAndEsV6Above')
            ->will($this->returnValue($isOneIndexAndOverV6));

        $document = new Document();
        $document->setType($module);
        $document->setDataField('any_name', 'any_value');
        // common field
        $document->setDataField('assigned_user_id', '12345');
        $document->setDataField('Common__owner_id', '1');

        $moduleNameHandlerMock->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            'no one index with well know module name' => [
                'Contacts',
                false,
                [
                    Mapping::MODULE_NAME_FIELD => 'Contacts',
                    'any_name' => 'any_value',
                    'assigned_user_id' => '12345',
                    'Common__owner_id' => '1',
                ],
            ],
            'no one index with random module name' => [
                'any_module_name',
                false,
                [
                    Mapping::MODULE_NAME_FIELD => 'any_module_name',
                    'any_name' => 'any_value',
                    'assigned_user_id' => '12345',
                    'Common__owner_id' => '1',
                ],
            ],
            'one index on with well know module name' => [
                'Contacts',
                true,
                [
                    Mapping::MODULE_NAME_FIELD => 'Contacts',
                    'Contacts__any_name' => 'any_value',
                    'Common__assigned_user_id' => '12345',
                    'Common__owner_id' => '1',
                ],
            ],
            'one index on with radnom module name' => [
                'any_module_name',
                true,
                [
                    Mapping::MODULE_NAME_FIELD => 'any_module_name',
                    'any_module_name__any_name' => 'any_value',
                    'Common__assigned_user_id' => '12345',
                    'Common__owner_id' => '1',
                ],
            ],
        ];
    }

    /**
     * Get ModuleFieldNameHandler Mock
     *
     * @param array $methods
     *
     * @return ModuleFieldNameHandler
     */
    protected function getModuleNameHandlerMock(array $methods = [])
    {
        return $this->createPartialMock(ModuleFieldNameHandler::class, $methods);
    }

    /**
     * Get SugarBean mock
     *
     * @return SugarBean
     */
    protected function getSugarBeanMock(string $module)
    {
        $mock = $this->createPartialMock(SugarBean::class, ['getModuleName']);
        $mock->expects($this->any())
            ->method('getModuleName')
            ->will($this->returnValue($module));
        return $mock;
    }
}
