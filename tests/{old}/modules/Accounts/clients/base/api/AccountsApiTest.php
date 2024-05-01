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

class AccountsApiTest extends TestCase
{
    /**
     * @var AccountsApi
     */
    protected $api;

    protected function setUp(): void
    {
        $this->api = new AccountsApi();

        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];
    }

    protected function tearDown(): void
    {
        Opportunity::$settings = [];
    }

    public function testGetOpportunityStatusFieldReturnsSalesStage()
    {
        $field = SugarTestReflection::callProtectedMethod($this->api, 'getOpportunityStatusField');

        $this->assertEquals('sales_stage', $field);
    }

    public function testGetOpportunityStatusFieldReturnsSalesStatus()
    {
        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];
        $field = SugarTestReflection::callProtectedMethod($this->api, 'getOpportunityStatusField');

        $this->assertEquals('sales_status', $field);
    }
}
