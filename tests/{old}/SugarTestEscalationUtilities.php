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


class SugarTestEscalationUtilities
{
    private static $createdEscalations = [];

    private function __construct()
    {
    }

    /**
     * Create and save a new Escalation bean.
     *
     * @param string $id ID of the record, defaults to ''.
     * @param array $escalationValues Key-value mapping of values to preassign.
     * @return escalation The created escalation.
     */
    public static function createEscalation($id = '', $escalationValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $escalation = new Escalation();

        if (!isset($escalationValues['name'])) {
            $escalation->name = 'SugarEscalation' . $time;
        }

        foreach ($escalationValues as $property => $value) {
            $escalation->$property = $value;
        }

        if (!empty($id)) {
            $escalation->new_with_id = true;
            $escalation->id = $id;
        }
        $escalation->save();
        $GLOBALS['db']->commit();
        self::$createdEscalations[] = $escalation;
        return $escalation;
    }

    /**
     * Hard-delete all escalations created
     */
    public static function removeAllCreatedEscalations()
    {
        $escalationIds = self::getCreatedEscalationIds();
        $GLOBALS['db']->query('DELETE FROM escalations WHERE id IN (\'' . implode("', '", $escalationIds) . '\')');
    }

    public static function getCreatedEscalationIds()
    {
        $escalationIds = [];
        foreach (self::$createdEscalations as $escalation) {
            $escalationIds[] = $escalation->id;
        }
        return $escalationIds;
    }
}
