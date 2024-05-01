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
 * @coversDefaultClass SugarJobCreatePurchasesAndPLIs
 */
class SugarJobCreatePurchasesAndPLIsTest extends TestCase
{
    private $account;
    private $purchase;
    private $product;
    private $opportunity;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUP('beanList');
        SugarTestHelper::setUP('beanFiles');
        SugarTestHelper::setUP('current_user');
        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];
    }

    public function setUp(): void
    {
        $this->account = SugarTestAccountUtilities::createAccount();
        $this->product = SugarTestProductTemplatesUtilities::createProductTemplate();
        $this->purchase = SugarTestPurchaseUtilities::createPurchase();
        $this->opportunity = SugarTestOpportunityUtilities::createOpportunity();
        $this->purchase->load_relationship('accounts');
        $this->purchase->load_relationship('product_templates');
        $this->purchase->accounts->add($this->account);
        $this->purchase->product_templates->add($this->product);
    }

    public function tearDown(): void
    {
        SugarTestPurchasedLineItemUtilities::removeAllCreatedPurchasedLineItems();
        SugarTestPurchaseUtilities::removeAllCreatedPurchases();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
    }

    /**
     * @covers ::run
     */
    public function testCreatePurchaseAndPlisJobNoExistingPurchases(): void
    {
        global $current_user;
        $rlis = [];
        $rli_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->load_relationship('account_link');
            $rli->load_relationship('opportunities');
            $rli->account_link->add($this->account);
            $rli->opportunities->add($this->opportunity);
            $rli->generate_purchase = 'Yes';
            $rli->sales_stage = 'Closed Won';
            $rli->date_closed = '2020-01-01';
            $rli->save();
            $rlis[] = $rli;
            $rli_ids[] = ['id' => $rli->id];
        }

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'TestJobQueue',
            'class::SugarJobCreatePurchasesAndPLIs',
            json_encode(['data' => $rli_ids]),
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status);

        $pli_ids = [];
        $purchase_ids = [];
        foreach ($rlis as $rli) {
            $pli = BeanFactory::retrieveBean('PurchasedLineItems', $rli->purchasedlineitem_id);
            $purchase = BeanFactory::retrieveBean('Purchases', $pli->purchase_id);
            $pli_ids[] = $pli->id;
            $purchase_ids[] = $purchase->id;
            $this->assertEquals($purchase->id, $pli->purchase_id);
            $this->assertEquals($pli->revenuelineitem_id, $rli->id);
            $this->assertEquals($rli->account_id, $purchase->account_id);
        }

        SugarTestPurchasedLineItemUtilities::removePurchasedLineItemsByID($pli_ids);
        SugarTestPurchaseUtilities::removePurchasesByID($purchase_ids);
    }

    /**
     * @covers ::run
     */
    public function testCreatePurchaseAndPlisJobWithExistingPurchases(): void
    {
        global $current_user;
        $rlis = [];
        $rli_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->load_relationship('account_link');
            $rli->load_relationship('rli_templates_link');
            $rli->load_relationship('opportunities');
            $rli->account_link->add($this->account);
            $rli->rli_templates_link->add($this->product);
            $rli->opportunities->add($this->opportunity);
            $rli->generate_purchase = 'Yes';
            $rli->sales_stage = 'Closed Won';
            $rli->date_closed = '2020-01-01';
            $rli->save();
            $rlis[] = $rli;
            $rli_ids[] = ['id' => $rli->id];
        }

        $job = SugarTestJobQueueUtilities::createAndRunJob(
            'TestJobQueue',
            'class::SugarJobCreatePurchasesAndPLIs',
            json_encode(['data' => $rli_ids]),
            $current_user
        );

        $this->assertEquals(SchedulersJob::JOB_SUCCESS, $job->resolution);
        $this->assertEquals(SchedulersJob::JOB_STATUS_DONE, $job->status);

        $pli_ids = [];
        foreach ($rlis as $rli) {
            $pli = BeanFactory::retrieveBean('PurchasedLineItems', $rli->purchasedlineitem_id);
            $pli_ids[] = $pli->id;
            $this->assertEquals($this->purchase->id, $pli->purchase_id);
            $this->assertEquals($pli->revenuelineitem_id, $rli->id);
        }

        SugarTestPurchasedLineItemUtilities::removePurchasedLineItemsByID($pli_ids);
    }
}
