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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * Test Class for PMSECasesListAPI methods.
 */
class PMSECasesListApiTest extends TestCase
{
    private $api;
    private $args;
    private $flow;
    private $inbox;
    private $project;
    private $process;
    private $user;

    /**
     * Create the minimum amount of data in the DB needed to get 1 record to return
     * from our API call.
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, 1]);
        $this->api = SugarTestRestUtilities::getRestServiceMock();
        $this->api->user = $GLOBALS['current_user']->getSystemUser();
        $this->args = ['order_by' => 'cas_id:asc',];
        $this->user = $GLOBALS['current_user'];

        $projectFields = ['prj_module' => 'Accounts', 'assigned_user_id' => $this->user->id];
        $this->project = SugarTestBpmUtilities::createBpmObject('Project', '', $projectFields);

        $processFields = ['prj_id' => $this->project->id];
        $this->process = SugarTestBpmUtilities::createBpmObject('BpmnProcess', '', $processFields);

        $inboxFields = [
            'pro_id' => $this->process->id,
            'assigned_user_id' => $this->user->id,
            'cas_module' => 'Accounts',
        ];
        $this->inbox = SugarTestBpmUtilities::createBpmObject('Inbox', '', $inboxFields);

        $flowFields = [
            'cas_id' => $this->inbox->cas_id,
            'cas_index' => 1,
            'pro_id' => $this->process->id,
        ];
        $this->flow = SugarTestBpmUtilities::createBpmObject('BpmFlow', '', $flowFields);
    }

    /**
     * Clean database of newly created records
     */
    protected function tearDown(): void
    {
        SugarTestBpmUtilities::removeAllCreatedBpmObjects();
    }

    /**
     * Assert that the API method returns the fields expected.
     */
    public function testSelectCasesListApiReturnsExpectedFields()
    {
        $casesListApi = $this->getMockBuilder('PMSECasesListApi')
            ->setMethods(null)
            ->getMock();
        $data = $casesListApi->selectCasesList($this->api, $this->args);

        $this->assertArrayHasKey('records', $data);
        $this->assertCount(1, $data['records']);

        $record = $data['records'][0];
        $expFields = [
            'id', 'name', 'date_entered', 'date_modified', 'modified_user_id', 'created_by', 'deleted', 'cas_id',
            'cas_parent', 'cas_status', 'pro_id', 'cas_title', 'cas_custom_status', 'cas_init_user', 'cas_create_date',
            'cas_update_date', 'cas_finish_date', 'cas_pin', 'cas_assigned_status', 'cas_module', 'team_id',
            'team_set_id', 'assigned_user_id', 'assigned_user_name', 'pro_title', 'prj_created_by', 'prj_module',
            'prj_run_order', 'cas_sugar_module', 'cas_sugar_object_id', 'prj_deleted',
        ];

        foreach ($expFields as $field) {
            $this->assertArrayHasKey($field, $record);
        }
    }
}
