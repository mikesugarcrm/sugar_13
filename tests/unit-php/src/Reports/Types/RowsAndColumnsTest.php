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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Types;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Reports\Types\RowsAndColumns
 */
class RowsAndColumnsTest extends TestCase
{
    /**
     * @covers ::getFilterData
     */
    public function testGetFilterData()
    {
        $mockReporter = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\RowsAndColumns::class);

        $this->assertNotEmpty($mockReporter->getFilterData());
    }

    /**
     * @covers ::getOrderBy
     * @dataProvider providerTestGetOrderBy
     */
    public function testGetOrderBy($inputData, $expectedData)
    {
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\RowsAndColumns::class);
        $mockReport = $this->getReporterMock('\Report');

        $mockReport->report_def['order_by'][] = $inputData;

        $orderBy = TestReflection::callProtectedMethod($mockSummary, 'getOrderBy', [$mockReport]);
        $this->assertSame($orderBy, $expectedData);
    }

    /**
     * Data for testGetOrderBy
     */
    public function providerTestGetOrderBy()
    {
        return [
            [
                [
                    'sort_dir' => 'a',
                    'name' => 'test_name',
                    'type' => 'test_type',
                    'table_key' => 'test_table_key',
                ],
                [
                    [
                        'sort_dir' => 'a',
                        'name' => 'test_name',
                        'type' => 'test_type',
                        'table_key' => 'test_table_key',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::getHeader
     * @dataProvider providerTestGetHeader
     */
    public function testGetHeader($data, $expectedData)
    {
        $mockedMethods = ['getFieldDefFromLayoutDef', 'getTableFromField', '_get_full_key'];
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\RowsAndColumns::class);
        $mockReport = $this->getReporterMock('\Report', $mockedMethods);
        $mockReport->report_def['display_columns'] = $data['display_columns'];

        $mockReport->expects($this->any())
            ->method('getFieldDefFromLayoutDef')
            ->willReturn($data['getFieldDefFromLayoutDef']);

        $mockReport->expects($this->any())
            ->method('getTableFromField')
            ->willReturn($data['getTableFromField']);

        $mockReport->expects($this->any())
            ->method('_get_full_key')
            ->willReturn($data['_get_full_key']);

        $header = TestReflection::callProtectedMethod($mockSummary, 'getHeader', [$mockReport]);

        $this->assertEqualsCanonicalizing($header, $expectedData);
    }

    /**
     * Data for testGetHeader
     */
    public function providerTestGetHeader()
    {
        return [
            [
                [
                    'display_columns' => [
                        [
                            'table_key' => 't1',
                        ],
                        [
                            'table_key' => 't2',
                        ],
                    ],
                    'getFieldDefFromLayoutDef' => [
                        'vname' => 'testVname',
                        'value' => 'testValue',
                    ],
                    'getTableFromField' => 'test_table',
                    '_get_full_key' => 'test_table_key',
                ],
                [
                    [
                        'vname' => 'testVname',
                        'value' => 'testValue',
                        'table_alias' => 'test_table',
                        'column_key' => 'test_table_key',
                        'table_key' => 't1',
                    ],
                    [
                        'vname' => 'testVname',
                        'value' => 'testValue',
                        'table_alias' => 'test_table',
                        'column_key' => 'test_table_key',
                        'table_key' => 't2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::formatRow
     * @dataProvider providerFormatRow
     */
    public function testFormatRow($data, $expectedData)
    {
        $mockedReportMethods = ['getFieldDefFromLayoutDef', 'getTableFromField', '_get_full_key'];
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\RowsAndColumns::class, ['resolveCustomField']);
        $mockReport = $this->getReporterMock('\Report', $mockedReportMethods);
        $mockSugarWidgetReportField = $this->getReporterMock('\SugarWidgetReportField', ['getSidecarFieldData']);
        $mockReport->report_def['display_columns'] = $data['display_columns'];

        $mockSummary->expects($this->any())
            ->method('resolveCustomField')
            ->willReturn('');

        $mockSugarWidgetReportField->expects($this->any())
            ->method('getSidecarFieldData')
            ->willReturn('field value');

        $mockReport->expects($this->any())
            ->method('getFieldDefFromLayoutDef')
            ->willReturnOnConsecutiveCalls($data['getFieldDefFromLayoutDef'][0], $data['getFieldDefFromLayoutDef'][1]);

        $mockReport->expects($this->any())
            ->method('getTableFromField')
            ->willReturn($data['getTableFromField']);

        $mockReport->expects($this->any())
            ->method('_get_full_key')
            ->willReturn($data['_get_full_key']);

        $header = TestReflection::callProtectedMethod(
            $mockSummary,
            'formatRow',
            [
                $mockReport,
                [],
                $mockSugarWidgetReportField,
            ]
        );

        $this->assertEqualsCanonicalizing($header, $expectedData);
    }

    /**
     * Data for testFormatRow
     */
    public function providerFormatRow()
    {
        return [
            [
                [
                    'display_columns' => [
                        [
                            'table_key' => 't1',
                        ],
                        [
                            'table_key' => 't2',
                        ],
                    ],
                    'getFieldDefFromLayoutDef' => [
                        [
                            'name' => 'street',
                            'type' => 'varchar',
                            'module' => 'Accounts',
                        ],
                        [
                            'name' => 'city',
                            'type' => 'varchar',
                            'module' => 'Contacts',
                        ],
                    ],
                    'getTableFromField' => 'test_table',
                    '_get_full_key' => 'test_table_key',
                ],
                [
                    [
                        'type' => 'varchar',
                        'module' => 'Accounts',
                        'name' => 'street',
                        'value' => 'field value',
                    ],
                    [
                        'type' => 'varchar',
                        'module' => 'Contacts',
                        'name' => 'city',
                        'value' => 'field value',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $mockPath
     * @param null|array $methods
     * @return \Summary
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
