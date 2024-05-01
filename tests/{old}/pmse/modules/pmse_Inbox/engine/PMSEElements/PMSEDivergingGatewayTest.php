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

class PMSEDivergingGatewayTest extends TestCase
{
    /**
     * @var type
     */
    protected $loggerMock;

    /**
     * @var PMSEElement
     */
    protected $divergingGateway;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug', 'warning'])
            ->getMock();
    }

    public function testRetrieveFollowingFlows()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(['fetchFromQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockBean->expects($this->once())
            ->method('fetchFromQuery')
            ->will($this->returnValue([]));

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['retrieveBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandler->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($mockBean));

        $this->divergingGateway->setCaseFlowHandler($caseFlowHandler);

        $flowData = [
            'bpmn_id' => '1234567890',
        ];

        $this->divergingGateway->retrieveFollowingFlows($flowData);
    }

    public function testEvaluateFlowDefault()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(['getDbHandler'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $mockFlow = $this->getMockBuilder('BpmFlow')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFlow->flo_type = 'DEFAULT';

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'bpmn_id' => '1234567890',
        ];

        $result = $this->divergingGateway->evaluateFlow($mockFlow, $mockBean, $flowData);
        $this->assertEquals(true, $result);
    }

    public function testEvaluateFlowWithoutCondition()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(['getDbHandler'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $mockFlow = $this->getMockBuilder('BpmFlow')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFlow->flo_type = 'SEQUENCE';
        $mockFlow->flo_condition = '';

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'bpmn_id' => '1234567890',
        ];

        $result = $this->divergingGateway->evaluateFlow($mockFlow, $mockBean, $flowData);
        $this->assertEquals(false, $result);
    }

    public function testEvaluateFlowWithCondition()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(['getDbHandler'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $mockFlow = $this->getMockBuilder('BpmFlow')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $mockFlow->flo_type = 'SEQUENCE';
        $mockFlow->flo_condition = '(a === 1)';

        $expressionEvaluator = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->setMethods(['evaluateExpression'])
            ->disableOriginalConstructor()
            ->getMock();

        $expressionEvaluator->expects($this->once())
            ->method('evaluateExpression')
            ->will($this->returnValue(true));

        $this->divergingGateway->setEvaluator($expressionEvaluator);

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'bpmn_id' => '1234567890',
            'cas_id' => 'abc12349123',
        ];

        $result = $this->divergingGateway->evaluateFlow($mockFlow, $mockBean, $flowData);
        $this->assertEquals(true, $result);
    }

    public function testFilterFlowsSingle()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(['evaluateFlow'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $this->divergingGateway->expects($this->once())
            ->method('evaluateFlow')
            ->willReturn(true);

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'bpmn_id' => '1234567890',
            'cas_id' => 'abc12349123',
        ];

        $firstFlow = new stdClass();
        $firstFlow->id = 'first_flow';

        $secondFlow = new stdClass();
        $secondFlow->id = 'second_flow';

        $flows = [
            $firstFlow,
            $secondFlow,
        ];

        $type = 'SINGLE';

        $filters = $this->divergingGateway->filterFlows($type, $flows, $mockBean, $flowData);
        $this->assertIsArray($filters);
        $this->assertCount(1, $filters);
    }

    public function testFilterFlowsAll()
    {
        $this->divergingGateway = $this->getMockBuilder('PMSEDivergingGateway')
            ->setMethods(['evaluateFlow'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->divergingGateway->setLogger($this->loggerMock);

        $this->divergingGateway->expects($this->exactly(2))
            ->method('evaluateFlow')
            ->willReturn(true);

        $mockBean = $this->getMockBuilder('SugarBean')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $flowData = [
            'bpmn_id' => '1234567890',
            'cas_id' => 'abc12349123',
        ];

        $firstFlow = new stdClass();
        $firstFlow->id = 'first_flow';

        $secondFlow = new stdClass();
        $secondFlow->id = 'second_flow';

        $flows = [
            $firstFlow,
            $secondFlow,
        ];

        $type = 'ALL';

        $filters = $this->divergingGateway->filterFlows($type, $flows, $mockBean, $flowData);
        $this->assertIsArray($filters);
        $this->assertCount(2, $filters);
    }
}
