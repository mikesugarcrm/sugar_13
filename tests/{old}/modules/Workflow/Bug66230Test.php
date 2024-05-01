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
 * Class Bug66230Test
 *
 * Test that nothing breaks time elapsed workflows with only one trigger
 */
class Bug66230Test extends TestCase
{
    protected $workFlowId;
    protected $workFlowTriggerShellId;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');

        // Create a workflow firing on New Quotes
        $wf = new WorkFlow();
        $wf->name = 'WF1 66230';
        $wf->base_module = 'Quotes';
        $wf->status = 1;
        $wf->type = 'Time';
        $wf->fire_order = 'alerts_actions';
        $wf->parent_id = null;
        $wf->record_type = 'New';
        $this->workFlowId = $wf->save();
        $wf->check_logic_hook_file();

        // Condition, if description doesn't change for 0 hours
        $wft = new WorkFlowTriggerShell();
        $wft->field = 'description';
        $wft->type = 'compare_any_time';
        $wft->frame_type = 'Primary';
        $wft->parameters = 0;
        $wft->parent_id = $wf->id;
        $wft->rel_module = '';
        $wft->show_past = 0;
        $wft->save();
        $wfo = $wft->glue_triggers('', '');
        $this->workFlowTriggerShellId = $wft->save();

        $wf->write_workflow();

        $this->workFlowId = $wf->id;

        // Refresh Hooks
        LogicHook::refreshHooks();

        $_SESSION['workflow_cron'] = [];
    }

    protected function tearDown(): void
    {
        // Remove workflow defs
        rmdir_recursive('custom/modules/Quotes/workflow');
        rmdir_recursive('custom/modules/Quotes/logic_hooks.php');
        // Delete all the stuff created for the workflow
        $GLOBALS['db']->query("DELETE FROM workflow WHERE id = '$this->workFlowId'");
        $GLOBALS['db']->query("DELETE FROM workflow_triggershells WHERE id = '$this->workFlowTriggerShellId'");
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->workFlowId'");
        // Refresh hooks
        LogicHook::refreshHooks();

        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestHelper::tearDown();
    }

    public function testSingleAfterTimeElapsesTrigger()
    {
        // Create a Quote
        $quote = SugarTestQuoteUtilities::createQuote();

        $result = $quote->db->query(
            "SELECT count(*) as count
                FROM workflow_schedules
                WHERE target_module = 'Quotes'
                AND bean_id = '{$quote->id}'"
        );
        $row = $quote->db->fetchByAssoc($result);

        // Check if the workflow fired by looking into workflow_schedules table
        $this->assertEquals(1, $row['count'], 'workflow_schedule not created');
    }
}
