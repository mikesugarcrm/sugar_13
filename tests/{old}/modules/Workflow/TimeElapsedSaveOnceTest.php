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
 * Class TimeElapsedWorkflowTest
 */
class TimeElapsedSaveOnceTest extends TestCase
{
    private $beanId;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        $this->beanId = create_guid();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM workflow_schedules WHERE bean_id = '{$this->beanId}'");
        SugarTestHelper::tearDown();
    }

    public function testUniqueSaveOnce()
    {
        // Initialize the mock
        $bean = $this->getMockBuilder('Account')->setMethods(['save'])->getMock();
        // We expect save() to run on this bean only once
        $bean->expects($this->once())->method('save');
        $bean->id = $this->beanId;
        $bean->fetched_row = [
            'deleted' => 0,
        ];
        // Need to register the mock, to be reused in process_scheduled
        BeanFactory::registerBean($bean);

        // Create 2 workflow_schedules for different workflows
        $temp = new WorkFlowSchedule();
        $temp->bean_id = $bean->id;
        $temp->workflow_id = 'TimeElapsedSaveOnceTest_1';
        $temp->target_module = $bean->module_dir;
        $temp->date_expired = '2010-01-01 00:00:00';
        $temp->save();

        $temp = new WorkFlowSchedule();
        $temp->bean_id = $bean->id;
        $temp->workflow_id = 'TimeElapsedSaveOnceTest_2';
        $temp->target_module = $bean->module_dir;
        $temp->date_expired = '2011-01-01 00:00:00';
        $temp->save();

        // Process schedules
        $workflowSchedule = new WorkFlowSchedule();
        $workflowSchedule->process_scheduled();
    }
}
