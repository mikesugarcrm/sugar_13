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
 * @covers RelateCollectionDefinition
 */
class RelateCollectionDefinitionTest extends TestCase
{
    /**
     * @var RelateCollectionDefinition
     */
    private $definition;

    protected function setUp(): void
    {
        $this->definition = $this->getMockBuilder('RelateCollectionDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['dummy'])
            ->getMockForAbstractClass();
    }

    public function testLoadDefinitionSuccess()
    {
        $fieldDef = [
            'type' => 'collection',
            'links' => [],
        ];

        $actual = $this->loadDefinition('test', $fieldDef);
        $this->assertEquals($fieldDef, $actual);
    }

    /**
     * @dataProvider loadDefinitionFailureProvider
     */
    public function testLoadDefinitionFailure($fieldDef)
    {
        $this->expectException(SugarApiExceptionNotFound::class);
        $this->loadDefinition('test', $fieldDef);
    }

    public static function loadDefinitionFailureProvider()
    {
        return [
            'non-array' => [
                null,
                'SugarApiExceptionNotFound',
            ],
            'non-collection' => [
                ['type' => 'varchar'],
                'SugarApiExceptionNotFound',
            ],
        ];
    }

    /**
     * @return SugarBean|MockObject
     */
    private function getCollectionDefinitionBeanMock($fieldName, $fieldDef)
    {
        /** @var SugarBean|MockObject $bean */
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldDefinition'])
            ->getMock();
        $bean->expects($this->once())
            ->method('getFieldDefinition')
            ->with($fieldName)
            ->will($this->returnValue($fieldDef));

        return $bean;
    }

    /**
     * @return SugarBean|MockObject
     */
    private function loadDefinition($fieldName, $fieldDef)
    {
        $bean = $this->getCollectionDefinitionBeanMock($fieldName, $fieldDef);

        SugarTestReflection::setProtectedValue($this->definition, 'name', $fieldName);
        SugarTestReflection::setProtectedValue($this->definition, 'bean', $bean);
        return SugarTestReflection::callProtectedMethod($this->definition, 'loadDefinition');
    }
}
