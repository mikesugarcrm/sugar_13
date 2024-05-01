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

class TemplateDateTimeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        global $timedate;
        $timedate->setNow(new SugarDateTime());
    }

    /**
     * @dataProvider valueProvider
     */
    public function testDefaultValues($value, $expextedYear, $expextedMonth, $expextedDay)
    {
        global $timedate;

        //Verify that each of the default values for TemplateDateTime modify the date correctly
        //Set the now on timedate correctly for consistent testing
        $now = $timedate->getNow(true)->setDate(2012, 10, 8)->setTime(16, 10);
        $timedate->setNow($now);

        $expected = clone $timedate->getNow();
        $expected->setDate($expextedYear, $expextedMonth, $expextedDay);

        //We have to make sure to run through parseDateDefault and set a time as on some versions of php,
        //setting the day will reset the time to midnight.
        //ex. in php 5.3.2 'next monday' will not change the time. In php 5.3.6 it will set the time to midnight
        $tdt = new TemplateDatetimecombo();
        $result = $this->parseDateDefault(new SugarBean(), $tdt->dateStrings[$value] . '&04:10pm');
        $this->assertEquals($timedate->asDb($expected), $result);
    }

    private function parseDateDefault(SugarBean $bean, $value)
    {
        return SugarTestReflection::callProtectedMethod($bean, __FUNCTION__, [$value, true]);
    }

    public static function valueProvider()
    {
        return [
            ['today', 2012, 10, 8],
            ['yesterday', 2012, 10, 7],
            ['tomorrow', 2012, 10, 9],
            ['next week', 2012, 10, 15],
            ['next friday', 2012, 10, 12],
            ['two weeks', 2012, 10, 22],
            ['next month', 2012, 11, 8],
            ['first day of next month', 2012, 11, 01],
            ['three months', 2013, 01, 8],
            ['six months', 2013, 04, 8],
            ['next year', 2013, 10, 8],
        ];
    }
}
