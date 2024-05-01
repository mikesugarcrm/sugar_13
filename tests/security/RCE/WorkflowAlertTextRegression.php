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
declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use Regression\SugarCRMScenario;

class WorkflowAlertTextRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-8668] Possible code injection in Workflows';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $scenario = $this->login('admin', 'asdf')
            ->bwcLogin()
            ->submitForm(
                'index.php',
                [
                    'module' => 'WorkFlow',
                    'record' => '',
                    'action' => 'Save',
                    'return_module' => 'WorkFlow',
                    'return_id' => '',
                    'return_action' => 'index',
                    'old_record_id' => '',
                    'is_duplicate' => '',
                    'button' => 'Save',
                    'name' => 'Malicious',
                    'type' => 'Normal',
                    'status' => 'Active',
                    'base_module' => 'Accounts',
                    'record_type' => 'All',
                    'fire_order' => 'actions_alerts',
                    'description' => '',
                ],
                'index.php?module=WorkFlow&action=EditView&return_module=WorkFlow&return_action=DetailView&bwcFrame=1'
            )
            ->expectStatusCode(200)
            ->expectSubstring('<form action="index.php" method="post" name="DetailView" id="form"')
            ->extractRegexp('workflowId', '~name="record" value="(.*?)"~is');

        $workflowId = $scenario->getVar('workflowId');
        $scenario->submitForm(
            'index.php',
            [
                'module' => 'WorkFlowTriggerShells',
                'record' => '',
                'workflow_id' => $workflowId,
                'parent_id' => $workflowId,
                'action' => 'Save',
                'return_module' => '',
                'return_id' => '',
                'return_action' => '',
                'sugar_body_only' => 'true',
                'plugin_action' => '',
                'plugin_module' => '',
                'frame_type' => 'Primary',
                'rel_module' => '',
                'prev_display_text' => '',
                'field' => '',
                'base_module' => 'Accounts',
                'meta_filter_name' => 'normal_trigger',
                'type' => 'trigger_record_change',
                'default_href_compare_specific_1' => 'field',
                'default_href_compare_change_1' => 'field',
                'default_href_filter_rel_field_1' => 'module',
                'save' => 'Save',
            ]
        );

        $scenario->submitForm(
            'index.php',
            [
                'module' => 'WorkFlowAlertShells',
                'module_tab' => 'WorkFlow',
                'record' => '',
                'parent_id' => $workflowId,
                'workflow_id' => $workflowId,
                'action' => 'Save',
                'return_module' => 'WorkFlow',
                'return_id' => $workflowId,
                'return_action' => 'DetailView',
                'button' => '  Save  ',
                'name' => 'hacked',
                'alert_type' => 'Email',
                'source_type' => 'Normal Message',
                'custom_template_id' => '',
                'alert_text' => 'hacked";file_put_contents(\'cache/hacked_' . $workflowId . '.txt\', \'pwnd\');"',
            ]
        );

        $scenario->apiCall(
            '/Accounts',
            'POST',
            [
                'deleted' => false,
                'is_escalated' => false,
                'assigned_user_id' => '1',
                'team_name' => [
                    [
                        'id' => '1',
                        'display_name' => 'Global',
                        'name' => 'Global',
                        'name_2' => '',
                        'primary' => true,
                        'selected' => false,
                    ],
                ],
                'industry' => '',
                'account_type' => '',
                'service_level' => '',
                'name' => 'demo acc',
            ]
        );

        $createdFileRequest = new Request(
            'GET',
            'cache/hacked_' . $workflowId . '.txt'
        );
        $scenario->send($createdFileRequest)
            ->expectStatusCode(404);
    }
}
