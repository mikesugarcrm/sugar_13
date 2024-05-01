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

class ProductsCurrencyRateUpdateTest extends TestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    private $db;

    /**
     * @var RevenueLineItemsCurrencyRateUpdate
     */
    private $mock;

    protected function setUp(): void
    {
        $this->db = SugarTestHelper::setUp('mock_db');
        $this->setupMockClass();
        SugarTestHelper::setUp('app_strings');
    }

    protected function tearDown(): void
    {
        $this->tearDownMockClass();
        SugarTestHelper::tearDown();
    }

    /**
     * setup the mock class and override getClosedStages to return a static array for the test
     */
    public function setupMockClass()
    {
        $this->mock = $this->createPartialMock('ProductsCurrencyRateUpdate', ['getProductsWithNonClosedQuote']);
        // we want to use our mock database for these tests, so replace it
        SugarTestReflection::setProtectedValue($this->mock, 'db', $this->db);
    }

    /**
     * tear down mock class
     */
    public function tearDownMockClass()
    {
        unset($this->mock);
    }

    public function testDoCustomUpdateUsDollarRate()
    {
        $this->mock->expects($this->once())
            ->method('getProductsWithNonClosedQuote')
            ->will($this->returnValue(['id1', 'id2']));

        // setup the query strings we are expecting and what they should return
        $this->db->addQuerySpy(
            'rate_update',
            "/UPDATE mytable SET amount_usdollar = 1\.234 \/ base_rate/",
            [[1]]
        );

        // run our tests with mockup data
        $result = $this->mock->doCustomUpdateUsDollarRate('mytable', 'amount_usdollar', '1.234', 'abc');
        // make sure we get the expected result and the expected run counts
        $this->assertEquals(true, $result);
        $this->assertEquals(1, $this->db->getQuerySpyRunCount('rate_update'));
    }
}
