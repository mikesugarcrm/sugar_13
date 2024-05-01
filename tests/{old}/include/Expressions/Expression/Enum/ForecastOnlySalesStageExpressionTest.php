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

class ForecastOnlySalesStageExpressionTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
    }


    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testEvaluateDoesNotContainClosedSalesStages()
    {
        $result = Parser::evaluate('forecastOnlySalesStages(false, false, true)')->evaluate();
        $this->assertNotContains('Closed Lost', $result);
        $this->assertNotContains('Closed Won', $result);
        $this->assertCount(8, $result);
    }

    public function testEvaluateContainsClosedWonAndNothingElse()
    {
        $result = Parser::evaluate('forecastOnlySalesStages(true, false, false)')->evaluate();
        $this->assertNotContains('Closed Lost', $result);
        $this->assertContains('Closed Won', $result);
        $this->assertCount(1, $result);
    }

    public function testEvaluateContainsClosedWonAndClosedLostAndNothingElse()
    {
        $result = Parser::evaluate('forecastOnlySalesStages(true, true, false)')->evaluate();
        $this->assertContains('Closed Lost', $result);
        $this->assertContains('Closed Won', $result);
        $this->assertCount(2, $result);
    }
}
