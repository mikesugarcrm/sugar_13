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

use PHPUnit\Framework\TestCase;

class Bug52133Test extends TestCase
{
    protected $bean;
    protected $hook;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    protected function setUp(): void
    {
        $this->bean = new Account();
        Bug52113TestHook::$count = 0;
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        if (!empty($this->hook)) {
            call_user_func_array('remove_logic_hook', $this->hook);
        }
    }

    /**
     * @ticket 52113
     */
    public function testCallLogicHookLoop()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook', __FILE__, 'Bug52113TestHook', 'count']];
        call_user_func_array('check_logic_hook_file', $this->hook);
        for ($i = 0; $i < 50; $i++) {
            $this->bean->call_custom_logic('test_event');
        }
        $this->assertEquals(50, Bug52113TestHook::$count);
    }

    /**
     * @ticket 52113
     */
    public function testCallLogicHookRecursive()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook', __FILE__, 'Bug52113TestHook', 'recurse']];
        call_user_func_array('check_logic_hook_file', $this->hook);
        for ($i = 0; $i < 20; $i++) {
            $this->bean->call_custom_logic('test_event');
        }
        $this->assertEquals(220, Bug52113TestHook::$count);
    }
}

class Bug52113TestHook
{
    public static $count = 0;

    public function count()
    {
        self::$count++;
    }

    public function recurse($bean, $event)
    {
        $this->count();
        $bean->call_custom_logic($event);
    }
}
