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

class NumericExpressionTest extends TestCase
{
    /**
     * @dataProvider dataProviderTestIsCurrencyField
     * @param array $def
     * @param boolean $expected
     */
    public function testIsCurrencyField($def, $expected)
    {
        /* @var $bean Opportunity|MockObject */
        $bean = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'getFieldDefinition'])
            ->disableOriginalConstructor()
            ->getMock();

        $bean->expects($this->once())
            ->method('getFieldDefinition')
            ->will($this->returnValue($def));

        $numeric_expression = $this->getMockBuilder('NumericExpression')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $return = SugarTestReflection::callProtectedMethod(
            $numeric_expression,
            'isCurrencyField',
            [
                $bean,
                'test_field',
            ]
        );

        $this->assertEquals($expected, $return);
    }

    public static function dataProviderTestIsCurrencyField()
    {
        return [
            [
                [
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                    'custom_type' => 'currency',
                ],
                true,
            ],
            [
                [
                    'type' => 'decimal',
                    'dbType' => 'currency',
                ],
                true,
            ],
            [
                [
                    'type' => 'currency',
                ],
                true,
            ],
            [
                [
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                    'custom_type' => 'decimal',
                ],
                false,
            ],
            [
                [
                    'type' => 'decimal',
                    'dbType' => 'decimal',
                ],
                false,
            ],
            [
                [
                    'type' => 'decimal',
                ],
                false,
            ],
        ];
    }
}
