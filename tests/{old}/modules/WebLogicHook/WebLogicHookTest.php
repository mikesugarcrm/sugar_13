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

class WebLogicHookTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', ['WebLogicHooks']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestWebLogicHookUtilities::removeAllCreatedWebLogicHook();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    /**
     * @ticket SP-942
     */
    public function testWebLogicHookFire()
    {
        $hook = SugarTestWebLogicHookUtilities::createWebLogicHook(false, [
            'name' => ('Text Hook ' . time()),
            'webhook_target_module' => 'Accounts',
            'request_method' => 'POST',
            'url' => 'http://www.example.com',
            'trigger_event' => 'after_save',
        ]);

        $account = SugarTestAccountUtilities::createAccount();
        $dispatchOptions = $hook::$dispatchOptions;

        $this->assertEquals('Account', get_class($dispatchOptions['seed']));
        $this->assertEquals($hook->id, $dispatchOptions['id']);
        $this->assertEquals($hook->trigger_event, $dispatchOptions['event']);
        $this->assertNotEmpty($dispatchOptions['seed']);
        $this->assertNotEmpty($dispatchOptions['event']);
        $this->assertNotEmpty($dispatchOptions['arguments']);
        $this->assertNotEmpty($dispatchOptions['id']);
    }
}
