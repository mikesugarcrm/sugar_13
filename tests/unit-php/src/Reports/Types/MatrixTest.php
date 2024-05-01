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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Reports\Types\Matrix
 */
class MatrixTest extends TestCase
{
    /**
     * @covers ::getFilterData
     */
    public function testGetFilterData()
    {
        $mockReporter = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Matrix::class);

        $this->assertNotEmpty($mockReporter->getFilterData());
    }

    /**
     * @covers ::createHeader
     * @dataProvider providerTestCreateHeader
     */
    public function testCreateHeader($data, $expectedData)
    {
        $mockedReportMethods = [];
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Matrix::class);
        $mockReport = $this->getReporterMock('\Report', $mockedReportMethods);

        $mockReport->report_def['group_defs'] = $data['group_defs'];

        $result = TestReflection::callProtectedMethod(
            $mockSummary,
            'createHeader',
            [
                $mockReport,
                $data['headerRow'],
                $data['layoutOptions'],
                $data['columnDataForSecondGroup'],
                $data['columnDataForThirdGroup'],
            ]
        );

        $this->assertEqualsCanonicalizing($result, $expectedData);
    }

    /**
     * Data for test createHeader
     */
    public function providerTestCreateHeader()
    {
        return [
            [
                [
                    'group_defs' => [
                        [
                            'name' => 'date_closed',
                            'label' => 'Quarter: Expected Close Date',
                            'column_function' => 'quarter',
                            'qualifier' => 'quarter',
                            'table_key' => 'self',
                            'type' => 'date',
                        ],
                        [
                            'name' => 'product_type',
                            'label' => 'Type',
                            'table_key' => 'self',
                            'type' => 'enum',
                        ],
                    ],
                    'headerRow' => [
                        'Quarter: Expected Close Date',
                        'Type',
                        'SUM: Best',
                        'AVG: Best',
                        'LBL_COUNT_LC',
                    ],
                    'layoutOptions' => '2x2',
                    'columnDataForSecondGroup' => [
                        '',
                        'Existing Business',
                        'New Business',
                    ],
                    'columnDataForThirdGroup' => null,
                ],
                [
                    [
                        'Quarter: Expected Close Date',
                        'Type',
                        'Grand Total',
                    ],
                    [
                        '',
                        'Existing Business',
                        'New Business',
                    ],
                ],
            ],
            [
                [
                    'group_defs' => [
                        [
                            'name' => 'date_closed',
                            'label' => 'Quarter: Expected Close Date',
                            'column_function' => 'quarter',
                            'qualifier' => 'quarter',
                            'table_key' => 'self',
                            'type' => 'date',
                        ],
                        [
                            'name' => 'user_name',
                            'label' => 'User Name',
                            'table_key' => 'RevenueLineItems:assigned_user_link',
                            'type' => 'username',
                        ],
                        [
                            'name' => 'product_type',
                            'label' => 'Type',
                            'table_key' => 'self',
                            'type' => 'enum',
                        ],
                    ],
                    'headerRow' => [
                        'Quarter: Expected Close Date',
                        'User Name',
                        'Type',
                        'SUM: Best',
                        'AVG: Best',
                        'LBL_COUNT_LC',
                    ],
                    'layoutOptions' => '1x2',
                    'columnDataForSecondGroup' => [
                        'chris',
                        'jim',
                        'max',
                        'sally',
                        'sarah',
                        'will',
                    ],
                    'columnDataForThirdGroup' => [
                        '',
                        'Existing Business',
                        'New Business',
                    ],
                ],
                [
                    [
                        'Quarter: Expected Close Date',
                        'User Name',
                        'Grand Total',
                    ],
                    [
                        'chris', 'jim', 'max', 'sally', 'sarah', 'will',
                    ],
                    [
                        'Type',
                    ],
                    [
                        '',
                        'Existing Business',
                        'New Business',
                    ],
                ],
            ],
        ];
    }


    /**
     * @covers ::setGroupDefsInfo
     * @dataProvider providerTestSetGroupDefsInfo
     */
    public function testSetGroupDefsInfo($data, $expectedData)
    {
        $mockedReportMethods = [
            'get_summary_header_row',
        ];

        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Matrix::class);
        $mockReport = $this->getReporterMock('\Report', $mockedReportMethods);

        $mockReport->group_defs_Info = null;
        $mockReport->report_def['group_defs'] = $data['group_defs'];
        $mockReport->report_def['summary_columns'] = $data['summary_columns'];

        $mockReport->expects($this->any())
            ->method('get_summary_header_row')
            ->willReturn([]);

        $this->assertSame($mockReport->group_defs_Info, null);

        TestReflection::callProtectedMethod($mockSummary, 'setGroupDefsInfo', [$mockReport]);

        $this->assertEqualsCanonicalizing($mockReport->group_defs_Info, $expectedData);
    }

    /**
     * Data for test setGroupDefsInfo
     */
    public function providerTestSetGroupDefsInfo()
    {
        return [
            [
                [
                    'group_defs' => [
                        [
                            'name' => 'date_closed',
                            'label' => 'Quarter: Expected Close Date',
                            'column_function' => 'quarter',
                            'qualifier' => 'quarter',
                            'table_key' => 'self',
                            'type' => 'date',
                        ],
                        [
                            'name' => 'user_name',
                            'label' => 'User Name',
                            'table_key' => 'RevenueLineItems:assigned_user_link',
                            'type' => 'username',
                        ],
                        [
                            'name' => 'product_type',
                            'label' => 'Type',
                            'table_key' => 'self',
                            'type' => 'enum',
                        ],
                    ],
                    'summary_columns' => [
                        [
                            'name' => 'date_closed',
                            'label' => 'Quarter: Expected Close Date',
                            'column_function' => 'quarter',
                            'qualifier' => 'quarter',
                            'table_key' => 'self',
                        ],
                        [
                            'name' => 'user_name',
                            'label' => 'User Name',
                            'table_key' => 'RevenueLineItems:assigned_user_link',
                        ],
                        [
                            'name' => 'product_type',
                            'label' => 'Type',
                            'table_key' => 'self',
                        ],
                        [
                            'name' => 'best_case',
                            'label' => 'SUM: Best',
                            'field_type' => 'currency',
                            'group_function' => 'sum',
                            'table_key' => 'self',
                        ],
                        [
                            'name' => 'best_case',
                            'label' => 'AVG: Best',
                            'field_type' => 'currency',
                            'group_function' => 'avg',
                            'table_key' => 'self',
                        ],
                        [
                            'name' => 'count',
                            'label' => 'LBL_COUNT_LC',
                            'field_type' => 'currency',
                            'group_function' => 'count',
                            'table_key' => 'self',
                        ],
                    ],
                ],
                [
                    'date_closed#self#Quarter: Expected Close Date' => [
                        'name' => 'date_closed',
                        'label' => 'Quarter: Expected Close Date',
                        'column_function' => 'quarter',
                        'qualifier' => 'quarter',
                        'table_key' => 'self',
                        'type' => 'date',
                        'index' => 0,
                    ],
                    'user_name#RevenueLineItems:assigned_user_link#User Name' => [
                        'name' => 'user_name',
                        'label' => 'User Name',
                        'table_key' => 'RevenueLineItems:assigned_user_link',
                        'type' => 'username',
                        'index' => 1,
                    ],
                    'product_type#self#Type' => [
                        'name' => 'product_type',
                        'label' => 'Type',
                        'table_key' => 'self',
                        'type' => 'enum',
                        'index' => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param null|array $methods
     * @return \Matrix
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
