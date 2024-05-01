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
     * @covers       ReportCSVExporterMatrix1x1::export
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

        $expected1 = "\"\",\"Area\",\"\",\"\",\"\"\r\n" .
            "\"Games\",\"Asia\",\"Europe\",\"America\",\"Grand Total\"\r\n" .
            "\"CS:GO\",\"1,000\",\"1,000\",\"1,000\",\"1000\"\r\n" .
            "\"\",\"100\",\"100\",\"100\",\"300\"\r\n" .
            "\"\",\"1,000\",\"1,000\",\"1,000\",\"3000\"\r\n" .
            "\"\",\"10\",\"10\",\"10\",\"10\"\r\n" .
            "\"\",\"10,000\",\"10,000\",\"10,000\",\"10000\"\r\n" .
            "\"Grand Total\",\"1000\",\"1000\",\"1000\",\"1000\"\r\n" .
            "\"\",\"100\",\"100\",\"100\",\"300\"\r\n" .
            "\"\",\"1000\",\"1000\",\"1000\",\"3000\"\r\n" .
            "\"\",\"10\",\"10\",\"10\",\"10\"\r\n" .
            "\"\",\"10000\",\"10000\",\"10000\",\"10000\"\r\n";

        return [
            [$layoutOption1, $detailHeaders1, $groupDefs1, $dataRows1, $expected1],
        ];
    }

    /**
     * to test the case when a label is changed
     */
    public function testExportMatrixWithLabelChange()
    {
        // label "Alternate Phone" is changed to "XYZ" in summary_columns
        $report_def_str = '{"display_columns":[],"module":"Accounts","group_defs":[{"name":"phone_alternate","label":"Alternate Phone","table_key":"self","type":"phone"},{"name":"annual_revenue","label":"Annual Revenue","table_key":"self","type":"varchar"}],"summary_columns":[{"name":"phone_alternate","label":"XYZ","table_key":"self"},{"name":"annual_revenue","label":"Annual Revenue","table_key":"self"},{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"test3","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"1","report_type":"summary","layout_options":"2x2","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts"}},"filters_def":{"Filter_1":{"operator":"AND"}}}';
        $reporter = new Report($report_def_str);
        $reporter->report_type = 'summary';
        $reporter->plain_text_output = true;
        $reporter->enable_paging = false;

        $csvMaker = new ReportExporter($reporter);
        // export should succeed abd should contain the changed label
        $this->assertContains('XYZ', $csvMaker->export());
    }
}
