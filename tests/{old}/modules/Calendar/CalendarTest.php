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

class CalendarTest extends TestCase
{
    /**
     * @var TimeDate
     */
    protected $time_date;

    protected $meeting_id = null;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->time_date = new TimeDate();
        $_REQUEST['module'] = 'Calendar';
        $_REQUEST['year'] = '2012';
        $_REQUEST['month'] = '01';
        $_REQUEST['day'] = '02';
    }

    protected function tearDown(): void
    {
        unset($_REQUEST['module']);
        unset($_REQUEST['year']);
        unset($_REQUEST['month']);
        unset($_REQUEST['day']);

        if (isset($this->meeting_id)) {
            $GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting_id}'");
            $GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting_id}'");
            unset($this->meeting_id);
        }
        SugarTestHelper::tearDown();
    }

    public function testCalendarDate()
    {
        $cal = new CalendarBWC('week');
        $this->assertEquals('2012', $cal->date_time->year);
    }

    public function testCalendarAddActivity()
    {
        $cal = new CalendarBWC('week');
        $cal->add_activities($GLOBALS['current_user']);
        $count1 = safeCount($cal->acts_arr[$GLOBALS['current_user']->id]);
        $cal->acts_arr = [];

        $this->meeting_id = uniqid();

        $db = $GLOBALS['db'];
        $db->query('INSERT INTO meetings (id,date_start,date_end,assigned_user_id) VALUES(' .
            $db->quoted($this->meeting_id) . ', ' .
            $db->convert($db->quoted('2012-01-02 00:00:00'), 'datetime') . ', ' .
            $db->convert($db->quoted('2012-01-02 01:00:00'), 'datetime') . ', ' .
            $db->quoted($GLOBALS['current_user']->id) . ')');

        $db->query('INSERT INTO meetings_users (id,meeting_id,user_id) VALUES (' .
            $db->quoted(uniqid()) . ', ' .
            $db->quoted($this->meeting_id) . ', ' .
            $db->quoted($GLOBALS['current_user']->id) . ')');

        $cal->add_activities($GLOBALS['current_user']);
        $count2 = safeCount($cal->acts_arr[$GLOBALS['current_user']->id]);

        $this->assertEquals($count1 + 1, $count2, 'Count of records should be one more after meeting added');
    }

    public function testCalendarLoadActivities()
    {
        $cal = new CalendarBWC('month');
        $cal->add_activities($GLOBALS['current_user']);
        $format = $GLOBALS['current_user']->getUserDateTimePreferences();
        $meeting = new Meeting();
        $meeting->meeting_id = uniqid();
        $meeting->date_start = $this->time_date->swap_formats('2012-01-01 11:00pm', 'Y-m-d h:ia', $format['date'] . ' ' . $format['time']);
        $meeting->name = 'test';
        $cal->acts_arr = [];
        $cal->acts_arr[$GLOBALS['current_user']->id] = [];
        $cal->acts_arr[$GLOBALS['current_user']->id][] = new CalendarActivity($meeting);
        $cal->load_activities();

        $this->assertEquals($cal->items[0]['time_start'], $this->time_date->swap_formats('2012-01-01 11:00pm', 'Y-m-d h:ia', $format['time']), 'Time should remain the same after load_activities');
    }

    public function testHandleOffset()
    {
        $gmt_today = $this->time_date->nowDb();
        $date1 = $this->time_date->handle_offset($gmt_today, $GLOBALS['timedate']->get_db_date_time_format());
        $date2 = $this->time_date->nowDb();
        $this->assertEquals($date1, $date2, 'HandleOffset should be equaivalent to nowDb');
    }

    public function testUserDateFormat()
    {
        $gmt_default_date_start = $this->time_date->get_gmt_db_datetime();
        $date1 = $this->time_date->handle_offset($gmt_default_date_start, $GLOBALS['timedate']->get_date_time_format());
        $date2 = $this->time_date->asUser($this->time_date->getNow());
        $this->assertEquals($date1, $date2, 'HandleOffset should be equaivalent to nowDb');
    }
}
