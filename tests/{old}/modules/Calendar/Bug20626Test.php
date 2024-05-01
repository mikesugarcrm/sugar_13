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

/**
 * @ticket 20626
 */
class Bug20626Test extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['reload_vardefs'] = true;
        global $current_user;

        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        $GLOBALS['reload_vardefs'] = false;
    }

    public function testDateAndTimeShownInCalendarActivityAdditionalDetailsPopup()
    {
        global $timedate, $sugar_config, $DO_USER_TIME_OFFSET, $current_user;

        $DO_USER_TIME_OFFSET = true;
        $timedate = TimeDate::getInstance();

        $meeting = new Meeting();
        $format = $current_user->getUserDateTimePreferences();
        $meeting->date_start = $timedate->swap_formats('2006-12-23 11:00pm', 'Y-m-d h:ia', $format['date'] . ' ' . $format['time']);
        $meeting->time_start = '';
        $meeting->object_name = 'Meeting';
        $meeting->duration_hours = 2;
        $ca = new CalendarActivity($meeting);
        $this->assertEquals($meeting->date_start, $ca->sugar_bean->date_start);
    }
}
