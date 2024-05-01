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
 * Class OpportunityTest
 * @coversDefaultClass Opportunity
 */
class OpportunityTest extends TestCase
{
    protected static $currentUser;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestCurrencyUtilities::createCurrency('MonkeyDollars', '$', 'MOD', 2.0);

        SugarTestForecastUtilities::setUpForecastConfig([
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up current user if needed
        if (static::$currentUser) {
            $GLOBALS['current_user'] = static::$currentUser;
            static::$currentUser = null;
        }

        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestProductTemplatesUtilities::removeAllCreatedProductTemplate();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty()
    {
        return [['best_case'], ['worst_case']];
    }

    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsAmountWhenCaseFieldEmpty($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $rli->$case = '';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->save();
        $this->assertEquals($opportunity->$case, $opportunity->amount);
    }


    /**
     * @dataProvider dataProviderCaseFieldEqualsAmountWhenCaseFieldEmpty
     * @group opportunities
     * @covers ::save
     */
    public function testCaseFieldEqualsZeroWhenCaseFieldSetToZero($case)
    {
        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->revenuelineitems->add($rli);
        $opportunity->sales_stage = 'Prospecting';
        $rli->$case = $rli->likely_case = 0;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity->$case = 0;
        $opportunity->save();
        $this->assertEquals(0, $opportunity->$case);
    }

    /**
     * Test that the base_rate field is populated with rate of currency_id
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testCurrencyRate()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = 'Test Opportunity Delete Me';
        $opportunity->amount = '5000.00';
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify('+10 days')->asDbDate();
        $opportunity->best_case = '1000.00';
        $opportunity->worst_case = '600.00';
        $opportunity->save();
        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }

    /**
     * Test that base currency exchange rates from EUR are working properly.
     * @group forecasts
     * @group opportunities
     * @covers ::save
     */
    public function testBaseCurrencyAmounts()
    {
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $currency = SugarTestCurrencyUtilities::getCurrencyByISO('MOD');
        // if Euro does not exist, will use default currency
        $opportunity->currency_id = $currency->id;
        $opportunity->name = 'Test Opportunity Delete Me';
        $opportunity->amount = '5000.00';
        $opportunity->date_closed = TimeDate::getInstance()->getNow()->modify('+10 days')->asDbDate();
        $opportunity->best_case = '1000.00';
        $opportunity->worst_case = '600.00';
        $opportunity->save();

        $this->assertEquals(
            sprintf('%.6f', $opportunity->base_rate),
            sprintf('%.6f', $currency->conversion_rate)
        );
    }


    /**
     * @group opportunities
     * @covers ::getClosedStages
     */
    public function testGetClosedStages()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $closedStages = $opp->getClosedStages();
        $this->assertTrue(is_array($closedStages));
    }

    /**
     * @dataProvider dataProviderMapProbabilityFromSalesStage
     * @group opportunities
     * @covers ::mapProbabilityFromSalesStage
     * @param string $sales_stage
     * @param string $probability
     */
    public function testMapProbabilityFromSalesStage($sales_stage, $probability)
    {
        /* @var $oppMock Opportunity */
        $oppMock = $this->createPartialMock('Opportunity', ['save']);
        $oppMock->sales_stage = $sales_stage;
        // use the Reflection Helper to call the Protected Method
        SugarTestReflection::callProtectedMethod($oppMock, 'mapProbabilityFromSalesStage');

        $this->assertEquals($probability, $oppMock->probability);
    }

    public static function dataProviderMapProbabilityFromSalesStage()
    {
        return [
            ['Prospecting', '10'],
            ['Qualification', '20'],
            ['Needs Analysis', '25'],
            ['Value Proposition', '30'],
            ['Id. Decision Makers', '40'],
            ['Perception Analysis', '50'],
            ['Proposal/Price Quote', '65'],
            ['Negotiation/Review', '80'],
            ['Closed Won', '100'],
            ['Closed Lost', '0'],
        ];
    }

    /**
     * @covers ::isCurrencyIdChanged
     * @dataProvider dataProviderIsCurrencyIdChanged
     * @param string $currency_id
     * @param string $fetched_row_id
     * @param bool $expected
     */
    public function testIsCurrencyIdChanged($currency_id, $fetched_row_id, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->currency_id = $currency_id;
        if (!is_null($fetched_row_id)) {
            $opp->fetched_row = ['currency_id' => $fetched_row_id];
        }

        $actual = SugarTestReflection::callProtectedMethod($opp, 'isCurrencyIdChanged');

        $this->assertSame($expected, $actual);
    }

    public static function dataProviderIsCurrencyIdChanged()
    {
        return [
            [
                'test_currency_id',
                'test_currency_id',
                false,
            ],
            [
                'test_currency_id',
                'test-currency-id',
                true,
            ],
            [
                null,
                'test-currency-id',
                true,
            ],
            [
                'test_currency_id',
                null,
                true,
            ],
        ];
    }

    /**
     * @covers ::updateCurrencyBaseRate
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $sales_stage
     * @param array $closed_stages
     * @param bool $expected
     */
    public function testUpdateCurrencyBaseRate($sales_stage, $closed_stages, $expected)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'getClosedStages'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->sales_stage = $sales_stage;

        $opp->expects($this->once())
            ->method('getClosedStages')
            ->willReturn($closed_stages);

        $actual = $opp->updateCurrencyBaseRate();
        $this->assertSame($expected, $actual);
    }

