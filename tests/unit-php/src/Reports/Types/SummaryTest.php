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
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Reports\Types\Summary
 */
class SummaryTest extends TestCase
{
    /**
     * @covers ::getFilterData
     */
    public function testGetFilterData()
    {
        $mockReporter = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Summary::class);

        $this->assertNotEmpty($mockReporter->getFilterData());
    }

    /**
     * @covers ::getOrderBy
     * @dataProvider providerTestGetOrderBy
     */
    public function testGetOrderBy($summaryOrderBy, $expectedData)
    {
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Summary::class);
        $mockReport = $this->getReporterMock('\Report');

        $mockReport->report_def['summary_order_by'][] = $summaryOrderBy;

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
                ],
                [
                    [
                        'sort_dir' => 'a',
                        'name' => 'test_name',
                        'type' => 'test_type',
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::getSummaryGrandTotal
     * @dataProvider providerTestGetSummaryGrandTotal
     */
    public function testGetSummaryGrandTotal($data, $expectedData)
    {
        $mockedMethods = ['run_total_query', '_load_currency', 'get_next_row', 'getFieldDefFromLayoutDef'];
        $mockSummary = $this->getReporterMock(\Sugarcrm\Sugarcrm\Reports\Types\Summary::class);
        $mockReport = $this->getReporterMock('\Report', $mockedMethods);

        $mockReport->expects($this->any())
            ->method('run_total_query')
            ->willReturn('');

        $mockReport->expects($this->any())
            ->method('_load_currency')
            ->willReturn('');

        $mockReport->expects($this->any())
            ->method('getFieldDefFromLayoutDef')
            ->willReturn($data['getFieldDefFromLayoutDef']);

        $mockReport->expects($this->any())
            ->method('get_next_row')
            ->willReturn($data['get_next_row']);

        $records = TestReflection::callProtectedMethod($mockSummary, 'getSummaryGrandTotal', [$mockReport]);

        $this->assertSame($records, $expectedData);
    }

    /**
     * Data for testGetSummaryGrandTotal
     */
    public function providerTestGetSummaryGrandTotal()
    {
        return [
            [
                [
                    'getFieldDefFromLayoutDef' => [
                        'vname' => 'testVname',
                    ],
                    'get_next_row' => [
                        'cells' => [
                            [
                                'name' => 'Chloe',
                                'value' => '101.33',
                                'vname' => 'testVname',
                                'isvNameTranslated' => true,
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'name' => 'Chloe',
                        'value' => '101.33',
                        'vname' => 'testVname',
                        'isvNameTranslated' => true,
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
