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
 * Test Fiscal Filters and Fiscal Group By for report date/time fields
 *
 * @author avucinic@sugarcrm.com
 */
class Bug62783Test extends TestCase
{
    private static $reportDef = [
        'display_columns' => [],
        'module' => 'Opportunities',
        'assigned_user_id' => '1',
        'report_type' => 'summary',
        'full_table_list' => [
            'self' => [
                'value' => 'Opportunities',
                'module' => 'Opportunities',
                'label' => 'Opportunities',
            ],
        ],
        'filters_def' => [
            'Filter_1' => [
                'operator' => 'AND',
                [
                    'name' => 'id',
                    'table_key' => 'self',
                    'qualifier_name' => 'is',
                ],
            ],
        ],
    ];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        // Setup Forecast defaults
        SugarTestForecastUtilities::setUpForecastConfig();
        ForecastsDefaults::setupForecastSettings();
    }

    protected function tearDown(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();

        SugarTestHelper::tearDown();
    }

    /**
     * Test if fiscal query filters for DateTime type fields are working properly
     *
     * @param $qualifier - qualifier (year/quarter)
     * @param $startDate - Fiscal start date
     * @param $date - date for which to to find the fiscal quarter/year
     * @param $modifyStart - Modification to start date
     * @param $modifyEnd - Modification to end date
     * @param $expectedStart - Expected start date in query
     * @param $expectedEnd - Expected end date in query
     * @param $timezone - User timezone
     *
     * @dataProvider filterDataProvider
     */
    public function testDateTimeFiscalQueryFilter(
        $qualifier,
        $type,
        $class,
        $startDate,
        $date,
        $modifyStart,
        $modifyEnd,
        $expectedStart,
        $expectedEnd,
        $timezone
    ) {

        // Setup Fiscal Start Date
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', $startDate, 'base');


        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $layoutManager = new LayoutManager();
        $layoutManager->setAttribute('reporter', new Report());
        $SWFDT = new $class($layoutManager);
        $layoutDef = [
            'qualifier_name' => $qualifier,
            'type' => $type,
        ];

        $result = $SWFDT->getFiscalYearFilter($layoutDef, $modifyStart, $modifyEnd, $date);

        $this->assertContains($expectedStart, $result, 'Greater than part of query generated incorrectly.');
        $this->assertContains($expectedEnd, $result, 'Lower than part of query generated incorrectly.');
    }

    /**
     * Test if groupBy query for fiscal year/quarter
     * on Date type fields is working properly
     *
     * @param $startDate - Fiscal start date
     * @param $timezone - User timezone
     * @param $expected - Expected result
     * @param $reportDef - Report def
     *
     * @dataProvider groupDateDataProvider
     */
    public function testDateFiscalQueryGroupBy($startDate, $timezone, $expected, $reportDef)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', $startDate, 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->date_closed = $startDate;
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->date_closed = $startDate;
        $opportunity->save();

        $reportDef['filters_def']['Filter_1'][0]['input_name0'] = $opportunity->id;

        $report = new Report(json_encode($reportDef));

        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertEquals(1, $row['count'], 'Report count should be 1');
        $this->assertEquals($expected, $row['cells'][0], 'Wrong grouping result');
    }

    /**
     * Test if groupBy query for fiscal year/quarter
     * on DateTime type fields is working properly
     *
     * @param $startDate - Fiscal start date
     * @param $timezone - User timezone
     * @param $expected - Expected result
     * @param $reportDef - Report def
     *
     * @dataProvider groupDateTimeDataProvider
     */
    public function testDateTimeFiscalQueryGroupBy($startDate, $timezone, $expected, $reportDef)
    {
        // Setup Fiscal Start Date
        $admin = BeanFactory::newBean('Administration');
        $admin->saveSetting('Forecasts', 'timeperiod_start_date', $startDate, 'base');

        $GLOBALS['current_user']->setPreference('timezone', $timezone);

        $id = create_guid();
        $rli = SugarTestRevenueLineItemUtilities::createRevenueLineItem();
        $rli->date_closed = $startDate . ' 00:00:00';
        $rli->opportunity_id = $id;
        $rli->save();
        $opportunity = SugarTestOpportunityUtilities::createOpportunity($id);
        $opportunity->date_modified = $startDate . ' 00:00:00';
        $opportunity->update_date_modified = false;
        $opportunity->save();

        $reportDef['filters_def']['Filter_1'][0]['input_name0'] = $opportunity->id;

        $report = new Report(json_encode($reportDef));

        $report->run_summary_query();
        $row = $report->get_summary_next_row();

        $this->assertEquals(1, $row['count'], 'Report count should be 1');
        $this->assertEquals($expected, $row['cells'][0], 'Wrong grouping result');
    }

    public static function groupDateTimeDataProvider()
    {
        $reportDefYear = Bug62783Test::$reportDef;
        $reportDefQuarter = Bug62783Test::$reportDef;

        $reportDefYear['group_defs'] = $reportDefYear['summary_columns'] =
            [
                [
                    'name' => 'date_modified',
                    'column_function' => 'fiscalYear',
                    'qualifier' => 'fiscalYear',
                    'table_key' => 'self',
                ],
            ];

        $reportDefQuarter['group_defs'] = $reportDefQuarter['summary_columns'] =
            [
                [
                    'name' => 'date_modified',
                    'column_function' => 'fiscalQuarter',
                    'qualifier' => 'fiscalQuarter',
                    'table_key' => 'self',
                ],
            ];

        return [
            [
                '2013-05-05',
                'America/Los_Angeles',
                '2012',
                $reportDefYear,
            ],
            [
                '2013-05-05',
                'UTC',
                '2013',
                $reportDefYear,
            ],
            [
                '2013-05-05',
                'Europe/Helsinki',
                '2013',
                $reportDefYear,
            ],
            [
                '2013-12-05',
                'America/Los_Angeles',
                'Q4 2012',
                $reportDefQuarter,
            ],
            [
                '2013-05-05',
                'UTC',
                'Q1 2013',
                $reportDefQuarter,
            ],
            [
                '2013-05-05',
                'Europe/Helsinki',
                'Q1 2013',
                $reportDefQuarter,
            ],
        ];
    }

    public static function groupDateDataProvider()
    {
        $reportDefYear = Bug62783Test::$reportDef;
        $reportDefQuarter = Bug62783Test::$reportDef;

        $reportDefYear['group_defs'] = $reportDefYear['summary_columns'] =
            [
                [
                    'name' => 'date_closed',
                    'column_function' => 'fiscalYear',
                    'qualifier' => 'fiscalYear',
                    'table_key' => 'self',
                ],
            ];

        $reportDefQuarter['group_defs'] = $reportDefQuarter['summary_columns'] =
            [
                [
                    'name' => 'date_closed',
                    'column_function' => 'fiscalQuarter',
                    'qualifier' => 'fiscalQuarter',
                    'table_key' => 'self',
                ],
            ];

        return [
            [
                '2013-05-05',
                'America/Los_Angeles',
                '2013',
                $reportDefYear,
            ],
            [
                '2013-05-05',
                'UTC',
                '2013',
                $reportDefYear,
            ],
            [
                '2013-05-05',
                'Europe/Helsinki',
                '2013',
                $reportDefYear,
            ],
            [
                '2013-05-05',
                'America/Los_Angeles',
                'Q1 2013',
                $reportDefQuarter,
            ],
            [
                '2013-01-05',
                'UTC',
                'Q1 2013',
                $reportDefQuarter,
            ],
            [
                '2013-01-05',
                'Europe/Helsinki',
                'Q1 2013',
                $reportDefQuarter,
            ],
        ];
    }

    public static function filterDataProvider()
    {
        $db = DBManagerFactory::getInstance();
        return [
            [
                'quarter',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '1987-01-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= {$db->convert($db->quoted('2013-04-01 07:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2013-07-01 06:59:59'), 'datetime')}",
                'America/Los_Angeles',
            ],
            [
                'year',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2013-12-31 22:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2014-12-31 21:59:59'), 'datetime')}",
                'Europe/Helsinki',
            ],
            [
                'quarter',
                'date',
                'SugarWidgetFielddate62783Test',
                '1987-01-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= {$db->convert($db->quoted('2013-04-01'), 'date')}",
                "<= {$db->convert($db->quoted('2013-06-30'), 'date')}",
                'America/Los_Angeles',
            ],
            [
                'year',
                'date',
                'SugarWidgetFielddate62783Test',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2014-01-01'), 'date')}",
                "<= {$db->convert($db->quoted('2014-12-31'), 'date')}",
                'Europe/Helsinki',
            ],
            [
                'quarter',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '1987-01-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= {$db->convert($db->quoted('2013-01-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2013-03-31 23:59:59'), 'datetime')}",
                'UTC',
            ],
            [
                'year',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '1987-01-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2014-01-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2014-12-31 23:59:59'), 'datetime')}",
                'UTC',
            ],
            [
                'quarter',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '2018-05-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= {$db->convert($db->quoted('2013-05-01 07:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2013-08-01 06:59:59'), 'datetime')}",
                'America/Los_Angeles',
            ],
            [
                'year',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2014-04-30 21:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2015-04-30 20:59:59'), 'datetime')}",
                'Europe/Helsinki',
            ],
            [
                'quarter',
                'date',
                'SugarWidgetFielddate62783Test',
                '2018-05-01',
                '2013-05-05',
                '',
                '+3 month',
                ">= {$db->convert($db->quoted('2013-05-01'), 'date')}",
                "<= {$db->convert($db->quoted('2013-07-31'), 'date')}",
                'America/Los_Angeles',
            ],
            [
                'year',
                'date',
                'SugarWidgetFielddate62783Test',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2014-05-01'), 'date')}",
                "<= {$db->convert($db->quoted('2015-04-30'), 'date')}",
                'Europe/Helsinki',
            ],
            [
                'quarter',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '2018-05-01',
                '2013-05-05',
                '-3 month',
                '',
                ">= {$db->convert($db->quoted('2013-02-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2013-04-30 23:59:59'), 'datetime')}",
                'UTC',
            ],
            [
                'year',
                'datetime',
                'SugarWidgetFielddatetime62783Test',
                '2018-05-01',
                '2013-05-05',
                '+1 year',
                '+2 year',
                ">= {$db->convert($db->quoted('2014-05-01 00:00:00'), 'datetime')}",
                "<= {$db->convert($db->quoted('2015-04-30 23:59:59'), 'datetime')}",
                'UTC',
            ],
        ];
    }
}

/**
 * Helper class for testing getFiscalYearFilter() method
 */
class SugarWidgetFielddatetime62783Test extends SugarWidgetFieldDateTime
{
    public function getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date = '')
    {
        return parent::getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date);
    }
}

/**
 * Helper class for testing getFiscalYearFilter() method
 */
class SugarWidgetFielddate62783Test extends SugarWidgetFieldDate
{
    public function getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date = '')
    {
        return parent::getFiscalYearFilter($layout_def, $modifyStart, $modifyEnd, $date);
    }
}
