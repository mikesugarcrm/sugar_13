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


class SugarTestWebLogicHookUtilities
{
    private static $createdWebLogicHooks = [];

    private function __construct()
    {
    }

    public static function createWebLogicHook($id = '', $attributes = [])
    {
        LogicHook::refreshHooks();
        $webLogicHook = new WebLogicHookMock();

        foreach ($attributes as $attribute => $value) {
            $webLogicHook->$attribute = $value;
        }

        if (!empty($id)) {
            $webLogicHook->new_with_id = true;
            $webLogicHook->id = $id;
        }

        $webLogicHook->save();
        $GLOBALS['db']->commit();
        self::$createdWebLogicHooks[] = $webLogicHook;
        return $webLogicHook;
    }

    public static function removeAllCreatedWebLogicHook()
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map([$db, 'quoted'], self::getCreatedWebLogicHookIds()));
        foreach (self::$createdWebLogicHooks as $hook) {
            $hook->mark_deleted($hook->id);
        }
        if (!empty($conditions)) {
            $db->query('DELETE FROM weblogichooks WHERE id IN (' . $conditions . ')');
        }
        WebLogicHookMock::$dispatchOptions = null;
        LogicHook::refreshHooks();
    }

    public static function getCreatedWebLogicHookIds()
    {
        $hook_ids = [];
        foreach (self::$createdWebLogicHooks as $hook) {
            $hook_ids[] = $hook->id;
        }
        return $hook_ids;
    }
}


class WebLogicHookMock extends WebLogicHook
{
    public static $dispatchOptions = null;

    protected function getActionArray()
    {
        return [1, $this->name, 'tests/{old}/SugarTestWebLogicHookUtilities.php', self::class, 'dispatchRequest', $this->id];
    }

    public function dispatchRequest(SugarBean $seed, $event, $arguments, $id)
    {
        self::$dispatchOptions = [
            'seed' => $seed,
            'event' => $event,
            'arguments' => $arguments,
            'id' => $id,
        ];
    }
}
