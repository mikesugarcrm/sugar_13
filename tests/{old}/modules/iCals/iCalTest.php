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

class iCalTest extends TestCase
{
    public $timedate;
    public $project;

    protected function setUp(): void
    {
        $this->timedate = new TimeDate();

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->full_name = 'Boro Sitnikovski';
        $GLOBALS['current_user']->email1 = 'bsitnikovski@sugarcrm.com';

        $meeting = SugarTestMeetingUtilities::createMeeting();
        $meeting->name = 'VeryImportantMeeting';
        $meeting->date_start = $this->timedate->to_display_date_time(gmdate('Y-m-d H:i:s', mktime(12, 30, 00, date('m'), date('d') + 1, date('Y'))));
        $meeting->save();
        $GLOBALS['db']->query(sprintf("INSERT INTO meetings_users (id, meeting_id, user_id, required, accept_status, date_modified, deleted) VALUES ('%s', '%s', '%s', '1', 'none', NULL, '0')", create_guid(), $meeting->id, $GLOBALS['current_user']->id));

        $task = SugarTestTaskUtilities::createTask();
        $task->assigned_user_id = $GLOBALS['current_user']->id;
        $task->name = 'VeryImportantTask';
        $task->save();

        $this->project = SugarTestProjectUtilities::createProject();
        $projectId = $this->project->id;
        $projectTaskData = [
            'project_id' => $projectId,
            'parent_task_id' => '',
            'project_task_id' => 1,
            'percent_complete' => 50,
            'name' => 'VeryImportantProjectTask',
        ];
        $projectTask = SugarTestProjectTaskUtilities::createProjectTask($projectTaskData);
        $projectTask->assigned_user_id = $GLOBALS['current_user']->id;
        $projectTask->save();
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestMeetingUtilities::removeMeetingUsers();
        SugarTestMeetingUtilities::removeAllCreatedMeetings();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestProjectUtilities::removeAllCreatedProjects();
        SugarTestProjectTaskUtilities::removeAllCreatedProjectTasks();
        unset($this->timedate);
        unset($this->project);
        unset($GLOBALS['current_user']);
    }

    public function testGetVcalIcal()
    {
        $iCal = new iCal();
        $iCalString = $iCal->getVcalIcal($GLOBALS['current_user'], null);

        $this->assertStringContainsString('VeryImportantMeeting', $iCalString);
        $this->assertStringContainsString('VeryImportantTask', $iCalString);
        $this->assertStringContainsString('VeryImportantProjectTask', $iCalString);
    }

    public function testiCalNewline()
    {
        $res = vCal::get_ical_event($this->getDummyBean('http://www.sugarcrm.com/'), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, 'DESCRIPTION');
        // Test to see if there are two newlines after url for description
        $this->assertStringContainsString("http://www.sugarcrm.com/\r\n\r\n", $desc);
    }

    public function testiCalEmptyJoinURL()
    {
        $res = vCal::get_ical_event($this->getDummyBean(), $GLOBALS['current_user']);

        $desc = $this->grabiCalField($res, 'DESCRIPTION');

        // Test to see if there are no newlines for empty url for description
        $this->assertStringNotContainsString('\\n\\n', $desc);
    }

    private function grabiCalField($iCal, $field)
    {
        $ical_arr = vCal::create_ical_array_from_string($iCal);

        foreach ($ical_arr as $ical_val) {
            if ($ical_val[0] == $field) {
                return $ical_val[1];
            }
        }

        return '';
    }

    private function getDummyBean($join_url = '')
    {
        $bean = new SugarBean();
        $bean->id = 123;
        $bean->date_start = $bean->date_end = $GLOBALS['timedate']->nowDb();
        $bean->name = 'Dummy Bean';
        $bean->location = 'Sugar, Cupertino; Sugar, EMEA';
        $bean->join_url = $join_url;
        $bean->description = "Hello, this is a dummy description.\n"
            . 'It contains newlines, backslash \\ semicolon ; and commas';
        return $bean;
    }
}
