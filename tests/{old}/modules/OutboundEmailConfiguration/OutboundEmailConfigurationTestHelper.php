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

class OutboundEmailConfigurationTestHelper
{
    private static $existingConfigurations = [];
    private static $existingAllowDefaultOutbound = null;
    private static $systemConfiguration;

    /**
     * This should be called before any users are created to avoid leaving test configurations in the database after
     * teardown.
     */
    public static function setUp()
    {
        self::backupExistingConfigurations();
        self::$systemConfiguration = self::createSystemOutboundEmailConfiguration();
    }

    public static function tearDown()
    {
        self::restoreExistingConfigurations();
        static::restoreAllowDefaultOutbound();
        static::$systemConfiguration = null;

        $oe = BeanFactory::newBean('OutboundEmail');
        $oe->resetSystemMailerCache();
    }

    public static function getSystemConfiguration()
    {
        return self::$systemConfiguration;
    }

    public static function backupExistingConfigurations()
    {
        self::$existingConfigurations = [];

        $sql = 'SELECT id FROM outbound_email';
        $result = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $outboundEmail = new OutboundEmail();
            $outboundEmail->disable_row_level_security = true;
            $outboundEmail->retrieve($row['id']);
            self::$existingConfigurations[] = $outboundEmail;
            $outboundEmail->delete();
        }

