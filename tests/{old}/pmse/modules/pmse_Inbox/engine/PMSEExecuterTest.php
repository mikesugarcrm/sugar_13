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

class PMSEExecuterTest extends TestCase
{
    protected $pmseExecuter;

    public function testRetrieveElementByType()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods([
                'retrieveActivityElement',
                'retrieveEventElement',
                'retrieveGatewayElement',
                'retrieveFlowElement',
                'retrievePMSEElement',
            ])
            ->getMock();

        $flowActData = ['bpmn_type' => 'bpmnActivity', 'bpmn_id' => 'act0001'];
        $pmseExecuterMock->expects($this->once())
            ->method('retrieveActivityElement')
            ->with($flowActData['bpmn_id']);
        $pmseExecuterMock->retrieveElementByType($flowActData);

        $flowEvnData = ['bpmn_type' => 'bpmnEvent', 'bpmn_id' => 'evn0001'];
        $pmseExecuterMock->expects($this->once())
            ->method('retrieveEventElement')
            ->with($flowEvnData['bpmn_id']);
        $pmseExecuterMock->retrieveElementByType($flowEvnData);

        $flowGatData = ['bpmn_type' => 'bpmnGateway', 'bpmn_id' => 'gat0001'];
        $pmseExecuterMock->expects($this->once())
            ->method('retrieveGatewayElement')
            ->with($flowGatData['bpmn_id']);
        $pmseExecuterMock->retrieveElementByType($flowGatData);

        $flowFloData = ['bpmn_type' => 'bpmnFlow', 'bpmn_id' => 'flo0001'];
        $pmseExecuterMock->expects($this->once())
            ->method('retrieveFlowElement')
            ->with($flowFloData['bpmn_id']);
        $pmseExecuterMock->retrieveElementByType($flowFloData);

        $flowEleData = ['bpmn_type' => 'invalid_value', 'bpmn_id' => 'inv0001'];
        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('');
        $pmseExecuterMock->retrieveElementByType($flowEleData);
    }

    /**
     * Busoness Rules
     */
    public function testRetrieveActivityElementBR()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'SCRIPTTASK';
        $beanMock->act_script_type = 'BUSINESS_RULE';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('BusinessRule')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementCF()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'SCRIPTTASK';
        $beanMock->act_script_type = 'CHANGE_FIELD';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('ChangeField')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementAT()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'SCRIPTTASK';
        $beanMock->act_script_type = 'ASSIGN_TEAM';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('RoundRobin')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementAU()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'SCRIPTTASK';
        $beanMock->act_script_type = 'ASSIGN_USER';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('AssignUser')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementADR()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'SCRIPTTASK';
        $beanMock->act_script_type = 'ADD_RELATED_RECORD';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('AddRelatedRecord')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementUserTask()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'USERTASK';
        $beanMock->act_script_type = 'ADD_RELATED_RECORD';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('UserTask')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }

    public function testRetrieveActivityElementInvalidTask()
    {
        $pmseExecuterMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['retrievePMSEElement'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $beanMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $beanMock->act_task_type = 'INVALID_TASK_TYPE';
        $beanMock->act_script_type = 'SOME_ELEMENT';

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();
        $definitionMock->execution_mode = 'SYNC';

        $caseFlowHandlerMock->expects($this->exactly(2))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmnActivity',
                ],
                [
                    'pmse_BpmActivityDefinition',
                ]
            )
            ->willReturnOnConsecutiveCalls($beanMock, $definitionMock);

        $elementMock = $this->getMockBuilder('PMSEElement')
            ->disableOriginalConstructor()
            ->setMethods(['setExecutionMode'])
            ->getMock();

        $pmseExecuterMock->expects($this->once())
            ->method('retrievePMSEElement')
            ->with('')
            ->will($this->returnValue($elementMock));

        $pmseExecuterMock->setCaseFlowHandler($caseFlowHandlerMock);
        $id = 1;
        $pmseExecuterMock->retrieveActivityElement($id);
    }
}
