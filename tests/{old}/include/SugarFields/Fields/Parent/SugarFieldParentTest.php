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

/**
 * Class SugarFieldParentTest
 *
 * Test cases for SugarFieldParent class
 */
class SugarFieldParentTest extends TestCase
{
    public function sugarFieldParentDataProvider()
    {
        return [
            [
                [], [], 'Custom', [], [], $this->createMock('ServiceBase'),
            ],
        ];
    }

    /**
     * @dataProvider sugarFieldParentDataProvider
     */
    public function testFormatFieldNonExistingParentType($data, $args, $fieldName, $properties, $fieldList, $service)
    {
        $sugarField = $this->getMockBuilder('SugarFieldParent')
            ->setMethods(['ensureApiFormatFieldArguments'])
            ->disableOriginalConstructor()
            ->getMock();
        $bean = $this->createMock('SugarBean');
        $bean->parent_type = 'NonExistingClass';
        $sugarField->expects(static::once())
            ->method('ensureApiFormatFieldArguments')
            ->with($fieldList, $service);
        $sugarField->apiFormatField($data, $bean, $args, $fieldName, $properties, $fieldList, $service);
        static::assertEquals([], $data['parent']);
    }
}
