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

class PMSEUserAssignmentHandlerTest extends TestCase
{
    protected $originals = [];

    protected function setUp(): void
    {
        $this->originals['current_user'] = $GLOBALS['current_user'];
        $this->originals['db'] = $GLOBALS['db'];
    }

    protected function tearDown(): void
    {
        foreach ($this->originals as $varname => $value) {
            $GLOBALS[$varname] = $value;
        }
    }

    public function testTaskAssignmentUndefinedMethod()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => '',
                    'act_assignment_method' => '',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => '',
                    'act_assignment_method' => '',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentStaticOwner()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'owner',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'owner',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentStaticSupervisor()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'supervisor',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'supervisor',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentStaticCurrentUser()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'currentuser',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'currentuser',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentStaticUser()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'administrator',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'administrator',
                    'act_assignment_method' => 'static',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentSelfService()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'selfservice',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'selfservice',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentBalanced()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId', 'getNextUserUsingRoundRobin'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'balanced',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => '',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'balanced',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentScriptTask()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId', 'getNextUserUsingRoundRobin'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => 'SCRIPTTASK',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'balanced',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [
                (object)[
                    'act_task_type' => 'SCRIPTTASK',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'balanced',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testTaskAssignmentNoActivities()
    {
        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getRecordOwnerId', 'getSupervisorId', 'getNextUserUsingRoundRobin'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['error', 'info'])
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $actList = [
            'list' => [
                (object)[
                    'act_task_type' => 'SCRIPTTASK',
                    'act_type' => '',
                    'act_assign_user' => 'team01',
                    'act_assignment_method' => 'balanced',
                    'act_assign_team' => '',
                ],
            ],
        ];

        $activityMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($actList));

        $defList = [
            'list' => [],
        ];

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['get_list'])
            ->getMock();

        $activityDefinitionMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($defList));

