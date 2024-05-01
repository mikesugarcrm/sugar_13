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
use Sugarcrm\SugarcrmTestsUnit\modules\Reports\unformat_number;
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummation;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass ReportCSVExporterSummation
 */
class ReportCSVExporterSummationTest extends TestCase
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
     * @param array $headerRow The headers of the main table
     * @param array $dataRows Contains rows of data that Report::get_next_row() will return when called
     * @param array $totalHeaderRow The headers of the grand total table
     * @param array $totalData The rows of data in grand total table
     * @param string $expected The expected csv output
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummation::export
     * @dataProvider summationExportProvider
     */
    public function testExportSummation(
        array  $headerRow,
        array  $dataRows,
        array  $totalHeaderRow,
        array  $totalData,
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
                'getDataTypeForColumnsForMatrix',
            ]
        );

        $reporter->report_type = 'summary';

        $reporter->method('get_summary_header_row')
            ->willReturn($headerRow);

        $dataRows[] = 0;
        $reporter->method('get_next_row')
            ->willReturnOnConsecutiveCalls(...$dataRows);

        // don't mock out the individual methods in the implementation of grand total,
        // instead mock the grand total at the exporter level below

        // get mock
        $csvMaker = $this->createMockExporter($reporter);
        $csvMaker->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn("Grand Total\r\nThe Grand Total Goes Here");

        $actual = $csvMaker->export();
        $this->assertEquals($expected, $actual);
    }

    public function summationExportProvider()
    {
        $headerRow1 = ['Name', 'Universe', 'Total Property Owned'];
        $dataRows1 = [
            [
                'cells' => ['Iron Man', 'Marvel', '$12,400,000,000'],
            ],
            [
                'cells' => ['Batman', 'DC', '$9,200,000,000'],
            ],
            [
                'cells' => ['Superman', 'DC', '$2,400,000'],
            ],
        ];

        $totalHeaderRow1 = ['', 'Count'];
        $totalData1 = [
            [
                'cells' => ['', '3'],
            ],
        ];

        $expected1 = "\"Name\",\"Universe\",\"Total Property Owned\"\r\n" .
            "\"Iron Man\",\"Marvel\",\"$12,400,000,000\"\r\n" .
            "\"Batman\",\"DC\",\"$9,200,000,000\"\r\n" .
            "\"Superman\",\"DC\",\"$2,400,000\"\r\n" .
            "\r\n\r\n" .
            "Grand Total\r\n" .
            'The Grand Total Goes Here';

        return [
            [$headerRow1, $dataRows1, $totalHeaderRow1, $totalData1, $expected1],
        ];
    }

    public function createMockExporter(\Report $reporter)
    {
        $mockExporter = $this->createPartialMock(ReportCSVExporterSummation::class, ['getGrandTotal']);
        TestReflection::setProtectedValue($mockExporter, 'reporter', $reporter);
        return $mockExporter;
    }
}
