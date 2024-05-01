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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Reports\Types\SummaryDetails
 */
class SummaryDetailsTest extends TestCase
{
    /**
     * @covers ::getFilterData
     */
    public function testGetFilterData()
    {
        $mockReporter = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\SummaryDetails::class);

        $this->assertNotEmpty($mockReporter->getFilterData());
    }

    /**
     * @covers ::generateData
     * @dataProvider providerTestGenerateData
     */
    public function testGenerateData($data, $expectedData)
    {
        $mockedReportMethods = [
            'getFieldDefFromLayoutDef',
            'getTableFromField',
            '_get_full_key',
            'run_summary_combo_query',
            'fixGroupLabels',
            'get_summary_header_row',
            'get_summary_next_row',
            'get_next_row',
            '_load_currency',
        ];

        $mockedSumamryDetails = [
            'getSummaryNextRow',
        ];

        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\SummaryDetails::class, $mockedSumamryDetails);
        $mockReport = $this->getReporterMock('\Report', $mockedReportMethods);

        $mockSummary->expects($this->any())
            ->method('getSummaryNextRow')
            ->willReturn(0);

        $mockReport->report_def['group_defs'] = $data['group_defs'];
        $mockReport->report_def['summary_columns'] = $data['summary_columns'];
        $mockReport->report_def['display_columns'] = $data['display_columns'];
        $mockReport->report_def['order_by'] = [];

        $mockReport->expects($this->any())
            ->method('getFieldDefFromLayoutDef')
            ->willReturnOnConsecutiveCalls(
                $data['getFieldDefFromLayoutDef'][0],
                $data['getFieldDefFromLayoutDef'][1]
            );

        $mockReport->expects($this->any())
            ->method('getTableFromField')
            ->willReturnOnConsecutiveCalls(
                $data['getTableFromField'][0],
                $data['getTableFromField'][1]
            );

        $mockReport->expects($this->any())
            ->method('_get_full_key')
            ->willReturnOnConsecutiveCalls(
                $data['_get_full_key'][0],
                $data['_get_full_key'][1]
            );

        $mockReport->expects($this->any())
            ->method('run_summary_combo_query');

        $mockReport->expects($this->any())
            ->method('fixGroupLabels');

        $mockReport->expects($this->any())
            ->method('get_summary_header_row')
            ->willReturn($data['_get_full_key']);

        $mockReport->expects($this->any())
            ->method('fixGroupLabels')
            ->willReturn([]);

        $mockReport->expects($this->any())
            ->method('get_summary_next_row')
            ->willReturn(0);

        $mockReport->expects($this->any())
            ->method('_load_currency');

        $mockReport->expects($this->any())
            ->method('get_next_row')
            ->willReturn($data['get_next_row']);

        $result = TestReflection::callProtectedMethod($mockSummary, 'generateData', [$mockReport]);

        $this->assertSame($result['header'], $expectedData['header']);
        $this->assertSame($result['grandTotal'], $expectedData['grandTotal']);
        $this->assertSame($result['groups'], $expectedData['groups']);
    }

    /**
     * Data for test generateData
     */
    public function providerTestGenerateData()
    {
        return [
            [
                [
                    'group_defs' => [
                        [
                            'name' => 'name',
                            'label' => 'Primary Team Name',
                            'table_key' => 'self',
                            'type' => 'name',
                            'force_label' => 'Primary Team Name',
                        ],
                    ],
                    'summary_columns' => [
                        [
                            'name' => 'name',
                            'label' => 'label',
                            'table_key' => 'self',
                        ],
                        [
                            'name' => 'count',
                            'label' => 'Count',
                            'field_type' => '',
                            'group_function' => 'count',
                            'table_key' => 'self',
                        ],
                    ],
                    'display_columns' => [
                        [
                            'name' => 'date_entered',
                            'label' => 'Date Entered',
                            'table_key' => 'Teams:users',
                        ],
                        [
                            'name' => 'full_name',
                            'label' => 'Reports To',
                            'table_key' => 'Teams:users:reports_to_link',
                        ],
                    ],
                    'getFieldDefFromLayoutDef' => [
                        [
                            'vname' => 'LBL_DATE_ENTERED',
                            'type' => 'datetime',
                            'module' => 'Users',
                        ],
                        [
                            'vname' => 'LBL_NAME',
                            'type' => 'fullname',
                            'module' => 'Users',
                        ],
                    ],
                    'getTableFromField' => [
                        'l1',
                        'l1',
                    ],
                    '_get_full_key' => [
                        'Teams:users:date_entered',
                        'Teams:users:full_name',
                    ],
                    'get_next_row' => [
                        'cells' => [
                            [
                                'type' => 'int',
                                'name' => 'count',
                                'vname' => 'Count',
                                'value' => '13',
                            ],
                        ],
                        'count' => '1',
                    ],
                ],
                [
                    'header' => [
                        [
                            'vname' => 'LBL_DATE_ENTERED',
                            'type' => 'datetime',
                            'module' => 'Users',
                            'table_alias' => 'l1',
                            'column_key' => 'Teams:users:date_entered',
                            'isvNameTranslated' => false,
                        ],
                        [
                            'vname' => 'Reports To',
                            'type' => 'fullname',
                            'module' => 'Users',
                            'table_alias' => 'l1',
                            'column_key' => 'Teams:users:full_name',
                            'isvNameTranslated' => true,
                        ],
                    ],
                    'grandTotal' => [
                        [
                            'type' => 'int',
                            'name' => 'count',
                            'vname' => 'Count',
                            'value' => '13',
                            'isvNameTranslated' => true,
                        ],
                    ],
                    'groups' => [],
                    'order_by' => [],
                ],
            ],
        ];
    }

    /**
     * @param null|array $methods
     * @return \SummaryDetails
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
