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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Charts;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\Charts\ConfigBuilder;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\Charts\ConfigBuilder
 */
class ConfigBuilderTest extends TestCase
{
    public function providerBuild()
    {
        return [
            [
                'chartData' => [
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
            ],
        ];
    }

    /**
     * test build
     *
     * @dataProvider providerBuild
     */
    public function testBuild($chartData)
    {
        $mockReport = $this->getReporterMock('\Report');
        $builder = new ConfigBuilder($chartData, $mockReport);
        $builder->build();
        $config = $builder->getConfig();

        $this->assertIsArray($config);
    }

    /**
     * @param string $mockPath
     * @param null|array $methods
     * @return \Report
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
