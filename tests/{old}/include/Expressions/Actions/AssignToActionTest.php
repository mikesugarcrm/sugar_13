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

class AssignToActionTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestTaskUtilities::removeAllCreatedTasks();
    }

    public function testSetAssignedUserId()
    {
        $task = SugarTestTaskUtilities::createTask();
        $task->assigned_user_id = null;
        $task->priority = 'High';
        $action = ActionFactory::getNewAction('AssignTo', [
            'value' => '"admin"',
        ]);
        $action->fire($task);
        $this->assertEquals(1, $task->assigned_user_id);
    }
}
