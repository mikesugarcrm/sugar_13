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

require_once 'include/workflow/action_utils.php';

class WorkFlowProcessActionsTest extends TestCase
{
    private $quote;
    private $wfArray;
    private $workflowId;

    protected function setUp(): void
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $team = SugarTestTeamUtilities::createAnonymousTeam();

        $GLOBALS['current_user'] = $user;

        // Create a workflow firing on New Quotes
        $workflow = new WorkFlow();
        $workflow->name = 'WF1261';
        $workflow->base_module = 'Quotes';
        $workflow->status = 1;
        $workflow->type = 'Normal';
        $workflow->fire_order = 'alerts_actions';
        $workflow->parent_id = null;
        $workflow->record_type = 'All';
        $workflow->save();
        $workflow->check_logic_hook_file();
        $workflow->write_workflow();
        $this->workflowId = $workflow->id;

        $this->quote = SugarTestQuoteUtilities::createQuote();

        $account = SugarTestAccountUtilities::createAccount();
        $this->quote->account_id = $account->id;
        $this->quote->shipping_account_id = $account->id;
        $this->quote->billing_account_id = $account->id;

        $this->quote->assigned_user_id = $user->id;
        $this->quote->save();

        $this->quote->load_relationship('teams');
        $this->quote->teams->setSaved(false);
        $this->quote->teams->add([$team->id, $user->team_id]);

        $this->wfArray = [
            'action_type' => 'new',
            'action_module' => 'Tasks',
            'rel_module' => '',
            'rel_module_type' => 'all',
            'basic' => [
                'name' => 'Created from workflow',
                'status' => 'Not Started',
                'priority' => 'Medium',
            ],
            'basic_ext' => [],
            'advanced' => [
                'team_id' => [
                    'value' => 'team_set_id',
                    'ext1' => '',
                    'ext2' => '',
                    'ext3' => '',
                    'adv_type' => 'exist_team',
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        rmdir_recursive('custom/modules/Quotes/workflow');
        rmdir_recursive('custom/modules/Quotes/logic_hooks.php');
        $GLOBALS['db']->query("DELETE FROM workflow WHERE id = '$this->workflowId'");
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE workflow_id = '$this->workflowId'");

        SugarTestQuoteUtilities::removeAllCreatedQuotes();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestTaskUtilities::removeAllCreatedTasks();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    /**
     * Testing workflow for correct teams in Quotes for new module
     * @group 1261
     */
    public function testWorkflowsForQuotesModule()
    {
        $quote_teams = $this->quote->teams->get();
        process_workflow_actions($this->quote, $this->wfArray);

        $this->quote->load_relationship('tasks');
        $quote_task_id = $this->quote->tasks->get();

        $task = SugarTestTaskUtilities::createTask();
        $task->retrieve(array_shift($quote_task_id));
        $task->load_relationship('teams');
        $task_teams = $task->teams->get();

        $this->assertCount(0, array_diff($quote_teams, $task_teams));
    }
}
