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
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportJSONExporterRowsAndColumns;

/**
 * @coversDefaultClass ReportJSONExporterRowsAndColumns
 */
class ReportJSONExporterRowsAndColumnsTest extends TestCase
{
    /**
     * @param array $headerRow The headers of the main table
     * @param array $dataRows Contains rows of data that Report::get_next_row() will return when called
     * @param string $expected The expected json output
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportJSONExporterRowsAndColumns::export
     * @dataProvider rowsAndColumnsExportProvider
     */
    public function testExportRowsAndColumns(
        array  $headerRow,
        array  $dataRows,
        string $expected
    ) {

        $reporter = $this->createPartialMock(
            '\Report',
            [
                'run_summary_query',
                'run_query',
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

        $reporter->report_type = 'tabular';

        $reporter->method('get_header_row')
            ->willReturn($headerRow);

        $dataRows[] = 0; // end of data

        $reporter->method('get_next_row')
            ->willReturnOnConsecutiveCalls(...$dataRows);

        $jsonMaker = new ReportJSONExporterRowsAndColumns($reporter);

        $this->assertEquals($expected, $jsonMaker->export());
    }

    public function rowsAndColumnsExportProvider()
    {
        $headerRow1 = ['Name', 'Universe', 'Total Property Owned'];
        $dataRows1 = [
            [
                'cells' => ['Iron Man', 'Marvel', '$12,400,000,000'],
            ],
            [
                'cells' => ['Bat Man', 'DC', '$9,200,000,000'],
            ],
            [
                'cells' => ['Superman', 'DC', '$2,400,000'],
            ],
        ];

        $expected1 = '[{"Name":"Iron Man","Universe":"Marvel","Total Property Owned":"$12,400,000,000"},' .
            '{"Name":"Bat Man","Universe":"DC","Total Property Owned":"$9,200,000,000"},' .
            '{"Name":"Superman","Universe":"DC","Total Property Owned":"$2,400,000"}]';

        $headerRow2 = ['Due Date', 'Subject', 'Status', 'Priority', 'Assigned To'];
        $dataRows2 = [
            [
                'cells' => ['04/17/2021 06:45am', 'Close out support request', 'Deferred', 'High', 'Chris Olliver'],
            ],
            [
                'cells' => ['04/20/2021 01:30am', 'Send literature', 'Deferred', 'Low', 'Jim Brennan'],
            ],
        ];

        $expected2 = '[{"Due Date":"04\/17\/2021 06:45am","Subject":"Close out support request","Status":"Deferred",' .
            '"Priority":"High","Assigned To":"Chris Olliver"},{"Due Date":"04\/20\/2021 01:30am",' .
            '"Subject":"Send literature","Status":"Deferred","Priority":"Low","Assigned To":"Jim Brennan"}]';

        return [
            [$headerRow1, $dataRows1, $expected1],
            [$headerRow2, $dataRows2, $expected2],
        ];
    }
}
