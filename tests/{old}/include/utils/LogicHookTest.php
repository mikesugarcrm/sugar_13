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

final class LogicHookTest extends TestCase
{
    protected $hook;

    protected function setUp(): void
    {
        SugarTestHelper::setUpFiles();

        // disable deprecated error for the PHP4-style hook constructor
        $this->iniSet('error_reporting', ini_get('error_reporting') & ~E_DEPRECATED);

        foreach (self::getHooks() as $class => $_) {
            $class::$invocationCount = 0;
        }

        unset($GLOBALS['logic_hook']);
        LogicHook::initialize();
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        if (!empty($this->hook)) {
            call_user_func_array('remove_logic_hook', $this->hook);
        }

        SugarTestHelper::tearDownFiles();

        unset($GLOBALS['logic_hook']);
        LogicHook::refreshHooks();
    }

    /**
     * @dataProvider hookProvider
     */
    public function testHooksDirect(string $module, string $class, string $method): void
    {
        $file = sprintf('custom/modules/%s/logic_hooks.php', $module);
        $this->registerHook($class, $method, $file);

        $this->callCustomLogic($module);
        $this->assertHookInvoked($class);
    }

    /**
     * @dataProvider hookProvider
     */
    public function testHooksExtDirect(string $module, string $class, string $method): void
    {
        $file = sprintf(
            'custom/%s/Ext/LogicHooks/logichooks.ext.php',
            $module
                ? sprintf('modules/%s', $module)
                : 'application'
        );
        $this->registerHook($class, $method, $file);

        $this->callCustomLogic($module);
        $this->assertHookInvoked($class);
    }

    public static function hookProvider(): iterable
    {
        $modules = ['', 'Accounts', 'Contacts'];
        $hooks = self::getHooks();

        foreach ($modules as $module) {
            foreach ($hooks as $class => $method) {
                yield [$module, $class, $method];
            }
        }
    }

    private static function getHooks(): iterable
    {
        return [
            TestLogicHook::class => 'invoke',
        ];
    }

    private function registerHook(string $class, string $method, string $targetFile): void
    {
        SugarTestHelper::saveFile($targetFile);

        SugarAutoLoader::ensureDir(dirname($targetFile));
        write_array_to_file('hook_array', [
            'test_event' => [
                [0, null, __FILE__, $class, $method],
            ],
        ], $targetFile);

        LogicHook::refreshHooks();
    }

    private function callCustomLogic(string $module): void
    {
        /** @var $logic_hook LogicHook */
        global $logic_hook;

        $logic_hook->call_custom_logic($module, 'test_event');
    }

    private function assertHookInvoked(string $class): void
    {
        $this->assertSame(1, $class::$invocationCount);
    }
    public function testCallLogicHookException()
    {
        $this->hook = ['Accounts', 'test_event', [1, 'Test hook', __FILE__, 'TestHook', 'logicHook']];
        call_user_func_array('check_logic_hook_file', $this->hook);
        $logicHook = $this->getMockBuilder(LogicHook::class)
            ->onlyMethods(['log'])
            ->getMock();
        $bean = BeanFactory::newBean('Accounts');
        $logicHook->setBean($bean);
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        $logicHook->call_custom_logic($bean->module_dir, 'test_event', ['bar']);
    }
}

class TestHook
{
    public function logicHook(&$bean, $event, $arguments)
    {
        throw new \SugarApiExceptionInvalidParameter();
    }
}
