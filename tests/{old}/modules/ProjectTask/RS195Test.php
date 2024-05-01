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

require_once 'modules/ProjectTask/ProjectTask.php';

/**
 *  RS195: Prepare ProjectTask Module.
 */
class RS195Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, false]);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestHelper::tearDown();
    }

    public function testTaskProject()
    {
        $project = SugarTestProjectUtilities::createProject();
        $bean = SugarTestProjectTaskUtilities::createProjectTask(
            [
                'project_id' => $project->id,
                'parent_task_id' => 0,
                'project_task_id' => 1,
                'percent_complete' => '30',
                'name' => 'RS195Task_1',
            ]
        );
        $res = $bean->_get_project_name($project->id);
        $this->assertEquals($project->name, $res);
        $res = $bean->_get_parent_name($project->id);
        $this->assertEquals($project->name, $res);
        $res = $bean->getResourceName();
        $this->assertEmpty($res);
        $res = SugarTestReflection::callProtectedMethod($bean, 'getNumberOfTasksInProject', [$project->id]);
        $this->assertEquals(1, $res);
    }

    public function testTaskParent()
    {
        $project = SugarTestProjectUtilities::createProject();
        $bean = SugarTestProjectTaskUtilities::createProjectTask(
            [
                'project_id' => $project->id,
                'parent_task_id' => 0,
                'project_task_id' => 1,
                'percent_complete' => '30',
                'name' => 'RS195Task_1',
            ]
        );
        $bean2 = SugarTestProjectTaskUtilities::createProjectTask(
            [
                'project_id' => $project->id,
                'parent_task_id' => 1,
                'project_task_id' => 2,
                'percent_complete' => '30',
                'name' => 'RS195Task_2',
            ]
        );
        $res = $bean2->_get_depends_on_name($bean->id);
        $this->assertEquals('RS195Task_1', $res);
        $res = $bean2->getProjectTaskParent();
        $this->assertEquals($res->id, $bean->id);
        $res = $bean->getAllSubProjectTasks();
        $this->assertCount(1, $res);
        $res = array_shift($res);
        $this->assertEquals($bean2->id, $res->id);
    }
}
