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
 * RS-99 Prepare Accounts Api
 */
class RS99Test extends TestCase
{
    /**
     * @var Account
     */
    protected $account = null;

    /**
     * @var Opportunity
     */
    protected $opportunity = null;

    /** @var Revenuelineitem */
    protected $revenuelineitem = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, true]);

        $this->account = SugarTestAccountUtilities::createAccount();
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity('', $this->account);

        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];

        $this->revenuelineitem = new RevenueLineItem();
        $this->revenuelineitem->name = 'Revenue Line Item ' . self::class;
        $this->revenuelineitem->opportunity_id = $this->opportunity->id;
        $this->revenuelineitem->sales_stage = 'Closed Lost';
        $this->revenuelineitem->save();

        $this->opportunity->retrieve($this->opportunity->id);
        $this->opportunity->sales_status = 'Closed Lost';
        $this->opportunity->save();
    }

    protected function tearDown(): void
    {
        Opportunity::$settings = [];

        if ($this->revenuelineitem instanceof SugarBean) {
            $this->revenuelineitem->mark_deleted($this->revenuelineitem->id);
        }
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    /**
     * Test asserts count of records (success result of query)
     */
    public function testOpportunityStats()
    {
        $api = new AccountsApi();
        $actual = $api->opportunityStats(SugarTestRestUtilities::getRestServiceMock(), [
            'module' => 'Accounts',
            'record' => $this->account->id,
        ]);
        $this->assertArrayHasKey('lost', $actual);
        $this->assertEquals(1, $actual['lost']['count']);
    }
}
