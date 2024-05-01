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


class SugarTestProjectUtilities extends SugarTestObjectUtilities
{
    public static $tableName = 'project';

    private function __construct()
    {
    }

    public static function createProject($id = '')
    {
        $timeDate = TimeDate::getInstance();
        $project = new Project();
        $project->name = 'testProject';
        $project->team_id = 1;
        $project->team_set_id = 1;
        $project->estimated_start_date = $timeDate->nowDbDate();
        $project->estimated_end_date = $timeDate->getNow()->modify('+1 day')->asDbDate();
        $project->save();
        self::pushObject($project);
        return $project;
    }

    public static function pushProject($project)
    {
        parent::pushObject($project);
    }

    public static function removeAllCreatedProjects()
    {
        parent::removeAllCreatedObjects(self::$tableName);
    }
}
