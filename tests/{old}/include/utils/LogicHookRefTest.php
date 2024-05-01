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

/**
 * @ticket BR-1345
 * Test byref logic hooks
 */
class LogicHookRefTest extends TestCase
{
    protected $bean;
    protected $hook;

    protected function setUp(): void
    {
        SugarTestHelper::setUpFiles();
        $this->bean = new Account();
        SugarTestHelper::setUp('current_user');
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        if (!empty($this->hook)) {
            call_user_func_array('remove_logic_hook', $this->hook);
        }
        SugarTestHelper::tearDown();
        SugarTestHelper::tearDownFiles();
    }

    public function testCallLogicHook()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook BR-1345', __FILE__, 'BR1345TestHook', 'count', 'foo', 123]];
        call_user_func_array('check_logic_hook_file', $this->hook);
        $this->bean->call_custom_logic('test_event', 'bar', 345);
        $this->assertInstanceOf('Account', BR1345TestHook::$args[0]);
        $this->assertEquals(['test_event', 'bar', 'foo', 123], array_slice(BR1345TestHook::$args, 1));
    }

    public function testCallLogicHookByRef()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook BR-11004', __FILE__, 'BR11004TestHook', 'count', 'foo', 123]];
        call_user_func_array('check_logic_hook_file', $this->hook);
        $logicHook = $this->getMockBuilder(LogicHook::class)
            ->onlyMethods(['log'])
            ->getMock();
        $bean = BeanFactory::newBean('Accounts');
        $logicHook->setBean($bean);
        $logicHook->call_custom_logic($bean->module_dir, 'test_event', ['bar']);
        $logicHook->expects($this->atMost(2))
            ->method('log')
            ->with(
                $this->matchesRegularExpression('/debug|error/'),
                $this->matchesRegularExpression('/Creating new instance|Error executing hook/')
            );
        $this->assertEquals([], BR11004TestHook::$args);
    }

    public function testCallLogicHookByRefProtected()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook BR-11004', __FILE__, 'BR11004TestHook', 'countProtected', 'foo', 123]];
        call_user_func_array('check_logic_hook_file', $this->hook);
        $logicHook = $this->getMockBuilder(LogicHook::class)
            ->onlyMethods(['log'])
            ->getMock();
        $bean = BeanFactory::newBean('Accounts');
        $logicHook->setBean($bean);
        $logicHook->call_custom_logic($bean->module_dir, 'test_event', ['bar']);
        $logicHook->expects($this->atMost(2))
            ->method('log')
            ->with(
                $this->matchesRegularExpression('/debug|fatal/'),
                $this->matchesRegularExpression('/Creating new instance|Error executing hook/')
            );
        $this->assertEquals([], BR11004TestHook::$args);
    }
}

class BR1345TestHook
{
    public static $args = [];

    public function count(&$bean, $event, $arguments)
    {
        self::$args = func_get_args();
    }
}

class BR11004TestHook
{
    public static $args = [];

    public function count(&$bean, &$event, $arguments)
    {
        self::$args = func_get_args();
    }

    protected function countProtected(&$bean, $event, $arguments)
    {
        self::$args = func_get_args();
    }
}
