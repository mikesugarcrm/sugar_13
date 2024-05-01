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


class SugarTestProspectListsUtilities
{
    private static $aCreatedProspectLists = [];
    private static $aCreatedProspectListsIds = [];

    private static $createdProspectLists = [];

    /**
     * @static Creates a test prospectList
     * @param string $prospect_list_id
     */
    public static function createProspectLists($id = '')
    {
        $name = 'SugarProspectListName';

        $prospectList = new ProspectList();
        $prospectList->name = $name;

        if (!empty($id)) {
            $prospectList->new_with_id = true;
            $prospectList->id = $id;
        }
        $prospectList->save();
        self::$createdProspectLists[] = $prospectList;
        return $prospectList;
    }

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeProspectLists($prospect_list_id)
    {
        if (is_array($prospect_list_id)) {
            $prospect_list_id = implode("','", $prospect_list_id);
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('{$prospect_list_id}')");
        } else {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$prospect_list_id}'");
        }
    }

    /**
     * @static
     * @param string $prospect_list_id
     * @param string $prospect_id
     */
    public static function removeProspectsListToProspectRelation($prospect_list_id, $prospect_id)
    {
        $GLOBALS['db']->query("DELETE FROM prospect_lists_prospects WHERE prospect_list_id='{$prospect_list_id}' AND related_id='{$prospect_id}'");
    }

    /**
     * @static
     */
    public static function removeAllCreatedProspectLists()
    {
        $prospectListIds = self::getCreatedProspectListIds();
        $GLOBALS['db']->query('DELETE FROM prospect_lists WHERE id IN (\'' . implode("', '", $prospectListIds) . '\')');
    }

    /**
     * @static
     */
    public static function getCreatedProspectListIds()
    {
        $prospectListIds = [];
        foreach (self::$createdProspectLists as $prospectList) {
            $prospectListIds[] = $prospectList->id;
        }
        return $prospectListIds;
    }

    public static function createProspectList($id = '', $aParams = [])
    {
        $time = random_int(0, mt_getrandmax());
        $oProspectList = new ProspectList();
        $oProspectList->name = 'ProspectList' . $time;
        if (!empty($id)) {
            $oProspectList->id = $id;
        }
        if (!empty($aParams)) {
            foreach ($aParams as $key => $val) {
                $oProspectList->$key = $val;
            }
        }
        $oProspectList->save();
        self::$aCreatedProspectLists[] = $oProspectList;
        self::$aCreatedProspectListsIds[] = $oProspectList->id;
        return $oProspectList;
    }

    /**
     * @static
     * @param mixed $prospect_list_id
     */
    public static function removeCreatedProspectLists($id = '')
    {
        if (!empty($id)) {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id = '{$id}'");
        } elseif (!empty(self::$aCreatedProspectLists)) {
            $GLOBALS['db']->query("DELETE FROM prospect_lists WHERE id IN ('" . implode("','", self::$aCreatedProspectListsIds) . "')");
        }
    }
}
