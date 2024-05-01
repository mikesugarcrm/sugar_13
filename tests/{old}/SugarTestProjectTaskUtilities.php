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


require_once 'modules/ProjectTask/ProjectTask.php';

class SugarTestProjectTaskUtilities extends SugarTestObjectUtilities
{
    public static $tableName = 'project_task';

    private function __construct()
    {
    }

    public static function createProjectTask(array $properties)
    {
        $task = new ProjectTask();

        foreach ($properties as $property => $value) {
            $task->$property = $value;
        }

        $task->save();
        self::pushObject($task);

        return $task;
    }

    public static function pushProject($project)
    {
        parent::pushObject($project);
    }

    public static function removeAllCreatedProjectTasks()
    {
        parent::removeAllCreatedObjects(self::$tableName);
    }
}
