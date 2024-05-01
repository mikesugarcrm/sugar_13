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
 * Class QuoteConvertApiTest
 * @coversDefaultClass QuoteConvertApi
 */
class QuoteConvertApiTest extends TestCase
{
    /**
     * @var Opportunity
     */
    protected $opp;

    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Link2
     */
    protected $opp_link2;

    /**
     * @var Link2
     */
    protected $quote_link2;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');

        $this->opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'retrieve', 'load_relationship', 'ACLAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->opp_link2 = $this->getMockBuilder('Link2')
            ->setMethods(['add', 'get'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder('Quote')
            ->setMethods(['save', 'retrieve', 'load_relationship', 'get_linked_beans', 'ACLAccess', 'get_product_bundles'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote_link2 = $this->getMockBuilder('Link2')
            ->setMethods(['add', 'get', 'getBeans'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        unset($this->opp, $this->quote, $this->quote_link2, $this->opp_link2);
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::registerApiRest
     */
    public function testRegisterApiRest()
    {
        $convertApi = $this->getConvertApi('UnitTest');

        $endpoints = $convertApi->registerApiRest();

        $this->assertEquals(1, safeCount($endpoints));
        $this->assertEquals(3, safeCount($endpoints['convert']['path']));
        $this->assertEquals('POST', $endpoints['convert']['reqType']);
    }

    /**
     * @covers ::convertQuote
     */
    public function testConvertQuoteThrowsExceptionWhenNoSaveAccessToOpportunity()
    {
        $convert_api = $this->getConvertApi('UnitTest', ['requireArgs', 'loadBean']);

        $this->quote->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(true));


        $this->opp->expects($this->once())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(false));

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $mockServiceBase = new RestService();

        $args = [
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record',
        ];

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $convert_api->convertQuote($mockServiceBase, $args);
    }

    /**
     * @covers ::convertQuote
     */
    public function testConvertQuoteThrowsExceptionWhenQuoteHasOpportunity()
    {
        $convert_api = $this->getConvertApi('UnitTest', ['requireArgs', 'loadBean']);

        $this->quote->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(true));

        $this->opp->expects($this->atLeastOnce())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(true));

        $this->quote->opportunities = $this->quote_link2;
        $this->quote->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities');

        $this->quote_link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue(['one record']));

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $mockServiceBase = new RestService();

        $args = [
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record',
        ];

        $this->expectException(SugarApiExceptionEditConflict::class);
        $convert_api->convertQuote($mockServiceBase, $args);
    }

    /**
     * @covers ::convertQuote
     */
    public function testConvertQuote()
    {
        $convert_api = $this->getConvertApi(
            'UnitTest',
            [
                'requireArgs',
                'loadBean',
                'formatBean',
                'mapQuoteToOpportunity',
                'convertQuoteLineItemsToRevenueLineItems',
                'linkQuoteContactsToOpportunity',
                'linkQuoteContractsToOpportunity',
            ]
        );

        $convert_api->expects($this->any())
            ->method('loadBean')
            ->will(
                $this->onConsecutiveCalls(
                    $this->quote,
                    $this->opp
                )
            );

        $convert_api->expects($this->once())
            ->method('mapQuoteToOpportunity');

        $convert_api->expects($this->once())
            ->method('convertQuoteLineItemsToRevenueLineItems');

        $convert_api->expects($this->once())
            ->method('linkQuoteContactsToOpportunity');

        $convert_api->expects($this->once())
            ->method('linkQuoteContractsToOpportunity');

        $convert_api->expects($this->exactly(2))
            ->method('formatBean')
            ->will(
                $this->onConsecutiveCalls(
                    ['id' => 'opp_id', '_module' => 'Opportunity'],
                    ['id' => 'quote_id', '_module' => 'Quote']
                )
            );

        $this->quote->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(true));


        $this->opp->expects($this->atLeastOnce())
            ->method('ACLAccess')
            ->with('save')
            ->will($this->returnValue(true));

        // we should call save once
        $this->opp->expects($this->once())
            ->method('save');

        $this->quote->opportunities = $this->quote_link2;
        $this->quote->expects($this->once())
            ->method('load_relationship')
            ->with('opportunities');

        $this->quote_link2->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue([]));

        $mockServiceBase = new RestService();

