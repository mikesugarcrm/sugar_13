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

use Sugarcrm\Sugarcrm\ProcessManager\Registry;
use PHPUnit\Framework\TestCase;

class PMSEUserTaskTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $userTask;

    /**
     * Registry object for maintaining state
     * @var Registry\Registry
     */
    private $registry;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->registry = Registry\Registry::getInstance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->registry->reset();
    }

    public function testRunAssignment()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(['prepareResponse', 'retrieveBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['taskAssignment'])
            ->getMock();

        $activityDefinition = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse'])
            ->getMock();
        $activityDefinition->act_response_buttons = 'ROUTE';
        $activityDefinition->act_assignment_method = 'static';

        $bean = new stdClass();
        $externalAction = '';
        $flowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
            'bpmn_id' => 'c5189a2e-1cff-e214-3e86-55664fcc93e6',
        ];

        $expectedFlowData = [
            'cas_user_id' => 2,
            'cas_index' => 1,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 2,
            'cas_adhoc_actions' => json_encode(['link_cancel', 'route', 'edit', 'continue']),
            'bpmn_id' => 'c5189a2e-1cff-e214-3e86-55664fcc93e6',
            'cas_assignment_method' => 'static',
        ];

        $expectedResult = [
            'route_action' => 'WAIT',
            'flow_action' => 'CREATE',
            'flow_data' => [
                'cas_user_id' => 2,
                'cas_index' => 1,
                'id' => 5,
                'cas_flow_status' => 'FORM',
            ],
            'flow_id' => $flowData['id'],
        ];

        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CREATE')
            ->will($this->returnValue($expectedResult));

        $userAssignment->expects($this->exactly(1))
            ->method('taskAssignment')
            ->with($flowData)
            ->will($this->returnValue(2));

        $this->userTask->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinition));

        $this->userTask->setUserAssignmentHandler($userAssignment);

        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRunAssignmentForm()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(['prepareResponse', 'retrieveBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['taskAssignment'])
            ->getMock();

        $activityDefinition = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse'])
            ->getMock();
        $activityDefinition->act_response_buttons = 'FORM';
        $activityDefinition->act_assignment_method = 'static';

        $bean = new stdClass();
        $externalAction = '';
        $flowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
            'bpmn_id' => 'c5189a2e-1cff-e214-3e86-55664fcc93e6',
        ];

        $expectedFlowData = [
            'cas_user_id' => 2,
            'cas_index' => 1,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 2,
            'cas_adhoc_actions' => json_encode(['link_cancel', 'approve', 'reject', 'edit']),
            'bpmn_id' => 'c5189a2e-1cff-e214-3e86-55664fcc93e6',
            'cas_assignment_method' => 'static',
        ];

        $expectedResult = [
            'route_action' => 'WAIT',
            'flow_action' => 'CREATE',
            'flow_data' => [
                'cas_user_id' => 2,
                'cas_index' => 1,
                'id' => 5,
                'cas_flow_status' => 'FORM',
            ],
            'flow_id' => $flowData['id'],
        ];

        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CREATE')
            ->will($this->returnValue($expectedResult));

        $userAssignment->expects($this->exactly(1))
            ->method('taskAssignment')
            ->with($flowData)
            ->will($this->returnValue(2));

        $this->userTask->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($activityDefinition));

        $this->userTask->setUserAssignmentHandler($userAssignment);

        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRunRoundTrip()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods([
                'prepareResponse',
                'processAction',
                'checkIfUsesAnEventBasedGateway',
                'checkIfExistEventBased',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['roundTripReassign'])
            ->getMock();

        $bean = new stdClass();
        $externalAction = 'ROUND_TRIP';
        $flowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5,
        ];

        $expectedResult = [
            'route_action' => 'WAIT',
            'flow_action' => 'CLOSE',
            'flow_data' => ['cas_flow_status' => 'FORM'],
            'flow_id' => $flowData['id'],
        ];

        $expectedFlowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1,
        ];

        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CLOSE')
            ->will($this->returnValue($expectedResult));

        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ROUND_TRIP'));

        $rtFlowData = $flowData;
        $userAssignment->expects($this->exactly(1))
            ->method('roundTripReassign')
            ->with($rtFlowData)
            ->will($this->returnValue(2));

        $this->userTask->setUserAssignmentHandler($userAssignment);

        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRunOneWay()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods([
                'prepareResponse',
                'processAction',
                'checkIfUsesAnEventBasedGateway',
                'checkIfExistEventBased',
            ])
            ->disableOriginalConstructor()
            ->getMock();


        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['oneWayReassign'])
            ->getMock();

        $bean = new stdClass();
        $externalAction = 'ONE_WAY';
        $flowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $expectedResult = [
            'route_action' => 'WAIT',
            'flow_action' => 'CLOSE',
            'flow_data' => ['cas_flow_status' => 'FORM'],
            'flow_id' => $flowData['id'],
        ];

        $expectedFlowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1,
        ];

        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'WAIT', 'CLOSE')
            ->will($this->returnValue($expectedResult));

        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ONE_WAY'));

        $owFlowData = $flowData;
        $userAssignment->expects($this->exactly(1))
            ->method('oneWayReassign')
            ->with($owFlowData)
            ->will($this->returnValue(2));

        $this->userTask->setUserAssignmentHandler($userAssignment);

        $result = $this->userTask->run($flowData, $bean, $externalAction);
        $this->assertEquals($expectedResult, $result);
    }

    public function testRunRouteWithArguments()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods([
                'lockFlowRoute',
                'saveBeanData',
                'prepareResponse',
                'processAction',
                'checkIfUsesAnEventBasedGateway',
                'checkIfExistEventBased',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $bean = new stdClass();
        $externalAction = 'SOME_ACTION';
        $flowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5,
        ];

        $expectedResult = [
            'route_action' => 'ROUTE',
            'flow_action' => 'UPDATE',
            'flow_data' => [
                'cas_user_id' => 1,
                'cas_index' => 2,
                'id' => 5,
                'cas_flow_status' => 'FORM',
                'assigned_user_id' => 1,
            ],
            'flow_id' => $flowData['id'],
            'flow_filters' => [],
        ];

        $expectedFlowData = [
            'cas_id' => 1,
            'cas_user_id' => 1,
            'cas_index' => 2,
            'id' => 5,
            'cas_flow_status' => 'FORM',
            'assigned_user_id' => 1,
        ];

        $this->userTask->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($expectedFlowData, 'ROUTE', 'UPDATE')
            ->will($this->returnValue($expectedResult));

        $this->userTask->expects($this->exactly(1))
            ->method('lockFlowRoute');

        $this->userTask->expects($this->exactly(1))
            ->method('saveBeanData');

        $this->userTask->expects($this->exactly(1))
            ->method('processAction')
            ->with($flowData)
            ->will($this->returnValue('ROUTE'));

        $arguments = ['idFlow' => 'abc123'];
        $result = $this->userTask->run($flowData, $bean, $externalAction, $arguments);
        $this->assertEquals($expectedResult, $result);
    }


    public function testProcessUserActionRT()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['isRoundTrip'])
            ->getMock();

        $paramFlowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $userAssignment->expects($this->once())
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(true));

        $expectedAction = 'ROUND_TRIP';

        $this->userTask->setUserAssignmentHandler($userAssignment);
        $action = $this->userTask->processUserAction($flowData);

        $this->assertEquals($expectedAction, $action);
    }

    public function testProcessUserActionOW()
    {
        $flowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['isRoundTrip', 'isOneWay', 'previousIsNormal'])
            ->getMock();

        $paramFlowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $userAssignment->expects($this->exactly(1))
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(false));

        $userAssignment->expects($this->exactly(1))
            ->method('isOneWay')
            ->with($paramFlowData)
            ->will($this->returnValue(true));

        $userAssignment->expects($this->exactly(1))
            ->method('previousIsNormal')
            ->with($paramFlowData)
            ->will($this->returnValue(false));

        $expectedAction = 'ONE_WAY';
        $this->userTask->setUserAssignmentHandler($userAssignment);

        $action = $this->userTask->processUserAction($flowData);

        $this->assertEquals($expectedAction, $action);
    }

    public function testProcessUserActionRoute()
    {
        $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $userAssignment = $this->getMockBuilder('PMSEUserAssignmentHandler')
            ->disableOriginalConstructor()
            ->setMethods(['isRoundTrip', 'isOneWay', 'previousIsNormal'])
            ->getMock();

        $paramFlowData = [
            'cas_user_id' => 1,
            'cas_index' => 1,
            'id' => 5,
        ];

        $userAssignment->expects($this->exactly(1))
            ->method('isRoundTrip')
            ->with($paramFlowData)
            ->will($this->returnValue(false));

        $userAssignment->expects($this->exactly(1))
            ->method('isOneWay')
            ->with($paramFlowData)
            ->will($this->returnValue(false));

        $userAssignment->expects($this->exactly(0))
            ->method('previousIsNormal')
            ->with($paramFlowData)
            ->will($this->returnValue(false));

        $expectedAction = 'ROUTE';
        $this->userTask->setUserAssignmentHandler($userAssignment);
        $action = $this->userTask->processUserAction($flowData);

        $this->assertEquals($expectedAction, $action);
    }

    public function testLockFlowRouteIfRegistered()
    {
        $this->userTask = $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $reg = Registry\Registry::getInstance();
        $reg->set('locked_flows', ['abc123' => 1]);
        $this->userTask->lockFlowRoute('zte890');
        $flows = $reg->get('locked_flows');
        $this->assertArrayHasKey('zte890', $flows);
    }

    public function testLockFlowRouteIfNew()
    {
        $reg = Registry\Registry::getInstance();
        $reg->drop('locked_flows');
        $this->userTask = $this->userTask = $this->getMockBuilder('PMSEUserTask')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userTask->lockFlowRoute('zte890');
        $flows = $reg->get('locked_flows');
        $this->assertArrayHasKey('zte890', $flows);
    }
}
