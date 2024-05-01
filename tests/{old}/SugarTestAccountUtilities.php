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


class SugarTestAccountUtilities
{
    private static $createdAccounts = [];

    private function __construct()
    {
    }

    /**
     * @return Account
     */
    public static function createAccount($id = '', $accountValues = [])
    {
        global $current_user;

        $time = random_int(0, mt_getrandmax());
        $account = BeanFactory::newBean('Accounts');

        $accountValues = array_merge([
            'name' => 'SugarAccount' . $time,
            'email' => 'account@' . $time . 'sugar.com',
            'assigned_user_id' => $current_user->id,
        ], $accountValues);

        // for backward compatibility with existing tests
        $accountValues['email1'] = $accountValues['email'];
        unset($accountValues['email']);

        foreach ($accountValues as $property => $value) {
            $account->$property = $value;
        }

        if (!empty($id)) {
            $account->new_with_id = true;
            $account->id = $id;
        }
        $account->save();
        $GLOBALS['db']->commit();
        self::$createdAccounts[] = $account;
        return $account;
    }

    public static function setCreatedAccount($account_ids)
    {
        foreach ($account_ids as $account_id) {
            $account = BeanFactory::newBean('Accounts');
            $account->id = $account_id;
            self::$createdAccounts[] = $account;
        } // foreach
    } // fn

    public static function removeAllCreatedAccounts()
    {
        $account_ids = self::getCreatedAccountIds();
        $GLOBALS['db']->query('DELETE FROM accounts WHERE id IN (\'' . implode("', '", $account_ids) . '\')');
        static::removeCreatedAccountsEmailAddresses();
    }

    /**
     * This function removes email addresses that may have been associated with the accounts created.
     *
     * @static
     */
    public static function removeCreatedAccountsEmailAddresses()
    {
        $accountIds = static::getCreatedAccountIds();
        $accountIdsSql = "'" . implode("','", $accountIds) . "'";

        if ($accountIds) {
            $subQuery = "SELECT DISTINCT email_address_id FROM email_addr_bean_rel WHERE bean_module ='Accounts' AND " .
                "bean_id IN ({$accountIdsSql})";
            $GLOBALS['db']->query("DELETE FROM email_addresses WHERE id IN ({$subQuery})");
            $GLOBALS['db']->query(
                "DELETE FROM emails_beans WHERE bean_module='Accounts' AND bean_id IN ({$accountIdsSql})"
            );
            $GLOBALS['db']->query(
                "DELETE FROM email_addr_bean_rel WHERE bean_module='Accounts' AND bean_id IN ({$accountIdsSql})"
            );
        }
    }

    public static function getCreatedAccountIds()
    {
        $account_ids = [];
        foreach (self::$createdAccounts as $account) {
            $account_ids[] = $account->id;
        }
        return $account_ids;
    }

    public static function deleteM2MRelationships($linkName)
    {
        $account_ids = self::getCreatedAccountIds();
        $GLOBALS['db']->query('DELETE FROM accounts_' . $linkName . ' WHERE account_id IN (\'' . implode("', '", $account_ids) . '\')');
    }
}
