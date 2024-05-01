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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DRI_WorkflowTest
 * @coversDefaultClass \DRI_Workflow
 */
class DRI_WorkflowTest extends TestCase
{
    /**
     * @var \Administrationcontroller|MockObject
     */
    private $controller;

    /**
     * @var \SugarTestCJHelper
     */
    private $cjTestHelper;

    /**
     * @var string
     */
    private $journeyId = 'testJourney';

    /**
     * @var string|false
     */
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['log'] = LoggerManager::getLogger();
        $GLOBALS['current_language'] = 'en_us';
        SugarTestHelper::init();

        //No need however to add the following
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
        $this->cjTestHelper = new \SugarTestCJHelper();
        $this->controller = new DRI_Workflow();
    }

    private function createJourney(): SugarBean
    {
        $mockUser = $this->cjTestHelper->createBean('Users');

        $this->cjTestHelper->setCurrentUser($mockUser);

        $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => 'testTemplate',
            'sort_order' => '1',
        ]);

        $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => 'testSubWorkflowTemplate',
        ]);

        $parentBean = $this->cjTestHelper->createBean('Accounts', [
            'account_type' => 'Analyst',
            'id' => 'testAccount',
            'name' => 'Test Account',
        ]);

        $this->cjTestHelper->createBean('DRI_Workflow_Templates', [
            'id' => 'testTemplate',
            'name' => 'Test Template',
            'url' => 'https://www.google.com',
            'headers' => 'Accept-Language: en-US,en;q=0.5',
        ]);

        $journey = $this->cjTestHelper->createBean('DRI_Workflows', [
            'id' => $this->journeyId,
            'dri_workflow_template_id' => 'testTemplate',
            'account_id' => $parentBean->id,
        ]);

        return $journey;
    }

    /**
     * @covers ::listEnabledModulesEnumOptions
     */
    public function testListEnabledModulesEnumOptions(): void
    {
        $response = DRI_Workflow:: listEnabledModulesEnumOptions();
        $arr = [
            'Contacts' => 'Contacts',
            'Accounts' => 'Accounts',
            'Opportunities' => 'Opportunities',
            'Cases' => 'Cases',
            'Notes' => 'Notes',
            'Calls' => 'Calls',
            'Emails' =>'Emails',
            'Meetings' => 'Meetings',
            'Tasks' => 'Tasks',
            'Calendar' => 'Calendar',
            'Leads' => 'Leads',
            'Contracts' => 'Contracts',
            'Quotes' => 'Quotes',
            'Products' => 'Quoted Line Items',
            'ProductTemplates' => 'Product Catalog',
            'Bugs' => 'Bugs',
            'Documents' => 'Documents',
            'HintAccountsets' => 'HintAccountsets',
            'HintNotificationTargets' => 'HintNotificationTargets',
            'HintNewsNotifications' => 'HintNewsNotifications',
            'HintEnrichFieldConfigs' => 'HintEnrichFieldConfigs',
            'ExternalUsers' => 'External Users',
            'Shifts' => 'Shifts',
            'ShiftExceptions' => 'Shift Exceptions',
            'Purchases' => 'Purchases',
            'PurchasedLineItems' => 'Purchased Line Items',
            'PushNotifications' => 'PushNotifications',
            'Escalations' => 'Escalations',
            'DocumentTemplates' => 'Document Templates',
            'DataPrivacy' => 'Data Privacy',
            'Messages' => 'Messages',
            'RevenueLineItems' => 'Revenue Line Items',
            'DocuSignEnvelopes' => 'DocuSign Envelopes',
            'KBContents' => 'Knowledge Base',
        ];
        $this->assertEquals($response, $arr);
    }

    /**
     * @covers ::getById
     */
    public function testGetById() : void
    {
        $journey = $this->createJourney();
        $response = DRI_Workflow::getById($this->journeyId);

        $this->assertEquals($response->id, $journey->id);
        $this->assertEquals($response->name, $journey->name);
    }

    /**
     * @covers ::getByName
     */
    public function testGetByName() : void
    {
        $journey = $this->createJourney();
        $response = DRI_Workflow::getByName($journey->name);

        $this->assertEquals($response->name, $journey->name);
    }

    /**
     * @covers changedToInProgress
     */
    public function testChangedToInProgress() : void
    {
        $this->controller = $this->createPartialMock(
            \DRI_Workflow::class,
            ['isFieldChanged']
        );
        $this->controller->method('isFieldChanged')->willReturn(true);
        $this->controller->state = 'in_progress';

        $response = $this->controller->changedToInProgress();
        $this->assertEquals($response, 1);
    }

    /**
     * @covers changedToCompleted
     */
    public function testChangedToCompleted() : void
    {
        $this->controller = $this->createPartialMock(
            \DRI_Workflow::class,
            ['isFieldChanged']
        );
        $this->controller->method('isFieldChanged')->willReturn(true);
        $this->controller->state = 'completed';

        $response = $this->controller->changedToCompleted();
        $this->assertEquals($response, 1);
    }

    /**
     * @covers archive
     */
    public function testArchive() : void
    {
        $journey = $this->createJourney();
        $journey->state = 'completed';
        $this->assertEquals($journey->archived, false);

        $journey->archive();
        $this->assertEquals($journey->archived, true);
    }

    /**
     * @covers setCurrentStageAndActivity
     */
    public function testSetCurrentStageAndActivity() : void
    {
        $journey = $this->createJourney();
        $journey->setCurrentStageAndActivity();

        $stageId = '';
        $activityId = '';

        foreach ($journey->getStages() as $stage) {
            $stageId = $stage->id;
            foreach ($stage->getActivities() as $activity) {
                $activityId = $activity->id;
            }
        }

        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('DRI_Workflows'));
        $query->select('id');
        $query->where()->equals('id', $journey->id);
        $query->where()->equals('parent_id', $activityId);
        $query->where()->equals('current_stage_id', $stageId);
        $result = $query->execute();

        $this->assertCount(1, $result);
    }

    /**
     * @covers reloadStages
     */
    public function testReloadStages(): void
    {
        $journey = $this->createJourney();
        $journey->reloadStages();

        $stages = $journey->getStages();
        $this->assertCount(1, $stages);
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
