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

class RevenueLineItemHooksTest extends TestCase
{
    /**
     * @var RevenueLineItem
     */
    protected $rli;

    public static function setUpBeforeClass(): void
    {
        SugarTestForecastUtilities::setUpForecastConfig([
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        ]);
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');

        $this->rli = $this->createPartialMock('RevenueLineItem', ['save']);
    }

    protected function tearDown(): void
    {
        $this->rli = null;
        SugarTestHelper::tearDown();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();

        $db = DBManagerFactory::getInstance();
        $db->query("DELETE FROM job_queue WHERE status='queued'");
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    /**
     * @covers       RevenueLineItemHooks::afterRelationshipDelete
     * @dataProvider dataAfterRelationshipDelete
     */
    public function testAfterRelationshipDelete($event, $link, $result, $deleted, $count)
    {
        $this->rli->deleted = $deleted;
        $hook = new RevenueLineItemHooks();

        $this->rli->expects($this->exactly($count))
            ->method('save');

        $ret = $hook->afterRelationshipDelete($this->rli, $event, $link);

        $this->assertEquals($result, $ret);
    }

    public function dataAfterRelationshipDelete()
    {
        return [
            ['after_relationship_delete', ['link' => 'account_link'], true, 0, 1],
            ['after_relationship_delete', ['link' => 'foo'], false, 0, 0],
            ['after_relationship_delete', ['link' => 'account_link'], false, 1, 0],
            ['after_relationship_delete', ['link' => 'foo'], false, 1, 0],
            ['foo', ['link' => 'account_link'], false, 0, 0],
            ['foo', ['link' => 'foo'], false, 0, 0],
        ];
    }


    /**
     * @param $useRlis
     * @param $salesStageChange
     * @param $generatePurchases
     * @param $expected
     * @dataProvider dataProviderQueuePurchaseGeneration
     */
    public function testQueuePurchaseGeneration(
        $useRlis,
        $salesStageChange,
        $generatePurchases,
        $expected
    ): void {

        $hookMock = new MockRevenueLineItemHooks();
        $hookMock::$useRevenueLineItems = $useRlis;
        $args = [
            'dataChanges' => [
                'sales_stage' => $salesStageChange ? ['after' => 'Closed Won',] : [],
                'generate_purchase' => $generatePurchases ? ['after' => 'Yes',] : ['after' => 'No',],
            ],
        ];
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add($rli);
        $opp->sales_stage = 'Prospecting';

        // RLI and Opp are open
        $result = $hookMock::queuePurchaseGeneration($rli, '', $args);
        $this->assertFalse($result);

        // RLI closed, Opp open
        $rli->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $result = $hookMock::queuePurchaseGeneration($rli, '', $args);
        $this->assertFalse($result);

        // Both RLI and Opp Closed
        $opp->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $result = $hookMock::queuePurchaseGeneration($rli, '', $args);
        $this->assertEquals($expected, $result);
    }

    public function dataProviderQueuePurchaseGeneration(): array
    {
        // args are:
        // 1. are we in RLIs mode
        // 2. is sales stage changing
        // 3. is generate purchases being set to "Yes"
        // 4. do we expect a job to be scheduled
        return [
            [true, false, true, true],
            [false, false, true, false],
            [true, true, true, false],
            [true, false, false, false],
            [true, true, false, false],
        ];
    }

    public function testQueuePurchaseGenerationUsingExistingClosedOpp()
    {
        $hookMock = new MockRevenueLineItemHooks();
        $hookMock::$useRevenueLineItems = true;

        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $opp->sales_stage = Opportunity::STAGE_CLOSED_WON;

        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->name = 'My test RLI';
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add($rli);

        $rli->sales_stage = Opportunity::STAGE_CLOSED_WON;
        $opp->sales_stage = Opportunity::STAGE_CLOSED_WON;

        $args = [
            'dataChanges' => [
                'sales_stage' => [
                    'before' => '',
                    'after' => Opportunity::STAGE_CLOSED_WON,
                ],
                'id' => [
                    'before' => '',
                    'after' => 'test-id',
                ],
                'generate_purchase' => [
                    'before' => '',
                    'after' => 'Yes',
                ],
            ],
        ];

        $result = $hookMock::queuePurchaseGeneration($rli, '', $args);
        $this->assertTrue($result);
    }
}

class MockRevenueLineItemHooks extends RevenueLineItemHooks
{
    public static $useRevenueLineItems = false;

    public static function useRevenueLineItems(): bool
    {
        return self::$useRevenueLineItems;
    }
}
