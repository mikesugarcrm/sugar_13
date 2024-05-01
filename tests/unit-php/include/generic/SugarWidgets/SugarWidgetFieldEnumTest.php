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

/** @noinspection PhpUndefinedFieldInspection */

namespace Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * Class SugarWidgetFieldEnumTest
 * @package Sugarcrm\SugarcrmTestsUnit\inc\generic\SugarWidgets
 * @coversDefaultClass \SugarWidgetFieldEnum
 */
class SugarWidgetFieldEnumTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Report|mixed
     */
    public $reporter;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\SugarWidgetFieldEnum|mixed
     */
    public $widgetField;

    protected function setUp(): void
    {
        $this->reporter = $this->createPartialMock(\Report::class, []);
        $db = $this->createPartialMock(\MysqliManager::class, ['convert', 'quoted', 'getIsNullSQL', 'getIsNotNullSQL']);
        $this->reporter->db = $db;
        $lm = $this->createPartialMock(\LayoutManager::class, []);
        $lm->setAttributePtr('reporter', $this->reporter);
        $this->widgetField = $this->createPartialMock(\SugarWidgetFieldEnum::class, ['getInputValue', 'logFatal']);
        $this->widgetField->layout_manager = $lm;
        TestReflection::setProtectedValue($this->widgetField, 'reporter', $this->reporter);
    }

    /**
     * @covers ::queryFilterEmpty
     */
    public function testQueryFilterEmpty()
    {
        $expected = "(coalesce(LENGTH(IFNULL(accounts.industry, 0)),0) = 0 OR IFNULL(accounts.industry, 0) = '^^')";
        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'empty',
            'input_name0' => 'empty',
            'input_name1' => 'on',
            'table_alias' => 'accounts',
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];

        $this->reporter->db->method('convert')
            ->withConsecutive(
                [
                    'accounts.industry',
                    'IFNULL',
                ],
                [
                    'IFNULL(accounts.industry, 0)',
                    'length',
                ]
            )
            ->will($this->onConsecutiveCalls(
                $this->returnCallback(function ($str) {
                    return "IFNULL($str, 0)";
                }),
                $this->returnCallback(function ($str) {
                    return "LENGTH($str)";
                })
            ));

        $filter = $this->widgetField->queryFilterEmpty($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilterNot_Empty
     */
    public function testQueryFilterNotEmpty()
    {
        $expected = "(coalesce(LENGTH(IFNULL(accounts.industry, 0)),0) > 0 AND IFNULL(accounts.industry, 0) != '^^' )";
        $expected .= "\n";

        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'not_empty',
            'input_name0' => 'not_empty',
            'input_name1' => 'on',
            'table_alias' => 'accounts',
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];

        $this->reporter->db->method('convert')
            ->withConsecutive(
                [
                    'accounts.industry',
                    'IFNULL',
                ],
                [
                    'IFNULL(accounts.industry, 0)',
                    'length',
                ]
            )
            ->will($this->onConsecutiveCalls(
                $this->returnCallback(function ($str) {
                    return "IFNULL($str, 0)";
                }),
                $this->returnCallback(function ($str) {
                    return "LENGTH($str)";
                })
            ));

        $filter = $this->widgetField->queryFilterNot_Empty($layoutDef);
        $this->assertEquals($expected, $filter);
    }


    /**
     * @covers ::queryFilteris
     */
    public function testQueryFilterIs()
    {
        $expected = "accounts.industry = \"Banking\"\n";
        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'is',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];
        $this->widgetField->expects($this->once())
            ->method('getInputValue')
            ->willReturnCallback(function ($def) {
                return $def['input_name0'][0];
            });
        $this->reporter->db->expects($this->once())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $filter = $this->widgetField->queryFilteris($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilteris_not
     */
    public function testQueryFilterIsNot()
    {
        $expected = 'accounts.industry <> "Banking" OR (accounts.industry IS NULL AND "Banking" IS NOT NULL)';
        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'is_not',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];
        $this->widgetField->expects($this->once())
            ->method('getInputValue')
            ->willReturnCallback(function ($def) {
                return $def['input_name0'][0];
            });
        $this->reporter->db->expects($this->once())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NULL";
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNotNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NOT NULL";
            });
        $filter = $this->widgetField->queryFilteris_not($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilterone_of
     */
    public function testQueryFilterOneOf()
    {
        $expected = "accounts.industry IN (\"Banking\",\"Technology\")\n";
        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'one_of',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking', 'Technology'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];
        $this->reporter->db->expects($this->exactly(2))
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $filter = $this->widgetField->queryFilterone_of($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * @covers ::queryFilternot_one_of
     */
    public function testQueryFilterNotOneOf()
    {
        $expected = "accounts.industry NOT IN (\"Banking\",\"Technology\") OR accounts.industry IS NULL\n";
        $layoutDef = $layoutDef = [
            'name' => 'industry',
            'table_key' => 'self',
            'qualifier_name' => 'not_one_of',
            'table_alias' => 'accounts',
            'input_name0' => ['Banking', 'Technology'],
            'column_key' => 'self:industry',
            'type' => 'enum',
        ];
        $this->reporter->db->expects($this->exactly(2))
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return '"' . $str . '"';
            });
        $this->reporter->db->expects($this->once())
            ->method('getIsNullSQL')
            ->willReturnCallback(function ($str) {
                return "$str IS NULL";
            });
        $filter = $this->widgetField->queryFilternot_one_of($layoutDef);
        $this->assertEquals($expected, $filter);
    }

    /**
     * Test getFieldControllerData method
     *
     * @param array $data
     * @param array $expectedData
     * @param bool $shouldThrownLog
     *
     * @dataProvider providerTestGetFieldControllerData
     * @covers ::getFieldControllerData
     */
    public function testGetFieldControllerData(array $data, array $expectedData, bool $shouldThrownLog)
    {
        $layoutDef = $data['layoutDef'];
        $columnKey = $data['columnKey'];
        $expected = $expectedData['value'];

        TestReflection::setProtectedValue(
            $this->reporter,
            'all_fields',
            [
                $columnKey => $layoutDef['fieldDef'],
            ]
        );

        if ($shouldThrownLog) {
            //it must call the logFatal method only in certain cases
            $this->widgetField->expects($this->once())
                ->method('logFatal')
                ->willReturn([]);
        }

        $cellValue = $this->widgetField->getFieldControllerData($layoutDef);

        $this->assertEquals($cellValue, $expected);
    }

    public function providerTestGetFieldControllerData()
    {
        return  [
            [
                [
                    'layoutDef' => [
                        'name' => 'status',
                        'label' => 'Status',
                        'table_key' => 'Accounts:calls',
                        'table_alias' => 'l1',
                        'fieldDef' => [
                            'name' => 'status',
                            'vname' => 'LBL_STATUS',
                            'type' => 'enum',
                            'len' => 100,
                            'options' => 'call_status_dom',
                            'default' => 'Planned',
                            'module' => 'Calls',
                            'real_table' => 'calls',
                            'rep_rel_name' => 'status_0',
                        ],
                        'type' => 'enum',
                        'fields' => [
                            'PRIMARYID' => 'd96a1e2c-8475-aaaa-b65d-acde48001122',
                            'ACCOUNTS_NAME' => 'SuperG Tech',
                            'L1_ID' => 'd9c2223e-8475-bbbb-b863-acde48001122',
                            'L1_STATUS' => 'Planned',
                            'L3_ID' => 'd96a1e2c-8475-aaaa-b65d-acde48001122',
                            'L3_NAME' => 'SuperG Tech',
                            'L4_ID' => 'seed_sally_id',
                            'L4_FIRST_NAME' => 'Sally',
                            'L4_LAST_NAME' => 'Bronsen',
                            'L4_TITLE' => 'Senior Account Rep',
                        ],
                        'module' => 'Calls',
                        'column_key' => 'Accounts:calls:status',
                    ],
                    'columnKey' => 'Accounts:calls:status',
                ],
                [
                    'value' => 'Planned',
                ],
                false,
            ],
            [
                [
                    'layoutDef' => [
                        'name' => 'status',
                        'label' => 'Status',
                        'table_key' => 'Accounts:calls',
                        'table_alias' => 'l1',
                        'fieldDef' => [
                            'name' => 'status',
                            'type' => 'enum',
                            'len' => 100,
                            'default' => 'Planned',
                            'module' => 'Calls',
                        ],
                        'type' => 'enum',
                        'module' => 'Calls',
                        'column_key' => 'Accounts:calls:status',
                    ],
                    'columnKey' => 'Accounts:calls:status',
                ],
                [
                    'value' => '',
                ],
                false,
            ],
            [
                [
                    'layoutDef' => [
                        'name' => 'status',
                        'label' => 'Status',
                        'table_key' => 'Accounts:calls',
                        'table_alias' => 'l1',
                        'fieldDef' => [
                            'name' => 'status',
                            'type' => 'enum',
                            'len' => 100,
                            'default' => 'Planned',
                            'module' => 'Calls',
                            'function' => [
                                'name' => 'parseShorthandBytes',
                                'params' => [
                                    0 => 'test',
                                ],
                            ],
                        ],
                        'type' => 'enum',
                        'module' => 'Calls',
                        'column_key' => 'Accounts:calls:status',
                    ],
                    'columnKey' => 'Accounts:calls:status',
                ],
                [
                    'value' => null,
                ],
                true,
            ],
        ];
    }
}
