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
 * Class PurchasedLineItemTest
 * @coversDefaulPurchasedLineItem
 */
class PurchasedLineItemTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['PurchasedLineItems']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }

    /**
     * @dataProvider dataProviderDiscountCalculation
     * @param $discount_price
     * @param $quantity
     * @param $discount_amount
     * @param $expected_total
     */
    public function testDiscountCalculation($discount_price, $quantity, $discount_amount, $expected_total): void
    {
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pli->discount_price = $discount_price;
        $pli->discount_amount = $discount_amount;
        $pli->quantity = $quantity;
        $pli->save();
        $this->assertEquals((float)$expected_total, (float)$pli->total_amount);
    }

    public function dataProviderDiscountCalculation(): array
    {
        // values are price, quantity, discount_amount, and expected_total
        return [
            ['100.00', 1, '0', '100'],
            ['200.00', -1, '10', '-190'],
            ['200.00', 1, '10', '190'],
        ];
    }

    /**
     * @dataProvider dataProviderAnnualRevenueCalculation
     * @param $revenue
     * @param $discount_price
     * @param $discount_amount
     * @param $quantity
     * @param $service
     * @param $service_duration_unit
     * @param $service_duration_value
     */
    public function testAnnualRevenueCalculation(
        $revenue,
        $discount_price,
        $discount_amount,
        $quantity,
        $service,
        $service_duration_unit,
        $service_duration_value,
        $annual_revenue
    ): void {

        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pli->revenue = $revenue;
        $pli->discount_price = $discount_price;
        $pli->discount_amount = $discount_amount;
        $pli->quantity = $quantity;
        $pli->service = $service;
        $pli->service_start_date = '2020-01-01';
        $pli->service_duration_unit = $service_duration_unit;
        $pli->service_duration_value = $service_duration_value;
        $pli->save();
        $this->assertEquals($annual_revenue, round($pli->annual_revenue, 2));
    }

    public function dataProviderAnnualRevenueCalculation(): array
    {
        // values are revenue, discount_price, discount_amount, quantity, service, service_duration_unit,
        // service_duration_value, annual_revenue
        return [
            ['1000.00', '1000.00', 0, 1, false, '', '', '1000.00'],
            ['300.00', '300.00', 0, 1, true, 'year', '3', '100.00'],
            ['-300.00', '-300.00', 0, 1, true, 'year', '3', '-100.00'],
            ['100.00', '100.00', 0, 1, true, 'month', '18', '66.67'],
            ['18.00', '18.00', 0, 1, true, 'day', '18', '365.00'],
            ['1000.00', '1000.00', 0, 1, true, 'day', '545', '669.72'],
        ];
    }

    /**
     * @covers ::updateRelatedPurchase
     * @dataProvider providerTestUpdateRelatedPurchase
     *
     * @param Array $pliDataArray array of PLIs to create
     * @param string $start_date the expected start_date of the Purchase affected
     * @param string $end_date the expected end_date of the Purchase affected
     */
    public function testUpdateRelatedPurchase($pliDataArray, $start_date, $end_date)
    {
        // Create a purchase
        $purchase = SugarTestPurchaseUtilities::createPurchase();

        // Create PLIs that point to the account. On save, they should update
        // that purchases's start_date and end_date fields
        foreach ($pliDataArray as $pliData) {
            $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
            $pli->purchase_id = $purchase->id;
            $pli->service = $pliData['service'];
            $pli->service_duration_unit = $pliData['service_duration_unit'];
            $pli->service_duration_value = $pliData['service_duration_value'];
            $pli->service_start_date = $pliData['service_start_date'];
            $pli->service_end_date = $pliData['service_end_date'];
            $pli->save();
        }

        // Check that the purchase's start_date and end_date were correctly calculated
        // on PLI save
        $resultPurchase = BeanFactory::retrieveBean('Purchases', $purchase->id);
        $this->assertEquals($start_date, $resultPurchase->start_date);
        $this->assertEquals($end_date, $resultPurchase->end_date);
    }

    public function providerTestUpdateRelatedPurchase()
    {
        return [
            // No related PLIs
            [
                [
                ],
                '',
                '',
            ],
            // 2 related service PLIs with different start and end dates
            [
                [
                    [
                        'service' => 1,
                        'service_start_date' => '2020-01-01',
                        'service_end_date' => '2020-11-12',
                        'service_duration_unit' => 'day',
                        'service_duration_value' => '317',
                    ],
                    [
                        'service' => 1,
                        'service_start_date' => '2020-05-01',
                        'service_end_date' => '2025-05-01',
                        'service_duration_unit' => 'year',
                        'service_duration_value' => '5',
                    ],
                ],
                '2020-01-01',
                '2025-04-30',
            ],
            // 2 related goods PLIs with the same start and end dates
            [
                [
                    [
                        'service' => 0,
                        'service_start_date' => '2019-10-05',
                        'service_end_date' => '2019-10-05',
                        'service_duration_unit' => 'day',
                        'service_duration_value' => '1',
                    ],
                    [
                        'service' => 0,
                        'service_start_date' => '2020-08-08',
                        'service_end_date' => '2020-08-08',
                        'service_duration_unit' => 'day',
                        'service_duration_value' => '1',],
                ],
                '2019-10-05',
                '2020-08-08',
            ],
            // 3 related PLIs one of which is service
            [
                [
                    [
                        'service' => 1,
                        'service_start_date' => '2017-01-31',
                        'service_end_date' => '2050-12-31',
                        'service_duration_unit' => 'year',
                        'service_duration_value' => '33',
                    ],
                    [
                        'service' => 0,
                        'service_start_date' => '2021-06-01',
                        'service_end_date' => '2021-06-01',
                        'service_duration_unit' => 'day',
                        'service_duration_value' => '1',
                    ],
                    [
                        'service' => 0,
                        'service_start_date' => '2060-10-05',
                        'service_end_date' => '2060-10-05',
                        'service_duration_unit' => 'day',
                        'service_duration_value' => '1',
                    ],
                ],
                '2017-01-31',
                '2060-10-05',
            ],
        ];
    }

    /**
     * @covers ::setServiceEndDate
     * @dataProvider providerTestSetServiceEndDate
     */
    public function testSetServiceEndDate(array $serviceFields, ?string $expectedEndDate, $shouldClearFields)
    {
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();

        // Set the service fields accordingly
        $pli->service = $serviceFields['service'];
        $pli->service_start_date = $serviceFields['service_start_date'];
        $pli->service_duration_value = $serviceFields['service_duration_value'];
        $pli->service_duration_unit = $serviceFields['service_duration_unit'];

        SugarTestReflection::callProtectedMethod($pli, 'setServiceEndDate');

        $this->assertSame($expectedEndDate, $pli->service_end_date);

        if ($shouldClearFields) {
            $this->assertSame(false, $pli->service);
            $this->assertSame('1', $pli->service_duration_value);
            $this->assertSame('day', $pli->service_duration_unit);
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
                    'service_duration_value' => '1',
                    'service_duration_unit' => 'day',
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
                false,
            ],
        ];
    }

    /**
     * testMarkRelationshipsDeleted
     * This test will mark a PLI as deleted and test that the RLI association is updated to remove the recently deleted
     * PLI id.
     */
    public function testMarkRelationshipsDeleted()
    {
        // Create an RLI and PLI, and link them together
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $rli->load_relationship('purchasedlineitem');
        $rli->purchasedlineitem->add($pli);
        $rli->purchasedlineitem_id = $pli->id;
        $rli->save();

        // Mark PLI as deleted
        $pli->mark_deleted($pli->id);

        // Check that the RLI's purchasedlineitem_id has been cleared
        $rli = BeanFactory::retrieveBean('RevenueLineItems', $rli->id, [
            'use_cache' => false,
        ]);
        $this->assertEquals(null, $rli->purchasedlineitem_id);
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

        $pli = SugarTestPurchasedLineItemUtilities::createPurchasedLineItem();
        $pli->discount_price = $discount_price;
        $pli->discount_amount = $discount_amount;
        $pli->discount_select = $discount_select;
        $pli->quantity = $quantity;
        $pli->service = 1;
        $pli->service_start_date = '2020-08-13';
        $pli->service_duration_value = $service_duration_value;
        $pli->service_duration_unit = $service_duration_unit;
        $pli->catalog_service_duration_value = $cat_service_duration_value;
        $pli->catalog_service_duration_unit = $cat_service_duration_unit;
        $pli->save();
        $this->assertEquals($total_amount, round($pli->total_amount, 2));
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
