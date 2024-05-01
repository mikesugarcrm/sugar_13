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
 * Class RevenueLineItemTest
 * @coversDefaultClass RevenueLineItem
 */
class RevenueLineItemTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['RevenueLineItems']);
        SugarTestHelper::setUp('beanList');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDownCustomFields();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestPurchaseUtilities::removeAllCreatedPurchases();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
        SugarTestProductTemplatesUtilities::removeAllCreatedProductTemplate();

        Forecast::$settings = [];
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';
        $rli->id = 'dummy_rli_id';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->discount_price, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not set (1:1)');
        $this->assertEquals($rli->id, $product->parent_rli_id, 'RLI to QLI Link is not set (1:M)');
        $this->assertEquals('Test', $product->sales_stage, 'Product does not match RevenueLineItem');
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuotedLineItemWithoutDiscountPriceSet()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '';
        $rli->sales_stage = 'Test';
        $rli->id = 'dummy_rli_id';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals($rli->likely_case, $product->discount_price);
        $this->assertEquals($rli->id, $product->revenuelineitem_id, 'RLI to QLI Link is not set (1:1)');
        $this->assertEquals($rli->id, $product->parent_rli_id, 'RLI to QLI Link is not set (1:M)');
        $this->assertEquals('Test', $product->sales_stage, 'Product does not match RevenueLineItem');
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetsCorrectDiscountAmount()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }

    /**
     * @group revenuelineitems
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQuoteLineItemsSetCorrectDiscountAmountWhenPercent()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->discount_amount = '25.00';
        $rli->quantity = '50';
        $rli->discount_price = '1.00';
        $rli->discount_select = 1;
        $rli->deal_calc = 0.25; // (discount_amount/100)*discount_price
        $product = $rli->convertToQuotedLineItem();

        $this->assertEquals('25.00', $product->discount_amount);
    }


    /**
     * @param bool $has_add_on
     * @dataProvider dataProviderConvertToQLICopiesAddOnPLI
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQLICopiesAddOnToPLI($has_add_on)
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';

        if ($has_add_on) {
            $add_on_to_pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem('add_on_to_pli_id');
            $rli->add_on_to_id = $add_on_to_pli->id;
        }

        $product = $rli->convertToQuotedLineItem();

        if ($has_add_on) {
            $this->assertEquals($rli->add_on_to_id, $product->add_on_to_id);
        } else {
            $this->assertNull($product->add_on_to_id);
        }
    }

    public function dataProviderConvertToQLICopiesAddOnPLI()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @covers ::convertToQuotedLineItem
     */
    public function testConvertToQLICopiesRenewal()
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = '100.00';
        $rli->discount_price = '200.00';
        $rli->sales_stage = 'Test';
        $rli->renewal = true;

        $product = $rli->convertToQuotedLineItem();
        $this->assertEquals(true, $product->renewal);
    }


    /**
     * @dataProvider dataProviderSetDiscountPrice
     * @covers ::setDiscountPrice
     * @param string $likely
     * @param string $quantity
     * @param string $discount_price
     * @param string $expected_discount
     */
    public function testSetDiscountPrice($likely, $quantity, $discount_price, $expected_discount)
    {
        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();
        $rli->likely_case = $likely;
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;

        SugarTestReflection::callProtectedMethod($rli, 'setDiscountPrice');

        $this->assertEquals($expected_discount, $rli->discount_price);
    }

    public function dataProviderSetDiscountPrice()
    {
        // values are likely, quantity, discount_price, expected_discount_price
        return [
            ['100.00', '1', '', '100.000000'],
            ['100.00', '1', '0.00', '0.00'],
            ['100.00', '1', '150.000000', '150.000000'],
        ];
    }

    /**
     * @param String $quantity
     * @param String $discount_price
     * @param String $discount_amount
     * @param String $discount_select
     * @param String $total_amount
     * @dataProvider totalAmountDataProvider
     */
    public function testCalculateTotalAmount(
        $quantity,
        $discount_price,
        $discount_amount,
        $discount_select,
        $total_amount
    ) {

        /* @var $rli RevenueLineItem */
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->quantity = $quantity;
        $rli->discount_price = $discount_price;
        $rli->discount_amount = $discount_amount;
        $rli->discount_select = $discount_select;
        $rli->save();

        // lets make sure the totals are correct
        $this->assertEquals(
            $total_amount,
            $rli->total_amount,
            'Total amount Is Wrong'
        );
    }

    /**
     * totalAmountDataProvider
     */
    public function totalAmountDataProvider()
    {
        // $quantity, $discount_price, $discount_amount, $discount_select, $total_amount
        return [
            ['-2', '100.000000', '10.000000', '0', '-190.000000'],
            ['-2', '100.000000', '10.000000', '1', '-180.000000'],
            ['2', '100.000000', '10.000000', '0', '190.000000'],
            ['2', '100.000000', '10.000000', '1', '180.000000'],
        ];
    }


    /**
     * @covers ::saveProductWorksheet
     */
    public function testSaveProductWorksheetReturnsFalse()
    {
        Forecast::$settings = [
            'is_setup' => 0,
        ];
        $mock = $this->getMockBuilder('RevenueLineItem')
            ->getMock();

        $actual = SugarTestReflection::callProtectedMethod($mock, 'saveProductWorksheet');
        $this->assertFalse($actual);
    }


    /**
     * @covers ::mapFieldsFromProductTemplate
     */
    public function testMapFieldsProductTemplate()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')->setMethods(['save'])->getMock();

        $arrExpected = [
            'category_id' => 'test_category_id',
            'mft_part_num' => 'test_mft_part_num',
            'list_price' => 'test_list_price',
            'cost_price' => 'test_cost_price',
            'discount_price' => 'test_discount_price',
            'list_usdollar' => 'test_list_usdollar',
            'cost_usdollar' => 'test_cost_usdollar',
            'discount_usdollar' => 'test_discount_usdollar',
            'tax_class' => 'test_tax_class',
            'weight' => 'test_weight',
        ];

        $pt = $this->getMockBuilder('ProductTemplate')
            ->setMethods(['save'])
            ->getMock();

        $pt->id = 'test_pt_id';
        foreach ($arrExpected as $key => $val) {
            $pt->$key = $val;
        }

        $rli->product_template_id = $pt->id;
        BeanFactory::registerBean($pt);

        SugarTestReflection::callProtectedMethod($rli, 'mapFieldsFromProductTemplate');

        foreach ($arrExpected as $key => $expected) {
            $this->assertEquals($expected, $rli->$key);
        }

        BeanFactory::unregisterBean($pt, $pt->id);
    }

    public static function dataProviderSetAccountIdForOpportunity()
    {
        return [
            [
                [
                    'test_account_id',
                ],
                true,
            ],
            [
                [],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSetAccountIdForOpportunity
     * @covers ::setAccountIdForOpportunity
     */
    public function testSetAccountIdForOpportunity($accounts, $expected)
    {
        $product = $this->createPartialMock('Product', ['save', 'load_relationship']);

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'load_relationship'])
            ->getMock();

        $opp->id = 'test_opp_id';

        $opp->expects($this->once())
            ->method('load_relationship')
            ->with('accounts')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('get')
            ->willReturn($accounts);


        $opp->accounts = $link2;

        BeanFactory::registerBean($opp);

        $actual = SugarTestReflection::callProtectedMethod($product, 'setAccountIdForOpportunity', [$opp->id]);

        $this->assertEquals($expected, $actual);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::mapFieldsFromOpportunity
     */
    public function testMapFieldsFromOpportunity()
    {
        $product = $this->createPartialMock('Product', ['save']);

        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->getMock();

        $opp->id = 'test_opp_id';
        $opp->opportunity_type = 'test_type';

        $product->opportunity_id = $opp->id;

        BeanFactory::registerBean($opp);

        SugarTestReflection::callProtectedMethod($product, 'mapFieldsFromOpportunity');

        $this->assertEquals('test_type', $product->product_type);

        BeanFactory::unregisterBean($opp, $opp->id);
    }

    /**
     * @covers ::setBestWorstFromLikely
     */
    public function testSetBestWorstFromLikelyDoesNotChangeBecauseOfAcl()
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['ACLFieldAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(false);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = 500;
        $rli->best_case = '';
        $rli->worst_case = 0;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertEquals(500, $rli->likely_case);
        $this->assertEquals('', $rli->best_case);
        $this->assertEquals(0, $rli->worst_case);
    }

    /**
     * @dataProvider dataProviderBestWorstAutoFill
     * @covers ::setBestWorstFromLikely
     */
    public function testBestWorstAutoFill($value, $likely, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['ACLFieldAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $rli->expects($this->atLeast(2))
            ->method('ACLFieldAccess')
            ->willReturn(true);

        /* @var $rli RevenueLineItem */
        $rli->likely_case = $likely;
        $rli->best_case = $value;
        $rli->worst_case = $value;

        SugarTestReflection::callProtectedMethod($rli, 'setBestWorstFromLikely');

        $this->assertSame($expected, $rli->best_case);
        $this->assertSame($expected, $rli->worst_case);
    }

    public function dataProviderBestWorstAutoFill()
    {
        return [
            [
                '',
                '100',
                '100',
            ],
            [
                null,
                '100',
                '100',
            ],
            [
                '42',
                '100',
                '42',
            ],
            [
                '0',
                '100',
                '0',
            ],
            [
                '0',
                100,
                '0',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @group revenuelineitems
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $rli->quantity = $actual;

        SugarTestReflection::callProtectedMethod($rli, 'checkQuantity');

        $this->assertEquals($expected, $rli->quantity);
    }


    public static function dataProviderCheckQuantity()
    {
        return [
            ['', 0],
            [null, 0],
            [0, 0],
            [1, 1],
            [42, 42],
            [-1, -1],
            [-42, -42],
        ];
    }

    /**
     * @dataProvider dataProviderCanConvertToQuote
     * @param $fields
     * @param $expected
     */
    public function testCanConvertToQuote($fields, $expected)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(null)
            ->getMock();

        foreach ($fields as $field => $value) {
            $rli->$field = $value;
        }

        $actual = $rli->canConvertToQuote();

        if ($expected === false) {
            // we have to assert not true, since it's returning a language string and testing against that
            // is bad!
            $this->assertNotTrue($actual);
        } else {
            $this->assertTrue($actual);
        }
    }

    public static function dataProviderCanConvertToQuote()
    {
        return [
            [
                [
                    'category_id' => 'test_cat_id',
                ],
                false,
            ],
            [
                [
                    'id' => 'test',
                ],
                true,
            ],
        ];
    }

    /**
     * @covers ::setCommitStage
     * @param string $salesStage
     * @param array $closedWonStages
     * @param array $closedLostStages
     * @param string $expectedCommitStage
     * @dataProvider providerTestSetCommitStage
     */
    public function testSetCommitStage($salesStage, $closedWonStages, $closedLostStages, $expectedCommitStage)
    {
        Forecast::$settings = [
            'is_setup' => 1,
            'sales_stage_won' => $closedWonStages,
            'sales_stage_lost' => $closedLostStages,
        ];

        $mockRli = $this->getMockBuilder(RevenueLineItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $mockRli->sales_stage = $salesStage;
        $mockRli->commit_stage = 'Current Commit Stage';
        SugarTestReflection::callProtectedMethod($mockRli, 'setCommitStage');

        $this->assertEquals($expectedCommitStage, $mockRli->commit_stage);
    }

    /**
     * Provider for testSetCommitStage
     *
     * @return array[]
     */
    public function providerTestSetCommitStage()
    {
        return [
            [
                'Prospecting',
                ['Closed Won'],
                ['Closed Lost'],
                'Current Commit Stage',
            ],
            [
                'Closed Won',
                ['Closed Won'],
                ['Closed Lost'],
                'include',
            ],
            [
                'Closed Lost',
                ['Closed Won'],
                ['Closed Lost'],
                'exclude',
            ],
            [
                'Value Proposition',
                ['Value Proposition', 'Closed Won'],
                ['Closed Lost'],
                'include',
            ],
        ];
    }


    /**
     * @covers ::setServiceEndDate
     * @dataProvider providerTestSetServiceEndDate
     */
    public function testSetServiceEndDate(array $serviceFields, ?string $expectedEndDate, $shouldClearFields)
    {
        $mockRLI = $this->getMockBuilder('RevenueLineItem')
            ->disableOriginalConstructor()
            ->getMock();

        // Set the service fields accordingly
        $mockRLI->service = $serviceFields['service'];
        $mockRLI->service_start_date = $serviceFields['service_start_date'];
        $mockRLI->service_duration_value = $serviceFields['service_duration_value'];
        $mockRLI->service_duration_unit = $serviceFields['service_duration_unit'];

        SugarTestReflection::callProtectedMethod($mockRLI, 'setServiceEndDate');

        $this->assertSame($expectedEndDate, $mockRLI->service_end_date);

        if ($shouldClearFields) {
            $this->assertSame(false, $mockRLI->service);
            $this->assertSame(null, $mockRLI->service_start_date);
            $this->assertSame(null, $mockRLI->service_duration_value);
            $this->assertSame(null, $mockRLI->service_duration_unit);
        }
    }

    public function providerTestSetServiceEndDate()
    {
        return [
            // Test days
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-25',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'day',
                ],
                '2019-09-25',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-25',
                    'service_duration_value' => 7,
                    'service_duration_unit' => 'day',
                ],
                '2019-10-01',
                false,
            ],
            // Test months
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-06-30',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'month',
                ],
                '2019-07-29',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-06-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'month',
                ],
                '2019-09-29',
                false,
            ],
            // Test years
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 1,
                    'service_duration_unit' => 'year',
                ],
                '2020-09-29',
                false,
            ],
            [
                [
                    'service' => true,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'year',
                ],
                '2022-09-29',
                false,
            ],
            // Test non-service type
            [
                [
                    'service' => false,
                    'service_start_date' => null,
                    'service_duration_value' => null,
                    'service_duration_unit' => null,
                ],
                null,
                false,
            ],
            // Test clearing of service data for non-services
            [
                [
                    'service' => false,
                    'service_start_date' => '2019-09-30',
                    'service_duration_value' => 3,
                    'service_duration_unit' => 'year',
                ],
                null,
                true,
            ],
            // Test clearing of service data for end date calculation errors
            [
                [
                    'service' => true,
                    'service_start_date' => 'Not a real date',
                    'service_duration_value' => 'Not a real number',
                    'service_duration_unit' => null,
                ],
                null,
                true,
            ],
        ];
    }

    /**
     * @covers ::updateRelatedAccount
     * @dataProvider providerTestUpdateRelatedAccount
     *
     * @param Array $rliDataArray array of RLIs to create
     * @param string $expected the expected next_renewal_date of the Account affected
     */
    public function testUpdateRelatedAccount($rliDataArray, $expected)
    {
        // Create an account
        $account = SugarTestAccountUtilities::createAccount();

        // Create RLIs that point to the account. On save, they should update
        // that account's next_renewal_date field
        foreach ($rliDataArray as $rliData) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->product_type = $rliData['product_type'];
            $rli->renewable = $rliData['renewable'];
            $rli->date_closed = $rliData['date_closed'];
            $rli->account_id = $account->id;
            $rli->save();
        }

        // Check that the account's next_renewal_date was correctly calculated
        // on RLI save
        $resultAccount = BeanFactory::retrieveBean('Accounts', $account->id, ['use_cache' => false]);
        $this->assertEquals($expected, $resultAccount->next_renewal_date);
    }

    public function providerTestUpdateRelatedAccount()
    {
        return [
            // No related RLIs
            [
                [],
                '',
            ],
            // 2 related RLIs, but none that fit the criteria
            [
                [
                    ['product_type' => 'New Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 0, 'date_closed' => '2019-01-01'],
                ],
                '',
            ],
            // 2 related RLIs, only one of which fits the criteria
            [
                [
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'New Business', 'renewable' => 1, 'date_closed' => '2019-01-01'],
                ],
                '2020-01-01',
            ],
            // 3 related RLIs, all of which fit the criteria but with different close dates
            [
                [
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2020-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2019-01-01'],
                    ['product_type' => 'Existing Business', 'renewable' => 1, 'date_closed' => '2021-01-01'],
                ],
                '2019-01-01',
            ],
        ];
    }

    /**
     * @covers ::hasMatchingPurchase
     * @dataProvider providerTestHasMatchingPurchase
     * @param $rliAccountId
     * @param $purchaseAccountId
     * @param $rliProductId
     * @param $purchaseProductId
     * @param $expected
     * @throws SugarQueryException
     */
    public function testHasMatchingPurchase(
        $rliAccountId,
        $purchaseAccountId,
        $rliProductId,
        $purchaseProductId,
        $expected
    ): void {

        $rliAccount = SugarTestAccountUtilities::createAccount($rliAccountId);
        $rliProduct = SugarTestProductTemplatesUtilities::createProductTemplate($rliProductId);
        $purchaseAccount = BeanFactory::retrieveBean('Accounts', $purchaseAccountId);
        if ($purchaseAccount === null) {
            $purchaseAccount = SugarTestAccountUtilities::createAccount($purchaseAccountId);
        }
        $purchaseProduct = BeanFactory::retrieveBean('ProductTemplates', $purchaseProductId);
        if ($purchaseProduct === null) {
            $purchaseProduct = SugarTestProductTemplatesUtilities::createProductTemplate($purchaseProductId);
        }

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->load_relationship('account_link');
        $rli->load_relationship('rli_templates_link');
        $rli->account_link->add($rliAccount);
        $rli->rli_templates_link->add($rliProduct);

        $purchase = SugarTestPurchaseUtilities::createPurchase('789');
        $purchase->load_relationship('accounts');
        $purchase->load_relationship('product_templates');
        $purchase->accounts->add($purchaseAccount);
        $purchase->product_templates->add($purchaseProduct);
        // For test legibility, this uses 3-char IDs. Trim the returned ID as
        // some of our supported DBs add whitespace to fill length requirements
        // on ID fields
        $this->assertEquals($expected, trim((string)$rli->getMatchingPurchaseId()));
    }

    public function providerTestHasMatchingPurchase(): array
    {
        return [
            ['123', '123', '456', '456', '789'],
            ['123', '321', '456', '456', null],
            ['123', '123', '456', '654', null],
            ['123', '321', '456', '654', null],
        ];
    }

    /**
     * @covers ::copyFieldsToBean
     * @dataProvider providerCopyFieldsToBean
     * @param $copyFields
     * @param $noCopyFields
     */
    public function testCopyFieldsToBean($copyFields, $noCopyFields): void
    {
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $rli->likely_case = 123;
        $rli->discount_amount = 100;
        $rli->service_duration_value = 1;
        $pli->likely_case = 125;
        $pli->discount_amount = 105;
        $pli->service_duration_value = 5;

        $rli->copyFieldsToBean($pli, $copyFields);

        foreach ($copyFields as $field) {
            $this->assertEquals($rli->$field, $pli->$field);
        }

        foreach ($noCopyFields as $field) {
            $this->assertNotEquals($rli->$field, $pli->$field);
        }
    }

    public function providerCopyFieldsToBean(): array
    {
        return [
            [['likely_case', 'discount_amount'], ['service_duration_value',],],
            [['likely_case', 'discount_amount', 'service_duration_value',], [],],
            [[], ['likely_case', 'discount_amount', 'service_duration_value',],],
        ];
    }

    /**
     * @covers ::mapFieldsToBean
     */
    public function testMapFieldsToBean(): void
    {
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $rli->likely_case = 123;
        $rli->discount_amount = 100;
        $rli->service_duration_value = 1;
        $pli->likely_case = 125;
        $pli->discount_amount = 105;
        $pli->service_duration_value = 5;

        $rli->mapFieldsToBean($pli, ['discount_amount' => 'service_duration_value']);

        $this->assertEquals($rli->discount_amount, $pli->service_duration_value);
        $this->assertNotEquals($rli->likely_case, $pli->likely_case);
        $this->assertNotEquals($rli->discount_amount, $pli->discount_amount);
    }

    /**
     * @covers ::generatePurchaseFromRli
     * @dataProvider providerGeneratePurchase
     */
    public function testGeneratePurchaseFromRLI($fields): void
    {
        global $current_user, $timedate;
        $current_date = '2015-08-13 18:13:00';
        $time = $timedate->fromString($current_date);
        $timedate->setNow($time);

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        foreach ($fields as $field => $value) {
            $rli->$field = $value;
        }
        $rli->save();
        $purchase = $rli->generatePurchaseFromRli();

        $this->assertEquals($current_user->id, $purchase->created_by);
        $this->assertEquals($current_user->id, $purchase->modified_user_id);
        $this->assertEquals($current_date, $purchase->date_modified);
        $this->assertEquals($current_date, $purchase->date_entered);
        foreach (array_keys($fields) as $field) {
            $this->assertEquals($rli->$field, $purchase->$field);
        }

        SugarTestPurchaseUtilities::removePurchasesByID([$purchase->id]);
    }

    public function providerGeneratePurchase(): array
    {
        return [
            [
                [
                    'name' => 'RLI Name',
                    'category_id' => '12345',
                    'type_id' => '54321',
                    'service' => '1',
                    'renewable' => '1',
                    'assigned_user_id' => 'abc1234oi',
                    'assigned_user_name' => 'SugarTestUser9000',
                    'team_id' => '1234lkjsdf',
                    'team_set_id' => '1234lkjsdf',
                    'acl_team_set_id' => '1234lkjsdf',
                    'account_id' => 'werowiusdlkfj234',
                    'account_name' => 'Account For Testing Purchases',
                    'product_template_id' => '1234567890',
                    'product_template_name' => '234890-',
                ],
            ],
        ];
    }

    /**
     * @covers ::generatePliFromRli
     * @dataProvider providerGeneratePli
     * @param $copyFields
     * @param $mappedFields
     * @param $expected
     * @param $hasRenewalRli
     */
    public function testGeneratePliFromRli($copyFields, $mappedFields, $expected, $hasRenewalRli): void
    {
        global $current_user, $timedate;
        $current_date = '2015-08-13 18:13:00';
        $time = $timedate->fromString($current_date);
        $timedate->setNow($time);

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        foreach ($copyFields as $field => $value) {
            $rli->$field = $value;
        }
        foreach ($mappedFields as $field => $mapping) {
            $rli->$field = $mapping['value'];
        }

        if ($hasRenewalRli) {
            $account = SugarTestAccountUtilities::createAccount();
            $opportunity = SugarTestOpportunityUtilities::createOpportunity('opportunity_id', $account);
            $renewalRli = SugarTestRevenueLineItemUtilities::createRevenueLineItem($id = 'renewal_rli');
            $renewalRli->load_relationship('opportunities');
            $renewalRli->opportunities->add($opportunity);
            $renewalRli->save();
            $rli->renewal_rli_id = $renewalRli->id;
        }

        $rli->save();

        $purchase = SugarTestPurchaseUtilities::createPurchase();
        $purchase->service = $copyFields['service'];
        $acct = SugarTestAccountUtilities::createAccount();
        $purchase->account_id = $acct->id;

        $pli = $rli->generatePliFromRli($purchase);
        $this->assertEquals($current_user->id, $pli->created_by);
        $this->assertEquals($current_user->id, $pli->modified_user_id);
        $this->assertEquals($current_date, $pli->date_modified);
        $this->assertEquals($current_date, $pli->date_entered);
        $this->assertEquals($purchase->id, $pli->purchase_id);
        $this->assertEquals($purchase->account_id, $pli->account_id);

        foreach (array_keys($copyFields) as $field) {
            $this->assertEquals($rli->$field, $pli->$field);
        }
        foreach ($mappedFields as $field => $mapping) {
            $this->assertEquals($rli->$field, $pli->{$mapping['mappedField']});
        }
        foreach ($expected as $field => $value) {
            $this->assertEquals($pli->$field, $value);
        }
        if ($hasRenewalRli) {
            $this->assertEquals($pli->renewal_opp_id, 'opportunity_id');
        } else {
            $this->assertNull($pli->renewal_opp_id);
        }

        SugarTestPurchasedLineItemUtilities::removePurchasedLineItemsByID([$pli->id]);
    }

    public function providerGeneratePli(): array
    {
        return [
            'nonService' => [
                'copy' => [
                    'name' => 'RliName',
                    'date_closed' => '2020-08-13',
                    'quantity' => 123,
                    'discount_select' => 1,
                    'discount_amount' => 10,
                    'discount_price' => 123,
                    'renewable' => '1',
                    'description' => 'This RLI will become a product',
                    'assigned_user_id' => 'abc123',
                    'assigned_user_name' => 'Jimothy Jericho',
                    'team_id' => '1234lkjsdf',
                    'team_set_id' => '1234lkjsdf',
                    'acl_team_set_id' => '1234lkjsdf',
                    'asset_number' => '1209384',
                    'base_rate' => 0.980000,
                    'vendor_part_num' => '109238',
                    'list_price' => 100.00,
                    'tax_class' => 'Taxable',
                    'weight' => 1.01,
                    'website' => 'https://www.sugarcrm.com',
                    'serial_number' => '12N658AA39PI',
                    'cost_price' => 100.08,
                    'mft_part_num' => '12n658aa39pi',
                    'book_value_date' => '2020-08-13',
                    'book_value' => 100.01,
                    'support_term' => 'This is a text field',
                    'support_title' => 'Another Text Field',
                    'support_expires' => '3000-01-01',
                    'support_starts' => '1750-07-28',
                    'support_contact' => 'Contact is a text field',
                    'support_desc' => 'Description of support',
                    'service' => false,
                    'renewal' => false,
                ],
                'mapped' => [
                    'likely_case' => [
                        'mappedField' => 'revenue',
                        'value' => 123.01,
                    ],
                ],
                'expected' => [
                    'service_start_date' => '2020-08-13',
                    'service_end_date' => '2020-08-13',
                    'service_duration_unit' => 'day',
                    'service_duration_value' => 1,
                ],
                'hasRenewalRli' => false,
            ],
            'service' => [
                'copy' => [
                    'name' => 'RliName',
                    'date_closed' => '2020-08-13',
                    'quantity' => 123,
                    'discount_select' => 1,
                    'discount_amount' => 10,
                    'discount_price' => 123,
                    'renewable' => '1',
                    'description' => 'This RLI will become a product',
                    'assigned_user_id' => 'abc123',
                    'assigned_user_name' => 'Jimothy Jericho',
                    'team_id' => '1234lkjsdf',
                    'team_set_id' => '1234lkjsdf',
                    'acl_team_set_id' => '1234lkjsdf',
                    'asset_number' => '1209384',
                    'base_rate' => 0.980000,
                    'vendor_part_num' => '109238',
                    'list_price' => 100.00,
                    'tax_class' => 'Taxable',
                    'weight' => 1.01,
                    'website' => 'https://www.sugarcrm.com',
                    'serial_number' => '12N658AA39PI',
                    'cost_price' => 100.08,
                    'mft_part_num' => '12n658aa39pi',
                    'book_value_date' => '2020-08-13',
                    'book_value' => 100.01,
                    'support_term' => 'This is a text field',
                    'support_title' => 'Another Text Field',
                    'support_expires' => '3000-01-01',
                    'support_starts' => '1750-07-28',
                    'support_contact' => 'Contact is a text field',
                    'support_desc' => 'Description of support',
                    'service' => true,
                    'service_start_date' => '2020-08-13',
                    'service_end_date' => '2021-1-13',
                    'service_duration_value' => 5,
                    'service_duration_unit' => 'month',
                    'renewal' => true,
                ],
                'mapped' => [
                    'likely_case' => [
                        'mappedField' => 'revenue',
                        'value' => 123.01,
                    ],
                ],
                'expected' => [
                ],
                'hasRenewalRli' => false,
            ],
            'coterm' => [
                'copy' => [
                    'name' => 'RliName',
                    'date_closed' => '2020-08-13',
                    'quantity' => 123,
                    'discount_select' => 1,
                    'discount_amount' => 10,
                    'discount_price' => 123,
                    'renewable' => '1',
                    'description' => 'This RLI will become a product',
                    'assigned_user_id' => 'abc123',
                    'assigned_user_name' => 'Jimothy Jericho',
                    'team_id' => '1234lkjsdf',
                    'team_set_id' => '1234lkjsdf',
                    'acl_team_set_id' => '1234lkjsdf',
                    'asset_number' => '1209384',
                    'base_rate' => 0.980000,
                    'vendor_part_num' => '109238',
                    'list_price' => 100.00,
                    'tax_class' => 'Taxable',
                    'weight' => 1.01,
                    'website' => 'https://www.sugarcrm.com',
                    'serial_number' => '12N658AA39PI',
                    'cost_price' => 100.08,
                    'mft_part_num' => '12n658aa39pi',
                    'book_value_date' => '2020-08-13',
                    'book_value' => 100.01,
                    'support_term' => 'This is a text field',
                    'support_title' => 'Another Text Field',
                    'support_expires' => '3000-01-01',
                    'support_starts' => '1750-07-28',
                    'support_contact' => 'Contact is a text field',
                    'support_desc' => 'Description of support',
                    'service' => true,
                    'service_start_date' => '2020-08-13',
                    'service_end_date' => '2021-1-13',
                    'service_duration_value' => 5,
                    'service_duration_unit' => 'month',
                    'renewal' => true,
                ],
                'mapped' => [
                    'likely_case' => [
                        'mappedField' => 'revenue',
                        'value' => 123.01,
                    ],
                ],
                'expected' => [
                ],
                'hasRenewalRli' => true,
            ],
        ];
    }

    /**
     * @covers ::copyCustomFields
     * @param $types
     * @param $values
     * @throws Exception
     * @dataProvider providerTestCopyFields
     */
    public function testCopyCustomFields($types, $values): void
    {
        $ftsSearch = \Sugarcrm\Sugarcrm\SearchEngine\SearchEngine::getInstance();
        $ftsSearch->setForceAsyncIndex(true);
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $purchase = SugarTestPurchaseUtilities::createPurchase();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        foreach ($types as $index => $type) {
            $name = 'test_' . strval($index) . '_c';
            $def = [
                'name' => $name,
                'type' => $type,
                'len' => 65,
                'source' => 'custom_fields',
            ];
            SugarTestHelper::setUpCustomField('RevenueLineItems', $def);
            $rli->$name = $values[$index];
            $rli->field_defs[$name] = $def;
            if ($index < 1) {
                SugarTestHelper::setUpCustomField('Purchases', $def);
                $purchase->field_defs[$name] = $def;
            } else {
                SugarTestHelper::setUpCustomField('PurchasedLineItems', $def);
                $pli->field_defs[$name] = $def;
            }
        }
        $rli->copyCustomFields($purchase);
        $rli->copyCustomFields($pli);
        $this->assertEquals($rli->test_0_c, $purchase->test_0_c);
        $this->assertNull($purchase->test_1_c);
        $this->assertEquals($rli->test_1_c, $pli->test_1_c);
        $this->assertNull($pli->test_0_c);
    }

    public function providerTestCopyFields(): array
    {
        return [
            [
                ['text', 'text'], ['fennel', 'ice cream',],
            ],
            [
                ['varchar', 'enum',], ['pizza', 23,],
            ],
        ];
    }

    /**
     * @covers ::processRliIds
     * @dataProvider providerProcessRlisIds
     * @param $hasMatchingPurchase bool
     */
    public function testProcessRliIdsMultipleRlis($hasMatchingPurchase): void
    {
        $account = SugarTestAccountUtilities::createAccount();
        $product = SugarTestProductTemplatesUtilities::createProductTemplate();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity('', $account);
        $opportunity->sales_stage = 'Closed Won';
        $opportunity->save();

        if ($hasMatchingPurchase) {
            $purchase = SugarTestPurchaseUtilities::createPurchase();
            $purchase->load_relationship('accounts');
            $purchase->load_relationship('product_templates');
            $purchase->accounts->add($account);
            $purchase->product_templates->add($product);
        }

        $rlis = [];
        $rli_ids = [];
        for ($i = 0; $i < 5; $i++) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->load_relationship('account_link');
            $rli->load_relationship('rli_templates_link');
            $rli->load_relationship('opportunities');
            $rli->account_link->add($account);
            $rli->rli_templates_link->add($product);
            $rli->opportunities->add($opportunity);
            if ($i % 2 === 0) {
                $rli->sales_stage = 'Closed Won';
                $rli->generate_purchase = 'Yes';
            } else {
                $rli->sales_stage = 'Prospecting';
                $rli->generate_purchase = 'No';
            }
            $rli->save();
            $rlis[$i] = $rli;
            $rli_ids[] = ['id' => $rli->id];
        }
        // Should generate NO Purchases/PLIs, as Opp sales stage is"Prospecting"
        RevenueLineItem::processRliIds($rli_ids);

        // Close our opp so that it cascades and closes all RLIs
        $opportunity->sales_stage_cascade = 'Closed Won';
        $opportunity->sales_stage = 'Closed Won';
        $opportunity->save();

        // Now every even indexed RLI should have generated a Purchase/PLI
        RevenueLineItem::processRliIds($rli_ids);

        $pli_ids = [];
        for ($i = 0; $i < 5; $i++) {
            $rli = BeanFactory::retrieveBean('RevenueLineItems', $rlis[$i]->id);
            if ($i % 2 === 0) {
                $pli = BeanFactory::retrieveBean('PurchasedLineItems', $rli->purchasedlineitem_id);
                $pli_ids[] = $pli->id;
                if ($hasMatchingPurchase) {
                    $this->assertEquals($purchase->id, $pli->purchase_id);
                }
                $this->assertEquals($pli->revenuelineitem_id, $rli->id);
                $this->assertEquals($rli->generate_purchase, 'Completed');
            } else {
                $this->assertNull($rli->purchasedlineitem_id);
                $this->assertEquals('No', $rli->generate_purchase);
            }
        }
        SugarTestPurchasedLineItemUtilities::removePurchasedLineItemsByID($pli_ids);
    }

    public function providerProcessRlisIds(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::setDurationFields
     * @dataProvider providerSetDurationFields
     * @param bool $hasAddOnToId
     * @param string $startDate
     * @param string $endDate
     * @param string $unit
     * @param int $expectedDiff
     */
    public function testSetDurationFields($hasAddOnToId, $startDate, $endDate, $unit, $expectedDiff)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->disableOriginalConstructor()
            ->getMock();
        $rli->service = true;
        $rli->service_start_date = $startDate;
        $rli->service_end_date = $endDate;

        if ($hasAddOnToId) {
            $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem($id = 'add_on_id');
            $rli->add_on_to_id = $pli->id;
        }

        SugarTestReflection::callProtectedMethod($rli, 'setDurationFields');

        if ($hasAddOnToId) {
            $this->assertEquals($unit, $rli->service_duration_unit);
            $this->assertEquals($expectedDiff, $rli->service_duration_value);
        } else {
            $this->assertEquals(null, $rli->service_duratation_unit);
            $this->assertEquals(null, $rli->service_duration_value);
        }

        // call setServiceEndDate and make sure the end date didn't change -
        // if that function changed it, then the calculation this function does
        // isn't right
        SugarTestReflection::callProtectedMethod($rli, 'setServiceEndDate');
        if ($hasAddOnToId) {
            $this->assertEquals($endDate, $rli->service_end_date);
        }

        SugarTestPurchasedLineItemUtilities::removePurchasedLineItemsByID(['add_on_id']);
    }

    public function providerSetDurationFields()
    {
        // $hasAddOnToId, $startDate, $endDate, $expectedDiff
        return [
            [false, '2020-01-01', '2020-01-01', 'day', 1],
            [true, '2020-01-01', '2020-01-01', 'day', 1],
            [true, '2020-01-01', '2020-02-01', 'day', 32],
            [true, '2020-01-01', '2021-01-01', 'day', 367], // leap year
            [true, '2021-01-01', '2022-01-01', 'day', 366], // non leap year
            [true, '2020-07-06', '2020-09-15', 'day', 72],
            [true, '2020-07-01', '2020-07-31', 'month', 1],
            [true, '2020-07-01', '2021-01-31', 'month', 7],
            [true, '2020-07-14', '2021-07-13', 'year', 1],
            [true, '2020-07-14', '2025-07-13', 'year', 5],
        ];
    }


    /**
     * Calculate total amount considering flexible duration
     *
     * @dataProvider dataProviderTotalAmountCalculation
     * @param $quantity
     * @param $discount_price
     * @param $discount_amount
     * @param $discount_select
     * @param $service_duration_value
     * @param $service_duration_unit
     * @param $cat_service_duration_value
     * @param $cat_service_duration_unit
     * @param $total_amount
     */
    public function testTotalAmountCalculation(
        $quantity,
        $discount_price,
        $discount_amount,
        $discount_select,
        $service_duration_value,
        $service_duration_unit,
        $cat_service_duration_value,
        $cat_service_duration_unit,
        $total_amount
    ): void {

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->discount_price = $discount_price;
        $rli->discount_amount = $discount_amount;
        $rli->discount_select = $discount_select;
        $rli->quantity = $quantity;
        $rli->service = 1;
        $rli->service_start_date = '2020-08-13';
        $rli->service_duration_value = $service_duration_value;
        $rli->service_duration_unit = $service_duration_unit;
        $rli->catalog_service_duration_value = $cat_service_duration_value;
        $rli->catalog_service_duration_unit = $cat_service_duration_unit;
        $rli->save();
        $this->assertEquals($total_amount, round($rli->total_amount, 2));
    }

    public function dataProviderTotalAmountCalculation(): array
    {
        //        $quantity,
        //        $discount_price,
        //        $discount_amount,
        //        $discount_select,
        //        $service_duration_value,
        //        $service_duration_unit,
        //        $cat_service_duration_value,
        //        $cat_service_duration_unit,
        //        $total_amount
        return [
            ['1', '100', 0, 0, '1', 'year', '1', 'year', '100'],
            ['10', '100', 0, 0, '1', 'year', '1', 'year', '1000'],
            ['1', '100', 0, 0, '3', 'year', '1', 'year', '300'],
            ['1', '100', 0, 0, '1', 'month', '1', 'year', '8.33'],
            ['1', '100', 0, 0, '18', 'month', '1', 'year', '150'],
            ['1', '100', 0, 0, '1', 'month', '2', 'year', '4.17'],
            ['1', '10', 0, 0, '7', 'day', '1', 'month', '2.30'],
            ['1', '10', 0, 0, '1', 'day', '3', 'month', '0.11'],
            ['1', '10', 0, 0, '6', 'month', '1', 'day', '1825'],
            ['1', '10', 0, 0, '18', 'month', '7', 'day', '782.14'],
            ['1', '10', 2, 0, '6', 'month', '1', 'day', '1823'],
            ['1', '10', 10, 1, '18', 'month', '7', 'day', '703.93'],
            ['1', '100', 10, 1, '730', 'day', '1', 'year', '180'],
            ['1', '-100', 10, 0, '1', 'year', '1', 'year', '-90'],
            ['1', '-100', 10, 1, '1', 'year', '1', 'year', '-90'],
            ['-1', '100', 10, 0, '1', 'year', '1', 'year', '-90'],
            ['-1', '100', 10, 1, '1', 'year', '1', 'year', '-90'],
        ];
    }
}
