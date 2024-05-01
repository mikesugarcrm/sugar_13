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

class MaxRelatedDateExpressionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    public static function dataProviderDateFormatCheck()
    {
        return [
            ['04/14/2014', 'm/d/Y'],
            ['14/04/2014', 'd/m/Y'],
            ['2014/04/14', 'Y/m/d'],
            ['04.14.2014', 'm.d.Y'],
            ['14.04.2014', 'd.m.Y'],
            ['2014.04.14', 'Y.m.d'],
            ['04-14-2014', 'm-d-Y'],
            ['14-04-2014', 'd-m-Y'],
            ['2014-04-14', 'Y-m-d'],
        ];
    }

    /**
     * @dataProvider dataProviderDateFormatCheck
     *
     * @param $date
     * @param $format
     */
    public function testMaxRelatedDateEvaluate($date, $format)
    {
        $opp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->getMock();

        $link2 = $this->getMockBuilder('Link2')
            ->setConstructorArgs(['revenuelineitems', $opp])
            ->setMethods(['getBeansForSugarLogic'])
            ->getMock();

        $rel_bean1 = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();
        $db_date = SugarDateTime::createFromFormat($format, $date)->setTime(0, 0, 0)->asDbDate();
        /* @var $rel_bean1 RevenueLineItem */
        $rel_bean1->date_closed = $date;
        $rel_bean1->fetched_row['date_closed'] = $db_date;


        $rel_bean2 = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $db_date2 = SugarDateTime::createFromFormat($format, $date)->modify('-20 days')->setTime(0, 0, 0);
        /* @var $rel_bean2 RevenueLineItem */
        $rel_bean2->date_closed = $db_date2->format($format);
        $rel_bean2->fetched_row['date_closed'] = $db_date2->asDbDate();

        $link2->expects($this->any())
            ->method('getBeansForSugarLogic')
            ->will($this->returnValue([$rel_bean1, $rel_bean2]));

        /* @var $opp Opportunity */
        $opp->revenuelineitems = $link2;

        $expr = 'maxRelatedDate($revenuelineitems, "date_closed")';
        $result = Parser::evaluate($expr, $opp)->evaluate();

        $this->assertEquals($db_date, $result);
    }
}
