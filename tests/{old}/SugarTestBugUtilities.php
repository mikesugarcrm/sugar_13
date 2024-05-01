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


class SugarTestBugUtilities
{
    private static $createdBugs = [];

    private function __construct()
    {
    }

    /**
     * Create and save a new Bug bean.
     *
     * @param string $id ID of the record, defaults to ''.
     * @param array $bugValues Key-value mapping of values to preassign.
     * @return escalation The created escalation.
     */
    public static function createBug($id = '', $bugValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $bug = new Bug();

        if (!isset($bugValues['name'])) {
            $bug->name = 'SugarEscalation' . $time;
        }

        foreach ($bugValues as $property => $value) {
            $bug->$property = $value;
        }

        if (!empty($id)) {
            $bug->new_with_id = true;
            $bug->id = $id;
        }
        $bug->save();
        $GLOBALS['db']->commit();
        self::$createdBugs[] = $bug;
        return $bug;
    }

    /**
     * Hard-delete all escalations created
     */
    public static function removeAllCreatedBugs()
    {
        $bugIds = self::getCreatedBugIds();
        $GLOBALS['db']->query('DELETE FROM bugs WHERE id IN (\'' . implode("', '", $bugIds) . '\')');
    }

    public static function getCreatedBugIds()
    {
        $bugIds = [];
        foreach (self::$createdBugs as $bug) {
            $bugIds[] = $bug->id;
        }
        return $bugIds;
    }
}
