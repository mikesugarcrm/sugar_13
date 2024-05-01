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


class SugarTestActivityUtilities
{
    private static $createdActivities = [];

    public static function createUnsavedActivity($new_id = '', $activityValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $data = ['value' => 'SugarActivity' . $time];
        $activity = BeanFactory::newBean('Activities');
        foreach ($activityValues as $property => $value) {
            $activity->$property = $value;
        }
        $activity->data = $data;
        if (!empty($new_id)) {
            $activity->new_with_id = true;
            $activity->id = $new_id;
        }
        return $activity;
    }

    public static function createActivity($new_id = '', $activityValues = [])
    {
        Activity::enable();
        $activity = self::createUnsavedActivity($new_id, $activityValues);
        $activity->save();
        Activity::restoreToPreviousState();
        $GLOBALS['db']->commit();
        self::$createdActivities[] = $activity;
        return $activity;
    }

    public static function setCreatedActivity($activity_ids)
    {
        foreach ($activity_ids as $activity_id) {
            $activity = BeanFactory::newBean('Activities');
            $activity->id = $activity_id;
            self::$createdActivities[] = $activity;
        }
    }

    public static function removeAllCreatedActivities()
    {
        $activity_ids = self::getCreatedActivityIds();
        $GLOBALS['db']->query('DELETE FROM activities_users WHERE activity_id IN (\'' . implode("', '", $activity_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM activities WHERE id IN (\'' . implode("', '", $activity_ids) . '\')');
    }

    public static function removeActivities(SugarBean $record)
    {
        $sql = 'DELETE FROM activities WHERE ';
        $sql .= 'activities.parent_module = "' . $record->module_name . '" ';
        if ($record->id) {
            $sql .= 'AND activities.parent_id = "' . $record->id . '"';
        }
        $GLOBALS['db']->query($sql);
    }

    public static function getCreatedActivityIds()
    {
        $activity_ids = [];
        foreach (self::$createdActivities as $activity) {
            $activity_ids[] = $activity->id;
        }
        return $activity_ids;
    }

    public static function activityExists($id, $deleted = false)
    {
        $sql = "SELECT id FROM activities WHERE id='{$id}'";
        if (!$deleted) {
            $sql .= ' AND deleted=0';
        }
        $result = $GLOBALS['db']->query($sql);
        $row = $GLOBALS['db']->fetchByAssoc($result);
        return !empty($row['id']);
    }
}
