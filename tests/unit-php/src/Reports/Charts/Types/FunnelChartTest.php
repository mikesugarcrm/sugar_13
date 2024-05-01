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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Charts\Types;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\Charts\ChartFactory;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\Charts\BaseChart
 */
class FunnelChartTest extends TestCase
{
    public function providerGetReportDef()
    {
        return [
            [
                'type' => 'funnelF',
                'data' => [
                    'properties' => [
                        [
                            'title' => 'Total is 517,910.33913656',
                            'subtitle' => '',
                            'type' => 'funnel chart 3D',
                            'legend' => 'on',
                            'labels' => 'value',
                            'print' => 'on',
                            'thousands' => '',
                            'base_module' => 'RevenueLineItems',
                            'label' => 'Pipeline Funnel (Revenue Line Items)',
                            'allow_drillthru' => '1',
                            'groupName' => 'Sales Stage',
                            'groupType' => 'string',
                            'xDataType' => 'ordinal',
                            'yDataType' => '',
                        ],
                    ],
                    'label' => [
                        'Prospecting',
                        'Qualification',
                        'Needs Analysis',
                        'Value Proposition',
                        'Id. Decision Makers',
                        'Perception Analysis',
                        'Proposal/Price Quote',
                        'Negotiation/Review',
                    ],
                    'color' => [
                        '#8c2b2b',
                        '#468c2b',
                        '#2b5d8c',
                        '#cd5200',
                        '#e6bf00',
                        '#7f3acd',
                        '#00a9b8',
                        '#572323',
                        '#004d00',
                        '#000087',
                        '#e48d30',
                        '#9fba09',
                        '#560066',
                        '#009f92',
                        '#b36262',
                        '#38795c',
                        '#3D3D99',
                        '#99623d',
                        '#998a3d',
                        '#994e78',
                        '#3d6899',
                        '#CC0000',
                        '#00CC00',
                        '#0000CC',
                        '#cc5200',
                        '#ccaa00',
                        '#6600cc',
                        '#005fcc',
                    ],
                    'values' => [
                        [
                            'label' => ['Prospecting'],
                            'values' => [94567.790706667],
                            'valuelabels' => ['$94,567.79'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Qualification'],
                            'values' => [76250.992492556],
                            'valuelabels' => ['$76,250.99'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Needs Analysis'],
                            'values' => [39935.349157667],
                            'valuelabels' => ['$39,935.35'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Value Proposition'],
                            'values' => [64756.554915556],
                            'valuelabels' => ['$64,756.55'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Id. Decision Makers'],
                            'values' => [51271.23807],
                            'valuelabels' => ['$51,271.24'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Perception Analysis'],
                            'values' => [45186.068608555],
                            'valuelabels' => ['$45,186.07'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Proposal/Price Quote'],
                            'values' => [90020.394512222],
                            'valuelabels' => ['$90,020.39'],
                            'links' => [''],
                        ],
                        [
                            'label' => ['Negotiation/Review'],
                            'values' => [55921.950673333],
                            'valuelabels' => ['$55,921.95'],
                            'links' => [''],
                        ],
                    ],
                ],
                'reportDef' => [
                    'display_columns' => [],
                    'module' => 'RevenueLineItems',
                    'group_defs' => [
                        0 => [
                            'name' => 'sales_stage',
                            'label' => 'Sales Stage',
                            'table_key' => 'self',
                            'type' => 'enum',
                            'force_label' => 'Sales Stage',
                        ],
                    ],
                    'summary_columns' => [
                        0 => [
                            'name' => 'sales_stage',
                            'label' => 'Sales Stage',
                            'table_key' => 'self',
                        ],
                        1 => [
                            'name' => 'likely_case',
                            'label' => 'Pipeline Amount ',
                            'field_type' => 'currency',
                            'group_function' => 'sum',
                            'table_key' => 'self',
                        ],
                    ],
                    'report_name' => 'Pipeline Funnel (Revenue Line Items)',
                    'chart_type' => 'funnelF',
                    'do_round' => 1,
                    'chart_description' => 'Pipeline Funnel (RLI)',
                    'numerical_chart_column' => 'self:likely_case:sum',
                    'numerical_chart_column_type' => '',
                    'assigned_user_id' => '1',
                    'report_type' => 'summary',
                    'full_table_list' => [
                        'self' => [
                            'value' => 'RevenueLineItems',
                            'module' => 'RevenueLineItems',
                            'label' => 'RevenueLineItems',
                        ],
                    ],
                    'filters_def' => [
                        'Filter_1' => [
                            'operator' => 'AND',
                            0 => [
                                'name' => 'commit_stage',
                                'table_key' => 'self',
                                'qualifier_name' => 'not_empty',
                                'runtime' => 1,
                                'input_name0' => 'not_empty',
                                'input_name1' => 'on',
                            ],
                            1 => [
                                'name' => 'sales_stage',
                                'table_key' => 'self',
                                'qualifier_name' => 'one_of',
                                'input_name0' => [
                                    0 => 'Prospecting',
                                    1 => 'Qualification',
                                    2 => 'Needs Analysis',
                                    3 => 'Value Proposition',
                                    4 => 'Id. Decision Makers',
                                    5 => 'Perception Analysis',
                                    6 => 'Proposal/Price Quote',
                                    7 => 'Negotiation/Review',
                                ],
                            ],
                            2 => [
                                'name' => 'date_closed',
                                'table_key' => 'self',
                                'qualifier_name' => 'tp_this_quarter',
                                'runtime' => 1,
                                'input_name0' => 'tp_this_quarter',
                                'input_name1' => 'on',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * getReportDef function
     * @dataProvider providerGetReportDef
     */
    public function testGetReportDef($type, $data, $reportDef)
    {
        $chart = ChartFactory::getChart($type, $data, $reportDef);
        $chart->setReportDef($reportDef);
        $this->assertInstanceOf(\Sugarcrm\Sugarcrm\Reports\Charts\Types\FunnelChart::class, $chart);
        $def = $chart->getReportDef();
        $this->assertSame($def, $reportDef);
    }

    /**
     * getOptions function
     * @dataProvider providerGetReportDef
     */
    public function testGetOptions($type, $data, $reportDef)
    {
        $chart = ChartFactory::getChart($type, $data, $reportDef);
        $chart->setReportDef($reportDef);
        $options = $chart->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('responsive', $options);
        $this->assertArrayHasKey('maintainAspectRatio', $options);
        $this->assertArrayHasKey('plugins', $options);
    }

    /**
     * transformData function
     * @dataProvider providerGetReportDef
     */
    public function testTransformData($type, $data, $reportDef)
    {
        $chart = ChartFactory::getChart($type, $data, $reportDef);
        $chart->setReportDef($reportDef);
        $data = $chart->transformData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
    }
}
