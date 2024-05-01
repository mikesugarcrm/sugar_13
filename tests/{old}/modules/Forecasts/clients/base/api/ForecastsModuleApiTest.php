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

class ForecastsModuleApiTest extends TestCase
{
    /**
     * @var ForecastsModuleApi
     */
    protected $api;

    /**
     * @var ServiceBase
     */
    protected $serviceMock;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setTimePeriod(SugarTestTimePeriodUtilities::createTimePeriod());
    }

    protected function setUp(): void
    {
        $this->api = new ForecastsModuleApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::setTimePeriod(null);
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for create record test
     *
     * @return array
     */
    public function createRecordDataProvider()
    {
        $expectedSalesRep = [
            'timeperiod_id' => 0,
            'amount' => 0,
            'best_case' => 0,
            'worst_case' => 0,
            'overall_amount' => 0,
            'overall_best' => 0,
            'overall_worst' => 0,
            'lost_count' => 0,
            'lost_amount' => 0,
            'lost_best' => 0,
            'lost_worst' => 0,
            'won_count' => 0,
            'won_amount' => 0,
            'won_best' => 0,
            'won_worst' => 0,
            'included_opp_count' => 0,
            'total_opp_count' => 0,
            'includedClosedCount' => 0,
            'includedClosedAmount' => 0,
            'includedClosedBest' => 0,
            'includedClosedWorst' => 0,
            'pipeline_amount' => 0,
            'pipeline_opp_count' => 0,
            'likely_case' => 0,
            'closed_amount' => 0,
        ];
        $expectedManager = [
            'quota' => 0,
            'best_case' => 0,
            'best_adjusted' => 0,
            'likely_case' => 0,
            'likely_adjusted' => 0,
            'worst_case' => 0,
            'worst_adjusted' => 0,
            'included_opp_count' => 0,
            'pipeline_opp_count' => 0,
            'pipeline_amount' => 0,
            'closed_amount' => 0,
        ];

        return [
            [
                [
                    'forecast_type' => 'Direct',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => 0,
                ],
                $expectedSalesRep,
            ],
            [
                [
                    'forecast_type' => 'direct',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => 0,
                ],
                $expectedSalesRep,
            ],
            [
                [
                    'forecast_type' => 'Rollup',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => 0,
                ],
                $expectedSalesRep,
            ],
            [
                [
                    'forecast_type' => 'rollup',
                    'commit_type' => 'sales_rep',
                    'timeperiod_id' => 0,
                ],
                $expectedSalesRep,
            ],
            [
                [
                    'forecast_type' => 'Direct',
                    'commit_type' => 'manager',
                    'timeperiod_id' => 0,
                ],
                $expectedManager,
            ],
            [
                [
                    'forecast_type' => 'direct',
                    'commit_type' => 'manager',
                    'timeperiod_id' => 0,
                ],
                $expectedManager,
            ],
            [
                [
                    'forecast_type' => 'Rollup',
                    'commit_type' => 'manager',
                    'timeperiod_id' => 0,
                ],
                $expectedManager,
            ],
            [
                [
                    'forecast_type' => 'rollup',
                    'commit_type' => 'manager',
                    'timeperiod_id' => 0,
                ],
                $expectedManager,
            ],
        ];
    }

    /**
     * Test creating record.
     *
     * @dataProvider createRecordDataProvider
     */
    public function testCreateRecord($args, $expected)
    {
        $timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();
        if (array_key_exists('timeperiod_id', $expected)) {
            $expected['timeperiod_id'] = $timeperiod->id;
        }
        if (array_key_exists('timeperiod_id', $args)) {
            $args['timeperiod_id'] = $timeperiod->id;
        }
        $result = $this->api->createRecord($this->serviceMock, $args);

        $this->assertNotEmpty($result);

        foreach ($expected as $expectedKey => $expectedValue) {
            $this->assertArrayHasKey($expectedKey, $result);
            $this->assertEquals($expectedValue, $result[$expectedKey]);
        }
    }
}
