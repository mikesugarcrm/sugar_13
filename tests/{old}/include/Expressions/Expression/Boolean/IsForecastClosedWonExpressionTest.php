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

class IsForecastClosedWonExpressionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        Forecast::$settings = [];
    }

    public static function dataProviderCheckStatus()
    {
        return [
            ['test stage 1', 'false'],
            ['Closed Won', 'true'],
            ['Closed Lost', 'false'],
        ];
    }

    /**
     * @dataProvider dataProviderCheckStatus
     *
     * @param $status
     * @param $expected
     * @throws Exception
     */
    public function testIsForecastClosedWonEvaluate($status, $expected)
    {
        Forecast::$settings = [
            'is_setup' => 1,
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
        ];

        /* @var $rli RevenueLineItem */
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $rli->sales_stage = $status;

        $expr = 'isForecastClosedWon($sales_stage)';
        $result = Parser::evaluate($expr, $rli)->evaluate();

        $this->assertSame($expected, strtolower($result));
    }
}
