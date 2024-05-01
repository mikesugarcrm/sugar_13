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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarFields\Fields\Base;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;

/**
 * @coversDefaultClass \SugarFieldBase
 */
class SugarFieldBaseTest extends TestCase
{
    /**
     * @covers ::getNormalizedDefs
     *
     * @dataProvider providerTestGetNormalizedDefs
     */
    public function testGetNormalizedDefs($vardef, $expected)
    {
        $testBean = TestMockHelper::getObjectMock($this, 'SugarFieldBase');
        $this->assertSame($expected, $testBean->getNormalizedDefs($vardef, null));
    }

    public function providerTestGetNormalizedDefs()
    {
        return [
            [
                [
                    'name' => 'false values',
                    'audited' => 0,
                    'pii' => '0',
                    'exportable' => false,
                    'massupdate' => '0',
                    'readonly' => 'false',
                    'required' => 0,
                    'sortable' => 'false',
                    'not_in_list' => 0,
                ],
                [
                    'name' => 'false values',
                    'audited' => false,
                    'pii' => false,
                    'exportable' => false,
                    'massupdate' => false,
                    'readonly' => false,
                    'required' => false,
                    'sortable' => false,
                    'not_in_list' => 0,
                ],
            ],
            [
                [
                    'name' => 'true value',
                    'audited' => 1,
                    'pii' => '1',
                    'exportable' => true,
                    'massupdate' => '1',
                    'readonly' => 'true',
                    'required' => 1,
                    'sortable' => 'true',
                    'not_in_list' => 'true',
                ],
                [
                    'name' => 'true value',
                    'audited' => true,
                    'pii' => true,
                    'exportable' => true,
                    'massupdate' => true,
                    'readonly' => true,
                    'required' => true,
                    'sortable' => true,
                    'not_in_list' => 'true',
                ],
            ],
        ];
    }

    /**
     * data provider for testApiValidateFieldSize
     */
    public function apiValidateFieldSizeProvider()
    {
        return [
            ['body_html', 'html', 5, 'body', true],
            ['body_text', 'text', 5, 'body body', false],
            ['body_html', 'text', -1, 'body body', true],
        ];
    }

    /**
     * @covers ::apiValidateFieldSize
     * @dataProvider apiValidateFieldSizeProvider
     */
    public function testApiValidateFieldSize($name, $type, $size, $value, $expected)
    {
        $mockBean = $this->createMock(\SugarBean::class);
        $mockDb = TestMockHelper::getMockForAbstractClass(
            $this,
            '\\DBManager',
            [
                'getMaxFieldSize',
            ]
        );
        $mockDb->method('getMaxFieldSize')->willReturn($size);
        $mockBean->db = $mockDb;
        $vardef = ['name' => $name, 'type' => $type];
        $params = [$name => $value];
        $field = \SugarFieldHandler::getSugarField($type);
        $this->assertEquals($expected, $field->apiValidateFieldSize($mockBean, $params, $name, $vardef));
    }

    public function trimmingFieldsProvider()
    {
        return [
            ['qwe', ['len' => 8, 'type' => 'varchar'], 'qwe'],
            [' qwe ', ['len' => 8, 'type' => 'varchar'], 'qwe'],
            [' qwe ', ['len' => 8, 'type' => 'name'], 'qwe'],
            [123, ['len' => 8, 'type' => 'varchar'], '123'],
            [123.45, ['len' => 8, 'type' => 'varchar'], '123.45'],
            [[], ['len' => 8, 'type' => 'varchar'], null],
            [false, ['len' => 8, 'type' => 'varchar'], null],
            [' 123 ', ['len' => 8, 'type' => 'id'], ' 123 '],
            [' 123 ', ['type' => 'name'], ' 123 '],
        ];
    }

    /**
     * @covers ::apiSave
     * @dataProvider trimmingFieldsProvider
     *
     * @param $value
     * @param $properties
     * @param $expected
     * @return void
     */
    public function testTrimmingFields($value, $properties, $expected): void
    {
        $mockBean = $this->createMock(\SugarBean::class);
        /** @var \SugarFieldBase $mockField */
        $mockField = $this->createPartialMock(\SugarFieldBase::class, []);

        $params = ['testfield' => $value];
        $mockField->apiSave($mockBean, $params, 'testfield', $properties);
        $this->assertSame($expected, $mockBean->testfield);
    }
}