    public static function dataProviderUpdateCurrencyBaseRate()
    {
        return [
            [
                'test_not_in_array',
                [
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4',
                ],
                true,
            ],
            [
                'test_in_array',
                [
                    'test_1',
                    'test_2',
                    'test_3',
                    'test_4',
                    'test_in_array',
                ],
                false,
            ],
        ];
    }

    /**
     * @covers ::save_relationship_changes
     */
    public function testSaveRelationshipChanges()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(
                [
                    'set_opportunity_contact_relationship',
                    'set_relationship_info',
                    'handle_preset_relationships',
                    'handle_remaining_relate_fields',
                    'update_parent_relationships',
                    'handle_request_relate',
                    'load_relationship',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->account_id = 'test_account_id1';
        $opp->rel_fields_before_value['account_id'] = 'test_account_id2';

        $linkAccounts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['delete'])
            ->getMock();

        $linkAccounts->expects($this->once())
            ->method('delete')
            ->with($opp->id, 'test_account_id2');

        $opp->accounts = $linkAccounts;

        $linkProducts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $mockProduct = $this->getMockBuilder('Product')
            ->setMethods(['save'])
            ->getMock();

        $mockProduct->expects($this->once())
            ->method('save');

        $linkProducts->expects($this->once())
            ->method('getBeans')
            ->willReturn([$mockProduct]);

        $opp->products = $linkProducts;

        $linkRLIs = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $mockRLI = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $mockRLI->expects($this->once())
            ->method('save');

        $linkRLIs->expects($this->once())
            ->method('getBeans')
            ->willReturn([$mockRLI]);

        $opp->revenuelineitems = $linkRLIs;

        $opp->expects($this->exactly(3))
            ->method('load_relationship')
            ->willReturnOnConsecutiveCalls($linkAccounts, $linkProducts, $linkRLIs);


        $opp->save_relationship_changes(true);

        $this->assertEquals('test_account_id1', $mockProduct->account_id);
        $this->assertEquals('test_account_id1', $mockRLI->account_id);
    }

    /**
     * @covers ::build_generic_where_clause
     */
    public function testBuildGenericWhereClause()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();

        $actual = $opp->build_generic_where_clause('test');

