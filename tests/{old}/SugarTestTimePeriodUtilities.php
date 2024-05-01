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

/**
 *
 * This is a test class to create test TimePeriod instances
 */
class SugarTestTimePeriodUtilities
{
    private static $createdTimePeriods = [];

    private function __construct()
    {
    }

    /**
     * @static
     * This is a static function to create a test TimePeriod instance
     *
     * @param $start_date String value of a db date default start date
     * @param $end_date String value of a db date default end date
     * @return TimePeriod Mixed TimePeriod test instance
     */
    public static function createTimePeriod($start_date = '', $end_date = '', $name = '', $parent_id = '', $fields = [])
    {
        global $timedate;
        $timedate = TimeDate::getInstance();
        $now = $timedate->getNow();
        $month = $timedate->getNow()->format('n');
        if ($month < 4) {
            $month = 1;
        } elseif ($month < 7) {
            $month = 4;
        } elseif ($month < 10) {
            $month = 7;
        } else {
            $month = 10;
        }

        $year = $timedate->getNow()->format('Y');
        $time = random_int(0, mt_getrandmax());
        if ($name == '') {
            $name = 'SugarTimePeriod' . $time;
        }
        $timeperiod = new TimePeriod();

        if (empty($start_date)) {
            $start_date = $timedate->asDbDate($now->get_day_begin(1, $month, $year));
        }

        if (empty($end_date)) {
            $end_date = new dateTime($start_date);
            $end_date = $end_date->modify('last day of next month');
            $end_date = $end_date->modify('last day of next month');
            $end_date = $timedate->asDbDate($end_date);
        }

        $timeperiod->start_date = $start_date;
        $timeperiod->end_date = $end_date;
        $timeperiod->name = $name;
        $timeperiod->is_fiscal_year = 0;
        if (!empty($parent_id)) {
            $timeperiod->parent_id = $parent_id;
        }
        $timeperiod->type = 'Quarter';

        foreach ($fields as $fieldName => $fieldValue) {
            $timeperiod->$fieldName = $fieldValue;
        }

        if (!empty($timeperiod->id)) {
            $timeperiod->new_with_id = true;
        }

        $timeperiod->save();

        $db = DBManagerFactory::getInstance();
        $db->commit();

        self::$createdTimePeriods[] = $timeperiod;
        return $timeperiod;
    }

    /*
     * magic tardis function
     */
    public static function createITimePeriod($type, $is_fiscal = false)
    {
        global $timedate;
        $timedate = TimeDate::getInstance();
        $time = random_int(0, mt_getrandmax());
        $name = 'Sugar' . $type . 'TimePeriod' . $time;
        $start_date = self::getRandDate();
        $timeperiod = TimePeriod::getByType($type);
        $timeperiod->is_fiscal = $is_fiscal;
        $timeperiod->setStartDate($timedate->asDbDate($start_date));

        $timeperiod->name = $name;
        $timeperiod->save();
        self::$createdTimePeriods[] = $timeperiod;
        return $timeperiod;
    }

    protected static function getRandDate()
    {
        global $timedate;
        $timedate = TimeDate::getInstance();
        $rand_date = $timedate->getNow();
        $month = $timedate->getNow()->format('n');
        if ($month < 4) {
            $month = 1;
        } elseif ($month < 8) {
            $month = 4;
        } elseif ($month < 11) {
            $month = 7;
        } else {
            $month = 10;
        }


        $year = $timedate->getNow()->format('Y');
        $rand_date->setDate($year, $month, 1);
        return $rand_date;
    }

    /**
     * @static
     * This is a static function to remove all created test TimePeriod instance
     */
    public static function removeAllCreatedTimePeriods()
    {
        $timeperiod_ids = self::getCreatedTimePeriodIds();
        $GLOBALS['db']->query('DELETE FROM timeperiods WHERE id IN (\'' . implode("', '", $timeperiod_ids) . '\')');
        SugarTestForecastUtilities::setTimePeriod(null);
    }

    /**
     * @static
     * this is a staitc function to append another timeperiod to the static timeperiods array
     *
     * @param $timeperiod
     */
    public static function addCreatedTimePeriod($timeperiod)
    {
        self::$createdTimePeriods[] = $timeperiod;
    }

    /**
     * @static
     * This is a static function to return all ids of created TimePeriod instances
     *
     * @return array of ids of the TimePeriod instances created
     */
    public static function getCreatedTimePeriodIds()
    {
        $timeperiod_ids = [];
        foreach (self::$createdTimePeriods as $tp) {
            $timeperiod_ids[] = $tp->id;
        }
        return $timeperiod_ids;
    }

    /**
     * @static
     * This is a static function to set all created TimePeriod instances
     *
     * @param $timePeriods Array of TimePeriod instances
     */
    public static function setCreatedTimePeriods($timePeriods = [])
    {
        self::$createdTimePeriods = $timePeriods;
    }
}
