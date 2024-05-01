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

class SugarTestCalendartUtilities
{
    private static $createdCalendars = [];

    private function __construct()
    {
    }

    /**
     * @return Calendar
     */
    public static function createCalendar($id = '', $calendarValues = [])
    {
        global $current_user;

        $time = random_int(0, mt_getrandmax());
        $calendar = BeanFactory::newBean('Calendar');

        $calendarValues = array_merge([
            'name' => 'SugarCalendar' . $time,
            'assigned_user_id' => $current_user->id,
        ], $calendarValues);


        foreach ($calendarValues as $property => $value) {
            $calendar->$property = $value;
        }

        if (!empty($id)) {
            $calendar->new_with_id = true;
            $calendar->id = $id;
        }
        $calendar->save();
        $GLOBALS['db']->commit();
        self::$createdCalendars[] = $calendar;
        return $calendar;
    }

    public static function removeAllCreatedCalendars()
    {
        $calendar_ids = self::getCreatedCalendarIds();
        $GLOBALS['db']->query('DELETE FROM calendar WHERE id IN (\'' . implode("', '", $calendar_ids) . '\')');
    }


    public static function getCreatedCalendarIds()
    {
        $calendar_ids = [];
        foreach (self::$createdCalendars as $calendar) {
            $calendar_ids[] = $calendar->id;
        }
        return $calendar_ids;
    }
}
