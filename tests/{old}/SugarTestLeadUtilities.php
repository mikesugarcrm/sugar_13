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


class SugarTestLeadUtilities
{
    private static $createdLeads = [];

    private function __construct()
    {
    }

    public static function createLead($id = '', $leadValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $lead = BeanFactory::newBean('Leads');


        $leadValues = array_merge(
            [
                'first_name' => "SugarLeadFirst{$time}",
                'last_name' => 'SugarLeadLast',
                'email' => "lead@{$time}sugar.com",
            ],
            $leadValues
        );

        // for backward compatibility with existing tests
        $leadValues['email1'] = $leadValues['email'];
        unset($leadValues['email']);

        foreach ($leadValues as $property => $value) {
            $lead->$property = $value;
        }

        if (!empty($id)) {
            $lead->new_with_id = true;
            $lead->id = $id;
        }

        $lead->save();
        $GLOBALS['db']->commit();
        self::$createdLeads[] = $lead;
        return $lead;
    }

    public static function setCreatedLead($lead_ids)
    {
        foreach ($lead_ids as $lead_id) {
            $lead = new Lead();
            $lead->id = $lead_id;
            self::$createdLeads[] = $lead;
        } // foreach
    } // fn

    public static function removeAllCreatedLeads()
    {
        $lead_ids = self::getCreatedLeadIds();
        $GLOBALS['db']->query('DELETE FROM leads WHERE id IN (\'' . implode("', '", $lead_ids) . '\')');
        static::removeCreatedLeadsEmailAddresses();
    }

    /**
     * removeCreatedLeadsEmailAddresses
     *
     * This function removes email addresses that may have been associated with the leads created
     *
     * @static
     * @return void
     */
    public static function removeCreatedLeadsEmailAddresses()
    {
        $lead_ids = self::getCreatedLeadIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module =\'Leads\' AND bean_id IN (\'' . implode("', '", $lead_ids) . '\'))');
        $GLOBALS['db']->query('DELETE FROM emails_beans WHERE bean_module=\'Leads\' AND bean_id IN (\'' . implode("', '", $lead_ids) . '\')');
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Leads\' AND bean_id IN (\'' . implode("', '", $lead_ids) . '\')');
    }

    public static function removeCreatedLeadsUsersRelationships()
    {
        $lead_ids = self::getCreatedLeadIds();
        $GLOBALS['db']->query('DELETE FROM leads_users WHERE lead_id IN (\'' . implode("', '", $lead_ids) . '\')');
    }

    public static function getCreatedLeadIds()
    {
        $lead_ids = [];
        foreach (self::$createdLeads as $lead) {
            $lead_ids[] = $lead->id;
        }
        return $lead_ids;
    }
}
