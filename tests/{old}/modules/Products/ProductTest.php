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
 * Class ProductTest
 * @coversDefaultClass Product
 */
class ProductTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestProductUtilities::removeAllCreatedProducts();
        SugarTestHelper::tearDown();
    }

    public function tearDown(): void
    {
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
    }

    /**
     * @param String $amount
     * @param String $quantity
     * @param String $discount
     * @param String $discount_select
     * @param String $likely_expected
     * @throws SugarMath_Exception
     * @dataProvider productDataProvider
     * @covers ::convertToRevenueLineItem
     */
    public function testConvertProductToRLI($amount, $quantity, $discount, $discount_select, $likely_expected)
    {
        /* @var $product Product */
        $product = $this->getMockBuilder('Product')->setMethods(['save'])->getMock();

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $discount_amount = $discount;

        if ($discount_select === 1) {
            $product->total_amount = SugarMath::init()->exp('(?*?)-((?*?)*(?/100))', [$amount, $quantity, $amount, $quantity, $discount_amount])->result();
        } else {
            $product->total_amount = SugarMath::init()->exp('((?*?)-?)', [$amount, $quantity, $discount_amount])->result();
        }

        $product->name = 'Hello World';
        $product->discount_price = $amount;
        $product->quantity = $quantity;
        $product->discount_amount = $discount;
        $product->discount_select = $discount_select;
        $product->fetched_row = [];

        foreach ($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->{$field['name']};
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        $this->assertEquals($product->revenuelineitem_id, $rli->id);
        $this->assertEquals($product->name, $rli->name);
        $this->assertEquals(
            $likely_expected,
            $rli->likely_case,
            'Likely Case Is Wrong'
        );
        // lets make sure that the discount_amount is correct
        $this->assertEquals(
            $discount_amount,
            $rli->discount_amount,
            'Discount Amount Is Wrong'
        );
    }

    /**
     * productDataProvider
     */
    public function productDataProvider()
    {
        // $amount, $quantity, $discount, $discount_select, $likely_expected
        return [
            ['100.00', '1', '0', null, '100.000000'],
            ['1000.00', '10', '0', null, '10000.000000'],
            ['100.00', '10', '1', null, '999.000000'],
            ['100.00', '1', '0', 1, '100.000000'],
            ['100.00', '1', '10', 1, '90.000000'],
            ['100.00', '2', '20', 1, '160.000000'],
            ['0.13', '1000', '10', 1, '117.000000'],
            ['0.25', '89765', '21456.00', null, '985.250000'],
        ];
    }


    /**
     * @param bool $has_add_on
     * @dataProvider dataProviderConvertToRLICopiesAddOnPLI
     * @covers ::convertToRevenueLineItem
     */
    public function testConvertToRLICopiesAddOnPLI($has_add_on)
    {
        /* @var $product Product */
        $product = $this->getMockBuilder('Product')->setMethods(['save'])->getMock();

        $product->expects($this->any())
            ->method('save')
            ->will($this->returnValue(true));

        $product->name = 'Hello World';
        $product->total_amount = '100.00';
        $product->discount_price = '100.00';
        $product->quantity = '1';
        $product->discount_amount = '0';
        $product->discount_select = null;
        $product->fetched_row = [];

        foreach ($product->getFieldDefinitions() as $field) {
            $product->fetched_row[$field['name']] = $product->{$field['name']};
        }

        if ($has_add_on) {
            $add_on_to_pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem('add_on_to_pli_id');
            $product->add_on_to_id = $add_on_to_pli->id;
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');

        $rli = $product->convertToRevenueLineItem();

        if ($has_add_on) {
            $this->assertEquals($product->add_on_to_id, $rli->add_on_to_id);
        } else {
            $this->assertNull($rli->add_on_to_id);
        }
    }

    public function dataProviderConvertToRLICopiesAddOnPLI()
    {
        return [
            [false],
            [true],
        ];
    }


    /**
     * @dataProvider dataProviderUpdateCurrencyBaseRate
     * @param string $stage
     * @param boolean $expected
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRate($stage, $expected)
    {
        $product = $this->createPartialMock('Product', ['save', 'load_relationship']);
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $bundle = $this->createPartialMock('ProductBundle', ['save', 'load_relationship']);

        $bundle->expects($this->once())
            ->method('load_relationship')
            ->with('quotes')
            ->willReturn(true);

        /* @var $quote Quote */
        $quote = $this->createPartialMock('Quote', ['save']);

        $quote->quote_stage = $stage;

        $quote_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $quote_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                [
                    $quote,
                ]
            );

        /* @var $product Product */
        $bundle->quotes = $quote_link2;

        $bundle_link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $bundle_link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                [
                    $bundle,
                ]
            );

        /* @var $product Product */
        $product->product_bundles = $bundle_link2;

        $this->assertEquals($expected, $product->updateCurrencyBaseRate());
    }

    public function dataProviderUpdateCurrencyBaseRate()
    {
        return [
            ['Draft', true],
            ['Negotiation', true],
            ['Delivered', true],
            ['On Hold', true],
            ['Confirmed', true],
            ['Closed Accepted', false],
            ['Closed Lost', false],
            ['Closed Dead', false],
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

        $product = SugarTestProductUtilities::createProduct();
        $product->quantity = $quantity;
        $product->discount_price = $discount_price;
        $product->discount_amount = $discount_amount;
        $product->discount_select = $discount_select;
        $product->save();

        // lets make sure the totals are correct
        $this->assertEquals(
            $total_amount,
            $product->total_amount,
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
     * @covers ::updateCurrencyBaseRate
     */
    public function testUpdateCurrencyBaseRateWithNotQuoteReturnTrue()
    {
        $product = $this->createPartialMock('Product', ['save', 'load_relationship']);
        $product->expects($this->once())
            ->method('load_relationship')
            ->with('product_bundles')
            ->willReturn(true);

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeans'])
            ->getMock();

        $link2->expects($this->once())
            ->method('getBeans')
            ->willReturn(
                []
            );

        /* @var $product Product */
        $product->product_bundles = $link2;

        $this->assertTrue($product->updateCurrencyBaseRate());
    }

    /**
     * @covers ::get_summary_text
     */
    public function testGetSummaryText()
    {
        $product = $this->createPartialMock('Product', ['save', 'load_relationship']);
        $product->name = 'test';

        $this->assertEquals('test', $product->get_summary_text());
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

    public static function dataProviderPopulateFromTemplateWillReturnFalse()
    {
        return [
            [null],
            ['one_id'],
        ];
    }

    /**
     * @dataProvider dataProviderPopulateFromTemplateWillReturnFalse
     * @param string $template_id
     * @covers ::populateFromTemplate
     */
    public function testPopulateFromTemplateWillReturnFalse($template_id)
    {
        $product = $this->createPartialMock('Product', ['save']);

        $product->product_template_id = $template_id;
        $product->fetched_row = [
            'product_template_id' => $template_id,
        ];

        $actual = SugarTestReflection::callProtectedMethod($product, 'populateFromTemplate');

        $this->assertFalse($actual);
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPriceDoesNotRunIfFieldEmpty()
    {
        $fields = [
            'pricing_formula',
            'cost_price',
            'list_price',
            'discount_price',
            'pricing_factor',
        ];

        $product = $this->createPartialMock('Product', ['save', 'getPriceFormula']);

        $product->expects($this->never())
            ->method('getPriceFormula');

        foreach ($fields as $field) {
            $product->$field = null;
        }

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    /**
     * @covers ::calculateDiscountPrice
     */
    public function testCalculateDiscountPrice()
    {
        $product = $this->createPartialMock('Product', ['save', 'getPriceFormula']);
        $product->pricing_formula = 'PercentageDiscount';
        $product->cost_price = '100.000000';
        $product->list_price = '150.000000';
        $product->discount_price = '25.000000';
        $product->pricing_factor = '12.00';


        SugarAutoLoader::load('modules/ProductTemplates/formulas/price_list_discount.php');

        $formula = $this->getMockBuilder('PercentageDiscount')
            ->setMethods(['calculate_price'])
            ->getMock();

        $formula->expects($this->once())
            ->method('calculate_price')
            ->with(
                $product->cost_price,
                $product->list_price,
                $product->discount_price,
                $product->pricing_factor
            );

        $product->expects($this->once())
            ->method('getPriceFormula')
            ->willReturn($formula);

        SugarTestReflection::callProtectedMethod($product, 'calculateDiscountPrice');
    }

    public function dataProviderGetPriceFormula()
    {
        return [
            ['Fixed'],
            ['ProfitMargin'],
            ['PercentageMarkup'],
            ['PercentageDiscount'],
            ['IsList'],
        ];
    }

    /**
     * @dataProvider dataProviderGetPriceFormula
     * @covers ::getPriceFormula
     * @param string $formula
     */
    public function testGetPriceFormula($formula)
    {
        $product = $this->createPartialMock('Product', ['save']);

        $actual = SugarTestReflection::callProtectedMethod($product, 'getPriceFormula', [$formula]);

        $this->assertInstanceOf($formula, $actual);

        unset($GLOBALS['price_formulas']);
    }

    /**
     * @dataProvider dataProviderCheckQuantity
     * @covers ::checkQuantity
     * @param mixed $actual
     * @param integer $expected
     */
    public function testQuantityNotDefaulted($actual, $expected)
    {
        $product = $this->getMockBuilder('Product')
            ->setMethods(['save'])
            ->getMock();

        $product->quantity = $actual;

        SugarTestReflection::callProtectedMethod($product, 'checkQuantity');

        $this->assertEquals($expected, $product->quantity);
    }

    public static function dataProviderCheckQuantity()
    {
        return [
            ['', 0],
            [null, 0],
            [0, 0],
            [1, 1],
            [42, 42],
        ];
    }

    /**
     * Tests to make sure product_bundles always saves before quotes
     *
     * @covers ::getRelatedCalcFields
     */
    public function testGetRelatedCalcFields()
    {
        global $dictionary;

        $product = $this->getMockBuilder('Product')
            ->setMethods(['save'])
            ->getMock();

        $initialState = $dictionary['Product']['related_calc_fields'];

        // set dictionary for test
        $dictionary['Product']['related_calc_fields'] = ['quotes', 'product_bundles'];

        $result = SugarTestReflection::callProtectedMethod($product, 'getRelatedCalcFields');

        $this->assertEquals($result, ['product_bundles', 'quotes']);

        // test if both product_bundles or quotes is missing from the list
        $dictionary['Product']['related_calc_fields'] = ['test', 'quotes'];

        $result = SugarTestReflection::callProtectedMethod($product, 'getRelatedCalcFields');

        $this->assertEquals($result, ['test', 'quotes']);

        // restore dictionary
        $dictionary['Product']['related_calc_fields'] = $initialState;
    }


    /**
     * @covers ::setServiceEndDate
     * @dataProvider providerTestSetServiceEndDate
     */
    public function testSetServiceEndDate(array $serviceFields, ?string $expectedEndDate, $shouldClearFields)
    {
        $mockProduct = $this->getMockBuilder('Product')
            ->disableOriginalConstructor()
            ->getMock();
        // Set the service fields accordingly
        $mockProduct->service = $serviceFields['service'];
        $mockProduct->service_start_date = $serviceFields['service_start_date'];
        $mockProduct->service_duration_value = $serviceFields['service_duration_value'];
        $mockProduct->service_duration_unit = $serviceFields['service_duration_unit'];
        SugarTestReflection::callProtectedMethod($mockProduct, 'setServiceEndDate');
        $this->assertSame($expectedEndDate, $mockProduct->service_end_date);
        if ($shouldClearFields) {
            $this->assertSame(false, $mockProduct->service);
            $this->assertSame(null, $mockProduct->service_start_date);
            $this->assertSame(null, $mockProduct->service_duration_value);
            $this->assertSame(null, $mockProduct->service_duration_unit);
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
                true,
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
        $qli = $this->getMockBuilder('Product')
            ->disableOriginalConstructor()
            ->getMock();
        $qli->service = true;
        $qli->service_start_date = $startDate;
        $qli->service_end_date = $endDate;

        if ($hasAddOnToId) {
            $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem($id = 'add_on_id');
            $qli->add_on_to_id = $pli->id;
        }

        SugarTestReflection::callProtectedMethod($qli, 'setDurationFields');

        if ($hasAddOnToId) {
            $this->assertEquals($unit, $qli->service_duration_unit);
            $this->assertEquals($expectedDiff, $qli->service_duration_value);
        } else {
            $this->assertEquals(null, $qli->service_duratation_unit);
            $this->assertEquals(null, $qli->service_duration_value);
        }

        // call setServiceEndDate and make sure the end date didn't change -
        // if that function changed it, then the calculation this function does
        // isn't right
        SugarTestReflection::callProtectedMethod($qli, 'setServiceEndDate');
        if ($hasAddOnToId) {
            $this->assertEquals($endDate, $qli->service_end_date);
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

        $product = SugarTestProductUtilities::createProduct();
        $product->discount_price = $discount_price;
        $product->discount_amount = $discount_amount;
        $product->discount_select = $discount_select;
        $product->quantity = $quantity;
        $product->service = 1;
        $product->service_start_date = '2020-08-13';
        $product->service_duration_value = $service_duration_value;
        $product->service_duration_unit = $service_duration_unit;
        $product->catalog_service_duration_value = $cat_service_duration_value;
        $product->catalog_service_duration_unit = $cat_service_duration_unit;
        $product->save();
        $this->assertEquals($total_amount, round($product->total_amount, 2));
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
