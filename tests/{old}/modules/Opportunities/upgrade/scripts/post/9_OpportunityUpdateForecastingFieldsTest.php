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
 * @coversDefaultClass SugarUpgradeOpportunityUpdateForecastingFields
 */
class SugarUpgradeOpportunityUpdateForecastingFieldsTest extends UpgradeTestCase
{
    public const OPPORTUNITY = '0e93c71a-e1f7-11ec-9227-acde48001123';
    public const REVENUE_LINE_ITEM_1 = '0e93c71a-e1f7-11ec-9227-acde48001124';
    public const REVENUE_LINE_ITEM_2 = '0e93c71a-e1f7-11ec-9227-acde48001125';

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('current_user');

        SugarTestOpportunityUtilities::createOpportunity(null, null, [
            'id' => self::OPPORTUNITY,
            'sales_stage' => 'Closed Won',
            'commit_stage' => 'include',
        ]);

        SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
            'id' => self::REVENUE_LINE_ITEM_1,
            'opportunity_id' => self::OPPORTUNITY,
            'sales_stage' => 'Closed Won',
            'commit_stage' => 'include',
            'likely_case' => 123.00,
            'base_rate' => 1.0,
        ]);

        SugarTestRevenueLineItemUtilities::createRevenueLineItem(null, [
            'id' => self::REVENUE_LINE_ITEM_2,
            'opportunity_id' => self::OPPORTUNITY,
            'sales_stage' => 'Closed Lost',
            'commit_stage' => 'exclude',
            'likely_case' => 456.00,
            'base_rate' => 0.5,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Before each test, set the values of the various fields to different
        // values so that we can tell when they have changed
        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->update('opportunities')
            ->set('forecasted_likely', 555)
            ->set('lost', 999)
            ->set('amount', 789)
            ->where($qb->expr()->eq('id', $qb->createPositionalParameter(self::OPPORTUNITY)))
            ->executeStatement();
        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->update('revenue_line_items')
            ->set('forecasted_likely', 999)
            ->where($qb->expr()->in(
                'id',
                $qb->createPositionalParameter(
                    [self::REVENUE_LINE_ITEM_1, self::REVENUE_LINE_ITEM_2],
                    \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
                )
            ))
            ->executeStatement();
    }

    /**
     * @covers ::updateOpportunitiesWithRlis
     */
    public function testUpdateOpportunitiesWithRlis()
    {
        $mockUpgrader = $this->getMockBuilder('SugarUpgradeOpportunityUpdateForecastingFields')
            ->disableOriginalConstructor()
            ->onlyMethods(['setFieldFiltering'])
            ->getMock();
        $mockUpgrader->db = DBManagerFactory::getInstance();
        $mockUpgrader->expects($this->once())->method('setFieldFiltering')->with('lost', true);

        // Only the Lost vardefs should be updated
        $mockConverter = $this->getMockBuilder('OpportunityWithRevenueLineItem')
            ->disableOriginalConstructor()
            ->onlyMethods(['updateFieldVardef'])
            ->getMock();
        $mockConverter->expects($this->once())->method('updateFieldVardef')->with('lost', [
            'calculated' => true,
            'enforced' => true,
            'formula' => 'rollupConditionalSum($revenuelineitems, "likely_case", "sales_stage", ' .
                'forecastOnlySalesStages(false,true,false))',
        ]);

        SugarTestReflection::callProtectedMethod($mockUpgrader, 'updateOpportunitiesWithRlis', [$mockConverter]);
        $opp = BeanFactory::retrieveBean('Opportunities', self::OPPORTUNITY, ['use_cache' => false]);

        // Only the Lost value should be updated
        $this->assertEquals(912.00, $opp->lost);
        $this->assertEquals(555.00, $opp->forecasted_likely);
    }

    /**
     * @covers ::updateOpportunitiesWithoutRlis
     * @dataProvider providerTestUpdateOpportunitiesWithoutRlis
     */
    public function testUpdateOpportunitiesWithoutRlis($forecastedLikelyDef)
    {
        $mockOpportunity = $this->getMockBuilder('Opportunity')
            ->disableOriginalConstructor()
            ->onlyMethods(['getFieldDefinition'])
            ->getMock();
        $mockOpportunity->expects($this->once())->method('getFieldDefinition')
            ->with('forecasted_likely')
            ->willReturn($forecastedLikelyDef);

        $mockUpgrader = $this->getMockBuilder('SugarUpgradeOpportunityUpdateForecastingFields')
            ->disableOriginalConstructor()
            ->onlyMethods(['getOpportunityBean'])
            ->getMock();
        $mockUpgrader->expects($this->once())->method('getOpportunityBean')->willReturn($mockOpportunity);
        $mockUpgrader->db = DBManagerFactory::getInstance();

        // Lost field vardefs should be updated. If the Forecasted Likely field formula is '',
        // if should also be updated
        $mockConverter = $this->getMockBuilder('OpportunityWithOutRevenueLineItem')
            ->disableOriginalConstructor()
            ->onlyMethods(['updateFieldVardef'])
            ->getMock();
        $callParams = [];
        $expectedCallParams = [
            [
                'lost',
                [
                    'calculated' => true,
                    'enforced' => true,
                    'formula' => 'ifElse(equal(indexOf($sales_stage, forecastOnlySalesStages(false, true, false)), -1), 0, ' .
                        '$amount)',
                    'studio' => true,
                ]
            ]
        ];
        $mockConverter->expects($this->any())->method('updateFieldVardef')->will(
            $this->returnCallback(function($arg1, $arg2) use (&$callParams) {
                $callParams[] = [$arg1, $arg2];
            })
        );
        if ($forecastedLikelyDef['calculated'] === false && $forecastedLikelyDef['formula'] === '') {
            $expectedCallParams[] = [
                'forecasted_likely',
                [
                    'calculated' => true,
                    'enforced' => true,
                    'formula' => 'ifElse(equal(indexOf($commit_stage, forecastIncludedCommitStages()), -1), 0, $amount)',
                ]
            ];
        }

        SugarTestReflection::callProtectedMethod($mockUpgrader, 'updateOpportunitiesWithoutRlis', [$mockConverter]);
        $opp = BeanFactory::retrieveBean('Opportunities', self::OPPORTUNITY, ['use_cache' => false]);

        // Check that the correct vardef updates were applied
        $this->assertEqualsCanonicalizing($expectedCallParams, $callParams);

        // Only the Lost value should be updated
        $this->assertEquals(0.00, $opp->lost);
        $this->assertEquals(555.00, $opp->forecasted_likely);
    }

    /**
     * Provider for testUpdateOpportunitiesWithoutRlis
     *
     * @return array[]
     */
    public function providerTestUpdateOpportunitiesWithoutRlis()
    {
        return [
            [
                [
                    'calculated' => true,
                    'formula' => 'ifElse(equal(indexOf($commit_stage, forecastIncludedCommitStages()), -1), 0, $amount)',
                ],
            ],
            [
                [
                    'calculated' => false,
                    'formula' => '',
                ],
            ],
        ];
    }
}
