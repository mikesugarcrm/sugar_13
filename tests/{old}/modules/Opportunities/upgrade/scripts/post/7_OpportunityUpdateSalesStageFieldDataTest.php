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

/**
 * @coversDefaultClass SugarUpgradeOpportunityUpdateSalesStageFieldData
 */
class SugarUpgradeOpportunityUpdateSalesStageFieldDataTest extends UpgradeTestCase
{
    /**
     * @var \DBManager|mixed
     */
    public $db;
    /**
     * @var string|mixed
     */
    public $scriptFileName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = DBManagerFactory::getInstance();
        $this->scriptFileName = '7_OpportunityUpdateSalesStageFieldData';

        SugarTestHelper::setUp('app_list_strings');
        $GLOBALS['app_list_strings'] = [
            'sales_stage_dom' => [
                'Prospecting' => 'Prospecting',
                'Qualification' => 'Qualification',
                'Needs Analysis' => 'Needs Analysis',
                'Value Proposition' => 'Value Proposition',
                'Id. Decision Makers' => 'Id. Decision Makers',
                'Perception Analysis' => 'Perception Analysis',
                'Proposal/Price Quote' => 'Proposal/Price Quote',
                'Negotiation/Review' => 'Negotiation/Review',
                'Closed Won' => 'Closed Won',
                'Closed Lost' => 'Closed Lost',
            ],
        ];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['app_list_strings']);
        SugarTestHelper::tearDown();
        parent::tearDown();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }

    /**
     * @covers ::run
     */
    public function testRun_IncorrectFlavor_DoesNotUpgrade()
    {
        $this->upgrader->setVersions('8.0.0', 'pro', '9.1.0', 'pro');
        $this->upgrader->setDb($this->db);

        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        $this->assertNotEmpty($script);

        $mock = $this->getMockBuilder(get_class($script))
            ->setMethods(['fixSalesStageField'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];

        $mock->expects($this->never())->method('fixSalesStageField');
        $mock->run();
    }

    /**
     * @covers ::run
     */
    public function testFixSalesStageField_OppsOnly_DoesNotUpgrade()
    {
        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];

        $this->upgrader->setVersions('8.0.0', 'ent', '9.1.0', 'ent');
        $this->upgrader->setDb($this->db);

        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        $this->assertNotEmpty($script);

        $mock = $this->getMockBuilder(get_class($script))
            ->setMethods(['fixSalesStageField'])
            ->setConstructorArgs([$this->upgrader])
            ->getMock();

        $mock->expects($this->never())->method('fixSalesStageField');
        $mock->run();
    }

    public function dataProviderFixSalesStageField_StageStatusNotClosed()
    {
        return [
            //one rli that is the first index of the sale_stage_dom
            [
                [
                    ['sales_stage' => 'Prospecting', 'date_closed' => '2019-01-01'],
                ],
                'Prospecting',
            ],
            [
                [
                    ['sales_stage' => 'Closed Won', 'date_closed' => '2019-06-01'],
                    ['sales_stage' => 'Closed Won', 'date_closed' => '2019-01-01'],
                    ['sales_stage' => 'Closed Lost', 'date_closed' => '2019-01-01'],
                    ['sales_stage' => 'Closed Lost', 'date_closed' => '2019-10-31'],
                    ['sales_stage' => 'Qualification', 'date_closed' => '2019-08-01'],
                    ['sales_stage' => 'Perception Analysis', 'date_closed' => '2019-08-01'],
                    ['sales_stage' => 'Value Proposition', 'date_closed' => '2019-08-01'],
                    ['sales_stage' => 'Id. Decision Makers', 'date_closed' => '2019-08-01'],
                    ['sales_stage' => 'Id. Decision Makers', 'date_closed' => '2019-08-01'],
                    ['sales_stage' => 'Negotiation/Review', 'date_closed' => '2019-03-01'],
                    ['sales_stage' => 'Negotiation/Review', 'date_closed' => '2019-03-01'],
                    ['sales_stage' => 'Negotiation/Review', 'date_closed' => '2019-03-01'],
                    ['sales_stage' => 'Negotiation/Review', 'date_closed' => '2019-03-01'],
                ],
                'Negotiation/Review',

            ],
            [
                [
                    ['sales_stage' => 'Closed Won', 'date_closed' => '2019-06-01'],
                    ['sales_stage' => 'Closed Won', 'date_closed' => '2019-01-01'],
                    ['sales_stage' => 'Closed Lost', 'date_closed' => '2019-01-01'],
                    ['sales_stage' => 'Closed Lost', 'date_closed' => '2019-10-31'],
                    ['sales_stage' => 'Qualification', 'date_closed' => '2019-05-01'],
                    ['sales_stage' => 'Qualifications', 'date_closed' => '2019-03-01'],
                    ['sales_stage' => 'Prospecting', 'date_closed' => '2019-10-01'],
                ],
                'Qualification',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFixSalesStageField_StageStatusNotClosed
     *
     * @covers ::fixSalesStageField
     */
    public function testFixSalesStageField_OppSalesStatusNotClosed_UpgradesDataBasedOnRlis($data, $expected)
    {
        $opp1 = SugarTestOpportunityUtilities::createOpportunity();

        foreach ($data as $row) {
            $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
            $rli->sales_stage = $row['sales_stage'];
            $rli->date_closed = $row['date_closed'];
            $rli->save();

            $opp1->revenuelineitems->add($rli);
        }

        //Need to set sales status back to In Progess because sales_status is a
        //calculated field
        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];
        $opp1->sales_status = 'In Progress';
        $opp1->save();

        Opportunity::$settings = [
            'opps_view_by' => 'RevenueLineItems',
        ];

        $this->upgrader->setVersions('8.0.0', 'ent', '9.1.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        SugarTestReflection::callProtectedMethod(
            $script,
            'fixSalesStageField'
        );

        $results = BeanFactory::retrieveBean($opp1->module_name, $opp1->id, ['use_cache' => false]);
        $this->assertSame($expected, $results->sales_stage);
    }

    /**
     * @covers ::fixSalesStageField
     */
    public function testFixSalesStageField_OppSalesStatusClosed_UpgradesData()
    {
        //Need to set to Opps view so that the Sales Status field doesn't calculate
        Opportunity::$settings = [
            'opps_view_by' => 'Opportunities',
        ];

        $salesStageInProgress = 'In Progress';
        $salesStageOption = 'Prospecting';

        $oppWon = SugarTestOpportunityUtilities::createOpportunity();
        $oppWon->sales_stage = $salesStageOption;
        $oppWon->sales_status = Opportunity::STATUS_CLOSED_WON;
        $oppWon->save();

        $oppLost = SugarTestOpportunityUtilities::createOpportunity();
        $oppWon->sales_stage = $salesStageOption;
        $oppLost->sales_status = Opportunity::STATUS_CLOSED_LOST;
        $oppLost->save();

        $this->upgrader->setVersions('8.0.0', 'ent', '9.1.0', 'ent');
        $this->upgrader->setDb($this->db);
        $script = $this->upgrader->getScript('post', $this->scriptFileName);

        SugarTestReflection::callProtectedMethod(
            $script,
            'fixSalesStageField'
        );

        $oppBean = BeanFactory::newBean($oppWon->module_name);
        $oppBean->retrieve($oppWon->id);

        $this->assertSame($oppWon->sales_status, $oppBean->sales_status);
        $this->assertSame(Opportunity::STATUS_CLOSED_WON, $oppBean->sales_stage);

        $oppBean = BeanFactory::newBean($oppLost->module_name);
        $oppBean->retrieve($oppLost->id);

        $this->assertSame($oppLost->sales_status, $oppBean->sales_status);
        $this->assertSame(Opportunity::STATUS_CLOSED_LOST, $oppBean->sales_stage);
    }
}
