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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports\Exporters;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass ReportCSVExporterMatrix
 */
class ReportCSVExporterMatrixTest extends TestCase
{
    protected function setUp(): void
    {
        global $current_user;

        // to setup Delimiter
        $current_user = $this->createPartialMock('User', ['getPreference']);

        $preferenceMap = [
            ['export_delimiter', ','],
            ['currency', '-99'],
        ];

        $current_user->expects($this->any())
            ->method('getPreference')
            ->will($this->returnValueMap($preferenceMap));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @param string $layoutOptions
     * @param array $expectedSubType
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::getSubTypeExporter
     * @dataProvider matrixSubTypeProvider
     */
    public function testGetSubTypeExporter(string $layoutOptions, string $expectedSubType)
    {
        $mock = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $reporter = $this->createPartialMock('\Report', [
            'get_summary_header_row',
            'get_summary_next_row',
        ]);

        $reporter->report_def = ['layout_options' => $layoutOptions];

        $this->assertEquals($expectedSubType, $mock->getSubTypeExporter($reporter));
    }

    public function matrixSubTypeProvider()
    {
        return [
            ['2x2', '1x1'],
            ['1x2', '1x2'],
            ['2x1', '2x1'],
        ];
    }

    /**
     * @param string $layoutOptions
     * @param array $expectedOutput
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::getLayoutOptions
     * @dataProvider matrixLayoutOptionsProvider
     */
    public function testGetLayoutOptions(string $layoutOptions, array $expectedOutput)
    {
        $mock = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        // don't need a real reporter here
        $reporter = new \stdClass();
        $reporter->report_def = ['layout_options' => $layoutOptions];
        TestReflection::setProtectedValue($mock, 'reporter', $reporter);

        $this->assertEquals($expectedOutput, TestReflection::callProtectedMethod($mock, 'getLayoutOptions'));
    }

    public function matrixLayoutOptionsProvider()
    {
        return [
            ['2x2', ['1', '1']],
            ['1x2', ['1', '2']],
            ['2x1', ['2', '1']],
        ];
    }

    /**
     * @param string $layoutOption
     * @param array $detailHeaders
     * @param array $groupDefs
     * @param array $dataRows
     * @param array $expectedTrie
     * @param array $expectedColumnHeaders
     * @param array $expectedRowHeaders
     * @param array $expectedCleanHeaders
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::matrixTrieBuilder
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::columnHeaderDictionary
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::rowHeaderDictionary
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::matrixCleanUpHeaders
     * @dataProvider matrixExportProvider
     */
    public function testMatrixFunctions(
        string $layoutOption,
        array  $detailHeaders,
        array  $groupDefs,
        array  $dataRows,
        array  $expectedTrie,
        array  $expectedColumnHeaders,
        array  $expectedRowHeaders,
        array  $expectedCleanHeaders
    ) {

        $reporter = $this->createPartialMock(
            '\Report',
            [
                'run_summary_query',
                'run_summary_combo_query',
                'run_total_query',
                '_load_currency',
                'get_summary_header_row',
                'get_total_header_row',
                'get_next_row',
                'get_summary_total_row',
                'get_summary_next_row',
                'get_header_row',
                'getDataTypeForColumnsForMatrix',
            ]
        );

        $reporter->report_def = [
            'layout_options' => $layoutOption,
            'group_defs' => $groupDefs,
        ];

        $headers = [];
        foreach ($detailHeaders as $detail_header) {
            $headers[] = $detail_header['label'];
        }

        $reporter->method('get_summary_header_row')
            ->willReturn($headers);

        $dataCount = count($dataRows);
        $reporter->expects($this->any())
            ->method('get_summary_next_row')
            ->willReturnCallback(function () use (&$dataCount, $dataRows) {
                if ($dataCount > 0) {
                    $dataCount--;
                    return $dataRows[count($dataRows) - $dataCount - 1];
                }

                return 0;
            });

        // get mock
        $csvMaker = $this->getMockBuilder('\\' . \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        TestReflection::setProtectedValue($csvMaker, 'reporter', $reporter);

        // test trie
        $trie = TestReflection::callProtectedMethod($csvMaker, 'matrixTrieBuilder');
        $this->assertEquals($expectedTrie, $trie);

        // trie property is needed for the tests below
        TestReflection::setProtectedValue($csvMaker, 'trie', $trie);

        // test columnHeaderDictionary
        $this->assertEquals($expectedColumnHeaders, TestReflection::callProtectedMethod($csvMaker, 'columnHeaderDictionary'));

        // test rowHeaderDictionary
        $this->assertEquals($expectedRowHeaders, TestReflection::callProtectedMethod($csvMaker, 'rowHeaderDictionary'));

        // test matrixCleanUpHeaders
        $this->assertEquals($expectedCleanHeaders, TestReflection::callProtectedMethod($csvMaker, 'matrixCleanUpHeaders'));
    }

    public function matrixExportProvider()
    {
        // 1x1 matrix
        $layoutOption1 = '2x2';

        $detailHeaders1 = [
            'Games' => [
                'label' => 'Games',
                'type' => 'nothing',
            ],
            'Area' => [
                'label' => 'Area',
                'type' => 'nothing',
            ],
            'Min' => [
                'group_function' => 'min',
                'label' => 'Min',
                'type' => 'nothing',
            ],
            'Count' => [
                'group_function' => 'count',
                'label' => 'Count',
                'type' => 'nothing',
            ],
            'Sum' => [
                'group_function' => 'sum',
                'label' => 'Sum',
                'type' => 'nothing',
            ],
            'AVG' => [
                'group_function' => 'avg',
                'label' => 'AVG',
                'type' => 'nothing',
            ],
            'Max' => [
                'group_function' => 'max',
                'label' => 'Max',
                'type' => 'nothing',
            ],
        ];

        $groupDefs1 = [
            [
                'label' => 'Games',
            ],
            [
                'label' => 'Area',
            ],
        ];

        $dataRows1 = [
            [
                'cells' => ['CS:GO', 'Asia', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'Europe', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'America', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ],
        ];

        $expectedTrie1 = [
            'Games' => [
                'CS:GO' => [
                    'Area' => [
                        'Asia' => [
                            [
                                'cells' => [
                                    'Min' => '1,000',
                                    'Count' => '100',
                                    'Sum' => '1,000',
                                    'AVG' => '10',
                                    'Max' => '10,000',
                                ],
                                'count' => 1,
                                'Count' => 1,
                            ],
                        ],
                        'Europe' => [
                            [
                                'cells' => [
                                    'Min' => '1,000',
                                    'Count' => '100',
                                    'Sum' => '1,000',
                                    'AVG' => '10',
                                    'Max' => '10,000',
                                ],
                                'count' => 1,
                                'Count' => 1,
                            ],
                        ],
                        'America' => [
                            [
                                'cells' => [
                                    'Min' => '1,000',
                                    'Count' => '100',
                                    'Sum' => '1,000',
                                    'AVG' => '10',
                                    'Max' => '10,000',
                                ],
                                'count' => 2,
                                'Count' => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expectedColumnHeaders1 = ['Asia', 'Europe', 'America'];

        $expectedRowHeaders1 = ['CS:GO'];

        $expectedCleanHeaders1 = ['Min', 'Count', 'Sum', 'AVG', 'Max'];

        // 1x2 matrix
        $layoutOption2 = '1x2';

        $detailHeaders2 = [
            'Games' => [
                'label' => 'Games',
                'type' => 'nothing',
            ],
            'Area' => [
                'label' => 'Area',
                'type' => 'nothing',
            ],
            'Time' => [
                'label' => 'Time',
                'type' => 'nothing',
            ],
            'Min' => [
                'group_function' => 'min',
                'label' => 'Min',
                'type' => 'nothing',
            ],
            'Count' => [
                'group_function' => 'count',
                'label' => 'Count',
                'type' => 'nothing',
            ],
            'Sum' => [
                'group_function' => 'sum',
                'label' => 'Sum',
                'type' => 'nothing',
            ],
            'AVG' => [
                'group_function' => 'avg',
                'label' => 'AVG',
                'type' => 'nothing',
            ],
            'Max' => [
                'group_function' => 'max',
                'label' => 'Max',
                'type' => 'nothing',
            ],
        ];

        $groupDefs2 = [
            [
                'label' => 'Games',
            ],
            [
                'label' => 'Area',
            ],
            [
                'label' => 'Time',
            ],
        ];

        $dataRows2 = [
            [
                'cells' => ['CS:GO', 'Asia', 'Day', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'Asia', 'Night', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'Europe', 'Day', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'Europe', 'Night', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['CS:GO', 'America', 'Day', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ],
            [
                'cells' => ['CS:GO', 'America', 'Night', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ],
        ];

        $expectedTrie2 = [
            'Games' => [
                'CS:GO' => [
                    'Area' => [
                        'Asia' => [
                            'Time' => [
                                'Day' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                                'Night' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                        'Europe' => [
                            'Time' => [
                                'Day' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                                'Night' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                        'America' => [
                            'Time' => [
                                'Day' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 2,
                                        'Count' => 2,
                                    ],
                                ],
                                'Night' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 2,
                                        'Count' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $expectedColumnHeaders2 = [
            [
                'Asia' => 1,
                'Europe' => 1,
                'America' => 1,
            ],
            [
                'Day' => 1,
                'Night' => 1,
            ],
        ];

        $expectedRowHeaders2 = ['CS:GO'];

        $expectedCleanHeaders2 = ['Min', 'Count', 'Sum', 'AVG', 'Max'];

        // 2x1 matrix
        $layoutOption3 = '2x1';

        $detailHeaders3 = [
            'Area' => [
                'label' => 'Area',
                'type' => 'nothing',
            ],
            'Time' => [
                'label' => 'Time',
                'type' => 'nothing',
            ],
            'Games' => [
                'label' => 'Games',
                'type' => 'nothing',
            ],
            'Min' => [
                'group_function' => 'min',
                'label' => 'Min',
                'type' => 'nothing',
            ],
            'Count' => [
                'group_function' => 'count',
                'label' => 'Count',
                'type' => 'nothing',
            ],
            'Sum' => [
                'group_function' => 'sum',
                'label' => 'Sum',
                'type' => 'nothing',
            ],
            'AVG' => [
                'group_function' => 'avg',
                'label' => 'AVG',
                'type' => 'nothing',
            ],
            'Max' => [
                'group_function' => 'max',
                'label' => 'Max',
                'type' => 'nothing',
            ],
        ];

        $groupDefs3 = [
            [
                'label' => 'Area',
            ],
            [
                'label' => 'Time',
            ],
            [
                'label' => 'Games',
            ],
        ];

        $dataRows3 = [
            [
                'cells' => ['Asia', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['Asia', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['Europe', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['Europe', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 1,
            ],
            [
                'cells' => ['America', 'Day', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ],
            [
                'cells' => ['America', 'Night', 'CS:GO', '1,000', '100', '1,000', '10', '10,000'],
                'count' => 2,
            ],
        ];

        $expectedTrie3 = [
            'Area' => [
                'Asia' => [
                    'Time' => [
                        'Day' => [
                            'Games' => [
                                'CS:GO' => [
                                    '0' => [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                        'Night' => [
                            'Games' => [
                                'CS:GO' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'Europe' => [
                    'Time' => [
                        'Day' => [
                            'Games' => [
                                'CS:GO' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                        'Night' => [
                            'Games' => [
                                'CS:GO' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 1,
                                        'Count' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'America' => [
                    'Time' => [
                        'Day' => [
                            'Games' => [
                                'CS:GO' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 2,
                                        'Count' => 2,
                                    ],
                                ],
                            ],
                        ],
                        'Night' => [
                            'Games' => [
                                'CS:GO' => [
                                    [
                                        'cells' => [
                                            'Min' => '1,000',
                                            'Count' => '100',
                                            'Sum' => '1,000',
                                            'AVG' => '10',
                                            'Max' => '10,000',
                                        ],
                                        'count' => 2,
                                        'Count' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];


        $expectedColumnHeaders3 = ['CS:GO'];

        $expectedRowHeaders3 = [
            [
                'Asia' => 1,
                'Europe' => 1,
                'America' => 1,
            ],
            [
                'Day' => 1,
                'Night' => 1,
            ],
        ];

        $expectedCleanHeaders3 = ['Min', 'Count', 'Sum', 'AVG', 'Max'];

        return [
            [
                $layoutOption1,
                $detailHeaders1,
                $groupDefs1,
                $dataRows1,
                $expectedTrie1,
                $expectedColumnHeaders1,
                $expectedRowHeaders1,
                $expectedCleanHeaders1,
            ],
            [
                $layoutOption2,
                $detailHeaders2,
                $groupDefs2,
                $dataRows2,
                $expectedTrie2,
                $expectedColumnHeaders2,
                $expectedRowHeaders2,
                $expectedCleanHeaders2,
            ],
            [
                $layoutOption3,
                $detailHeaders3,
                $groupDefs3,
                $dataRows3,
                $expectedTrie3,
                $expectedColumnHeaders3,
                $expectedRowHeaders3,
                $expectedCleanHeaders3,
            ],
        ];
    }
}
