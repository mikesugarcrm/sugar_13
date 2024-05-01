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
 * @coversDefaultClass OpportunitiesApi
 */
class OpportunitiesApiTest extends TestCase
{
    /**
     * @var \RestService|mixed
     */
    public $service;
    /**
     * @var OpportunitiesApi
     */
    protected $api;

    protected function setUp(): void
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new OpportunitiesApi();
    }

    public static function setUpBeforeClass(): void
    {
        SugarTestForecastUtilities::setUpForecastConfig([
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        Opportunity::$settings = [];
    }

    /**
     * @covers ::updateRecord
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_OppportunityMode_DoesNotUpdateRLIs()
    {
        $args = [];
        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];

        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;

        $api = $this->createPartialMock('OpportunitiesApi', ['updateRevenueLineItems', 'loadBean']);
        $api->expects($this->never())
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $api->updateRecord($this->service, $args);
    }

    public function dataProviderPutOpportunity_OppportunityRliMode_UpdateRLIs()
    {
        return [
            // sales stage, date closed
            [
                [
                    'sales_stage' => 'Proposed',
                    'date_closed' => '2019-01-01',
                    'other_field' => 'test',
                ],
                0,
            ],
            // sales stage
            [
                [
                    'sales_stage' => 'Proposed',
                    'other_field' => 'test',
                ],
                0,
            ],
            // date closed
            [
                [
                    'date_closed' => '2019-01-01',
                    'other_field' => 'test',
                ],
                0,
            ],
            // other fields
            [
                [
                    'other_field' => 'test',
                ],
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderPutOpportunity_OppportunityRliMode_UpdateRLIs
     *
     * @covers ::updateRecord
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_OppportunityRliMode_UpdateRLIs($args, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;

        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];

        $api = $this->createPartialMock('OpportunitiesApi', ['updateRevenueLineItems', 'loadBean']);
        $api->expects($this->exactly($expected))
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $api->updateRecord($this->service, $args);
    }

    /**
     * @covers ::updateRecord
     * @covers ::updateRevenueLineItems
     */
    public function testPutOpportunity_UpdateRevenueLineItems_CalledWithCorrectArgs()
    {
        $args = [];
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->sales_stage = 'Closed Lost';

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;
        $args['sales_stage'] = 'Prospecting';
        $args['date_closed'] = '2019-01-01';

        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];

        $api = $this->createPartialMock('OpportunitiesApi', ['updateRevenueLineItems', 'loadBean']);
        $api->expects($this->never())
            ->method('updateRevenueLineItems');
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $api->updateRecord($this->service, $args);
    }

    public function dataProviderUpdateRevenueLineItems()
    {
        return [
            [
                ['sales_stage' => 'Closed Won', 'date_closed' => '2017-05-05'],
                ['sales_stage' => 'Closed Won', 'date_closed' => '2017-05-05'],
            ],
            [
                ['sales_stage' => 'Closed Lost', 'date_closed' => '2017-06-06'],
                ['sales_stage' => 'Closed Lost', 'date_closed' => '2017-06-06'],
            ],
            [
                ['sales_stage' => 'Prospecting', 'date_closed' => '2017-02-02'],
                ['sales_stage' => 'Qualification', 'date_closed' => '2017-01-01'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderUpdateRevenueLineItems
     *
     * @covers ::updateRevenueLineItems
     */
    public function testUpdateRevenueLineItems_RlisUpdated($args, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->save();

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->opportunity_id = $opp->id;
        $rli->sales_stage = $args['sales_stage'];
        $rli->date_closed = $args['date_closed'];
        $rli->save();

        $data = [
            'sales_stage' => 'Qualification',
            'date_closed' => '2017-01-01',
        ];

        SugarTestReflection::callProtectedMethod(
            $this->api,
            'updateRevenueLineItems',
            [
                $opp,
                $data,
            ]
        );

        $rli = BeanFactory::retrieveBean('RevenueLineItems', $rli->id);

        $this->assertSame($rli->sales_stage, $expected['sales_stage']);
        $this->assertSame($rli->date_closed, $expected['date_closed']);
    }


    /**
     * @dataProvider dataProviderIsValidServiceStartDate
     *
     * @covers ::isValidServiceStartDate
     */
    public function testIsValidServiceStartDate($args, $sales_stage, $add_on_to_id, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity('my_opp_id');
        $opp->save();

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem('my_rli_id');
        $rli->opportunity_id = $opp->id;
        $rli->sales_stage = $sales_stage;
        $rli->service = '1';
        $rli->service_start_date = '2020-01-01';
        $rli->service_end_date = '2020-01-31';
        $rli->service_duration_value = '1';
        $rli->service_duration_unit = 'month';
        $rli->add_on_to_id = $add_on_to_id;
        $rli->save();

        $args['module'] = 'Opportunities';
        $args['record'] = $opp->id;

        $api = $this->createPartialMock('OpportunitiesApi', ['loadBean']);
        $api->expects($this->any())
            ->method('loadBean')
            ->willReturn($opp);

        $isValid = SugarTestReflection::callProtectedMethod(
            $api,
            'isValidServiceStartDate',
            [
                $this->service,
                $args,
            ]
        );
        $this->assertEquals($expected, $isValid);
    }

    public function dataProviderIsValidServiceStartDate()
    {
        // $args, $service_start_date, $add_on_to_id, $expected
        return [
            [
                ['service_start_date' => '2020-01-10'], 'Prospecting', 'pli_id', true,
            ],
            [
                ['service_start_date' => '2019-12-30'], 'Prospecting', 'pli_id', true,
            ],
            [
                ['service_start_date' => '2020-02-01'], 'Closed Won', 'pli_id', true,
            ],
            [
                ['service_start_date' => '2020-02-01'], 'Prospecting', '', true,
            ],
            [
                ['service_start_date' => '2020-02-01'], 'Prospecting', 'pli_id', false,
            ],
        ];
    }
}
