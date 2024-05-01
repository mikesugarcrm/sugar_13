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

class OpportunityHooksTest extends TestCase
{
    protected static $queuedPurchaseJobs = [];

    protected static $currentUser;

    public static function setUpBeforeClass(): void
    {
        SugarTestForecastUtilities::setUpForecastConfig([
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        ]);
    }

    protected function tearDown(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        // Clean up current user if needed
        if (static::$currentUser) {
            $GLOBALS['current_user'] = static::$currentUser;
            static::$currentUser = null;
        }

        $db = DBManagerFactory::getInstance();
        $ids = [];
        foreach (static::$queuedPurchaseJobs as $id) {
            $ids[] = $db->quoted($id);
        }

        if ($ids) {
            $db->query('DELETE FROM job_queue WHERE id IN (' . implode(',', $ids) . ')');
        }
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestHelper::tearDown();
    }

    public function dataProviderSetOpportunitySalesStatus()
    {
        // # of won, # of lost, #total, #status
        return [
            // all closed_won
            [2, 0, 2, Opportunity::STATUS_CLOSED_WON],
            // closed won and closed lost
            [2, 2, 4, Opportunity::STATUS_CLOSED_WON],
            // all closed lost
            [0, 2, 2, Opportunity::STATUS_CLOSED_LOST],
            // only closed lost but higher total
            [0, 2, 4, Opportunity::STATUS_IN_PROGRESS],
            // only cosed won but higher total
            [2, 0, 4, Opportunity::STATUS_IN_PROGRESS],
            // no closed won or lost but still a total
            [0, 0, 4, Opportunity::STATUS_IN_PROGRESS],
            // no closed won, closed lost and total
            [0, 0, 0, Opportunity::STATUS_NEW],
        ];
    }

