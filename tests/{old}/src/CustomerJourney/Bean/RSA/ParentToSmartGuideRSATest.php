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
use Sugarcrm\Sugarcrm\CustomerJourney\Bean\RSA\ParentToSmartGuideRSA;

class ParentToSmartGuideRSATest extends TestCase
{
    private \SugarTestCJHelper $cjTestHelper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->cjTestHelper = new SugarTestCJHelper();
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @covers ::checkAndPerformParentRSA()
     */
    public function testCheckAndPerformParentRSA()
    {
        $executed = true;

        try {
            $api = new RestService();
            $api->user = $GLOBALS['current_user'];
            $parentBean = $this->cjTestHelper->createBean('Accounts', [
                'account_type' => 'Analyst',
            ]);
            $workflowTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
                'url' => 'https://www.google.com',
                'headers' => 'Accept-Language: en-US,en;q=0.5',
            ]);
            $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
                'dri_workflow_template_id' => 'testTemplate1',
            ]);
            $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
                'dri_workflow_template_id' => 'testTaskTemplate1',
                'name' => 'Test Task Template 1',
                'dri_subworkflow_template_id' => 'testSubWorkflowTemplate',
                'id' => 'testTaskTemplate1',
                'sort_order' => '1',
            ]);

            $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
                'dri_workflow_template_id' => 'testTaskTemplate2',
                'name' => 'Test Task Template 2',
                'dri_subworkflow_template_id' => 'testSubWorkflowTemplate',
                'id' => 'testTaskTemplate2',
                'sort_order' => '2',
            ]);

            $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
                'dri_workflow_template_id' => 'testTaskTemplate3',
                'name' => 'Test Task Template 3',
                'dri_subworkflow_template_id' => 'testSubWorkflowTemplate',
                'id' => 'testTaskTemplate3',
                'sort_order' => '3',
            ]);

            $testForm = $this->cjTestHelper->createBean('CJ_Forms', [
                'dri_workflow_template_id' => 'testTemplate1',
                'smart_guide_template_id' => 'testTemplate1',
                'module_trigger' => 'Accounts',
                'main_trigger_type' => 'sugar_action_to_smart_guide',
                'field_trigger' => '{
                    "filterId":{
                       "deleted":false,
                       "sync_key":null,
                       "is_escalated":false,
                       "currentFilterId":"assigned_to_me",
                       "filter_id":"assigned_to_me",
                       "filter_definition":[
                          {
                             "account_type":{
                                "$in":[
                                   "Analyst"
                                ]
                             }
                          }
                       ],
                       "filter_template":[
                          {
                             "account_type":{
                                "$in":[
                                   "Analyst"
                                ]
                             }
                          }
                       ]
                    },
                    "filterName":"",
                    "filterDef":[
                       {
                          "account_type":{
                             "$in":[
                                "Analyst"
                             ]
                          }
                       }
                    ],
                    "filterTpl":[
                       {
                          "account_type":{
                             "$in":[
                                "Analyst"
                             ]
                          }
                       }
                    ]
                 }',
                'target_action' => '[
                    {
                       "id":"Smart Guide",
                       "value":"",
                       "remove_button":false,
                       "index":0,
                       "action_id":"completed",
                       "action_value":"mark_all_completed"
                    }
                 ]',
            ]);

            $workflowTemplate->load_relationship("forms");
            $workflowTemplate->forms->add($testForm);
            $this->cjTestHelper->createBean('DRI_Workflows', [
                'dri_workflow_template_id' => 'testTemplate1',
                'account_id' => $parentBean->id,
            ]);

            $activities = ParentToSmartGuideRSA::checkAndPerformParentRSA($parentBean);
            $activities = array_values($activities);
            $this->assertNotEmpty($activities);
            $this->assertEquals($activities[0]['order'], '01.01');
            $this->assertEquals($activities[1]['order'], '01.02');
            $this->assertEquals($activities[2]['order'], '01.03');
        } catch (Throwable $e) {
            $executed = false;
            $this->tearDown();
        }

        $this->assertEquals($executed, true);
    }

    /**
     * @covers ::fetchAllActiveSmartGuides()
     */
    public function testFetchAllActiveSmartGuides()
    {
        $executed = true;
        try {
            $api = new RestService();
            $api->user = $GLOBALS['current_user'];
            $parentBean = $this->cjTestHelper->createBean('Accounts', [
                'account_type' => 'Analyst',
                'id' => 'testAccount1',
            ]);

            $workflowTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
                'id' => 'testTemplate2',
                'name' => 'Test Template 2',
                'url' => 'https://www.google.com',
                'headers' => 'Accept-Language: en-US,en;q=0.5',
            ]);
            $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
                'dri_workflow_template_id' => 'testTemplate2',
                'id' => 'testSubWorkflowTemplate2',
            ]);
            $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
                'dri_workflow_template_id' => 'testTemplate2',
                'name' => 'Test Task Template 1',
                'dri_subworkflow_template_id' => 'testSubWorkflowTemplate2',
                'id' => 'testTaskTemplate',
                'sort_order' => '1',
            ]);

            $this->cjTestHelper->createBean('DRI_Workflows', [
                'id' => 'workflow1',
                'dri_workflow_template_id' => 'testTemplate2',
                'account_id' => $parentBean->id,
            ]);

            $this->cjTestHelper->createBean('DRI_Workflows', [
                'id' => 'workflow2',
                'dri_workflow_template_id' => 'testTemplate2',
                'account_id' => $parentBean->id,
            ]);

            ParentToSmartGuideRSA::setParentData($parentBean);
            $smartGuides = ParentToSmartGuideRSA::fetchAllActiveSmartGuides($workflowTemplate->id);
            $this->assertNotEmpty($smartGuides);
        } catch (Throwable $e) {
            $executed = false;
            $this->tearDown();
        }
        $this->assertEquals($executed, true);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $this->cjTestHelper->tearDown();
    }
}
