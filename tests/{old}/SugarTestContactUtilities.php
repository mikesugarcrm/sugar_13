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

use Doctrine\DBAL\Connection;

class SugarTestContactUtilities
{
    private static $createdContacts = [];

    private function __construct()
    {
    }

    /**
     * @param string $id
     * @param array $contactValues
     * @return Contact
     */
    public static function createContact($id = '', $contactValues = [], $class = 'Contact')
    {
        $time = random_int(0, mt_getrandmax());
        $contact = new $class();

        $contactValues = array_merge([
            'first_name' => 'SugarContactFirst' . $time,
            'last_name' => 'SugarContactLast' . $time,
            'email' => 'contact@' . $time . 'sugar.com',
        ], $contactValues);

        // for backward compatibility with existing tests
        $contactValues['email1'] = $contactValues['email'];
        unset($contactValues['email']);

        foreach ($contactValues as $property => $value) {
            $contact->$property = $value;
        }

        if (!empty($id)) {
            $contact->new_with_id = true;
            $contact->id = $id;
        }
        $contact->save();
        $GLOBALS['db']->commit();
        self::$createdContacts[] = $contact;
        return $contact;
    }

    public static function setCreatedContact($contact_ids)
    {
        foreach ($contact_ids as $contact_id) {
            $contact = new Contact();
            $contact->id = $contact_id;
            self::$createdContacts[] = $contact;
        } // foreach
    } // fn

    public static function removeAllCreatedContacts()
    {
        $ids = self::getCreatedContactIds();

        if (safeCount($ids)) {
            $conn = DBManagerFactory::getConnection();
            $tables = [
                'accounts_contacts' => 'contact_id',
                'contacts' => 'id',
            ];

            foreach ($tables as $table => $key) {
                $query = sprintf('DELETE FROM %s WHERE %s IN (?)', $table, $key);
                $conn->executeUpdate($query, [$ids], [Connection::PARAM_STR_ARRAY]);
            }
        }

        static::removeCreatedContactsEmailAddresses();
    }

    /**
     * removeCreatedContactsEmailAddresses
     *
     * This function removes email addresses that may have been associated with the contacts created
     *
     * @static
     * @return void
     */
    private static function removeCreatedContactsEmailAddresses()
    {
        $contact_ids = self::getCreatedContactIds();
        if ($contact_ids) {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module =\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\'))');
            $GLOBALS['db']->query('DELETE FROM emails_beans WHERE bean_module=\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\')');
            $GLOBALS['db']->query('DELETE FROM email_addr_bean_rel WHERE bean_module=\'Contacts\' AND bean_id IN (\'' .
                implode("', '", $contact_ids) . '\')');
        }
    }

    public static function removeCreatedContactsUsersRelationships()
    {
        $contact_ids = self::getCreatedContactIds();
        if ($contact_ids) {
            $GLOBALS['db']->query('DELETE FROM contacts_users WHERE contact_id IN (\'' . implode("', '", $contact_ids) .
                '\')');
        }
    }

    public static function getCreatedContactIds()
    {
        $contact_ids = [];
        foreach (self::$createdContacts as $contact) {
            $contact_ids[] = $contact->id;
        }
        return $contact_ids;
    }
}
