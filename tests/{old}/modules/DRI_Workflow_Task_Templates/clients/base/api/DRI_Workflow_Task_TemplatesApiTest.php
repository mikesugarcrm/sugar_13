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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DRI_Workflow_Task_TemplatesApiTest
 * @coversDefaultClass \DRI_Workflow_Task_TemplatesApi
 */
class DRI_Workflow_Task_TemplatesApiTest extends TestCase
{
    /**
     * @var \ServiceBase|MockObject
     */
    private $apiService;

    /**
     * @var \AdministrationApi|MockObject
     */
    private $api;

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
        $this->cjTestHelper = new SugarTestCJHelper();
        $this->apiService = $this->createMock(\ServiceBase::class);
        $this->api = $this->createPartialMock(
            \DRI_Workflow_Task_TemplatesApi::class,
            ['requireArgs']
        );
    }

    /**
     * @covers ::getTemplateAvailableModules
     */
    public function testGetTemplateAvailableModules(): void
    {
        $args = [
            'module' => 'DRI_Workflow_Task_Templates',
            'template_id' => '08a24b0a-70a0-11ed-9894-7e1727e7747e',
        ];
        $workflowTemplate = $this->cjTestHelper->createBean('DRI_Workflow_Templates');
        $subWorkflowTemplate = $this->cjTestHelper->createBean('DRI_SubWorkflow_Templates', [
            'dri_workflow_template_id' => $workflowTemplate->id,
        ]);
        $this->cjTestHelper->createBean('DRI_Workflow_Task_Templates', [
            'dri_subworkflow_template_id' => $subWorkflowTemplate->id,
        ]);
        $response = $this->api->getTemplateAvailableModules($this->apiService, $args);

        $this->assertEquals($response, []);
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