        $flowData = [
            'bpmn_id' => 'act01',
            'cas_user_id' => 'user01',
            'cas_sugar_object_id' => 'lead01',
            'cas_sugar_module' => 'Leads',
            'cas_id' => 1,
            'cas_index' => 1,
        ];

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->taskAssignment($flowData);
    }

    public function testAdhocReassignRoundTrip()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'someID';
        $current_user->full_name = 'Some Name';

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 1,
            'cas_adhoc_type' => 'ROUND_TRIP',
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'abc012',
            'full_name' => 'Some Name',
            'user_name' => 'someName',
            'taskName' => 'Task 01',
        ];

        $userId = 'user01';

        $isRoundTripReassign = true;

        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'reassignCaseToUser'])
            ->getMock();

        $flowMock = $this->getMockBuilder('PMSEBpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->cas_delegate_date = new DateTime();

        $wrapperMock = $this->getMockBuilder('PMSEWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getSelectRows'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['activity'])
            ->getMock();

        $caseBean = new stdClass();

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($caseBean));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentMock->expects($this->at(2))
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentMock->setWrapper($wrapperMock);
        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->adhocReassign($caseData, $userId, $isRoundTripReassign);
    }

    public function testAdhocReassignOneWay()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'someID';
        $current_user->full_name = 'Some Name';

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 1,
            'cas_adhoc_type' => 'ROUND_TRIP',
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'abc012',
            'full_name' => 'Some Name',
            'user_name' => 'someName',
            'taskName' => 'Task 01',
        ];

        $userId = 'user01';

        $isRoundTripReassign = false;

        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'reassignCaseToUser'])
            ->getMock();

        $flowMock = $this->getMockBuilder('PMSEBpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->cas_delegate_date = new DateTime();

        $wrapperMock = $this->getMockBuilder('PMSEWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getSelectRows'])
            ->getMock();

        $caseBean = new stdClass();

        $loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['activity'])
            ->getMock();

        $userAssignmentMock->expects($this->at(0))
            ->method('retrieveBean')
            ->will($this->returnValue($caseBean));

        $userAssignmentMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentMock->expects($this->at(2))
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentMock->setWrapper($wrapperMock);
        $userAssignmentMock->setLogger($loggerMock);

        $userAssignmentMock->adhocReassign($caseData, $userId, $isRoundTripReassign);
    }

    public function testOriginReassign()
    {
        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_adhoc_type' => 'ROUND_TRIP',
        ];

        $userId = 'user01';

        $userAssignmentMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'reassignCaseToUser'])
            ->getMock();

        $flowMock = $this->getMockBuilder('PMSEBpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['get_list', 'retrieve_by_string_fields', 'save'])
            ->getMock();


        $flow = [
            'list' => [
                (object)[
                    'cas_task_start_date' => '2012-01-01',
                    'cas_delegate_date' => '2012-01-01',
                ],
            ],
        ];

        $flowMock->expects($this->once())
            ->method('get_list')
            ->will($this->returnValue($flow));

        $flowMock->cas_delegate_date = new DateTime();

        $wrapperMock = $this->getMockBuilder('PMSEWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getSelectRows'])
            ->getMock();

        $maxIndex = [
            'rowList' => [
                [
                    'max_index' => 2,
                ],
            ],
        ];

        $wrapperMock->expects($this->once())
            ->method('getSelectRows')
            ->will($this->returnValue($maxIndex));

        $userAssignmentMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentMock->setWrapper($wrapperMock);


        $userAssignmentMock->originReassign($caseData, $userId);
    }

    public function testRoundTripReassign()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'adhocReassign'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'get_full_list', 'save'])
            ->getMock();

        $beanArray = [
            (object)['cas_user_id' => 'user01'],
        ];

        $flowMock->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue($beanArray));

        $flowMock->bpmn_id = 'act01';
        $flowMock->bpmn_type = 'BpmnActivity';
        $flowMock->cas_reassign_level = 2;
        $flowMock->cas_delegate_date = '2012-01-02';

        $userAssignmentHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_thread' => 1,
            'cas_adhoc_type' => 'ROUND_TRIP',
        ];

        $userAssignmentHandlerMock->roundTripReassign($caseData);
    }

    public function testIsRoundTripTrue()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $result = $userAssignmentHandlerMock->isRoundTrip($caseData);
        $this->assertEquals(true, $result);
    }

    public function testIsRoundTripFalse()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ONE_WAY';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $result = $userAssignmentHandlerMock->isRoundTrip($caseData);
        $this->assertEquals(false, $result);
    }

    public function testOneWayReassign()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'originReassign'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'get_full_list', 'save'])
            ->getMock();

        $beanArray = [
            (object)['cas_user_id' => 'user01'],
        ];

        $flowMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue($beanArray));

        $flowMock->bpmn_id = 'act01';
        $flowMock->bpmn_type = 'BpmnActivity';
        $flowMock->cas_reassign_level = 2;
        $flowMock->cas_delegate_date = '2012-01-02';

        $wrapperMock = $this->getMockBuilder('PMSEWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getSelectRows'])
            ->getMock();

        $userAssignmentHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $userAssignmentHandlerMock->setWrapper($wrapperMock);

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_thread' => 1,
            'cas_adhoc_type' => 'ONE_WAY',
        ];

        $userAssignmentHandlerMock->oneWayReassign($caseData);
    }

    public function testIsOneWayTrue()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ONE_WAY';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $result = $userAssignmentHandlerMock->isOneWay($caseData);
        $this->assertEquals(true, $result);
    }

    public function testIsOneWayFalse()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $result = $userAssignmentHandlerMock->isOneWay($caseData);
        $this->assertEquals(false, $result);
    }

    public function testReassignCaseToUserSuccess()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $userId = 'user01';

        $result = $userAssignmentHandlerMock->reassignCaseToUser($caseData, $userId);
        $this->assertEquals(true, $result);
    }

    public function testReassignCaseToUserFailure()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $userId = 'user01';

        $result = $userAssignmentHandlerMock->reassignCaseToUser($caseData, $userId);
        $this->assertEquals(false, $result);
    }


    public function testReassignRecordToUserTrue()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';
        $flowMock->cas_sugar_module = 'Leads';
        $flowMock->cas_sugar_object_id = 'lead01';

        $userAssignmentHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $userId = 'user01';

        $result = $userAssignmentHandlerMock->reassignRecordToUser($caseData, $userId);
        $this->assertEquals(true, $result);
    }

    public function testReassignRecordToUserFalse()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $flowMock->expects($this->once())
            ->method('save')
            ->will($this->returnValue(false));

        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_adhoc_type = 'ROUND_TRIP';
        $flowMock->cas_sugar_module = 'Leads';
        $flowMock->cas_sugar_object_id = 'lead01';

        $userAssignmentHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $userId = 'user01';

        $result = $userAssignmentHandlerMock->reassignRecordToUser($caseData, $userId);
        $this->assertEquals(false, $result);
    }

    public function testGetReassignedUserList()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save', 'get_full_list'])
            ->getMock();

        $flowList = [
            (object)[
                'cas_user_id' => 'user01',
            ],
            (object)[
                'cas_user_id' => 'user02',
            ],
            (object)[
                'cas_user_id' => 'user03',
            ],
        ];

        $flowMock->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue($flowList));

        $userAssignmentHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($flowMock));

        $caseId = 1;
        $bpmnId = 'act01';
        $bpmnType = 'BpmnActivity';
        $casReassignLevel = 0;

        $list = $userAssignmentHandlerMock->getReassignedUserList($caseId, $bpmnId, $bpmnType, $casReassignLevel);
        $this->assertCount(3, $list);
    }

    public function testGetAssignableUserList()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getReassignedUserList'])
            ->getMock();

        $userAssignmentHandlerMock->expects($this->once())
            ->method('getReassignedUserList')
            ->will($this->returnValue(['user01']));

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save', 'get_full_list'])
            ->getMock();

        $flowMock->cas_id = 1;
        $flowMock->cas_index = 2;
        $flowMock->bpmn_id = 'act01';
        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_reassign_level = 2;

        /*$userAssignmentHandlerMock->expects($this->at(0))
                ->method('retrieveBean')
                ->will($this->returnValue($flowMock));*/

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $activityMock->act_adhoc_team = 'teamAdhoc';
        $activityMock->act_reassign_team = 'teamReassign';

        $userAssignmentHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $membershipMock = $this->getMockBuilder('TeamMembership')
            ->disableOriginalConstructor()
            ->setMethods(['get_full_list'])
            ->getMock();

        $membersMock = [
            (object)['user_id' => 'user01'],
            (object)['user_id' => 'user02'],
            (object)['user_id' => 'user03'],
        ];

        $membershipMock->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue($membersMock));

        $userAssignmentHandlerMock->expects($this->at(3))
            ->method('retrieveBean')
            ->will($this->returnValue($membershipMock));

        $userMock = new stdClass();

        $userAssignmentHandlerMock->expects($this->at(4, 5, 6))
            ->method('retrieveBean')
            ->will($this->returnValue($userMock));

        $caseId = 1;
        $caseIndex = 2;
        $fullList = false;
        $type = 'ADHOC';

        $expectedList = ['user02', 'user03'];

        $list = $userAssignmentHandlerMock->getAssignableUserList($flowMock, $fullList, $type);
        $this->assertCount(2, $list);
    }

    public function testGetAssignableUserListForCurrentTeam()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'current_user_01';

        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getReassignedUserList'])
            ->getMock();

        $userAssignmentHandlerMock->expects($this->once())
            ->method('getReassignedUserList')
            ->will($this->returnValue(['user01']));

        $flowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save', 'get_full_list'])
            ->getMock();

        $flowMock->cas_id = 1;
        $flowMock->cas_index = 2;
        $flowMock->bpmn_id = 'act01';
        $flowMock->bpmn_type = 'bpmnActivity';
        $flowMock->cas_reassign_level = 2;

        $activityDefinitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $activityDefinitionMock->act_adhoc_team = 'current_team';

        $userAssignmentHandlerMock->expects($this->at(1))
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinitionMock));

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $activityMock->act_adhoc_team = 'current_team';
        $activityMock->act_reassign_team = 'teamReassign';

        $userAssignmentHandlerMock->expects($this->at(2))
            ->method('retrieveBean')
            ->will($this->returnValue($activityMock));

        $membershipMock = $this->getMockBuilder('TeamMembership')
            ->disableOriginalConstructor()
            ->setMethods(['get_full_list'])
            ->getMock();

        $membersMock = [
            (object)['user_id' => 'user01'],
            (object)['user_id' => 'user02'],
            (object)['user_id' => 'user03'],
        ];

        $membershipMock->expects($this->once())
            ->method('get_full_list')
            ->will($this->returnValue($membersMock));

        $userAssignmentHandlerMock->expects($this->at(3))
            ->method('retrieveBean')
            ->will($this->returnValue($membershipMock));

        $caseId = 1;
        $caseIndex = 2;
        $fullList = false;
        $type = 'REASSIGN';


        $list = $userAssignmentHandlerMock->getAssignableUserList($flowMock, $fullList, $type);
        $this->assertCount(2, $list);
    }

    public function testGetCurrentUserId()
    {
        global $current_user;
        $current_user = new stdClass();
        $current_user->id = 'user01';

        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $result = $userAssignmentHandlerMock->getCurrentUserId();

        $this->assertEquals('user01', $result);
    }

    public function testGetRecordOwnerIdAssigned()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = new stdClass();
        $beanMock->assigned_user_id = 'user01';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $result = $userAssignmentHandlerMock->getRecordOwnerId('lead01', 'Leads');

        $this->assertEquals('user01', $result);
    }

    public function testGetRecordOwnerIdCreated()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = new stdClass();
        $beanMock->created_by = 'user01';

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $result = $userAssignmentHandlerMock->getRecordOwnerId('lead01', 'Leads');

        $this->assertEquals('user01', $result);
    }

    public function testGetRecordOwnerIdUnknown()
    {
        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = new stdClass();

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($beanMock));

        $result = $userAssignmentHandlerMock->getRecordOwnerId('lead01', 'Leads');

        $this->assertEquals('unknown', $result);
    }

    public function testGetSupervisorId()
    {
        global $db;
        $db = $this->getMockBuilder('DBHandler')
            ->disableOriginalConstructor()
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $users = [
            'reports_to_id' => 'supervisor_id',
        ];

        $db->expects($this->once())
            ->method('fetchByAssoc')
            ->will($this->returnValue($users));

        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $result = $userAssignmentHandlerMock->getSupervisorId('current_user_id');

        $this->assertEquals('supervisor_id', $result);
    }

    public function testGetNextUserUsingRoundRobin()
    {
        global $db;
        $db = $this->getMockBuilder('DBHandler')
            ->disableOriginalConstructor()
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $definition = [
            'act_assign_team' => 'current_team_01',
            'act_last_user_assigned' => 'user_01',
        ];

        $db->expects($this->once())
            ->method('fetchByAssoc')
            ->will($this->returnValue($definition));

        $db->expects($this->atLeastOnce())
            ->method('query')
            ->will($this->returnValue(true));

        $userAssignmentHandlerMock = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $teamMock = $this->getMockBuilder('Teams')
            ->disableOriginalConstructor()
            ->setMethods(['get_team_members'])
            ->getMock();

        $members = [
            (object)[
                'id' => 'user_02',
            ],
            (object)[
                'id' => 'user_03',
            ],
            (object)[
                'id' => 'user_04',
            ],
        ];

        $teamMock->expects($this->once())
            ->method('get_team_members')
            ->will($this->returnValue($members));

        $userAssignmentHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($teamMock));

        $result = $userAssignmentHandlerMock->getNextUserUsingRoundRobin('act01');

        $this->assertEquals('user_02', $result);
    }
}
