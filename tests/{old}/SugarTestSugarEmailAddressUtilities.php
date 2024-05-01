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

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

class SugarTestSugarEmailAddressUtilities
{
    private static $createdEmailAddresses = [];

    private static $createdContact;

    private function __construct()
    {
    } // not an instantiated class.

    /**
     * creates a Parent Bean to hang Emails from
     * @param $time
     * @return Contact|null
     */
    private static function createContact($time)
    {
        if (self::$createdContact === null) {
            $name = 'SugarEmailAddressContact';
            $contact = new Contact();
            $contact->first_name = $name . $time;
            $contact->last_name = 'LastName';
            $contact->save();

            $GLOBALS['db']->commit();
            self::$createdContact = $contact;
        }

        return self::$createdContact;
    }

    /**
     * Create a SugarEmailAddress
     * - This version doesn't bother attaching a SugarEmailAddress to a parent bean.
     * - As such, save() doesn't work on the email addresses.
     * @access public
     * @param string $address - custom address to pass, otherwise pass null.
     * @param string $id - pass parameter to set a specific uuid for the SugarEmailAddress
     * @param array $override - pass key => value array of parameters to override the defaults
     * @return SugarEmailAddress
     */
    public static function createEmailAddress($address = null, $id = '', $override = [])
    {
        $params = [];
        $time = random_int(0, mt_getrandmax());
        $contact = self::createContact($time);
        if (!empty($address)) {
            $override['email_address'] = $address;
        }

        $params['email_address'] = 'semailaddress@' . $time . 'sugar.com';
        $params['primary'] = true;
        $params['reply_to'] = false;
        $params['invalid'] = false;
        $params['opt_out'] = false;
        foreach ($override as $key => $value) {
            $params[$key] = $value;
        }

        $contact->emailAddress->addAddress(
            $params['email_address'],
            $params['primary'],
            $params['reply_to'],
            $params['invalid'],
            $params['opt_out'],
            $id
        );
        $contact->emailAddress->save($contact->id, $contact->module_dir);

        self::$createdEmailAddresses[] = $contact->emailAddress;

        return $contact->emailAddress;
    }

    /**
     * Clean up after use
     * @access public
     */
    public static function removeAllCreatedEmailAddresses()
    {
        $address_ids = self::getCreatedEmailAddressIds();
        $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $address_ids) . '\')');
    }

    /**
     * clean up the related bean and the relationship table
     * @access public
     */
    public static function removeCreatedContactAndRelationships()
    {
        if (self::$createdContact === null) {
            return;
        }

        $GLOBALS['db']->query("DELETE FROM contacts WHERE id = '" . self::$createdContact->id . "'");
        $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id =\'' . self::$createdContact->id . '\'');
        self::$createdContact = null;
    }


    /**
     * Retrieve a list of all ids of SugarEmailAddresses created through this class
     * @access public
     * @return array ids of all SugarEmailAddresses created
     */
    public static function getCreatedEmailAddressIds()
    {
        $address_ids = [];
        foreach (self::$createdEmailAddresses as $address) {
            $address_ids[] = $address->id;
        }
        return $address_ids;
    }

    /**
     * In case we don't have our bean's UUID - get it via address
     * @param $address - email address
     * @return string|null UUID of bean for email address.
     */
    public static function fetchEmailIdByAddress($address)
    {
        $email_caps = sugarStrToUpper(trim($address));
        $rs = $GLOBALS['db']->query("SELECT id from email_addresses where email_address_caps='$email_caps'");
        $a = $GLOBALS['db']->fetchByAssoc($rs);

        if (!empty($a['id'])) {
            return $a['id'];
        } else {
            return null;
        }
    }

    /**
     * get our parent bean
     * @return Contact|null
     */
    public static function getContact()
    {
        return self::createContact(random_int(0, mt_getrandmax()));
    }
}