    /**
     * @group opportunities
     */
    public function testSetOpportunitySalesStatusOnNewOpp()
    {
        $oppMock = $this->createPartialMock('Opportunity', ['save', 'retrieve']);
        $oppMock->id = 'test';

        /* @var $hookMock OpportunityHooks */
        MockOpportunityHooks::$useRevenueLineItems = true;
        MockOpportunityHooks::$returnFromCount = [
            'sales_stage_won' => 0,
            'sales_stage_lost' => 0,
            'default' => 0,
        ];

        MockOpportunityHooks::setSalesStatus($oppMock, 'before_save', []);

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, Opportunity::STATUS_NEW);
    }

    /**
     * @dataProvider dataProviderSetOpportunitySalesStatus
     * @group opportunities
     * @group revenuelineitems
     */
    public function testSetOpportunitySalesStatusWithAccess($won_count, $lost_count, $total_count, $status)
    {
        $oppMock = $this->createPartialMock('Opportunity', [
            'save',
            'retrieve',
            'ACLFieldAccess',
            'retrieveSalesStatus',
        ]);
        $oppMock->id = 'test';
        $oppMock->fetched_row['id'] = 'test';

        MockOpportunityHooks::$useRevenueLineItems = true;

        $closed_won = ['won'];
        $closed_lost = ['lost'];

        MockOpportunityHooks::$settings = [
            'is_setup' => 1,
            'sales_stage_won' => $closed_won,
            'sales_stage_lost' => $closed_lost,
        ];

        MockOpportunityHooks::$returnFromCount = [
            'sales_stage_won' => $won_count,
            'sales_stage_lost' => $lost_count,
            'default' => $total_count,
        ];

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(true));

        MockOpportunityHooks::setSalesStatus($oppMock, 'before_save', []);

        // assert the status is what it should be
        $this->assertEquals($oppMock->sales_status, $status);
    }

    public function testSetOpportunitySalesStatusWithoutAccess()
    {
        $oppMock = $this->createPartialMock('Opportunity', ['save', 'retrieve', 'ACLFieldAccess']);

        $closed_won = ['won'];
        $closed_lost = ['lost'];

        MockOpportunityHooks::$settings = [
            'is_setup' => 1,
            'sales_stage_won' => $closed_won,
            'sales_stage_lost' => $closed_lost,
        ];

        $oppMock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(false));

        $oppMock->sales_status = 'testing1';

        MockOpportunityHooks::setSalesStatus($oppMock, 'before_save', []);

        // assert the status is what it should be
        $this->assertEquals('testing1', $oppMock->sales_status);
    }

    /**
     * @dataProvider beforeSaveIncludedCheckProvider
     */
    public function testBeforeSaveIncludedCheck($sales_stage, $commit_stage, $probability, $expected)
    {
        $hookMock = new MockOpportunityHooks();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $opp->probability = $probability;
        $opp->sales_stage = $sales_stage;
        $opp->commit_stage = $commit_stage;

        $hookMock->beforeSaveIncludedCheck($opp, 'before_save', null);
        $this->assertEquals($opp->commit_stage, $expected);
    }

    public function beforeSaveIncludedCheckProvider()
    {
        return [
            ['Closed Won', 'exclude', 100, 'include'],
            ['Closed Lost', 'include', 0, 'exclude'],
        ];
    }

    /**
     * @dataProvider fixWorksheetAccountAssignmentProvider
     */
    public function testfixWorksheetAccountAssignment($useRlis, $useForecast, $args, $result)
    {
        $hookMock = new MockOpportunityHooks();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $hookMock::$bypassSaveWorksheet = true;
        $hookMock::$useRevenueLineItems = $useRlis;
        $hookMock::$forecastIsSetup = $useForecast;

        $returnVal = $hookMock::fixWorksheetAccountAssignment($opp, '', $args);
        $this->assertEquals($returnVal, $result);
    }

    public function fixWorksheetAccountAssignmentProvider()
    {
        return [
            [false, true, ['relationship' => 'accounts_opportunities', 'related_id' => 'foo'], true],
            [true, true, ['relationship' => 'accounts_opportunities'], false],
            [true, false, ['relationship' => 'accounts_opportunities'], false],
            [false, false, ['relationship' => 'accounts_opportunities'], false],
            [true, true, ['relationship' => 'foo'], false],
            [false, true, ['relationship' => 'foo'], false],
            [true, false, ['relationship' => 'foo'], false],
            [false, false, ['relationship' => 'foo'], false],
            [true, true, [], false],
            [false, true, [], false],
            [true, false, [], false],
            [false, false, [], false],
            [true, true, null, false],
            [false, true, null, false],
            [true, false, null, false],
            [false, false, null, false],
        ];
    }


    /**
     * @param bool $useRlis Are we using RLIs
     * @param string $salesStage The sales stage of the opp
     * @param bool $licenses Current users licenses
     * @param bool $result The expectation of the test
     * @throws SugarQueryException
     * @dataProvider dataProviderQueueRliToPurchase
     */
    public function testQueueRliToPurchaseJob($useRlis, $salesStage, $licenses, $result): void
    {
        $hookMock = new MockOpportunityHooks();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $userMock = $this->createPartialMock('User', [
            'getLicenseTypes',
        ]);

        $userMock->expects($this->any())
            ->method('getLicenseTypes')
            ->willReturn($licenses);

        if (isset($GLOBALS['current_user'])) {
            static::$currentUser = $GLOBALS['current_user'];
        }

        $GLOBALS['current_user'] = $userMock;

        $args = [
            'dataChanges' => [
                'sales_stage' => [
                    'after' => $salesStage,
                ],
            ],
        ];
        $hookMock::$useRevenueLineItems = $useRlis;

        // Clean up the environment for each test
        $hookMock::resetScheduledJobIDs();

        // Run the test
        $returnVal = $hookMock::queueRLItoPurchaseJob($opp, '', $args);

        // Capture what needs cleanup
        static::$queuedPurchaseJobs = array_merge(
            static::$queuedPurchaseJobs,
            $hookMock::getScheduledJobIDs()
        );

        // Handle assertions
        $this->assertEquals($result, $returnVal);
    }

    public function dataProviderQueueRliToPurchase()
    {
        return [
            [true, '', ['SUGAR_SERVE'], false,],
            [true, '', ['SUGAR_SELL'], false,],
            [true, Opportunity::STAGE_CLOSED_LOST, ['SUGAR_SELL'], false,],
            [false, Opportunity::STAGE_CLOSED_WON, ['SUGAR_SELL'], false,],
            [true, Opportunity::STAGE_CLOSED_WON, ['SUGAR_SELL'], true,],
        ];
    }

    /**
     * @covers ::generateRenewalOpportunity()
     */
    public function testGenerateRenewalOpportunity()
    {
        $args = [];
        $rliBean = $this->createMock('RevenueLineItem');
        // Only expect 1 save, for when the renewal RLI ID is saved to the parent RLI
        $rliBean->expects($this->exactly(1))->method('save');
        $newRliBean = $this->createMock('RevenueLineItem');
        $newRliBean->id = 'id';
        $renewalOppBean = $this->createMock('Opportunity');
        $renewalOppBean->method('load_relationship')->willReturn(true);
        $renewalOppBean->expects($this->exactly(1))
            ->method('createNewRenewalRLI')
            ->with($rliBean)
            ->willReturn($newRliBean);

        // case1: parent renewal
        $parentBean = $this->createPartialMock('Opportunity', [
            'getExistingRenewalOpportunity',
        ]);
        $parentBean->expects($this->once())
            ->method('getExistingRenewalOpportunity')
            ->willReturn($renewalOppBean);
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'updateRenewalRLIs',
            'canRenew',
            'retrieveRLIBean',
        ]);
        $opBean->method('retrieveRliBean')->willReturn($newRliBean);
        $opBean->method('canRenew')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn($parentBean);
        $opBean->method('updateRenewalRLIs')->with([$rliBean])->willReturn([]);
        $args['dataChanges']['sales_status']['after'] = Opportunity::STATUS_CLOSED_WON;
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));

        // check that the renewal RLI ID on the original RLI was set
        $this->assertEquals($rliBean->renewal_rli_id, $newRliBean->id);

        // case2: existing renewal
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'getExistingRenewalOpportunity',
            'canRenew',
            'retrieveRLIBean',
        ]);
        $opBean->method('retrieveRliBean')->willReturn($newRliBean);
        $opBean->method('canRenew')->willReturn(true);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn(null);
        $opBean->method('getExistingRenewalOpportunity')->willReturn($renewalOppBean);
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));

        // case3: new renewal
        $opBean = $this->createPartialMock('Opportunity', [
            'getClosedWonRenewableRLIs',
            'getRenewalParent',
            'getExistingRenewalOpportunity',
            'createNewRenewalOpportunity',
            'canRenew',
            'retrieveRLIBean',
        ]);
        $opBean->method('canRenew')->willReturn(true);
        $opBean->method('retrieveRliBean')->willReturn($newRliBean);
        $opBean->method('getClosedWonRenewableRLIs')->willReturn([$rliBean]);
        $opBean->method('getRenewalParent')->willReturn(null);
        $opBean->method('getExistingRenewalOpportunity')->willReturn(null);
        $opBean->method('createNewRenewalOpportunity')->willReturn($renewalOppBean);
        $this->assertTrue(OpportunityHooks::generateRenewalOpportunity($opBean, 'after_save', $args));
    }
}

class MockOpportunityHooks extends OpportunityHooks
{
    public static $useRevenueLineItems = false;
    public static $forecastIsSetup = true;
    public static $bypassSaveWorksheet = false;

    public static $returnFromCount = [
        'sales_stage_won' => 0,
        'sales_stage_lost' => 0,
        'default' => 0,
    ];

    public static function useRevenueLineItems()
    {
        return self::$useRevenueLineItems;
    }


    public static function isForecastSetup()
    {
        return self::$forecastIsSetup;
    }

    public static function saveWorksheet(Opportunity $bean, $event, $args)
    {
        if (self::$bypassSaveWorksheet) {
            return true;
        } else {
            return parent::saveWorksheet($bean, $event, $args);
        }
    }

    public static function getCountOfRevenueLineItems($opportunityId, $salesStages = [])
    {
        switch ($salesStages) {
            case static::$settings['sales_stage_won']:
                $result = self::$returnFromCount['sales_stage_won'];
                break;
            case static::$settings['sales_stage_lost']:
                $result = self::$returnFromCount['sales_stage_lost'];
                break;
            default:
                $result = self::$returnFromCount['default'];
        }
        return $result;
    }
}
