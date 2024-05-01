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
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter;

class ReportCSVExporterMatrix1x1Test extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $GLOBALS['current_user']->setPreference('export_delimiter', ',');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param string $layoutOption The layout option of the matrix, can be 2x2, 1x2 or 2x1
     * @param array $detailHeaders The detailed header information of the data
     * @param array $groupDefs The group definition of this matrix
     * @param array $dataRows The data, in rows
     * @param string $expected The expected csv output
     * @covers       ReportCSVExporterMatrix1x2::export
     * @covers       ReportCSVExporterBase::getReportType
     * @dataProvider matrixProvider
     */
    public function testExportMatrix(
        string $layoutOption,
        array  $detailHeaders,
        array  $groupDefs,
        array  $dataRows,
        string $expected
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
                'getReportType',
                'getDataTypeForColumnsForMatrix',
            ]
        );

        $reporter->report_type = 'summary';
        $reporter->report_def = [
            'layout_options' => $layoutOption,
            'group_defs' => $groupDefs,
        ];

        $reporter->method('getDataTypeForColumnsForMatrix')
            ->willReturn($detailHeaders);

        $headers = [];
        foreach ($detailHeaders as $detail_header) {
            $headers[] = $detail_header['label'];
        }

        $reporter->method('get_summary_header_row')
            ->willReturn($headers);

        $reporter->method('getReportType')
            ->willReturn('Matrix');


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

        include_once 'modules/Currencies/Currency.php';
        $csvMaker = new ReportExporter($reporter);

        $this->assertEquals($expected, $csvMaker->export());
    }

    public function matrixProvider()
    {
        // 1x2 matrix
        $layoutOption1 = '1x2';

        $detailHeaders1 = [
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

        $groupDefs1 = [
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

        $dataRows1 = [
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

        $expected1 = "\"\",\"Area\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"\r\n" .
            "\"\",\"Asia\",\"\",\"\",\"Europe\",\"\",\"\",\"America\",\"\",\"\",\"\"\r\n" .
            "\"\",\"Time\",\"\",\"\",\"Time\",\"\",\"\",\"Time\",\"\",\"\",\"\"\r\n" .
            "\"Games\",\"Day\",\"Night\",\"Total\",\"Day\",\"Night\",\"Total\",\"Day\",\"Night\",\"Total\",\"Grand Total\"\r\n" .
            "\"CS:GO\",\"1,000\",\"1,000\",\"1000\",\"1,000\",\"1,000\",\"1000\",\"1,000\",\"1,000\",\"1000\",\"1000\"\r\n" .
            "\"\",\"100\",\"100\",\"200\",\"100\",\"100\",\"200\",\"100\",\"100\",\"200\",\"600\"\r\n" .
            "\"\",\"1,000\",\"1,000\",\"2000\",\"1,000\",\"1,000\",\"2000\",\"1,000\",\"1,000\",\"2000\",\"6000\"\r\n" .
            "\"\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\"\r\n" .
            "\"\",\"10,000\",\"10,000\",\"10000\",\"10,000\",\"10,000\",\"10000\",\"10,000\",\"10,000\",\"10000\",\"10000\"\r\n" .
            "\"Grand Total\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\",\"1000\"\r\n" .
            "\"\",\"100\",\"100\",\"200\",\"100\",\"100\",\"200\",\"100\",\"100\",\"200\",\"600\"\r\n" .
            "\"\",\"1000\",\"1000\",\"2000\",\"1000\",\"1000\",\"2000\",\"1000\",\"1000\",\"2000\",\"6000\"\r\n" .
            "\"\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\",\"10\"\r\n" .
            "\"\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\",\"10000\"\r\n";

        return [
            [$layoutOption1, $detailHeaders1, $groupDefs1, $dataRows1, $expected1],
        ];
    }
}
