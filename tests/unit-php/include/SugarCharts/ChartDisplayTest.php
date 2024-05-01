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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarCharts;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ChartDisplay
 */
class ChartDisplayTest extends TestCase
{
    public function shouldUnformatProvider()
    {
        return [
            ['123.456', 'currency', false],
            ['123,456', 'currency', true],
            [123.456, 'currency', false],
            ['123.456', 'float', true],
            ['123,456', 'float', true],
            [123.456, 'float', false],
            ['123.456', 'integer', true],
            ['123,456', 'integer', true],
            [123456, 'integer', false],
        ];
    }

    /**
     * @covers       ::shouldUnformat
     * @dataProvider shouldUnformatProvider
     *
     * @param mixed $val
     * @param string $type
     * @param boolean $expected
     */
    public function testShouldUnformat($val, $type, $expected)
    {
        $chart = $this->createPartialMock(\ChartDisplay::class, []);

        $reporter = new \stdClass();
        $reporter->report_def = ['numerical_chart_column_type' => $type];
        TestReflection::setProtectedValue($chart, 'reporter', $reporter);

        $result = TestReflection::callProtectedMethod($chart, 'shouldUnformat', [$val]);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::sortByDataSeries
     * @dataProvider providerTestSortByDataSeries
     */
    public function testSortByDataSeries($data, $expectedData)
    {
        $chart = $this->createPartialMock(\ChartDisplay::class, []);
        TestReflection::setProtectedValue($chart, 'chartRows', $data['chartRows']);

        $reportDef = [
            'numerical_chart_column' => $data['numerical_chart_column'],
            'order_by' => $data['order_by'],
        ];

        $chart->sortByDataSeries($reportDef);

        $chartRows = TestReflection::getProtectedValue($chart, 'chartRows');
        $result = array_keys($chartRows);

        $this->assertEquals($result, $expectedData);
    }

    /**
     * Data for testSortByDataSeries
     */
    public function providerTestSortByDataSeries()
    {
        return [
            [
                [
                    'chartRows' => [
                        'Hospitality' => [
                            'sarah' => ['numerical_value' => 1],
                            'sally' => ['numerical_value' => 1],
                            'jim' => ['numerical_value' => 1],
                        ],
                        'Transportation' => [
                            'will' => ['numerical_value' => 1],
                            'max' => ['numerical_value' => 1],
                            'sarah' => ['numerical_value' => 2],
                        ],
                        'Media' => [
                            'sarah' => ['numerical_value' => 1],
                        ],
                        'Electronics' => [
                            'jim' => ['numerical_value' => 1],
                            'chris' => ['numerical_value' => 1],
                        ],
                        'Recreation' => [
                            'sally' => ['numerical_value' => 1],
                        ],
                        'Retail' => [
                            'sally' => ['numerical_value' => 1],
                            'sarah' => ['numerical_value' => 1],
                            'max' => ['numerical_value' => 2],
                        ],
                        'Environmental' => [
                            'sarah' => ['numerical_value' => 1],
                        ],
                        'Shipping' => [
                            'max' => ['numerical_value' => 1],
                        ],
                        'Energy' => [
                            'sarah' => ['numerical_value' => 7],
                        ],
                    ],
                    'numerical_chart_column' => 'self:count',
                    'order_by' => [
                        [
                            'name' => 'count',
                            'group_function' => 'count',
                            'table_key' => 'self',
                            'sort_dir' => 'a',
                            'column_key' => 'self:count',
                        ],
                    ],
                ],
                [
                    'Media',
                    'Recreation',
                    'Environmental',
                    'Shipping',
                    'Electronics',
                    'Hospitality',
                    'Transportation',
                    'Retail',
                    'Energy',
                ],
            ],
        ];
    }
}
