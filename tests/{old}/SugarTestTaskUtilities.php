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


class SugarTestTaskUtilities
{
    private static $createdTasks = [];

    private function __construct()
    {
    }

    /**
     * @return Task
     */
    public static function createTask($id = '', $values = [])
    {
        $time = random_int(0, mt_getrandmax());
        $task = BeanFactory::newBean('Tasks');

        $values = array_merge([
            'name' => 'SugarTask' . $time,
        ], $values);

        foreach ($values as $property => $value) {
            $task->$property = $value;
        }

        if (!empty($id)) {
            $task->new_with_id = true;
            $task->id = $id;
        }
        $task->save();
        self::$createdTasks[] = $task;
        return $task;
    }

    public static function setCreatedTask($task_ids)
    {
        foreach ($task_ids as $task_id) {
            $task = new Task();
            $task->id = $task_id;
            self::$createdTasks[] = $task;
        } // foreach
    } // fn

    public static function removeAllCreatedTasks()
    {
        $task_ids = self::getCreatedTaskIds();
        $GLOBALS['db']->query('DELETE FROM tasks WHERE id IN (\'' . implode("', '", $task_ids) . '\')');
    }

    public static function getCreatedTaskIds()
    {
        $task_ids = [];
        foreach (self::$createdTasks as $task) {
            $task_ids[] = $task->id;
        }
        return $task_ids;
    }
}