        $sql = 'SELECT id FROM inbound_email';
        $result = $GLOBALS['db']->query($sql);

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $inboundEmail = new InboundEmail();
            $inboundEmail->retrieve($row['id'], true, false);
            self::$existingConfigurations[] = $inboundEmail;
            $inboundEmail->hardDelete($inboundEmail->id);
        }
    }

    public static function restoreExistingConfigurations()
    {
        self::removeAllCreatedEmailRecords();

        foreach (self::$existingConfigurations as $configuration) {
            $configuration->new_with_id = true;
            $configuration->save();
        }
    }

    public static function createSystemOutboundEmailConfiguration()
    {
        $configuration = self::mergeOutboundEmailConfigurations();

        return self::createOutboundEmail($configuration);
    }

    public static function createSystemOverrideOutboundEmailConfiguration($userId = '1')
    {
        if (empty($userId)) {
            $userId = $GLOBALS['current_user']->id;
        }

        $oe = new OutboundEmail();
        $outboundEmail = $oe->getUsersMailerForSystemOverride($userId);
        if (!empty($outboundEmail)) {
            $sql = "DELETE FROM outbound_email WHERE type='system-override' AND user_id = '{$userId}'";
            $GLOBALS['db']->query($sql);
        }

        $user = BeanFactory::retrieveBean('Users', $userId);
        $userData = $user->getUsersNameAndEmail();
        $name = $userData['name'];
        $email = empty($userData['email']) ? "{$userId}@unit.net" : $userData['email'];

        $configuration = [
            'name' => $name,
            'type' => 'system-override',
            'user_id' => $userId,
            'from_email' => $email,
            'from_name' => $name,
            'team_id' => $user->getPrivateTeamID(),
            'team_set_id' => $user->getPrivateTeamID(),
            'reply_to_name' => $name,
            'reply_to_email_address' => "reply.{$userId}@unit.net",
        ];
        $configuration = self::mergeOutboundEmailConfigurations($configuration);
        $outboundEmail = self::createOutboundEmail($configuration);

        return $outboundEmail;
    }

    public static function createUserOutboundEmailConfiguration($userId = '1')
    {
        if (empty($userId)) {
            $userId = $GLOBALS['current_user']->id;
        }

        $name = "For User {$userId}";
        $configuration = [
            'name' => $name,
            'type' => 'user',
            'user_id' => $userId,
            'from_email' => "{$userId}@unit.net",
            'from_name' => $name,
        ];
        $configuration = self::mergeOutboundEmailConfigurations($configuration);

        return self::createOutboundEmail($configuration);
    }

    public static function createUserOutboundEmailConfigurations($seedCount = 1)
    {
        $configurations = [];

        for ($i = 0; $i < $seedCount; $i++) {
            $outboundEmail = self::createUserOutboundEmailConfiguration($GLOBALS['current_user']->id);

            $storedOptions = [
                'from_addr' => "{$GLOBALS['current_user']->id}@unit.net",
                'from_name' => "For User {$GLOBALS['current_user']->id}",
                'outbound_email' => $outboundEmail->id,
            ];
            $inboundEmail = self::createInboundEmail($GLOBALS['current_user']->id, $storedOptions);

            $configurations[$i] = [
                'inbound' => $inboundEmail,
                'outbound' => $outboundEmail,
            ];
        }

        return $configurations;
    }

    public static function mergeOutboundEmailConfigurations($configuration = [])
    {
        $defaults = [
            'name' => 'System',
            'type' => 'system',
            'user_id' => '1',
            'from_email' => 'foo@bar.com',
            'from_name' => 'Foo Bar',
            'mail_sendtype' => 'SMTP',
            'mail_smtptype' => 'other',
            'mail_smtpserver' => 'smtp.bar.com',
            'mail_smtpport' => '25',
            'mail_smtpuser' => 'foo',
            'mail_smtppass' => 'foobar',
            'mail_smtpauth_req' => '1',
            'mail_smtpssl' => '0',
            'team_id' => '1',
            'team_set_id' => '1',
        ];

        return array_merge($defaults, $configuration);
    }

    public static function createOutboundEmail($configuration)
    {
        $sea = new SugarEmailAddress();

        $outboundEmail = new OutboundEmail();
        $outboundEmail->new_with_id = true;
        $outboundEmail->id = create_guid();
        $outboundEmail->name = $configuration['name'];
        $outboundEmail->type = $configuration['type'];
        $outboundEmail->user_id = $configuration['user_id'];
        $outboundEmail->mail_sendtype = $configuration['mail_sendtype'];
        $outboundEmail->mail_smtptype = $configuration['mail_smtptype'];
        $outboundEmail->mail_smtpserver = $configuration['mail_smtpserver'];
        $outboundEmail->mail_smtpport = $configuration['mail_smtpport'];
        $outboundEmail->mail_smtpuser = $configuration['mail_smtpuser'];
        $outboundEmail->mail_smtppass = $configuration['mail_smtppass'];
        $outboundEmail->mail_smtpauth_req = $configuration['mail_smtpauth_req'];
        $outboundEmail->mail_smtpssl = $configuration['mail_smtpssl'];
        $outboundEmail->email_address = $configuration['from_email'];
        $outboundEmail->email_address_id = $sea->getEmailGUID($outboundEmail->email_address);
        $outboundEmail->team_id = $configuration['team_id'];
        $outboundEmail->team_set_id = $configuration['team_set_id'];
        $outboundEmail->reply_to_email_address = $configuration['reply_to_email_address'] ?? '';
        $outboundEmail->reply_to_email_address_id = $sea->getEmailGUID($outboundEmail->reply_to_email_address);
        $outboundEmail->reply_to_name = $configuration['reply_to_name'] ?? '';
        $outboundEmail->save();

        return $outboundEmail;
    }

    public static function createInboundEmail($userId = '1', $storedOptions = [])
    {
        if (empty($userId)) {
            $userId = $GLOBALS['current_user']->id;
        }

        $inboundEmail = BeanFactory::newBean('InboundEmail');
        $inboundEmail->new_with_id = true;
        $inboundEmail->id = create_guid();
        $inboundEmail->name = "For User {$userId}";
        $inboundEmail->stored_options = base64_encode(serialize($storedOptions));
        $inboundEmail->is_personal = true;
        $inboundEmail->created_by = $userId;
        $inboundEmail->group_id = $userId;
        $inboundEmail->team_set_id = '1';
        $inboundEmail->team_id = '1';
        $inboundEmail->save();

        return $inboundEmail;
    }

    public static function removeAllCreatedEmailRecords()
    {
        $sql = 'DELETE FROM outbound_email';
        $GLOBALS['db']->query($sql);

        $sql = 'DELETE FROM inbound_email';
        $GLOBALS['db']->query($sql);
    }

    /**
     * Sets the notify_allow_default_outbound admin setting, which determines which configurations can be used by the
     * current user.
     *
     * @param int $allow 0, 1, or 2
     */
    public static function setAllowDefaultOutbound($allow)
    {
        $admin = BeanFactory::getBean('Administration');

        if (is_null(static::$existingAllowDefaultOutbound)) {
            $admin->retrieveSettings('', true);

            if (isset($admin->settings['notify_allow_default_outbound'])) {
                static::$existingAllowDefaultOutbound = $admin->settings['notify_allow_default_outbound'];
            } else {
                static::$existingAllowDefaultOutbound = 0;
            }
        }

        $admin->saveSetting('notify', 'allow_default_outbound', $allow);

        // The values of fields on OutboundEmail records, like `email_address_id`, are dependent on the
        // `notify_allow_default_outbound` admin setting. Anytime a test changes it, we need to make sure these records
        // are not retrieved from cache.
        BeanFactory::clearCache();
    }

    /**
     * Restores the notify_allow_default_outbound admin setting to its value prior to running tests.
     */
    public static function restoreAllowDefaultOutbound()
    {
        if (!is_null(static::$existingAllowDefaultOutbound)) {
            $admin = BeanFactory::getBean('Administration');
            $admin->saveSetting('notify', 'allow_default_outbound', static::$existingAllowDefaultOutbound);

            // The values of fields on OutboundEmail records, like `email_address_id`, are dependent on the
            // `notify_allow_default_outbound` admin setting. Anytime a test changes it, we need to make sure these records
            // are not retrieved from cache.
            BeanFactory::clearCache();
        }
    }
}
