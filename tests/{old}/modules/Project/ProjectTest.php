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

class ProjectTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
    }

    public function testRemoval()
    {
        $project = SugarTestProjectUtilities::createProject();
        $task = SugarTestProjectTaskUtilities::createProjectTask([
            'project_id' => $project->id,
        ]);

        $project->mark_deleted($project->id);

        $this->assertNull(BeanFactory::retrieveBean($task->module_name, $task->id));
    }
}
