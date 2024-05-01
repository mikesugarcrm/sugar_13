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

class WhereTest extends TestCase
{
    /**
     * asDb should be called if bean isn't present
     * asDbType shouldn't be called
     */
    public function testDateRangeWithoutBean()
    {
        $bean = $this->getMockBuilder('Account')->setMethods(['getFieldDefinition'])->getMock();
        $bean->expects($this->never())->method('getFieldDefinition');
        $q = new SugarQuery();
        $q->from($bean);
        $dateTime = new DateTime();

        /** @var TimeDate|MockObject $timeDate */
        $timeDate = $this->createPartialMock('TimeDate', ['parseDateRange', 'asDb', 'asDbType']);
        $timeDate->expects($this->once())->method('parseDateRange')->will($this->returnValue([$dateTime, $dateTime]));
        $timeDate->expects($this->exactly(2))->method('asDb')->will($this->returnValue(3));
        $timeDate->expects($this->never())->method('asDbType');

        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockForAbstractClass('SugarQuery_Builder_Where', [$q], '', false, true, true, ['timeDateInstance', 'queryAnd', 'lte', 'gte'], false);
        $where->expects($this->any())->method('timeDateInstance')->will($this->returnValue($timeDate));
        $where->expects($this->any())->method('queryAnd')->will($this->returnValue($where));
        $where->expects($this->once())->method('gte')->with($this->equalTo('field'), $this->equalTo(3), $this->equalTo(false));
        $where->expects($this->once())->method('lte')->with($this->equalTo('field'), $this->equalTo(3), $this->equalTo(false));

        $where->dateRange('field', '');
    }

    /**
     * asDbType should be called with current type if bean is present
     * asDb shouldn't be called
     *
     * @dataProvider getDataForTestDateRangeWithBeanDateField
     */
    public function testDateRangeWithBeanDateField($type)
    {
        $bean = $this->getMockBuilder('Account')->setMethods(['getFieldDefinition'])->getMock();
        $bean->expects($this->once())->method('getFieldDefinition')->will($this->returnValue([
            'type' => $type,
        ]));
        $q = new SugarQuery();
        $q->from($bean);
        $dateTime = new DateTime();

        /** @var TimeDate|MockObject $timeDate */
        $timeDate = $this->createPartialMock('TimeDate', ['parseDateRange', 'asDb', 'asDbType']);
        $timeDate->expects($this->once())->method('parseDateRange')->will($this->returnValue([$dateTime, $dateTime]));
        $timeDate->expects($this->exactly(2))->method('asDbType')->with($this->equalTo($dateTime), $this->equalTo($type), $this->equalTo(false))->will($this->returnValue(3));
        $timeDate->expects($this->never())->method('asDb');

        /** @var SugarQuery_Builder_Where|MockObject $where */
        $where = $this->getMockForAbstractClass('SugarQuery_Builder_Where', [$q], '', false, true, true, ['timeDateInstance', 'queryAnd', 'lte', 'gte'], false);
        $where->expects($this->any())->method('timeDateInstance')->will($this->returnValue($timeDate));
        $where->expects($this->any())->method('queryAnd')->will($this->returnValue($where));
        $where->expects($this->once())->method('gte')->with($this->equalTo('field'), $this->equalTo(3), $this->equalTo($bean));
        $where->expects($this->once())->method('lte')->with($this->equalTo('field'), $this->equalTo(3), $this->equalTo($bean));

        $where->dateRange('field', '', $bean);
    }

    /**
     * Data provider for testDateRangeWithBeanDateField
     * @return array
     */
    public static function getDataForTestDateRangeWithBeanDateField()
    {
        return [
            ['date'],
            ['time'],
            ['datetime'],
            ['datetimecombo'],
        ];
    }
}
