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
use Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass ReportExporter
 */
class ReportExporterTest extends TestCase
{
    protected function setUp(): void
    {
        global $current_user;

        // to setup Delimiter
        $current_user = $this->createPartialMock('User', ['getPreference']);

        $preference_map = [
            ['export_delimiter', ','],
            ['currency', '-99'],
        ];

        $current_user->expects($this->any())
            ->method('getPreference')
            ->will($this->returnValueMap($preference_map));
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['current_user']);
    }

    /**
     * @param string $reportType Report Type
     * @param string $layoutOptions Reporter's layout options
     * @param string $format output format
     * @param string $exporterClass expected exporter class
     * @covers       Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter::__construct
     * @dataProvider reportExporterProvider
     */
    public function testReportExporter(
        string $reportType,
        string $layoutOptions,
        string $format,
        string $exporterClass
    ) {

        $reporter = $this->createPartialMock('\Report', ['getReportType']);
        $reporter->method('getReportType')
            ->willReturn($reportType);

        $reporter->report_def = ['layout_options' => $layoutOptions];
        $reporter->report_type = $reportType;

        $exporter = $this->getMockBuilder(\Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportExporter::class)
            ->setConstructorArgs([$reporter, $format])
            ->getMock();

        // member variable $exporter should be an instance of the correct exporter class
        $this->assertInstanceOf(
            $exporterClass,
            TestReflection::getProtectedValue($exporter, 'exporter')
        );
    }

    public function reportExporterProvider()
    {
        return [
            [
                'summary',
                '',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummation::class,
            ],
            [
                'detailed_summary',
                '',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterSummationWithDetails::class,
            ],
            [
                'tabular',
                '',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterRowsAndColumns::class,
                '',
            ],
            [
                'Matrix',
                '2x2',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix1x1::class,
            ],
            [
                'Matrix',
                '1x2',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix1x2::class,
            ],
            [
                'Matrix',
                '2x1',
                'CSV',
                \Sugarcrm\Sugarcrm\modules\Reports\Exporters\ReportCSVExporterMatrix2x1::class,
            ],
        ];
    }
}
