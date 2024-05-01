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

class PMSEReceiveMessageEventTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $receiveMessageEvent;

    public function testRunWithoutAction()
    {
        $this->receiveMessageEvent = $this->getMockBuilder('PMSEReceiveMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse'])
            ->getMock();

        $this->receiveMessageEvent->expects($this->exactly(2))
            ->method('prepareResponse');

        $flowData = [];

        $bean = new stdClass();

        $this->receiveMessageEvent->run($flowData, $bean, '');
    }

    public function testRunWithInvalidAction()
    {
        $this->receiveMessageEvent = $this->getMockBuilder('PMSEReceiveMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse', 'checkIfUsesAnEventBasedGateway', 'checkIfExistEventBased'])
            ->getMock();

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $this->receiveMessageEvent->expects($this->exactly(1))
            ->method('prepareResponse');

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 2,
            'cas_sugar_module' => 'Opportunities',
            'bpmn_id' => '9283jd9238j3d',
            'rel_process_module' => 'Leads',
            'rel_element_module' => 'Leads',
            'cas_sugar_object_id' => '893u2d89qj2398d',
            'rel_element_relationship' => 'Some related module',
        ];

        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['load_relationships'])
            ->getMock();
        $bean->parent_type = 'Leads';
        $bean->parent_id = '893u2d89qj2398d';

        $definitionMock = new stdClass();
        $definitionMock->evn_criteria = 'Some Criteria';

        $this->receiveMessageEvent->setCaseFlowHandler($caseHandlerMock);
        $this->receiveMessageEvent->run($flowData, $bean, 'WAKE_UP');
    }

    public function testRunWithValidAction()
    {
        $this->receiveMessageEvent = $this->getMockBuilder('PMSEReceiveMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse', 'checkIfUsesAnEventBasedGateway', 'checkIfExistEventBased'])
            ->getMock();

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $this->receiveMessageEvent->expects($this->once())
            ->method('prepareResponse');

        $this->receiveMessageEvent->expects($this->once())
            ->method('checkIfUsesAnEventBasedGateway');

        $this->receiveMessageEvent->expects($this->once())
            ->method('checkIfExistEventBased');

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 2,
            'bpmn_id' => '9283jd9238j3d',
            'cas_sugar_module' => 'Opportunities',
            'rel_process_module' => 'Leads',
            'rel_element_module' => 'Leads',
            'cas_sugar_object_id' => '893u2d89qj2398d',
            'rel_element_relationship' => 'Some related module',
        ];

        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['load_relationships'])
            ->getMock();

        $bean->parent_type = 'Leads';
        $bean->parent_id = '893u2d89qj2398d';

        $definitionMock = new stdClass();
        $definitionMock->evn_criteria = 'Some Criteria';
        $this->receiveMessageEvent->setCaseFlowHandler($caseHandlerMock);
        $this->receiveMessageEvent->run($flowData, $bean, 'EVALUATE_RELATED_MODULE');
    }

    public function testRunWithValidActionAndBean()
    {
        $this->receiveMessageEvent = $this->getMockBuilder('PMSEReceiveMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse', 'checkIfUsesAnEventBasedGateway', 'checkIfExistEventBased', 'getProcessBean'])
            ->getMock();

        // Set our expectation
        $this->receiveMessageEvent->method('getProcessBean')
            ->willReturn(true);

        $caseHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $this->receiveMessageEvent->expects($this->exactly(2))
            ->method('prepareResponse');

        $this->receiveMessageEvent->expects($this->once())
            ->method('checkIfUsesAnEventBasedGateway');

        $this->receiveMessageEvent->expects($this->once())
            ->method('checkIfExistEventBased');

        $this->receiveMessageEvent->expects($this->once())
            ->method('getProcessBean');

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 2,
            'bpmn_id' => '9283jd9238j3d',
            'cas_sugar_module' => 'Opportunities',
            'rel_process_module' => 'Leads',
            'rel_element_module' => 'Leads',
            'cas_sugar_object_id' => '893u2d89qj2398d',
            'rel_element_relationship' => 'Some related module',
        ];

        $bean = $this->getMockBuilder('SugarBean')
            ->setMethods(['load_relationships'])
            ->getMock();

        $bean->parent_type = 'Leads';
        $bean->parent_id = '893u2d89qj2398d';

        $definitionMock = new stdClass();
        $definitionMock->evn_criteria = 'Some Criteria';
        $this->receiveMessageEvent->setCaseFlowHandler($caseHandlerMock);
        $this->receiveMessageEvent->run($flowData, $bean, 'EVALUATE_RELATED_MODULE');
    }
}
