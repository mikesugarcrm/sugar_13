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

/**
 * @coversDefaultClass ForecastMetricsApi
 */
class ForecastMetricsApiTest extends TestCase
{
    public const TOP_LEVEL_MANAGER = 'fb9de8cc-1111-1111-1111-acde48001122';
    public const MID_LEVEL_MANAGER = 'fb9de8cc-2222-2222-2222-acde48001122';
    public const SALES_REP = 'fb9de8cc-3333-3333-3333-acde48001122';
    public const TIME_PERIOD = 'fb9de8cc-4444-4444-4444-acde48001122';

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        self::createTestUsers();
        self::createTestTimePeriods();
        self::createTestOpportunities();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @covers ::getMetrics
     * @dataProvider providerTestGetMetrics
     */
    public function testGetMetrics($args, $expected)
    {
        $mockService = $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->getMock();

        $mockApi = new ForecastMetricsApi();

        $actual = $mockApi->getMetrics($mockService, $args)['metrics'];
        $this->assertEquals($expected, $actual);
    }

    /**
     * Provider for testGetMetrics
     *
     * @return array[]
     */
    public function providerTestGetMetrics()
    {
        return [
            [
                [
                    'module' => 'Opportunities',
                    'filter' => [
                        [
                            'name' => [
                                '$starts' => 'testOpp',
                            ],
                        ],
                    ],
                    'user_id' => self::TOP_LEVEL_MANAGER,
                    'type' => 'Rollup',
                    'time_period' => self::TIME_PERIOD,
                    'metrics' => [
                        [
                            'name' => 'testMetric',
                            'sum_fields' => ['amount'],
                            'filter' => [
                                [
                                    'description' => [
                                        '$equals' => 'testDescription',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'testMetric' => [
                        'name' => 'testMetric',
                        'values' => [
                            'sum' => 5464,
                            'count' => 2,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Creates User hierarchy
     */
    protected static function createTestUsers()
    {
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::TOP_LEVEL_MANAGER,
        ]);
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::MID_LEVEL_MANAGER,
            'reports_to_id' => self::TOP_LEVEL_MANAGER,
        ]);
        SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'id' => self::SALES_REP,
            'reports_to_id' => self::MID_LEVEL_MANAGER,
        ]);
    }

    /**
     * Creates time periods
     */
    protected static function createTestTimePeriods()
    {
        SugarTestTimePeriodUtilities::createTimePeriod('2022-01-01', '2022-04-01', null, null, [
            'id' => self::TIME_PERIOD,
        ]);
    }

    /**
     * Creates Opportunities with attached RLI(s) that are assigned to various
     * test Users
     */
    protected static function createTestOpportunities()
    {
        $opp1 = SugarTestOpportunityUtilities::createOpportunity(null, null, [
            'name' => 'testOpp1',
            'assigned_user_id' => self::TOP_LEVEL_MANAGER,
            'description' => 'testDescription',
            'amount' => 123,
            'date_closed' => '2022-02-03',
        ]);
        $opp2 = SugarTestOpportunityUtilities::createOpportunity(null, null, [
            'name' => 'testOpp2',
            'assigned_user_id' => self::MID_LEVEL_MANAGER,
            'description' => 'differentDescription',
            'amount' => 3425,
            'date_closed' => '2022-04-03',
        ]);
        $opp3 = SugarTestOpportunityUtilities::createOpportunity(null, null, [
            'name' => 'testOpp3',
            'assigned_user_id' => self::SALES_REP,
            'description' => 'testDescription',
            'amount' => 5341,
            'date_closed' => '2022-01-03',
        ]);
        $opp4 = SugarTestOpportunityUtilities::createOpportunity(null, null, [
            'name' => 'differentName',
            'assigned_user_id' => self::SALES_REP,
            'description' => 'testDescription',
            'amount' => 68171,
            'date_closed' => '2022-03-03',
        ]);

        // If the instance is in Revenue Line Items mode, the amount and
        // expected close date of the Opportunities are determined by RLIs
        $settings = Opportunity::getSettings();
        if (isset($settings['opps_view_by']) && $settings['opps_view_by'] === 'RevenueLineItems') {
            $opp1->load_relationship('revenuelineitems');
            $opp1->revenuelineitems->add(SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
                'likely_case' => 123,
                'date_closed' => '2022-02-03',
            ]));

            $opp2->load_relationship('revenuelineitems');
            $opp2->revenuelineitems->add(SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
                'likely_case' => 3425,
                'date_closed' => '2022-04-03',
            ]));

            $opp3->load_relationship('revenuelineitems');
            $opp3->revenuelineitems->add(SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
                'likely_case' => 5341,
                'date_closed' => '2022-01-03',
            ]));

            $opp4->load_relationship('revenuelineitems');
            $opp4->revenuelineitems->add(SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
                'likely_case' => 68171,
                'date_closed' => '2022-03-03',
            ]));
        }
    }
}
