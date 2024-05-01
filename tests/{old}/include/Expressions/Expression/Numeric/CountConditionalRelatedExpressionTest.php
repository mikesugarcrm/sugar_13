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

class CountConditionalRelatedExpressionTest extends TestCase
{
    public function testCountConditionalRelated()
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save', 'load_relationship'])
            ->getMock();


        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getBeansForSugarLogic'])
            ->getMock();

        $opp->revenuelineitems = $link2;

        $rlis = [];
        for ($x = 1; $x <= 3; $x++) {
            $rli = $this->getMockBuilder('RevenueLineItem')
                ->setMethods(['save'])
                ->getMock();
            $rli->tax = $x;

            $rlis[] = $rli;
        }

        $opp->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValue(true));

        $link2->expects($this->any())
            ->method('getBeansForSugarLogic')
            ->will($this->returnValue($rlis));

        $expr = 'countConditional($revenuelineitems, "tax", "2")';
        $result = Parser::evaluate($expr, $opp)->evaluate();
        $this->assertSame(1, $result);
    }
}
