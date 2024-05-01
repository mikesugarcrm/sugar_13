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


class SugarTestProspectUtilities
{
    private static $createdProspects = [];

    private function __construct()
    {
    }

    public static function createProspect($id = '', $prospectValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $prospect = BeanFactory::newBean('Prospects');

        $prospectValues = array_merge(
            [
                'first_name' => "SugarProspectFirst{$time}",
                'last_name' => 'SugarProspectLast',
                'title' => 'Test prospect title',
                'email' => "prospect@{$time}sugar.com",
            ],
            $prospectValues
        );

        // for backward compatibility with existing tests
        $prospectValues['email1'] = $prospectValues['email'];
        unset($prospectValues['email']);

        foreach ($prospectValues as $property => $value) {
            $prospect->$property = $value;
        }

        if (!empty($id)) {
            $prospect->new_with_id = true;
            $prospect->id = $id;
        }
        $prospect->save();
        self::$createdProspects[] = $prospect;
        return $prospect;
    }


    public static function removeAllCreatedProspects()
    {
        $prospect_ids = self::getCreatedProspectIds();
        $GLOBALS['db']->query('DELETE FROM prospects WHERE id IN (\'' . implode("', '", $prospect_ids) . '\')');
        static::removeCreatedProspectsEmailAddresses();
    }

    /**
     * This function removes email addresses that may have been associated with the accounts created.
     *
     * @static
     */
    public static function removeCreatedProspectsEmailAddresses()
    {
        $prospectIds = static::getCreatedProspectIds();
        $prospectIdsSql = "'" . implode("','", $prospectIds) . "'";

        if ($prospectIds) {
            $subQuery = "SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module ='Prospects' " .
                "AND bean_id IN ({$prospectIdsSql})";
            $GLOBALS['db']->query("DELETE FROM email_addresses WHERE id IN ({$subQuery})");
            $GLOBALS['db']->query(
                "DELETE FROM emails_beans WHERE bean_module='Prospects' AND bean_id IN ({$prospectIdsSql})"
            );
            $GLOBALS['db']->query(
                "DELETE FROM email_addr_bean_rel WHERE bean_module='Prospects' AND bean_id IN ({$prospectIdsSql})"
            );
        }
    }

    public static function getCreatedProspectIds()
    {
        $prospect_ids = [];
        foreach (self::$createdProspects as $prospect) {
            $prospect_ids[] = $prospect->id;
        }
        return $prospect_ids;
    }
}
