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
 * Removal of primary trigger should condition
 * removal of all triggers, and the related schedules
 *
 * @author avucinic@sugarcrm.com
 * @ticket 64815
 */
class Bug64815Test extends TestCase
{
    protected $workFlowId;

    /**
     * @var DBManager
     */
    protected $db;

    protected function setUp(): void
    {
        $this->db = $GLOBALS['db'];
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', [true, 1]);

        $wf = new WorkFlow();
        $wf->name = 'WF 64815';
        $wf->base_module = 'Calls';
        $wf->status = 1;
        $wf->type = 'Time';
        $wf->description = '';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'All';
        $wf->save();
        $wf->check_logic_hook_file();
        // Save workflow id
        $this->workFlowId = $wf->id;

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'description';
        $wft->type = 'compare_any_time';
        $wft->frame_type = 'Primary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = null;
        $wft->show_past = 0;
        $wft->parameters = 0;
        $wfo = $wft->glue_triggers('', '');
        $wft->save();

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'outlook_id';
        $wft->type = 'compare_specific';
        $wft->frame_type = 'Secondary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->eval = '((!(isset($focus->fetched_row["outlook_id"]) && $focus->fetched_row["outlook_id"] == "64815")) && (isset($focus->outlook_id) && $focus->outlook_id == "64815")) || ((isset($focus->outlook_id) && $focus->outlook_id == "64815") && !empty($_SESSION["workflow_cron"]) && $_SESSION["workflow_cron"] == "Yes")';
        $wft->save();

        $wf->write_workflow();
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        TimeDate::getInstance()->clearCache();

        rmdir_recursive('custom/modules/Calls/workflow');

        $this->db->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        LogicHook::refreshHooks();

        SugarTestCallUtilities::removeAllCreatedCalls();
        SugarTestHelper::tearDown();

        $_REQUEST = [];
    }

    /**
     * Ensure that deleting primary workflow trigger deletes all secondary triggers
     * and the schedules related to the workflow
     */
    public function testTimeElapsedWorkFlowScheduleCreation()
    {
        // Override TimeDate so we can test the update time
        $timeDate = TimeDate::getInstance();
        $time = $timeDate->fromString('2013-05-05 10:10:10');
        $timeDate->setNow($time);


        // Create a Call without setting the outlook_id and check that no schedule was created
        $bean = SugarTestCallUtilities::createCall();

        $result = $this->db->query(
            "SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            '0',
            $row['count'],
            'Workflow schedule should not be created.'
        );


        // Now set the needed value for the secondary trigger
        $bean = $bean->retrieve($bean->id);
        $bean->outlook_id = '64815';
        $bean->save();

        // Check that schedule was created
        $result = $this->db->query(
            "SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            '1',
            $row['count'],
            'Workflow schedule should be created.'
        );


        // Change description and check that workflow schedule got updated
        $timeDate->setNow($timeDate->getNow()->modify('+1 second'));
        $bean->description = 'New Description';
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $timeDate->asDb($timeDate->getNow()),
            $this->db->fromConvert($row['date_expired'], 'datetime'),
            'Workflow schedule should get updated for primary trigger'
        );


        // Change an attribute that has no triggers attached to it, and check that the workflow is not updated
        $timeDate->setNow($timeDate->getNow()->modify('+1 second'));
        $dateExpiredOld = $this->db->fromConvert($row['date_expired'], 'datetime');
        $bean = $bean->retrieve($bean->id);
        $bean->name = 'New Name';
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired, count(*) as count
            FROM workflow_schedules
            WHERE workflow_id = {$this->db->quoted($this->workFlowId)}
            GROUP BY date_expired"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $dateExpiredOld,
            $this->db->fromConvert($row['date_expired'], 'datetime'),
            'Workflow schedule should not get updated'
        );


        // Now change the outlook_id field to something other than what the trigger requires
        // and see that it doesn't update the schedule
        $timeDate->setNow($timeDate->getNow()->modify('+1 second'));
        $bean = $bean->retrieve($bean->id);
        $bean->outlook_id = 'New Location';
        $bean->save();
        $result = $this->db->query(
            "SELECT date_expired FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'"
        );
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals(
            $dateExpiredOld,
            $this->db->fromConvert($row['date_expired'], 'datetime'),
            'Workflow schedule should not get updated when secondary trigger not equal to the expected value'
        );
    }
}
