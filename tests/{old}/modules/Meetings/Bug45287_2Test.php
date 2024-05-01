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

require_once 'modules/Meetings/Meeting.php';
require_once 'include/SearchForm/SearchForm2.php';


class Bug45287_2Test extends TestCase
{
    public $timezone;
    public $meetingsArr;
    public $searchDefs;
    public $searchFields;
    public $timedate;


    protected function setUp(): void
    {
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        global $current_user;
        // Create Anon User setted on PDT TimeZone
        $current_user->setPreference('datef', 'd/m/Y');
        $current_user->setPreference('timef', 'H:i:s');
        $current_user->setPreference('timezone', 'America/Los_Angeles');

        // new object to avoid TZ caching
        $this->timedate = new TimeDate();

        $this->meetingsArr = [];

        // Create a Bunch of Meetings
        $d = 12;
        $cnt = 0;
        while ($d < 15) {
            $this->meetingsArr[$cnt] = new Meeting();
            $this->meetingsArr[$cnt]->name = 'Bug45287 Meeting ' . ($cnt + 1);
            $this->meetingsArr[$cnt]->assigned_user_id = $current_user->id;
            $this->meetingsArr[$cnt]->date_start = $this->timedate->to_display_date_time(gmdate('Y-m-d H:i:s', mktime(10 + $cnt, 30, 00, 7, $d, 2011)));
            $this->meetingsArr[$cnt]->save();
            $d++;
            $cnt++;
        }

        $this->searchDefs = [
            'Meetings' => [
                'layout' => [
                    'basic_search' => [
                        'name' => [
                            'name' => 'name',
                            'default' => true,
                            'width' => '10%',
                        ],
                        'date_start' => ['name' => 'date_start',
                            'default' => true,
                            'width' => '10%',
                            'type' => 'datetimecombo',
                        ],
                    ],
                ],
            ],
        ];

        $this->searchFields = [
            'Meetings' => [
                'name' => ['query_type' => 'default'],
                'date_start' => ['query_type' => 'default'],
                'range_date_start' => ['query_type' => 'default',
                    'enable_range_search' => 1,
                    'is_date_field' => 1,
                ],
                'start_range_date_start' => ['query_type' => 'default',
                    'enable_range_search' => 1,
                    'is_date_field' => 1,
                ],
                'end_range_date_start' => ['query_type' => 'default',
                    'enable_range_search' => 1,
                    'is_date_field' => 1,
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        foreach ($this->meetingsArr as $m) {
            $GLOBALS['db']->query('DELETE FROM meetings WHERE id = \'' . $m->id . '\' ');
        }

        unset($m);
        unset($this->meetingsArr);
        unset($this->searchDefs);
        unset($this->searchFields);
        unset($this->timezone);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testRetrieveByExactDate()
    {
        $_REQUEST = $_POST = ['module' => 'Meetings',
            'action' => 'index',
            'searchFormTab' => 'basic_search',
            'query' => 'true',
            'name_basic' => '',
            'current_user_only_basic' => '0',
            'favorites_only_basic' => '0',
            'open_only_basic' => '0',
            'date_start_basic_range_choice' => '=',
            'range_date_start_basic' => '14/07/2011',
            'start_range_date_start_basic' => '',
            'end_range_date_start_basic' => '',
            'button' => 'Search',
        ];

        $srch = new SearchForm(new Meeting(), 'Meetings');
        $srch->setup($this->searchDefs, $this->searchFields, '');
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDates = $this->timedate->getDayStartEndGMT('2011-07-14');

        // Current User is on GMT-7.
        // Asking for meeting of 14 July 2011, I expect to search (GMT) from 14 July at 07:00 until 15 July at 07:00 (excluded)
        $expectedWhere = 'meetings.date_start >= ' . $GLOBALS['db']->convert($GLOBALS['db']->quoted($GMTDates['start']), 'datetime') .
            ' AND meetings.date_start <= ' . $GLOBALS['db']->convert($GLOBALS['db']->quoted($GMTDates['end']), 'datetime');

        $this->assertStringContainsString($expectedWhere, $w[0]);
    }

    public function testRetrieveByDaterange()
    {
        $_REQUEST = $_POST = [
            'module' => 'Meetings',
            'action' => 'index',
            'searchFormTab' => 'basic_search',
            'query' => 'true',
            'name_basic' => '',
            'current_user_only_basic' => '0',
            'favorites_only_basic' => '0',
            'open_only_basic' => '0',
            'date_start_basic_range_choice' => 'between',
            'range_date_start_basic' => '',
            'start_range_date_start_basic' => '13/07/2011',
            'end_range_date_start_basic' => '14/07/2011',
            'button' => 'Search',
        ];

        $srch = new SearchForm(new Meeting(), 'Meetings');
        $srch->setup($this->searchDefs, $this->searchFields, '');
        $srch->populateFromRequest();
        $w = $srch->generateSearchWhere();

        // Due to daylight savings, I cannot hardcode intervals...
        $GMTDatesStart = $this->timedate->getDayStartEndGMT('2011-07-13');
        $GMTDatesEnd = $this->timedate->getDayStartEndGMT('2011-07-14');

        // Current User is on GMT-7.
        // Asking for meeting between 13 and 14 July 2011, I expect to search from 13 July at 07:00 until 15 July at 07:00 (excluded)
        $expectedWhere = 'meetings.date_start >= ' . $GLOBALS['db']->convert($GLOBALS['db']->quoted($GMTDatesStart['start']), 'datetime') .
            ' AND meetings.date_start <= ' . $GLOBALS['db']->convert($GLOBALS['db']->quoted($GMTDatesEnd['end']), 'datetime');

        $this->assertStringContainsString($expectedWhere, $w[0]);
    }
}
