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

require_once 'include/workflow/action_utils.php';

class Bug47403Test extends TestCase
{
    private $focus;
    private $actionArray;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->focus = SugarTestAccountUtilities::createAccount();

        $this->actionArray = [
            'action_module' => '',
            'action_type' => 'update',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic_ext' => [],
            'advanced' => [],
        ];
    }

    protected function tearDown(): void
    {
        unset($this->actionArray);
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
    }

    public function testWorkflowCanSetNonRequiredFieldToEmpty()
    {
        $this->focus->assigned_user_id = $GLOBALS['current_user']->id;
        $this->actionArray['basic'] = ['assigned_user_id' => ''];

        $this->assertSame($GLOBALS['current_user']->id, $this->focus->assigned_user_id);
        process_action_update($this->focus, $this->actionArray);
        $this->assertSame('', $this->focus->assigned_user_id);
    }

    public function testWorkflowCanNotSetRequiredFieldToEmpty()
    {
        $this->focus->user_name = $GLOBALS['current_user']->user_name;
        $this->actionArray['basic'] = ['name' => ''];

        $this->assertSame($GLOBALS['current_user']->user_name, $this->focus->user_name);
        process_action_update($this->focus, $this->actionArray);
        $this->assertNotSame('', $this->focus->user_name);
    }
}
