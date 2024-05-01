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
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ErasedFieldsHandler;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\MappingHandlerInterface;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\ProcessDocumentHandlerInterface;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Handler\Implement\ErasedFieldsHandler
 */
class ErasedFieldsHandlerTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testRequiredInterfaces()
    {
        $implements = class_implements(ErasedFieldsHandler::class);

        $this->assertContains(MappingHandlerInterface::class, $implements);
        $this->assertContains(ProcessDocumentHandlerInterface::class, $implements);
    }

    /**
     * @covers ::buildMapping
     * @dataProvider providerTestBuildMapping
     */
    public function testBuildMapping($module, array $fields, array $defs, array $expected)
    {
        $mapping = new Mapping($module);
        $sut = $this->getErasedFieldsHandlerMock();

        // make sure only 1 es field has been created
        foreach ($fields as $field) {
            $sut->buildMapping($mapping, $field, $defs);
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
                    'Common__erased_fields' => [
                        'type' => 'keyword',
                        'index' => false,
                        'fields' => [
                            'erased_fields' => ['type' => 'keyword'],
                        ],
                    ],
                    'erased_fields' => [
                        'type' => 'keyword',
                        'index' => false,
                        'copy_to' => ['Common__erased_fields'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::processDocumentPreIndex
     * @dataProvider providerTestProcessDocumentPreIndex
     */
    public function testProcessDocumentPreIndex($module, $erasedFields, $expected)
    {
        $bean = $this->getSugarBeanMock();
        $sut = $this->getErasedFieldsHandlerMock(['retrieveErasedFields']);

        $sut->expects($this->once())
            ->method('retrieveErasedFields')
            ->will($this->returnValue($erasedFields));

        $document = new Document();
        $document->setType($module);

        $sut->processDocumentPreIndex($document, $bean);
        $this->assertEquals($expected, $document->getData());
    }

    public function providerTestProcessDocumentPreIndex()
    {
        return [
            // string value
            [
                'Contacts',
                '["email_address","email_address_caps"]',
                ['erased_fields' => '["email_address","email_address_caps"]'],
            ],
            // array value
            [
                'Contacts',
                ['email_address', 'email_address_caps'],
                ['erased_fields' => ['email_address', 'email_address_caps']],
            ],
            // empty value
            [
                'Accounts',
                null,
                ['erased_fields' => null],
            ],
        ];
    }

    /**
     * @covers ::retrieveErasedFields
     *
     * @dataProvider providerTestRetrieveErasedFields
     */
    public function testRetrieveErasedFields($beanValue, $expected)
    {
        $bean = $this->getSugarBeanMock();
        $bean->erased_fields = $beanValue;
        $sut = $this->getErasedFieldsHandlerMock();
        $result = TestReflection::callProtectedMethod($sut, 'retrieveErasedFields', [$bean]);
        $this->assertSame($expected, $result);
    }

    public function providerTestRetrieveErasedFields()
    {
        return [
            [
                ['email_address_array', 'email_address_caps_array'],
                '["email_address_array","email_address_caps_array"]',
            ],
            // empty value
            [
                [],
                '[]',
            ],
        ];
    }

    /**
     * Get ErasedFieldsHandler Mock
     *
     * @param array $methods
     *
     * @return ErasedFieldsHandler
     */
    protected function getErasedFieldsHandlerMock(array $methods = [])
    {
        return $this->createPartialMock(ErasedFieldsHandler::class, $methods);
    }

    /**
     * Get SugarBean mock
     *
     * @return SugarBean
     */
    protected function getSugarBeanMock()
    {
        return $this->createMock(SugarBean::class);
    }
}