        $this->assertEquals("opportunities.name like 'test%' or accounts.name like 'test%'", $actual);
    }

    /**
     * @covers ::set_opportunity_contact_relationship
     */
    public function testSetOpportunityContactRelationship()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['load_relationship'])
            ->disableOriginalConstructor()
            ->getMock();

        $opp->contacts = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();

        $opp->contacts->expects($this->once())
            ->method('add')
            ->with('test_contact_id', ['contact_role' => 'default']);

        $opp->expects($this->once())
            ->method('load_relationship');

        $GLOBALS['app_list_strings'] = [
            'opportunity_relationship_type_default_key' => 'default',
        ];

        $opp->set_opportunity_contact_relationship('test_contact_id');
    }


    /**
     * @covers ::updateRLIRollupFields
     * @dataProvider providerTestUpdateRLIRollupFields
     *
     * @param array $rliDataArray array of RLIs to create
     * @param array $expected the array of expected field values for the rollup fields
     * @throws SugarQueryException
     */
    public function testUpdateRLIRollupFields($rliDataArray, $expected)
    {
        // Create an Opportunity
        $opportunity = SugarTestOpportunityUtilities::createOpportunity();

        if (empty($rliDataArray)) {
            // There aren't any related RLIs, so test the update function directly
            $opportunity->updateRLIRollupFields();
        } else {
            // Create RLIs related to the opportunity. On save, they should update
            // the rollup fields of the Opportunity
            foreach ($rliDataArray as $rliData) {
                $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
                $rli->sales_stage = $rliData['sales_stage'];
                $rli->service_start_date = $rliData['service_start_date'];
                $rli->date_closed = $rliData['date_closed'];
                $rli->service = $rliData['service'];
                $rli->service_duration_value = $rliData['service_duration_value'];
                $rli->service_duration_unit = $rliData['service_duration_unit'];
                if (!empty($rliData['add_on_to_id'])) {
                    $rli->add_on_to_id = $rliData['add_on_to_id'];
                }
                if (!empty($rliData['product_template_id'])) {
                    $pt = SugarTestProductTemplatesUtilities::createProductTemplate($id = $rliData['product_template_id']);
                    $pt->service = 1;
                    $pt->lock_duration = $rliData['product_template_lock_duration'];
                    $pt->save();
                    $rli->product_template_id = $pt->id;
                }
                $opportunity->revenuelineitems->add($rli);
            }
        }

        // Check that the Opportunity's rollup fields were correctly calculated
        $this->assertEquals($expected['service_start_date'], $opportunity->service_start_date);
        $this->assertEquals($expected['sales_stage'], $opportunity->sales_stage);
        $this->assertEquals($expected['date_closed'], $opportunity->date_closed);
        $this->assertEquals($expected['service_duration_value'], $opportunity->service_duration_value);
        $this->assertEquals($expected['service_duration_unit'], $opportunity->service_duration_unit);
    }

    public function providerTestUpdateRLIRollupFields()
    {
        return [
            [
                [],
                [
                    'service_start_date' => '',
                    'sales_stage' => '',
                    'date_closed' => '',
                    'service_duration_value' => '',
                    'service_duration_unit' => '',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Prospecting',
                        'service' => 1,
                        'service_start_date' => '2020-04-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '2020-04-01',
                    'sales_stage' => 'Prospecting',
                    'date_closed' => '2020-03-15',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'year',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2019-01-01',
                        'date_closed' => '2019-10-15',
                        'service_duration_value' => 10,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-01-10',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '2019-01-01',
                    'sales_stage' => 'Closed Won',
                    'date_closed' => '2019-10-15',
                    'service_duration_value' => 10,
                    'service_duration_unit' => 'month',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2019-01-01',
                        'date_closed' => '2019-10-15',
                        'service_duration_value' => 10,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-01-10',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '',
                    'sales_stage' => 'Closed Lost',
                    'date_closed' => '2020-01-10',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'year',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 10,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Qualification',
                        'service' => 1,
                        'service_start_date' => '2019-01-01',
                        'date_closed' => '2020-01-20',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Prospecting',
                        'service' => 1,
                        'service_start_date' => '2019-06-01',
                        'date_closed' => '2020-03-05',
                        'service_duration_value' => 20,
                        'service_duration_unit' => 'day',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2018-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 2,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '2019-01-01',
                    'sales_stage' => 'Qualification',
                    'date_closed' => '2020-03-05',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'month',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 10,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Qualification',
                        'service' => 1,
                        'service_start_date' => '2019-01-01',
                        'date_closed' => '2020-01-20',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'month',
                        'add_on_to_id' => 'my_add_on_to_pli',
                    ],
                    [
                        'sales_stage' => 'Prospecting',
                        'service' => 1,
                        'service_start_date' => '2019-06-01',
                        'date_closed' => '2020-03-05',
                        'service_duration_value' => 20,
                        'service_duration_unit' => 'day',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2018-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 2,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '2019-01-01',
                    'sales_stage' => 'Qualification',
                    'date_closed' => '2020-03-05',
                    'service_duration_value' => 20,
                    'service_duration_unit' => 'day',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 20,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Qualification',
                        'service' => 1,
                        'service_start_date' => '2019-01-01',
                        'date_closed' => '2020-01-20',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'month',
                        'add_on_to_id' => 'my_add_on_to_pli_1',
                    ],
                    [
                        'sales_stage' => 'Prospecting',
                        'service' => 1,
                        'service_start_date' => '2019-06-01',
                        'date_closed' => '2020-03-05',
                        'service_duration_value' => 20,
                        'service_duration_unit' => 'day',
                        'add_on_to_id' => 'my_add_on_to_pli_2',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2018-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 2,
                        'service_duration_unit' => 'year',
                    ],
                ],
                [
                    'service_start_date' => '2019-01-01',
                    'sales_stage' => 'Qualification',
                    'date_closed' => '2020-03-05',
                    'service_duration_value' => 20,
                    'service_duration_unit' => 'month',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 10,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Closed Lost',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 11,
                        'service_duration_unit' => 'month',
                    ],
                    [
                        'sales_stage' => 'Qualification',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-01-20',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'month',
                        'product_template_id' => 'my_product_template_id',
                        'product_template_lock_duration' => 1,
                    ],
                ],
                [
                    'service_start_date' => '2020-01-01',
                    'sales_stage' => 'Qualification',
                    'date_closed' => '2020-01-20',
                    'service_duration_value' => 10,
                    'service_duration_unit' => 'month',
                ],
            ],
            [
                [
                    [
                        'sales_stage' => 'Closed Won',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-03-15',
                        'service_duration_value' => 2,
                        'service_duration_unit' => 'year',
                    ],
                    [
                        'sales_stage' => 'Qualification',
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'date_closed' => '2020-01-20',
                        'service_duration_value' => 1,
                        'service_duration_unit' => 'month',
                        'product_template_id' => 'my_product_template_id',
                        'product_template_lock_duration' => 0,
                    ],
                ],
                [
                    'service_start_date' => '2020-01-01',
                    'sales_stage' => 'Qualification',
                    'date_closed' => '2020-01-20',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'month',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCascade
     * @covers::cascade
     */
    public function testCascade(
        $sales_stage,
        $service_start_date,
        $date_closed,
        $service_duration_value,
        $service_duration_unit
    ) {

        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $rli1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli1->opportunity_id = $opp->id;
        $rli1->sales_stage = 'Closed Won';
        $rli1->date_closed = '2019-10-25';
        $rli1->service = 1;
        $rli1->service_start_date = '2019-09-25';
        $rli1->service_duration_value = 1;
        $rli1->service_duration_unit = 'year';

        $opp->revenuelineitems->add($rli1);

        $rli2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli2->opportunity_id = $opp->id;
        $rli2->sales_stage = 'Closed Lost';
        $rli2->date_closed = '2020-08-20';
        $rli2->service = 1;
        $rli2->service_start_date = '2021-04-28';
        $rli2->service_duration_value = 1;
        $rli2->service_duration_unit = 'year';

        $opp->revenuelineitems->add($rli2);

        $rli3 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli3->opportunity_id = $opp->id;
        $rli3->sales_stage = 'Needs Analysis';
        $rli3->date_closed = '2020-08-08';
        $rli3->service = 1;
        $rli3->service_start_date = '2021-04-28';
        $rli3->service_duration_value = 1;
        $rli3->service_duration_unit = 'year';

        $pt = SugarTestProductTemplatesUtilities::createProductTemplate('product_template_id');
        $pt->service = 1;
        $pt->lock_duration = 1;
        $rli3->product_template_id = $pt->id;

        $opp->revenuelineitems->add($rli3);

        $rli4 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli4->opportunity_id = $opp->id;
        $rli4->sales_stage = 'Needs Analysis';
        $rli4->date_closed = '2019-09-25';
        $rli4->service = 0;
        $rli4->service_start_date = null;

        $opp->revenuelineitems->add($rli4);

        $rli5 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli5->opportunity_id = $opp->id;
        $rli5->sales_stage = 'Needs Analysis';
        $rli5->date_closed = '2020-08-08';
        $rli5->service = 1;
        $rli5->service_start_date = '2021-04-28';
        $rli5->service_duration_value = 1;
        $rli5->service_duration_unit = 'day';

        $opp->revenuelineitems->add($rli5);

        // Opp level RLI management fields  will have been recalculated above, so reset it
        $opp->sales_stage_cascade = $sales_stage;
        $opp->service_start_date_cascade = $service_start_date;
        $opp->date_closed_cascade = $date_closed;
        $opp->service_duration_value_cascade = $service_duration_value;
        $opp->service_duration_unit_cascade = $service_duration_unit;
        SugarTestReflection::callProtectedMethod(
            $opp,
            'cascade'
        );

        $this->assertSame($rli1->sales_stage, 'Closed Won');
        $this->assertSame($rli1->service_start_date, '2019-09-25');
        $this->assertSame($rli1->date_closed, '2019-10-25');
        $this->assertSame($rli1->service_duration_value, 1);
        $this->assertSame($rli1->service_duration_unit, 'year');

        $this->assertSame($rli2->sales_stage, 'Closed Lost');
        $this->assertSame($rli2->service_start_date, '2021-04-28');
        $this->assertSame($rli2->date_closed, '2020-08-20');
        $this->assertSame($rli2->service_duration_value, 1);
        $this->assertSame($rli2->service_duration_unit, 'year');

        $this->assertSame($rli3->sales_stage, $sales_stage);
        $this->assertSame($rli3->service_start_date, $service_start_date);
        $this->assertSame($rli3->date_closed, $date_closed);
        $this->assertSame($rli3->service_duration_value, 1);
        $this->assertSame($rli3->service_duration_unit, 'year');

        $this->assertSame($rli4->sales_stage, $sales_stage);
        $this->assertSame($rli4->service_start_date, null);
        $this->assertSame($rli4->date_closed, $date_closed);
        $this->assertSame($rli4->service_duration_value, null);
        $this->assertSame($rli4->service_duration_unit, null);

        $this->assertSame($rli5->sales_stage, $sales_stage);
        $this->assertSame($rli5->service_start_date, $service_start_date);
        $this->assertSame($rli5->date_closed, $date_closed);
        $this->assertSame($rli5->service_duration_value, $service_duration_value);
        $this->assertSame($rli5->service_duration_unit, $service_duration_unit);
    }

    public function dataProviderCascade()
    {
        // $sales_stage, $service_start_date, $date_closed, $service_duration_value, $service_duration_unit
        return [
            ['Prospecting', '2020-02-20', '2019-08-25', 13, 'month'],
            ['Value Proposition', '2020-02-20', '2019-09-26', 2, 'year'],
        ];
    }

    /**
     * @dataProvider dataProviderCalculate
     * @param string $stages
     * @param string $expected
     * @covers::save
     */
    public function testCalculationDoesNotCascade($stages, $expected)
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $rlis = [];
        for ($i = 0; $i < safeCount($stages); $i++) {
            // Save a new RLI related to the Opportunity with the given sales stage
            // On save, the Opportunity Sales Stage should be recalculated
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->sales_stage = $stages[$i];
            $opp->revenuelineitems->add($rli);
            $rlis[] = $rli;

            // Check that the Opportunity Sales Stage was calculated correctly
            $this->assertEquals($expected[$i], $opp->sales_stage);
        }

        // Check that the changes to the Opportunity did not cascade back down to the RLIs
        foreach ($stages as $index => $stage) {
            $rli = $rlis[$index];
            $this->assertEquals($stage, $rli->sales_stage);
        }
    }

    public function dataProviderCalculate()
    {
        return [
            [
                'stages' => ['Prospecting', 'Value Proposition', 'Needs Analysis',],
                'expected' => ['Prospecting', 'Value Proposition', 'Value Proposition',],
            ],
            [
                'stages' => ['Closed Lost', 'Closed Won', 'Value Proposition',],
                'expected' => ['Closed Lost', 'Closed Won', 'Value Proposition',],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGeneratePurchaseRliIds
     * @param string $generate_purchase The value for the purchase generation flag
     * @param string $sales_stage The sales stage of the opp
     * @param array $licenses Array of licenses for the test user
     * @param int $count The number of expected results
     * @param array $expected The expected result array
     * @throws SugarQueryException
     */
    public function testGetGeneratePurchaseRliIDs($generate_purchase, $sales_stage, $licenses, $count, $expected): void
    {
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        static $idCounter = 1;
        $rliId = 'ut-opp-test-' . $idCounter;
        $idCounter++;

        // Begin mocks for the collector
        $userMock = $this->getMockBuilder('User')
            ->onlyMethods([
                'getLicenseTypes',
                'isAdmin',
            ])
            ->getMock();

        $userMock->expects($this->any())
            ->method('getLicenseTypes')
            ->willReturn($licenses);

        $userMock->expects($this->any())
            ->method('isAdmin')
            ->willReturn(true);

        $userMock->is_admin = '1';
        $userMock->id = 'ut-opp-user';

        if (isset($GLOBALS['current_user'])) {
            static::$currentUser = $GLOBALS['current_user'];
        }

        // Current user needs to be set before the test objects for visibility reasons
        $GLOBALS['current_user'] = $userMock;

        $opp->sales_stage = 'Closed Won';
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem($rliId);
        $rli->generate_purchase = $generate_purchase;
        $rli->sales_stage = $sales_stage;
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add($rli);

        $this->assertEquals($rli->opportunity_id, $opp->id);
        $this->assertEquals($rli->generate_purchase, $generate_purchase);

        // Used to ensure test data is the same as production data
        $sql = "SELECT id
                FROM revenue_line_items
                WHERE opportunity_id = '$opp->id'
                AND generate_purchase = '$generate_purchase'";
        $rs = $rli->db->query($sql);
        $rows = [];
        while ($row = $rli->db->fetchByAssoc($rs)) {
            $rows[] = $row;
        }

        $this->assertCount(1, $rows);


        $ids = $opp->getGeneratePurchaseRliIds();

        // Begin licensed assertions
        $this->assertCount($count, $ids);

        //var_dump($ids);
        // Ensure IDs returned from DB are trimmed, as some DB2 enforces length
        // on our ID fields
        foreach ($ids as &$row) {
            $row['id'] = trim($row['id']);
        }

        $this->assertEquals($expected, $ids);
    }

    public function dataProviderGeneratePurchaseRliIds(): array
    {
        return [
            ['Yes', 'Closed Won', ['SUGAR_SELL'], 1, [['id' => 'ut-opp-test-1'],],],
            ['No', 'Closed Won', ['SUGAR_SELL'], 0, [],],
            ['Yes', 'Qualification', ['SUGAR_SELL'], 1, [['id' => 'ut-opp-test-3'],],],
            ['Completed', 'Closed Won', ['SUGAR_SELL'], 0, [],],
        ];
    }

    /**
     * @covers ::updateRenewalRLIs()
     */
    public function testUpdateRenewalRLIs()
    {
        // Create original RLI for the eventual closed-won opp
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->sales_stage = 'Prospecting';
        $rli->renewable = 1;
        $rli->product_template_id = '1234-5678';
        $rli->quantity = '20';
        $rli->likely_case = '2000';
        $rli->save();

        // Create the renewal RLI based on $rli that will be added to the renewal opp
        $renewalRli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $renewalRli->sales_sate = 'Prospecting';
        $renewalRli->renewable = 1;
        $renewalRli->product_template_id = '1234-5678';
        $renewalRli->quantity = '20';
        $renewalRli->likely_case = '2000';
        $renewalRli->save();

        // Set the this new renewal RLI on $rli
        $rli->renewal_rli_id = $renewalRli->id;
        $rli->save();

        $reNewOppBean = SugarTestOpportunityUtilities::createOpportunity();
        $reNewOppBean->sales_status = 'Prospecting';
        $reNewOppBean->renewal = 1;

        // Add the renewal RLI to the new renewal opp
        $reNewOppBean->revenuelineitems->add($renewalRli);
        $reNewOppBean->save();

        // Create the PLI based on $rli so we can add-on items
        $pliBean = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pliBean->renewal_opp_id = $reNewOppBean->id;
        $pliBean->save();

        $rliBeans = [];
        $rliBeans[] = $rli;

        $rliBean1 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rliBean1->name = 'RLI1';
        // Add-On-To PLI that links to an open renewal Opp ($reNewOppBean)
        $rliBean1->add_on_to_id = $pliBean->id;
        // $reNewOppBean has an existing RLI whose Product (1234-5678) matches current RLI
        $rliBean1->product_template_id = '1234-5678';
        $rliBean1->quantity = '15';
        $rliBean1->likely_case = '1500';
        $rliBean1->currency_id = '-99';
        $rliBean1->service = 1;
        $rliBean1->service_start_date = '2020-08-11';
        $rliBean1->service_end_date = '2020-10-10';
        $rliBean1->service_duration_value = '2';
        $rliBean1->service_duration_unit = 'month';
        $rliBean1->save();
        $rliBeans[] = $rliBean1;

        $rliBean2 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rliBean2->name = 'RLI2';
        // Add-On-To PLI that links to an open renewal Opp ($reNewOppBean)
        $rliBean2->add_on_to_id = $pliBean->id;
        // $reNewOppBean has an existing RLI whose Product (1234-5678) doesn't match current RLI
        $rliBean2->product_template_id = '8765-4321';
        $rliBean2->quantity = '10';
        $rliBean2->likely_case = '1000';
        $rliBean2->currency_id = '-99';
        $rliBean2->service = 1;
        $rliBean2->service_start_date = '2020-08-11';
        $rliBean2->service_end_date = '2020-10-10';
        $rliBean2->service_duration_value = '2';
        $rliBean2->service_duration_unit = 'month';
        $rliBean2->save();
        $rliBeans[] = $rliBean2;

        $rliBean3 = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rliBean3->name = 'RLI3';
        // Add-On-To PLI that doesn't link to an open renewal Opp
        $rliBean3->add_on_to_id = '';
        $rliBean3->product_template_id = '';
        $rliBean3->quantity = '5';
        $rliBean3->likely_case = '500';
        $rliBean3->currency_id = '-99';
        $rliBean3->service = 1;
        $rliBean3->service_start_date = '2020-08-11';
        $rliBean3->service_end_date = '2020-10-10';
        $rliBean3->service_duration_value = '2';
        $rliBean3->service_duration_unit = 'month';
        $rliBean3->save();
        $rliBeans[] = $rliBean3;

        $oppBean = SugarTestOpportunityUtilities::createOpportunity();
        $rlisUpdated = $oppBean->updateRenewalRLIs($rliBeans);

        // Only $rliBean1 is added to the existing renewal RLI in the existing renewal Opp
        $this->assertEquals('35', $renewalRli->quantity);
        $this->assertEquals('3500', $renewalRli->likely_case);

        $reNewOppBean->load_relationship('revenuelineitems');
        $beanArr = [];
        foreach ($reNewOppBean->revenuelineitems->getBeans() as $bean) {
            $beanArr[] = $bean->name;
        }
        $this->assertFalse(in_array($rliBean1->name, $beanArr));
        // A new renewal RLI is created/added to the existing renewal Opp based on $rliBean2
        $this->assertTrue(in_array($rliBean2->name, $beanArr));
        $this->assertFalse(in_array($rliBean3->name, $beanArr));

        $this->assertTrue(in_array($rliBean1->id, $rlisUpdated));
        $this->assertTrue(in_array($rliBean2->id, $rlisUpdated));
        // $rliBean3 is not in the $rlisUpdated that is returned by $oppBean->updateRenewalRLIs($rliBeans)
        $this->assertFalse(in_array($rliBean3->id, $rlisUpdated));
        $this->assertEquals($rliBean1->renewal_rli_id, $renewalRli->id);
    }

    public function dataProviderGetSortedForecastRangeKeys()
    {
        return [
            [
                'show_binary',
                [
                    'include' => [
                        'max' => 100,
                        'min' => 70,
                    ],
                    'exclude' => [
                        'max' => 69,
                        'min' => 0,
                    ],
                ],
                [],
                [],
                ['include', 'exclude'],
            ],
            [
                'show_buckets',
                [],
                [
                    'include' => [
                        'max' => 100,
                        'min' => 85,
                    ],
                    'upside' => [
                        'max' => 84,
                        'min' => 70,
                    ],
                    'exclude' => [
                        'max' => 69,
                        'min' => 0,
                    ],
                ],
                [],
                ['include', 'upside', 'exclude'],
            ],
            [
                'show_custom_buckets',
                [],
                [],
                [
                    'include' => [
                        'max' => 100,
                        'min' => 90,
                    ],
                    'upside' => [
                        'max' => 89,
                        'min' => 80,
                    ],
                    'exclude' => [
                        'max' => 9,
                        'min' => 0,
                    ],
                    'custom_1' => [
                        'max' => 79,
                        'min' => 50,
                    ],
                    'custom_2' => [
                        'max' => 49,
                        'min' => 10,
                    ],
                    'custom_without_probability_1' => [
                        'max' => 0,
                        'min' => 0,
                    ],
                    'custom_without_probability_2' => [
                        'max' => 0,
                        'min' => 0,
                    ],
                ],
                [
                    'include',
                    'upside',
                    'custom_1',
                    'custom_2',
                    'exclude',
                    'custom_without_probability_1',
                    'custom_without_probability_2',
                ],
            ],
            [
                'show_custom_buckets',
                [
                    'include' => [
                        'max' => 100,
                        'min' => 70,
                    ],
                    'exclude' => [
                        'max' => 69,
                        'min' => 0,
                    ],
                ],
                [
                    'include' => [
                        'max' => 100,
                        'min' => 85,
                    ],
                    'upside' => [
                        'max' => 84,
                        'min' => 70,
                    ],
                    'exclude' => [
                        'max' => 69,
                        'min' => 0,
                    ],
                ],
                [
                    'custom_without_probability_1' => [
                        'max' => 0,
                        'min' => 0,
                    ],
                    'custom_without_probability_2' => [
                        'max' => 0,
                        'min' => 0,
                    ],
                    'include' => [
                        'max' => 100,
                        'min' => 90,
                    ],
                    'custom_2' => [
                        'max' => 49,
                        'min' => 10,
                    ],
                    'exclude' => [
                        'max' => 9,
                        'min' => 0,
                    ],
                    'custom_1' => [
                        'max' => 79,
                        'min' => 50,
                    ],
                    'upside' => [
                        'max' => 89,
                        'min' => 80,
                    ],
                ],
                [
                    'include',
                    'upside',
                    'custom_1',
                    'custom_2',
                    'exclude',
                    'custom_without_probability_1',
                    'custom_without_probability_2',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetSortedForecastRangeKeys
     * @covers ::getSortedForecastRangeKeys
     * @param $useRange
     * @param $binaryRanges
     * @param $bucketRanges
     * @param $customRanges
     * @param $expected
     */
    public function testGetSortedForecastRangeKeys($useRange, $binaryRanges, $bucketRanges, $customRanges, $expected)
    {
        SugarTestConfigUtilities::setConfig('Forecasts', 'is_setup', 1);
        SugarTestConfigUtilities::setConfig('Forecasts', 'forecast_ranges', $useRange);
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_binary_ranges', $binaryRanges);
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_bucket_ranges', $bucketRanges);
        SugarTestConfigUtilities::setConfig('Forecasts', 'show_custom_buckets_ranges', $customRanges);

        $oppBean = SugarTestOpportunityUtilities::createOpportunity();
        $this->assertEquals($expected, $oppBean->getSortedForecastRangeKeys());

        SugarTestConfigUtilities::resetConfig();
    }
}
