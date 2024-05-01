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
 * @author avucinic
 * @ticket 49723
 */
class Bug49723Test extends TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;

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
        $wf->name = 'WF1';
        $wf->base_module = 'Contacts';
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
        $wft->parameters = 345600;
        $wfo = $wft->glue_triggers('', '');
        $wft->save();
        // Save primary trigger id
        $this->workFlowTriggerShellId = $wft->id;

        $wft = new WorkFlowTriggerShell();
        $wft->field = 'department';
        $wft->type = 'filter_field';
        $wft->frame_type = 'Secondary';
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->eval = '(isset($focus->department) && $focus->department == \'www\')';
        $wft->save();

        $wf->write_workflow();
        LogicHook::refreshHooks();
    }

    protected function tearDown(): void
    {
        // Bad idea, but because of include_once all tests that run after this one need the workflow..
        // rmdir_recursive('custom/modules/Contacts/workflow');

        $this->db->query("DELETE FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $this->db->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        LogicHook::refreshHooks();

        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestHelper::tearDown();

        $_REQUEST = [];
    }

    /**
     * Ensure that deleting primary workflow trigger deletes all secondary triggers
     * and the schedules related to the workflow
     *
     * @group 49723
     */
    public function testWorkFlowDeleteTriggers()
    {
        $bean = SugarTestContactUtilities::createContact();
        $bean->description = 'Test';
        $bean->department = 'www';
        $bean->save();

        // Check that triggers were created
        $result = $this->db->query("SELECT count(*) as count FROM workflow_triggershells WHERE parent_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('2', $row['count'], 'Workflow triggers not created.');

        // Check that schedule was created
        $result = $this->db->query("SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('1', $row['count'], 'Workflow schedule not created.');

        // Delete primary trigger
        $wft = new WorkFlowTriggerShell();
        $wft->retrieve($this->workFlowTriggerShellId);
        $wft->mark_deleted($wft->id);

        // Check that triggers were deleted
        $result = $this->db->query("SELECT count(*) as count FROM workflow_triggershells WHERE parent_id = '$this->workFlowId' AND deleted = 1");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('2', $row['count'], 'Workflow triggers not deleted.');

        // Check that schedule was deleted
        $result = $this->db->query("SELECT count(*) as count FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals('0', $row['count'], 'Workflow schedule not deleted.');
    }
}