        $args = [
            'targetModule' => 'Opportunity',
            'module' => 'Quotes',
            'record' => 'test_record',
        ];

        $return = $convert_api->convertQuote($mockServiceBase, $args);

        $this->assertEquals('Opportunity', $return['record']['_module']);
        $this->assertEquals('Quote', $return['related_record']['_module']);
    }

    /**
     * @covers ::mapQuoteToOpportunity
     */
    public function testMapQuoteToOpportunity()
    {
        $values = [
            'id' => create_guid(),
            'name' => 'UnitTest',
            'assigned_user_id' => 'TestAssignedUserId',
            'assigned_user_name' => 'TestAssignedUserName',
            'total' => '100.00',
            'currency_id' => 'CurrencyId',
            'base_rate' => '1.0',
            'billing_account_id' => 'BillingAccountId',
            'date_quote_expected_closed' => '2004-01-01',
        ];

        foreach ($values as $key => $value) {
            $this->quote->$key = $value;
        }

        $quoteToOppKeyMap = [
            'date_quote_expected_closed' => 'date_closed',
            'billing_account_id' => 'account_id',
            'id' => 'quote_id',
            'total' => 'amount',
        ];

        $this->opp->expects($this->once())
            ->method('save');

        $convert_api = $this->getConvertApi('Opportunities');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'mapQuoteToOpportunity',
            [$this->quote, $this->opp]
        );

        foreach ($values as $key => $value) {
            if (isset($quoteToOppKeyMap[$key])) {
                $key = $quoteToOppKeyMap[$key];
            }

            $this->assertEquals($this->opp->$key, $value);
        }
    }

    /**
     * @covers ::mapQuoteToOpportunity
     */
    public function testMapQuoteToOpportunityDoesNotSetOppAmountWhenNotForecastingByOpps()
    {
        $values = [
            'id' => create_guid(),
            'name' => 'UnitTest',
            'assigned_user_id' => 'TestAssignedUserId',
            'assigned_user_name' => 'TestAssignedUserName',
            'total' => '100.00',
            'currency_id' => 'CurrencyId',
            'base_rate' => '1.0',
            'billing_account_id' => 'BillingAccountId',
            'date_quote_expected_closed' => '2004-01-01',
        ];

        foreach ($values as $key => $value) {
            $this->quote->$key = $value;
        }

        $this->opp->expects($this->once())
            ->method('save');

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'mapQuoteToOpportunity',
            [$this->quote, $this->opp]
        );

        $this->assertEmpty($this->opp->amount);
    }

    /**
     * @covers ::convertQuoteLineItemsToRevenueLineItems
     */
    public function testConvertQuoteLineItemsToRevenueLineItems()
    {
        $this->opp->revenuelineitems = $this->opp_link2;

        $this->opp_link2->expects($this->exactly(3))
            ->method('add');

        $rliMock = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $mockRliClassName = get_class($rliMock);

        $products = [];

        for ($i = 0; $i < 3; $i++) {
            $productMock = $this->getMockBuilder('Product')
                ->setMethods(['convertToRevenueLineItem'])
                ->getMock();

            $productMock->expects($this->once())
                ->method('convertToRevenueLineItem')
                ->will($this->returnValue(new $mockRliClassName()));

            $products[] = $productMock;
        }

        $mockBundle = $this->getMockBuilder('ProductBundle')
            ->setMethods(['getProducts'])
            ->getMock();

        $mockBundle->expects($this->once())
            ->method('getProducts')
            ->willReturn($products);

        $this->quote->expects($this->once())
            ->method('get_product_bundles')
            ->will(
                $this->returnValue([$mockBundle])
            );

        $convert_api = $this->getConvertApi('RevenueLineItems');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'convertQuoteLineItemsToRevenueLineItems',
            [$this->quote, $this->opp]
        );
    }

    /**
     * @dataProvider dataProviderGetCommitStage
     * @param $probability
     * @param $expected
     * @covers ::getCommitStage
     */
    public function testGetCommitStage($probability, $expected)
    {
        $convert_api = $this->getConvertApi('RevenueLineItems');
        $actual = SugarTestReflection::callProtectedMethod(
            $convert_api,
            'getCommitStage',
            [$probability]
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * Data Provider
     *
     * @return array
     */
    public static function dataProviderGetCommitStage()
    {
        return [
            ['10', 'exclude'],
            ['65', 'include'],
        ];
    }

    /**
     * @covers ::convertQuoteLineItemsToRevenueLineItems
     */
    public function testConvertQuoteLineItemsToRevenueLineItemsReturnsFalseWhenForecastNotByRLI()
    {
        $convert_api = $this->getConvertApi('UnitTest');

        $return = SugarTestReflection::callProtectedMethod(
            $convert_api,
            'convertQuoteLineItemsToRevenueLineItems',
            [$this->quote, $this->opp]
        );

        $this->assertFalse($return);
    }

    /**
     * @covers ::linkQuoteContractsToOpportunity
     */
    public function testLinkQuoteContractsToOpportunity()
    {
        $this->opp->contracts = $this->opp_link2;

        $this->opp_link2->expects($this->exactly(2))
            ->method('add');

        $this->quote->contracts = $this->quote_link2;

        $this->quote_link2->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['id_1', 'id-2']));

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContractsToOpportunity',
            [$this->quote, $this->opp]
        );
    }

    /**
     * @covers ::linkQuoteContractsToOpportunity
     */
    public function testLinkQuoteContractsToOpportunityDoesNotCallAddWhenNoContracts()
    {
        $this->opp->contracts = $this->opp_link2;

        $this->opp_link2->expects($this->never())
            ->method('add');

        $this->quote->contracts = $this->quote_link2;

        $this->quote_link2->expects($this->once())
            ->method('get')
            ->will($this->returnValue([]));

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContractsToOpportunity',
            [$this->quote, $this->opp]
        );
    }

    /**
     * @covers ::linkQuoteContactsToOpportunity
     */
    public function testLinkQuoteContactsToOpportunity()
    {
        $this->opp->contacts = $this->opp_link2;

        $this->opp->expects($this->once())
            ->method('load_relationship')
            ->with('contacts');

        $this->opp_link2->expects($this->exactly(2))
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    ['id_1'],
                    ['id_2']
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            [$this->quote, $this->opp]
        );
    }

    /**
     * @covers ::linkQuoteContactsToOpportunity
     */
    public function testLinkQuoteContactsToOpportunityOnlyAddsOneWhenIdsAreTheSame()
    {
        $this->opp->contacts = $this->opp_link2;

        $this->opp->expects($this->once())
            ->method('load_relationship')
            ->with('contacts');

        $this->opp_link2->expects($this->once())
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    ['id_1'],
                    ['id_1']
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            [$this->quote, $this->opp]
        );
    }

    /**
     * @covers ::linkQuoteContactsToOpportunity
     */
    public function testLinkQuoteContactsToOpportunityDoesNotAddWhenNoContacts()
    {
        $this->opp->contacts = $this->opp_link2;

        $this->opp_link2->expects($this->never())
            ->method('add');

        $this->quote->shipping_contacts = $this->quote_link2;
        $this->quote->billing_contacts = $this->quote_link2;

        $this->quote_link2->expects($this->exactly(2))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    [],
                    []
                )
            );

        $convert_api = $this->getConvertApi('UnitTest');

        SugarTestReflection::callProtectedMethod(
            $convert_api,
            'linkQuoteContactsToOpportunity',
            [$this->quote, $this->opp]
        );
    }

    /**
     * Utility method
     *
     * @param string $forecast_by
     * @param array $mock_methods
     * @return MockObject
     */
    protected function getConvertApi($forecast_by, array $mock_methods = [])
    {
        if (!in_array('getForecastConfig', $mock_methods)) {
            $mock_methods[] = 'getForecastConfig';
        }

        $convert_api = $this->getMockBuilder('QuoteConvertApi')
            ->setMethods($mock_methods)
            ->disableOriginalConstructor()
            ->getMock();

        $convert_api->expects($this->any())
            ->method('getForecastConfig')
            ->will($this->returnValue(
                [
                    'forecast_by' => $forecast_by,
                    'forecast_ranges' => 'show_binary',
                    'show_binary_ranges' => [
                        'include' => ['min' => 60, 'max' => 100],
                        'exclude' => ['min' => 0, 'max' => 59],
                    ],
                ]
            ));

        return $convert_api;
    }
}
