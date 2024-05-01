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

class TrackerTestUtility
{
    private static $trackerSettings = [];

    public static function setUp()
    {
        require 'modules/Trackers/config.php';
        foreach ($tracker_config as $entry) {
            if (isset($entry['bean'])) {
                $GLOBALS['tracker_' . $entry['name']] = false;
            } //if
        } //foreach

        $result = $GLOBALS['db']->query("SELECT category, name, value from config WHERE category = 'tracker' and name != 'prune_interval'");
        self::$trackerSettings = [];
        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            self::$trackerSettings[$row['name']] = $row['value'];
            $GLOBALS['db']->query("DELETE from config where category = 'tracker' and name = '{$row['name']}'");
        }
    }

    public static function tearDown()
    {
        foreach (self::$trackerSettings as $name => $value) {
            $GLOBALS['db']->query("INSERT into config (category, name, value) values ('tracker', '{$name}', '{$value}')");
        }
    }
}
