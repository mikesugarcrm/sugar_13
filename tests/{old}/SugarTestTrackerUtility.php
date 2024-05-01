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

class SugarTestTrackerUtility
{
    private static $trackerSettings = [];
    private static $monitorId;

    private function __construct()
    {
    }

    public static function setup()
    {
        $tracker_config = [];
        require 'modules/Trackers/config.php';
        foreach ($tracker_config as $entry) {
            if (isset($entry['bean'])) {
                $GLOBALS['tracker_' . $entry['name']] = false;
            } //if
        } //foreach

        $result = $GLOBALS['db']->query("SELECT category, name, value from config WHERE category = 'tracker' and name != 'prune_interval'");
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            self::$trackerSettings[$row['name']] = $row['value'];
            $GLOBALS['db']->query("DELETE FROM config WHERE category = 'tracker' AND name = '{$row['name']}'");
        }

        // make sure that the next requested TrackerManager instance is properly configured
        TrackerManager::resetInstance();
    }

    public static function restore()
    {
        foreach (self::$trackerSettings as $name => $value) {
            $GLOBALS['db']->query("INSERT INTO config (category, name, value) VALUES ('tracker', '{$name}', '{$value}')");
        }
    }

    public static function insertTrackerEntry($bean, $action)
    {
        $trackerManager = TrackerManager::getInstance();
        $timeStamp = gmdate($GLOBALS['timedate']->get_db_date_time_format());
        $_REQUEST['action'] = $action;
        if ($monitor = $trackerManager->getMonitor('tracker')) {
            $monitor->setValue('team_id', $GLOBALS['current_user']->getPrivateTeamID());
            $monitor->setValue('action', $action);
            $monitor->setValue('user_id', $GLOBALS['current_user']->id);
            $monitor->setValue('module_name', $bean->module_dir);
            $monitor->setValue('date_modified', $timeStamp);
            $monitor->setValue('visible', (
                ($action == 'detailview') || ($action == 'editview')
                || ($action == 'wirelessdetail') || ($action == 'wirelessedit')
            ) ? 1 : 0);

            if (!empty($bean->id)) {
                $monitor->setValue('item_id', $bean->id);
                $monitor->setValue('item_summary', $bean->get_summary_text());
            }

            //If visible is true, but there is no bean, do not track (invalid/unauthorized reference)
            //Also, do not track save actions where there is no bean id
            if ($monitor->visible && empty($bean->id)) {
                $trackerManager->unsetMonitor($monitor);
                return false;
            }
            $trackerManager->saveMonitor($monitor, true, true);
            if (empty(self::$monitorId)) {
                self::$monitorId = $monitor->monitor_id;
            }
        }
    }

    public static function removeAllTrackerEntries()
    {
        if (!empty(self::$monitorId)) {
            $GLOBALS['db']->query("DELETE FROM tracker WHERE monitor_id = '" . self::$monitorId . "'");
        }

        // make sure that next requested TrackerManager instance has default configuration
        TrackerManager::resetInstance();
    }
}
