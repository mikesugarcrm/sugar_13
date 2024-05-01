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

class Pat756Test extends TestCase
{
    private $task;

    protected function setUp(): void
    {
        $current_user = SugarTestUserUtilities::createAnonymousUser();

        $this->task = SugarTestTaskUtilities::createTask();
        $this->task->assigned_user_id = $current_user->id;
    }

    protected function tearDown(): void
    {
        SugarTestTaskUtilities::removeAllCreatedTasks();
        $GLOBALS['db']->query("DELETE FROM notes WHERE name = 'note756'", true);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        SugarTestHelper::tearDown();
    }

    public function testProcessActionNewAssnUserId()
    {
        $action_array = [
            'action_type' => 'new',
            'action_module' => 'notes',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => [
                'name' => 'note756',
            ],
            'basic_ext' => [],
            'advanced' => [],
        ];

        process_action_new($this->task, $action_array);

        $assigned_user_id = $GLOBALS['db']->getOne("SELECT assigned_user_id FROM notes WHERE name = 'note756'", true);

        $this->assertEquals($this->task->assigned_user_id, $assigned_user_id, 'assigned_user_id does not match');
    }
}
