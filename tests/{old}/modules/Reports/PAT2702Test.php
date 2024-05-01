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

/**
 * Reports on next/current/previous fiscal quarter do not work in split-year fiscal year
 *
 * @author bsitnikovski@sugarcrm.com
 * @ticket PAT-2702
 */
class PAT2702Test extends TestCase
{
    /**
     * Test if quarter fiscal query filters for DateTime type fields are working properly
     *
     * @param string $startDate Fiscal start date
     * @param array $currentDate Dates for which to find the fiscal quarter
     * @param string $expectedStart Expected start date in query
     * @param string $expectedEnd Expected end date in query
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeQuarterFiscalQueryFilter(
        $startDate,
        $currentDates,
        $expectedStart,
        $expectedEnd
    ) {

        // Setup Fiscal Start Date
        $admin = BeanFactory::getBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', $startDate, 'base');
        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $swfdtMock = new SugarWidgetFielddatetimePAT2702Test($layoutManager);
        $layoutDef = [
            'qualifier_name' => 'quarter',
            'type' => 'datetime',
        ];
        foreach ($currentDates as $currentDate) {
            $result = $swfdtMock->getFiscalYearFilter($layoutDef, '', '+3 month', $currentDate);
            $this->assertContains($expectedStart, $result, 'Greater than part of query generated incorrectly.');
            $this->assertContains($expectedEnd, $result, 'Lower than part of query generated incorrectly.');
        }
    }

    public static function filterDataProvider()
    {
        $db = DBManagerFactory::getInstance();
        // Tests when the month of the start fiscal date is at the lower bound
        $monthLowerBound = [
            // Q1 Tests
            [
                '2015-01-01',
                ['2016-01-01', '2016-02-01', '2016-03-01'],
                ">= {$db->convert($db->quoted('2016-01-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-03-31 23:59:59'), 'datetime')}",
            ],
            // Q2 Tests
            [
                '2015-01-01',
                ['2016-04-01', '2016-05-01', '2016-06-01'],
                ">= {$db->convert($db->quoted('2016-04-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-06-30 23:59:59'), 'datetime')}",
            ],
            // Q3 Tests
            [
                '2015-01-01',
                ['2016-07-01', '2016-08-01', '2016-09-01'],
                ">= {$db->convert($db->quoted('2016-07-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-09-30 23:59:59'), 'datetime')}",
            ],
            // Q4 Tests
            [
                '2015-01-01',
                ['2016-10-01', '2016-11-01', '2016-12-01'],
                ">= {$db->convert($db->quoted('2016-10-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-12-31 23:59:59'), 'datetime')}",
            ],
        ];
        // Tests when the month of the start fiscal date is at the upper bound
        $monthUpperBound = [
            // Q1 Tests
            [
                '2015-12-01',
                ['2015-12-31', '2016-01-01', '2016-02-01'],
                ">= {$db->convert($db->quoted('2015-12-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-02-29 23:59:59'), 'datetime')}",
            ],
            // Q2 Tests
            [
                '2015-12-01',
                ['2016-03-01', '2016-04-01', '2016-05-01'],
                ">= {$db->convert($db->quoted('2016-03-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-05-31 23:59:59'), 'datetime')}",
            ],
            // Q3 Tests
            [
                '2015-12-01',
                ['2016-06-01', '2016-07-01', '2016-08-01'],
                ">= {$db->convert($db->quoted('2016-06-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-08-31 23:59:59'), 'datetime')}",
            ],
            // Q4 Tests
            [
                '2015-12-01',
                ['2016-09-01', '2016-10-01', '2016-11-01'],
                ">= {$db->convert($db->quoted('2016-09-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2016-11-30 23:59:59'), 'datetime')}",
            ],
        ];
        return array_merge($monthLowerBound, $monthUpperBound);
    }
}

/**
 * Mock class for SugarWidgetFieldDateTime
 */
class SugarWidgetFielddatetimePAT2702Test extends SugarWidgetFieldDateTime
{
    public function getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date = '')
    {
        return parent::getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date);
    }
}
