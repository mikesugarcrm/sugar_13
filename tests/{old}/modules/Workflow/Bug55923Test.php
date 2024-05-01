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
 * Bug #55923
 * Workflow doesn't trigger when date_field changes
 *
 * @author vromanenko@sugarcrm.com
 * @ticket 55923
 */
class Bug55923Test extends TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;
    protected $workFlowActionShellId;
    protected $workFlowActionId;
    private $hasWorkflowFile = false;

    /**
     * @var Opportunity
     */
    protected $meeting;

    /**
     * @var DBManager
     */
    protected $db;

    protected function setUp(): void
    {
        $this->db = $GLOBALS['db'];
        $_REQUEST['base_module'] = 'Meetings';
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $wf = new WorkFlow();
        $wf->name = 'WF1';
        $wf->base_module = 'Meetings';
        $wf->status = 1;
        $wf->type = 'Normal';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'All';
        $this->workFlowId = $wf->save();
        $wf->check_logic_hook_file();

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'date_start';
        $wft->type = 'compare_change';
        $wft->frame_type = 'Primary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->save();
        $wfo = $wft->glue_triggers('', '');
        $this->workFlowTriggerShellId = $wft->save();

        $wfa = new WorkFlowActionShell();
        $wfa->action_type = 'update';
        $wfa->parent_id = $wf->id;
        $wfa->rel_module = '';
        $wfa->action_module = '';
        $this->workFlowActionShellId = $wfa->save();
        $actionObject = new WorkFlowAction();
        $actionObject->adv_type = '';
        $actionObject->ext1 = '';
        $actionObject->ext2 = '';
        $actionObject->ext3 = '';
        $actionObject->field = 'description';
        $actionObject->value = 'TRIGGERED';
        $actionObject->set_type = 'Basic';
        $actionObject->adv_type = '';
        $actionObject->parent_id = $wfa->id;
        $this->workFlowActionId = $actionObject->save();

        $wf = $wfa->get_workflow_object();
        $wfa->check_for_invitee_bridge($wf);
        $wf->write_workflow();

        $this->workFlowId = $wf->id;
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        SugarTestMeetingUtilities::removeAllCreatedMeetings();

        rmdir_recursive('custom/modules/Meetings/workflow');
        $this->db->query("delete from workflow where id = '$this->workFlowId'");
        $this->db->query("delete from workflow_triggershells where id = '$this->workFlowTriggerShellId'");
        $this->db->query("delete from workflow_actionshells where id = '$this->workFlowActionShellId'");
        $this->db->query("delete from workflow_actions where id = '$this->workFlowActionId'");
        LogicHook::refreshHooks();

        $_REQUEST = [];
        SugarTestHelper::tearDown();
    }

    /**
     * Ensure that workflow triggers actions when date field changes on newly created record.
     *
     * @group 55923
     */
    public function testWorkFlowTriggersWhenSavingNewOpportunityWithDateClosedChanged()
    {
        $meeting = SugarTestMeetingUtilities::createMeeting('workflow_meeting_test_id');
        $this->assertEquals('TRIGGERED', $meeting->description);
    }
}
