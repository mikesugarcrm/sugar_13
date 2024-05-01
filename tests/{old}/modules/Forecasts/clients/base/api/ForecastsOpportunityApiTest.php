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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @group ApiTests
 */
class ForecastsOpportunityApiTest extends TestCase
{
    /**
     * @var \RestService|mixed
     */
    public $serviceMock;
    public static $opps;
    public static $rlis;
    public static $oldLimit;
    public static $manager;
    public static $reportee;
    public static $timePeriod;

    /** @var ForecastsOpportunityApi */
    private $ForecastsOpportunityApi;

    /**
     * Creates Opportunities for a timeperiod. 3 At the beginning and three at
     * the end. Assigned to a manger, reportee and another user respectively
     * @return void
     * @throws SugarApiExceptionInvalidParameter
     * @throws SugarApiExceptionLicenseSeatsNeeded
     * @throws SugarApiExceptionNotAuthorized
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');

        $timedate = TimeDate::getInstance();
        $start_date = new DateTime();
        $start_date->modify('first day of this month');
        $end_date = new DateTime();
        $end_date = $end_date->modify('last day of next month');
        $end_date = $end_date->modify('last day of next month');

        $start_date = $timedate->asDbDate($start_date);
        $end_date = $timedate->asDbDate($end_date);
        self::$timePeriod = SugarTestTimePeriodUtilities::createTimePeriod($start_date, $end_date);
        self::$manager = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee = SugarTestUserUtilities::createAnonymousUser();
        self::$reportee->reports_to_id = self::$manager->id;
        self::$reportee->save();

        //Reportee Opps
        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = self::$reportee->id;
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $start_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $start_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = self::$reportee->id;
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $end_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $end_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        //Manager Opp
        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = self::$manager->id;
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $start_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $start_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = self::$manager->id;
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $end_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $end_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        //Opps not within the Reports to Tree.
        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = 'test-user-id';
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $start_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $start_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        $opp = BeanFactory::newBean('Opportunities');
        $opp->id = 'UNIT-TEST-' . create_guid_section(10);
        $opp->new_with_id = true;
        $opp->name = "TEST $start_date Opportunity";
        $opp->assigned_user_id = 'test-user-id';
        $opp->save();
        $rli = BeanFactory::newBean('RevenueLineItems');
        $rli->id = 'UNIT-TEST-' . create_guid_section(10);
        $rli->new_with_id = true;
        $rli->name = "TEST $end_date Opportunity RLI";
        $rli->likely_case = 10000;
        $rli->date_closed = $end_date;
        $rli->save();
        $opp->load_relationship('revenuelineitems');
        $opp->revenuelineitems->add([$rli]);
        self::$opps[] = $opp;
        self::$rlis[] = $rli;

        // Clean up any hanging related records
        SugarRelationship::resaveRelatedBeans();
    }

    protected function setUp(): void
    {
        $this->ForecastsOpportunityApi = new ForecastsOpportunityApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM sugarfavorites WHERE created_by = '" . $GLOBALS['current_user']->id . "'");
        $GLOBALS['db']->query("DELETE FROM subscriptions WHERE created_by = '{$GLOBALS['current_user']->id}'");
        $GLOBALS['sugar_config']['max_list_limit'] = self::$oldLimit;
        SugarConfig::getInstance()->clearCache();
    }

    /**
     * Cleans up any records created for the test.
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        // Opportunities clean up
        if (safeCount(self::$opps)) {
            $oppIds = [];
            foreach (self::$opps as $opp) {
                $oppIds[] = $opp->id;
            }
            $oppIds = "('" . implode("','", $oppIds) . "')";
            $GLOBALS['db']->query("DELETE FROM opportunities WHERE id IN {$oppIds}");
        }

        if (safeCount(self::$rlis)) {
            $rliIds = [];
            foreach (self::$rlis as $rli) {
                $rliIds[] = $opp->id;
            }
            $rliIds = "('" . implode("','", $rliIds) . "')";
            $GLOBALS['db']->query("DELETE FROM revenuelineitems WHERE id IN {$rliIds}");
        }
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }


    /*
     * Checks that the API only returns records for the currrent user when type
     * is rep
     */
    public function testRepForecastOpportunities()
    {
        $result = $this->ForecastsOpportunityApi->getForecastOpportunities(
            $this->serviceMock,
            [
                'filter' => [['name' => ['$starts' => 'TEST']]],
                'user_id' => self::$reportee->id,
                'type' => 'Direct',
                'time_period' => self::$timePeriod->id,
            ]
        );

        $this->assertEquals(2, safeCount($result['records']), 'Incorrect number of results');
    }

    /**
     * Checks that the API returns records for both the manager and reportee
     * when the type is Manager
     */
    public function testManagerForecastOpportunities()
    {
        $result = $this->ForecastsOpportunityApi->getForecastOpportunities(
            $this->serviceMock,
            [
                'filter' => [['name' => ['$starts' => 'TEST']]],
                'user_id' => self::$manager->id,
                'type' => 'Rollup',
                'time_period' => self::$timePeriod->id,
            ]
        );

        $this->assertEquals(4, safeCount($result['records']), 'Incorrect number of results');
    }

    /**
     * Checks that only the managers records are returned when the type is set
     * to rep.
     */
    public function testManagerSelfForecastOpportunities()
    {
        $result = $this->ForecastsOpportunityApi->getForecastOpportunities(
            $this->serviceMock,
            [
                'filter' => [['name' => ['$starts' => 'TEST']]],
                'user_id' => self::$manager->id,
                'type' => 'Direct',
                'time_period' => self::$timePeriod->id,
            ]
        );

        $this->assertEquals(2, safeCount($result['records']), 'Incorrect number of results');
    }

    /*
     * Checks that the fall backs are used for type and time period. The
     * fall backs are type = Rollup and Time Period = current time period.
     */
    public function testFallbackForecastOpportunitiesOptions()
    {
        //This does rely on $self:timePeriod is the one that is returned by within the API as the currentTime.
        $result = $this->ForecastsOpportunityApi->getForecastOpportunities(
            $this->serviceMock,
            [
                'filter' => [['name' => ['$starts' => 'TEST']]],
                'user_id' => self::$reportee->id,
                'type' => 'junk-data',
                'time_period' => 'junk-id-test',
            ]
        );

        $this->assertEquals(2, safeCount($result['records']), 'Incorrect number of results');
    }
}
