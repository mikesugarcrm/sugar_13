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

class PMSEFlowRouterTest extends TestCase
{
    /**
     * @var PMSEFlowRouter
     */
    private $flowRouterObject;

    /**
     * @covers PMSEFlowRouter::retrieveElement
     */
    public function testRetrieveElement()
    {
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = ['cas_id' => 1, 'cas_index' => 2];
        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveElementByType'])
            ->getMock();

        $testPmseObject = new stdClass();

        $mockCaseFlowHandler->expects($this->exactly(1))
            ->method('retrieveElementByType')
            ->with($flowData)
            ->will($this->returnValue($testPmseObject));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);
        $this->flowRouterObject->retrieveElement($flowData);
    }

    public function testRouteFlowActionCreate()
    {
        $flowData = [
            'id' => '837278dh2837e',
            'cas_id' => 2,
            'cas_index' => 3,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'aiuj2d8931',
        ];

        $previousFlowData = [
            'id' => '2189su9128sda',
            'cas_id' => 2,
            'cas_index' => 2,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'nsiojqwd98',
        ];

        $executionResult = [
            'route_action' => 'ROUTE',
            'flow_action' => 'CREATE',
            'flow_filters' => [],
            'flow_data' => $flowData,
            'flow_id' => $flowData['id'],
        ];

        $nextElements = [
            'next_elements' => [
                [
                    'cas_id' => 2,
                    'cas_index' => 4,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 5,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 6,
                ],
            ],
        ];

        // We need to override the execute Element since that method is not
        // evaluated in this test but is called inside the routeFlow method
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['processElement', 'retrieveFollowingElements'])
            ->disableOriginalConstructor()
            ->getMock();

        // preparing the case flow handler mock
        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['closePreviousFlow', 'prepareFlowData', 'saveFlowData'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(1))
            ->method('closePreviousFlow');

        $this->flowRouterObject->expects($this->exactly(1))
            ->method('retrieveFollowingElements')
            ->will($this->returnValue($nextElements));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);

        $result = $this->flowRouterObject->routeFlow($executionResult, $previousFlowData);
        $this->assertArrayHasKey('processed_flow', $result);
        $this->assertArrayHasKey('next_elements', $result);
    }

    public function testRouteFlowActionUpdate()
    {
        $flowData = [
            'id' => '837278dh2837e',
            'cas_id' => 2,
            'cas_index' => 3,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'aiuj2d8931',
        ];

        $previousFlowData = [
            'id' => '2189su9128sda',
            'cas_id' => 2,
            'cas_index' => 2,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'nsiojqwd98',
        ];

        $executionResult = [
            'route_action' => 'ROUTE',
            'flow_action' => 'UPDATE',
            'flow_filters' => [],
            'flow_data' => $flowData,
            'flow_id' => $flowData['id'],
        ];

        $nextElements = [
            'next_elements' => [
                [
                    'cas_id' => 2,
                    'cas_index' => 4,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 5,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 6,
                ],
            ],
        ];

        // We need to override the execute Element since that method is not
        // evaluated in this test but is called inside the routeFlow method
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['processElement', 'retrieveFollowingElements'])
            ->disableOriginalConstructor()
            ->getMock();

        // preparing the case flow handler mock
        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['closePreviousFlow', 'prepareFlowData', 'saveFlowData'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(0))
            ->method('closePreviousFlow');

        $this->flowRouterObject->expects($this->exactly(1))
            ->method('retrieveFollowingElements')
            ->will($this->returnValue($nextElements));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);

        $result = $this->flowRouterObject->routeFlow($executionResult, $previousFlowData);
        $this->assertArrayHasKey('processed_flow', $result);
        $this->assertArrayHasKey('next_elements', $result);
    }

    public function testRouteFlowActionNone()
    {
        $flowData = [
            'id' => '837278dh2837e',
            'cas_id' => 2,
            'cas_index' => 3,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'aiuj2d8931',
        ];

        $previousFlowData = [
            'id' => '2189su9128sda',
            'cas_id' => 2,
            'cas_index' => 2,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'nsiojqwd98',
        ];

        $executionResult = [
            'route_action' => 'ROUTE',
            'flow_action' => 'NONE',
            'flow_filters' => [],
            'flow_data' => $flowData,
            'flow_id' => $flowData['id'],
        ];

        $nextElements = [
            'next_elements' => [
                [
                    'cas_id' => 2,
                    'cas_index' => 4,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 5,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 6,
                ],
            ],
        ];

        // We need to override the execute Element since that method is not
        // evaluated in this test but is called inside the routeFlow method
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['processElement', 'retrieveFollowingElements'])
            ->disableOriginalConstructor()
            ->getMock();

        // preparing the case flow handler mock
        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['closePreviousFlow', 'prepareFlowData', 'saveFlowData'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(0))
            ->method('closePreviousFlow');

        $this->flowRouterObject->expects($this->exactly(1))
            ->method('retrieveFollowingElements')
            ->will($this->returnValue($nextElements));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);

        $result = $this->flowRouterObject->routeFlow($executionResult, $previousFlowData);
        $this->assertArrayHasKey('processed_flow', $result);
        $this->assertArrayHasKey('next_elements', $result);
    }

    public function testRouteFlowActionClose()
    {
        $flowData = [
            'id' => '837278dh2837e',
            'cas_id' => 2,
            'cas_index' => 3,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'aiuj2d8931',
        ];

        $previousFlowData = [
            'id' => '2189su9128sda',
            'cas_id' => 2,
            'cas_index' => 2,
            'bpmn_type' => 'bpmnActivity',
            'bpmn_id' => 'nsiojqwd98',
        ];

        $executionResult = [
            'processed_flow' => [],
            'route_action' => 'WAIT',
            'flow_action' => 'CLOSE',
            'flow_filters' => [],
            'flow_data' => $flowData,
            'flow_id' => $flowData['id'],
        ];

        $nextElements = [
            'next_elements' => [
                [
                    'cas_id' => 2,
                    'cas_index' => 4,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 5,
                ],
                [
                    'cas_id' => 2,
                    'cas_index' => 6,
                ],
            ],
        ];

        // We need to override the execute Element since that method is not
        // evaluated in this test but is called inside the routeFlow method
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['processElement', 'retrieveFollowingElements'])
            ->disableOriginalConstructor()
            ->getMock();

        // preparing the case flow handler mock
        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['closePreviousFlow', 'prepareFlowData', 'saveFlowData'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(1))
            ->method('closePreviousFlow');

        $this->flowRouterObject->expects($this->exactly(1))
            ->method('retrieveFollowingElements')
            ->will($this->returnValue($nextElements));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);

        $result = $this->flowRouterObject->routeFlow($executionResult, $previousFlowData);
        $this->assertArrayHasKey('processed_flow', $result);
        $this->assertArrayHasKey('next_elements', $result);
    }

    public function testRetrieveFollowingElementsRoute()
    {
        $executionResult = [
            'route_action' => 'ROUTE',
            'flow_filters' => [],
        ];

        $flowData = [
            'cas_id' => 2,
            'cas_index' => 3,
        ];

        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['filterFlows'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->flowRouterObject->expects($this->once())
            ->method('filterFlows');

        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFollowingElements'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(1))
            ->method('retrieveFollowingElements');

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);
        $this->flowRouterObject->retrieveFollowingElements($executionResult, $flowData);
    }

    public function testRetrieveFollowingElementsQueue()
    {
        $executionResult = ['route_action' => 'QUEUE', 'flow_filters' => []];
        $flowData = [
            'cas_id' => 2,
            'cas_index' => 3,
        ];

        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(['queueJob'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockCaseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveFollowingElements'])
            ->getMock();

        $mockCaseFlowHandler->expects($this->exactly(1))
            ->method('retrieveFollowingElements')
            ->will($this->returnValue([]));

        $this->flowRouterObject->setCaseFlowHandler($mockCaseFlowHandler);

        $expectedResult = [];
        $result = $this->flowRouterObject->retrieveFollowingElements($executionResult, $flowData);
        $this->assertEquals($expectedResult, $result);
    }

    public function testQueueJob()
    {
        $flowData = [
            'cas_id' => 2,
            'cas_index' => 3,
        ];

        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $mockJobQueueHandler = $this->getMockBuilder('PMSEJobQueueHandler')
            ->setMethods(['submitPMSEJob'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockJobQueueHandler->expects($this->exactly(1))
            ->method('submitPMSEJob')
            ->will($this->returnValue('abc'));

        $expectedResult = 'abc';

        $this->flowRouterObject->setJobQueueHandler($mockJobQueueHandler);
        $result = $this->flowRouterObject->queueJob($flowData);
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterFlows()
    {
        $this->flowRouterObject = $this->getMockBuilder('PMSEFlowRouter')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $nextElements = [
            ['bpmn_id' => 'first_id'],
            ['bpmn_id' => 'second_id'],
            ['bpmn_id' => 'third_id'],
            ['bpmn_id' => 'fourth_id'],
        ];

        $flowFilters = [
            'first_id', 'third_id',
        ];

        $expectedResult = [
            ['bpmn_id' => 'first_id'],
            ['bpmn_id' => 'third_id'],
        ];

        $result = $this->flowRouterObject->filterFlows($nextElements, $flowFilters);
        $this->assertEquals($expectedResult, $result);
    }
}
