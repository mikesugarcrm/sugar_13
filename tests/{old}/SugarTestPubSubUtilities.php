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

class SugarTestPubSubUtilities
{
    private static array $createdModuleEventPushSubs = [];

    private function __construct()
    {
    }

    public static function createModuleEventPushSubscription(array $args): PubSub_ModuleEvent_PushSub
    {
        $sub = BeanFactory::newBean('PubSub_ModuleEvent_PushSubs');

        foreach ($args as $field => $value) {
            $sub->{$field} = $value;
        }

        if (!empty($sub->id)) {
            $sub->new_with_id = true;
        }

        $sub->save();
        static::registerModuleEventPushSubscription($sub);

        return $sub;
    }

    public static function registerModuleEventPushSubscription(PubSub_ModuleEvent_PushSub $sub): void
    {
        static::$createdModuleEventPushSubs[$sub->id] = $sub;
    }

    public static function removeCreatedModuleEventPushSubscriptions(): void
    {
        $q = 'DELETE FROM pubsub_moduleevent_pushsubs WHERE id IN (?)';
        $conn = DBManagerFactory::getConnection();
        $conn->executeUpdate($q, [array_keys(static::$createdModuleEventPushSubs)], [Connection::PARAM_STR_ARRAY]);

        static::$createdModuleEventPushSubs = [];
    }
}
