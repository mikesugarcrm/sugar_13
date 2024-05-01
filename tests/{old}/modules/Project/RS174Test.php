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
 *  RS174: Prepare Project Module.
 */
class RS174Test extends TestCase
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

    public function testEfforts()
    {
        $bean = SugarTestProjectUtilities::createProject();
        $res = $bean->_get_total_estimated_effort($bean->id);
        $this->assertEmpty($res);
        $res = $bean->_get_total_actual_effort($bean->id);
        $this->assertEmpty($res);
    }

    public function testTasks()
    {
        $bean = SugarTestProjectUtilities::createProject();
        SugarTestProjectTaskUtilities::createProjectTask(
            [
                'project_id' => $bean->id,
                'parent_task_id' => 0,
                'project_task_id' => time(),
                'percent_complete' => '30',
                'name' => 'RS174Task',
            ]
        );
        $res = $bean->getAllProjectTasks();
        $this->assertCount(1, $res);
    }

    public function testHolidays()
    {
        $bean = SugarTestProjectUtilities::createProject();
        $query = $bean->getProjectHolidays();
        $resource = DBManagerFactory::getInstance()->query($query);
        $result = DBManagerFactory::getInstance()->fetchByAssoc($resource);
        $this->assertEmpty($result);
    }
}
