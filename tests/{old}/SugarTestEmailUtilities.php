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


class SugarTestEmailUtilities
{
    private static $createdEmails = [];

    private function __construct()
    {
    }

    public static function createEmail($id = '', $override = [], $save = true)
    {
        global $timedate;

        $time = random_int(0, mt_getrandmax());
        $name = 'SugarEmail';
        $email = new Email();
        $email->name = $name . $time;
        $email->type = 'out';
        $email->status = 'sent';
        // Two days ago
        $email->date_sent = $timedate->to_display_date_time(gmdate('Y-m-d H:i:s', (time() - (3600 * 24 * 2))));
        if (!empty($id)) {
            $email->new_with_id = true;
            $email->id = $id;
        }
        foreach ($override as $key => $value) {
            $email->$key = $value;
        }

        if ($save) {
            $email->save();
        }

        if (!empty($override['parent_id']) && !empty($override['parent_type'])) {
            self::createEmailsBeansRelationship($email->id, $override['parent_type'], $override['parent_id']);
        }
        self::$createdEmails[] = $email;
        return $email;
    }

    public static function removeAllCreatedEmails()
    {
        $email_ids = self::getCreatedEmailIds();
        $emailIdsSql = "'" . implode("','", $email_ids) . "'";
        $GLOBALS['db']->query("DELETE FROM emails WHERE id IN ({$emailIdsSql})");
        $GLOBALS['db']->query("DELETE FROM emails_text WHERE email_id IN ({$emailIdsSql})");
        $GLOBALS['db']->query("DELETE FROM emails_email_addr_rel WHERE email_id IN ({$emailIdsSql})");
        self::removeCreatedEmailBeansRelationships();
        static::removeCreatedEmailsAttachments();
        self::$createdEmails = [];
    }

    private static function createEmailsBeansRelationship($email_id, $parent_type, $parent_id)
    {
        $unique_id = create_guid();
        $GLOBALS['db']->query('INSERT INTO emails_beans ( id, email_id, bean_id, bean_module, date_modified, deleted ) ' .
            "VALUES ( '{$unique_id}', '{$email_id}', '{$parent_id}', '{$parent_type}', '" . gmdate('Y-m-d H:i:s') . "', 0)");
    }

    private static function removeCreatedEmailBeansRelationships()
    {
        $email_ids = self::getCreatedEmailIds();
        $GLOBALS['db']->query('DELETE FROM emails_beans WHERE email_id IN (\'' . implode("', '", $email_ids) . '\')');
    }

    /**
     * Deletes all notes, and associated files, that are attached to created emails.
     */
    private static function removeCreatedEmailsAttachments()
    {
        $emailIds = static::getCreatedEmailIds();
        $result = $GLOBALS['db']->query("SELECT id FROM notes WHERE email_id IN ('" . implode("','", $emailIds) . "')");

        while ($result && $row = $GLOBALS['db']->fetchByAssoc($result)) {
            foreach (['', 'tmp/'] as $loc) {
                $file = "upload://{$loc}{$row['id']}";

                if (file_exists($file)) {
                    unlink($file);
                    continue;
                }
            }
        }

        $GLOBALS['db']->query("DELETE FROM notes WHERE email_id IN ('" . implode("','", $emailIds) . "')");
    }

    public static function getCreatedEmailIds()
    {
        $email_ids = [];
        foreach (self::$createdEmails as $email) {
            $email_ids[] = $email->id;
        }
        return $email_ids;
    }

    public static function setCreatedEmail($ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        foreach ($ids as $id) {
            $email = new Email();
            $email->id = $id;
            self::$createdEmails[] = $email;
        }
    }
}
