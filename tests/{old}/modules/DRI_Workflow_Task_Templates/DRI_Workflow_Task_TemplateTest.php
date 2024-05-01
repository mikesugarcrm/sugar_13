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
 * Class DRI_Workflow_Task_TemplateTest
 * @coversDefaultClass \DRI_Workflow_Task_Template
 */
class DRI_Workflow_Task_TemplateTest extends TestCase
{
    /**
     * @var \SugarBean
     */
    private $DRI_Workflow_Task_Template;

    /**
     * @var \SugarTestCJHelper
     */
    private $cjTestHelper;

    /**
     * @var string|false
     */
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['log'] = LoggerManager::getLogger();
        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::init();

        //No need however to add the following
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->DRI_Workflow_Task_Template = new DRI_Workflow_Task_Template();
        $this->cjTestHelper = new SugarTestCJHelper();
    }

    /**
     * @covers ::getById
     */
    public function testGetById(): void
    {
        $mockUser = $this->cjTestHelper->createBean('Users');

        $this->cjTestHelper->setCurrentUser($mockUser);

        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
        ]);
        $key = DRI_Workflow_Task_Template::getById($dri_workflow_task_template->id);

        $this->assertEquals($key->available_modules, '^Accounts^,^Cases^,^Opportunities^');
    }

    /**
     * @covers ::getByNameAndParent
     */
    public function testGetByNameAndParent(): void
    {
        $mockUser = $this->cjTestHelper->createBean('Users');

        $this->cjTestHelper->setCurrentUser($mockUser);

        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
        ]);
        $key = DRI_Workflow_Task_Template::getByNameAndParent($dri_workflow_task_template->name, $subWorkflowTemplate->id, null);

        $this->assertEquals($key->available_modules, '^Accounts^,^Cases^,^Opportunities^');
        $this->assertEquals($key->name, 'Test Workflow Task Template');
    }

    /**
     * @covers getPreviousStageTemplate
     */
    public function testGetPreviousStageTemplate(): void
    {

        $this->DRI_Workflow_Task_Template->fetched_row['dri_subworkflow_template_id'] = '01a62f80-f836-11e6-b51e-5254009e5526';

        $key = $this->DRI_Workflow_Task_Template->getPreviousStageTemplate();

        $this->assertEquals($key->name, 'Q3');
        $this->assertEquals($key->sort_order, 3);
    }

    /**
     * @covers getAssigneeRule
     */
    public function testGetAssigneeRule(): void
    {
        $this->cjTestHelper->createBean('Accounts', [
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
        ]);
        $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
        ]);
        $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => 'testTemplate',
        ]);
        $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' =>
                'testSubWorkflowTemplate',
        ]);
        $Workflow = $this->cjTestHelper->createBean('DRI_Workflows', [
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
        ]);
        $Workflow->load_relationship('dri_subworkflows');
        $dri_subworkflows = $Workflow->dri_subworkflows->getBeans();

        foreach ($dri_subworkflows as $result) {
            $key = $this->DRI_Workflow_Task_Template->getAssigneeRule($result);
            $this->assertEquals($key, 'stage_start');
            $this->cjTestHelper->addBeanToDeleteAssets(BeanFactory::retrieveBean('DRI_SubWorkflows', $result->id));
        }
    }

    /**
     * @covers getForms
     */
    public function testGetForms(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
        ]);
        $testWebhook = $this->cjTestHelper->createBean('CJ_Forms');
        $dri_workflow_task_template->load_relationship('forms');
        $dri_workflow_task_template->forms->getBeans($testWebhook->id);
        $key = $this->DRI_Workflow_Task_Template->getForms();

        $this->assertEquals($key, []);
    }

    /**
     * @covers isParent
     */
    public function testIsParent(): void
    {
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['getChildren']
        );
        $this->DRI_Workflow_Task_Template->method('getChildren')->willReturn(['1', '2']);
        $key = $this->DRI_Workflow_Task_Template->isParent();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers getChildren
     */
    public function testGetChildren(): void
    {
        $key = $this->DRI_Workflow_Task_Template->getChildren();

        $this->assertEquals($key, []);
    }

    /**
     * @covers getParent
     */
    public function testGetParent(): void
    {
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['hasParent']
        );
        $this->DRI_Workflow_Task_Template->method('hasParent')->willReturn(true);
        $key = $this->DRI_Workflow_Task_Template->getParent();

        $this->assertEquals($key->type, 'customer_task');
    }

    /**
     * @covers hasParent
     */
    public function testHasParent(): void
    {
        $this->DRI_Workflow_Task_Template->parent_id = '1231231231231';
        $key = $this->DRI_Workflow_Task_Template->hasParent();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers hasStageTemplate
     */
    public function testHasStageTemplate(): void
    {
        $this->DRI_Workflow_Task_Template->dri_subworkflow_template_id = '1231231231231';
        $key = $this->DRI_Workflow_Task_Template->hasStageTemplate();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers getStageTemplate
     */
    public function testGetStageTemplate(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['hasStageTemplate']
        );
        $this->DRI_Workflow_Task_Template->method('hasStageTemplate')->willReturn(true);
        $this->DRI_Workflow_Task_Template->dri_subworkflow_template_id = $subWorkflowTemplate->id;
        $key = $this->DRI_Workflow_Task_Template->getStageTemplate();

        $this->assertEquals($key, $subWorkflowTemplate);
    }

    /**
     * @covers hasJourneyTemplate
     */
    public function testHasJourneyTemplate(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $this->DRI_Workflow_Task_Template->dri_subworkflow_template_id = $subWorkflowTemplate->id;
        $key = $this->DRI_Workflow_Task_Template->hasJourneyTemplate();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers getJourneyTemplate
     */
    public function testGetJourneyTemplate(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['hasJourneyTemplate']
        );
        $this->DRI_Workflow_Task_Template->method('hasJourneyTemplate')->willReturn(true);
        $this->DRI_Workflow_Task_Template->dri_workflow_template_id = $testTemplate->id;
        $key = $this->DRI_Workflow_Task_Template->getJourneyTemplate();

        $this->assertEquals($key, $testTemplate);
    }

    /**
     * @covers getBlockedBy
     */
    public function testGetBlockedBy(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
        ]);
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['getBlockedByIds']
        );
        $this->DRI_Workflow_Task_Template->blocked_by = '123312';
        $this->DRI_Workflow_Task_Template->method('getBlockedByIds')->willReturn([$dri_workflow_task_template->id]);
        $key = $this->DRI_Workflow_Task_Template->getBlockedBy();

        $this->assertEquals($key, [$dri_workflow_task_template]);
    }

    /**
     * @covers getBlockedByIds
     */
    public function testGetBlockedByIds(): void
    {
        $this->DRI_Workflow_Task_Template->blocked_by = '123312';
        $key = $this->DRI_Workflow_Task_Template->getBlockedByIds();

        $this->assertEquals($key, '123312');
    }

    /**
     * @covers getBlockedByStageIds
     */
    public function testGetBlockedByStageIds(): void
    {
        $this->DRI_Workflow_Task_Template->blocked_by_stages = '123312';
        $key = $this->DRI_Workflow_Task_Template->getBlockedByStageIds();

        $this->assertEquals($key, '123312');
    }

    /**
     * @covers isBlocked
     */
    public function testIsBlocked(): void
    {
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['getBlockedByIds']
        );
        $this->DRI_Workflow_Task_Template->method('getBlockedByIds')->willReturn(['1', '2']);
        $key = $this->DRI_Workflow_Task_Template->isBlocked();

        $this->assertEquals($key, 1);
    }


    /**
     * @covers isDuplicateActivityByOrder
     */
    public function testIsDuplicateActivityByOrder(): void
    {
        $this->DRI_Workflow_Task_Template->sort_order = '1';
        $this->DRI_Workflow_Task_Template->dri_subworkflow_template_id = '0369bfea-6f64-11e6-b835-5254009e5526';
        $key = $this->DRI_Workflow_Task_Template->isDuplicateActivityByOrder();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers getChildOrder
     */
    public function testGetChildOrder(): void
    {
        $this->DRI_Workflow_Task_Template->sort_order = '5';
        $key = $this->DRI_Workflow_Task_Template->getChildOrder();

        $this->assertEquals($key, 5);
    }

    /**
     * @covers hasBlockedBy
     */
    public function testHasBlockedBy(): void
    {
        $this->DRI_Workflow_Task_Template = $this->createPartialMock(
            \DRI_Workflow_Task_Template::class,
            ['getBlockedByIds']
        );
        $this->DRI_Workflow_Task_Template->method('getBlockedByIds')->willReturn(['1', '2']);
        $key = $this->DRI_Workflow_Task_Template->hasBlockedBy();

        $this->assertEquals($key, 1);
    }

    /**
     * @covers save
     */
    public function testSave(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => 'testSubWorkflowTemplate',
        ]);
        $response = $dri_workflow_task_template->save(true);

        $this->assertEquals($response, 'testTaskTemplate');
    }

    /**
     * @covers mark_deleted
     */
    public function testMark_deleted(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'id' => 'parent_task',
            'name' => 'parent_task',
            'module_name' => 'Tasks',
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
            'dri_workflow_template_id' => $testTemplate->id,
            'is_parent' => 1,
        ]);
        $this->assertEquals($dri_workflow_task_template->deleted, 0);
        $this->DRI_Workflow_Task_Template->id = $dri_workflow_task_template->id;

        $dri_workflow_task_template->mark_deleted($dri_workflow_task_template->id);
        $this->assertEquals($dri_workflow_task_template->deleted, 1);
    }

    /**
     * @covers deleteForms
     */
    public function testDeleteForms(): void
    {
        $testTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $testTemplate->id,
        ]);
        $dri_workflow_task_template = $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' =>
                'testSubWorkflowTemplate',
        ]);

        if ($dri_workflow_task_template->load_relationship('forms')) {
            $dri_workflow_task_template->forms->getBeans();
        }
        $this->assertEquals($dri_workflow_task_template->deleted, 0);
        $this->DRI_Workflow_Task_Template->deleteForms();
        // related forms should have deleted flag true
        foreach ($dri_workflow_task_template->forms->getBeans() as $Forms) {
            $this->assertEquals($dri_workflow_task_template->deleted, 1);
        }
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
