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

require_once 'include/SearchForm/SearchForm2.php';

/**
 * @group Bug45966
 * @coversDefaultClass SearchForm
 */
class Bug45966 extends TestCase
{
    public $module = 'Notes';
    public $action = 'index';
    public $seed;
    /**
     * @var SearchForm
     */
    public $form;
    public $array;

    protected $timezone = 'America/Denver';

    protected function setUp(): void
    {
        $searchdefs = null;
        $searchFields = null;
        $listViewDefs = null;
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('timedate');

        require 'modules/' . $this->module . '/metadata/searchdefs.php';
        require 'modules/' . $this->module . '/metadata/SearchFields.php';
        require 'modules/' . $this->module . '/metadata/listviewdefs.php';

        $this->seed = BeanFactory::newBean($this->module);
        $this->form = new SearchForm($this->seed, $this->module, $this->action);
        $this->form->setup($searchdefs, $searchFields, 'include/SearchForm/tpls/SearchFormGeneric.tpl', 'advanced_search', $listViewDefs);

        $this->array = [
            'module' => $this->module,
            'action' => $this->action,
            'searchFormTab' => 'advanced_search',
            'query' => 'true',
            'date_entered_advanced_range_choice' => '',
            'range_date_entered_advanced' => '',
            'start_range_date_entered_advanced' => '',
            'end_range_date_entered_advanced' => '',
        ];
        $GLOBALS['current_user']->setPreference('datef', 'm/d/Y');
        $GLOBALS['current_user']->setPreference('timef', 'H:i:s');
        $GLOBALS['current_user']->setPreference('timezone', $this->timezone);
        $GLOBALS['timedate']->allow_cache = false;
    }

    protected function tearDown(): void
    {
        unset(
            $this->array,
            $this->form,
            $this->seed
        );
        SugarTestHelper::tearDown();
    }

    public function testSearchDateEqualsAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('>=', $adjDate['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, '=', $testDate);
    }

    public function testSearchNotOnDateAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = strtolower($this->module) . '.date_entered IS NULL OR ' .
            $this->getExpectedPart('<', $adjDate['start'], 'datetime') .
            ' OR ' . $this->getExpectedPart('>', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'not_equal', $testDate);
    }

    public function testSearchAfterDateAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = '12/31/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('>', $adjDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'greater_than', $testDate);
    }

    public function testSearchBeforeDateAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = '01/01/2011';

        $adjDate = $timedate->getDayStartEndGMT($testDate, $current_user);

        $expected = $this->getExpectedPart('<', $adjDate['start'], 'datetime');

        $this->assertResultQuery($expected, 'less_than', $testDate);
    }

    /**
     * @dataProvider dataProviderSearchAdjustsForTimeZone
     *
     * @covers ::generateSearchWhere
     * @param $testDate
     * @param $adjust
     */
    public function testSearchAdjustsForTimeZone($testDate, $adjust)
    {
        global $timedate, $current_user;

        $timedate->allow_cache = true;
        // set the static time/date since we always want to use a specific time
        $timedate->setNow(new SugarDateTime('2010-01-19 08:00:00', new DateTimeZone($this->timezone)));

        $today = $timedate->getNow(true);
        $endDate = $timedate->getNow(true);

        if (strpos($adjust, '-') === 0) {
            $today = $today->get($adjust);
        } else {
            $endDate = $endDate->get($adjust);
        }

        $adjToday = $timedate->getDayStartEndGMT($today, $current_user);
        $adjEndDate = $timedate->getDayStartEndGMT($endDate, $current_user);

        $testToday = new DateTime($adjToday['start']);
        $testEnd = new DateTime($adjEndDate['end']);
        $expectedDiff = $testToday->diff($testEnd)->format('%a days');

        $this->assertSame(substr($adjust, 1), $expectedDiff, 'Expected Difference Is Not Correct');


        $expected = $this->getExpectedPart('>=', $adjToday['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjEndDate['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
        $timedate->allow_cache = false;
    }

    public static function dataProviderSearchAdjustsForTimeZone()
    {
        return [
            [
                'last_7_days',
                '-6 days',
            ],
            [
                'next_7_days',
                '+6 days',
            ],
            [
                'last_30_days',
                '-29 days',
            ],
            [
                'next_30_days',
                '+29 days',
            ],
        ];
    }

    public function testSearchLastMonthAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'last_month';

        $now = $timedate->getNow(true);
        $month_number = $now->month == 1 ? 12 : $now->month - 1;
        $year_number = $now->month == 1 ? $now->year - 1 : $now->year;
        $month = $now->get_day_begin(1, $month_number, $year_number);

        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchThisMonthAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'this_month';

        $month = $timedate->getNow(true)->get_day_begin(1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextMonthAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'next_month';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, $now->month + 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT(
            $month->get_day_begin($month->days_in_month),
            $current_user
        );

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchLastYearAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'last_year';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year - 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchThisYearAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'this_year';

        $month = $timedate->getNow(true)->get_day_begin(1, 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchNextYearAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testDate = 'next_year';

        $now = $timedate->getNow(true);
        $month = $now->get_day_begin(1, 1, $now->year + 1);
        $adjThisMonthFirstDay = $timedate->getDayStartEndGMT($month, $current_user);
        $adjThisMonthLastDay = $timedate->getDayStartEndGMT($month->get_day_begin(31, 12), $current_user);

        $expected = $this->getExpectedPart('>=', $adjThisMonthFirstDay['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjThisMonthLastDay['end'], 'datetime');

        $this->assertResultQuery($expected, $testDate, "[{$testDate}]");
    }

    public function testSearchDateIsBetweenAdjustsForTimeZone()
    {
        global $timedate, $current_user;

        $testStartDate = '01/01/2011';
        $testEndDate = '12/31/2011';

        $this->array['start_range_date_entered_advanced'] = $testStartDate;
        $this->array['end_range_date_entered_advanced'] = $testEndDate;

        $adjStartDate = $timedate->getDayStartEndGMT($testStartDate, $current_user);
        $adjEndDate = $timedate->getDayStartEndGMT($testEndDate, $current_user);

        $expected = $this->getExpectedPart('>=', $adjStartDate['start'], 'datetime') .
            ' AND ' . $this->getExpectedPart('<=', $adjEndDate['end'], 'datetime');

        $this->assertResultQuery($expected, 'between', '');
    }

    protected function getExpectedPart($compare, $value, $type)
    {
        $db = DBManagerFactory::getInstance();
        $tablename = strtolower($this->seed->table_name);
        return "{$tablename}.date_entered {$compare} {$db->convert($db->quoted($value), $type)}";
    }

    protected function assertResultQuery($expected, $date, $range)
    {
        $this->array['date_entered_advanced_range_choice'] = $date;
        $this->array['range_date_entered_advanced'] = $range;
        $this->form->populateFromArray($this->array);
        $query = $this->form->generateSearchWhere($this->seed, $this->module);
        $this->assertStringContainsString($expected, $query[0]);
    }
}
