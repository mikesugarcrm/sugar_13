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

class PMSETimerEventTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $timerEvent;

    /**
     * In this test the method tries to wake up a timer event before
     * their due date passed, the condition is set to not wake up the flow
     * in this case
     */
    public function testRunTryWakeUpBeforeTime()
    {
        $this->timerEvent = $this->getMockBuilder('PMSETimerEvent')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareResponse',
                    'checkIfUsesAnEventBasedGateway',
                    'checkIfExistEventBased',
                    'getCurrentTime',
                ]
            )
            ->getMock();

        $dateObject = new DateTime('2014-05-27 16:26:38');
        $date = $dateObject->getTimestamp();

        $this->timerEvent->expects($this->any())
            ->method('getCurrentTime')
            ->will($this->returnValue($date));

        $flowData = [
            'cas_due_date' => '2014-05-28 16:26:38',
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_previous' => 2,
        ];
        $bean = new stdClass();

        $this->timerEvent->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'UPDATE');

        $this->timerEvent->run($flowData, $bean, 'WAKE_UP');
    }

    /**
     * In this test the method tries to wake up a timer event after
     * their due date passed, the condition is set to wake up the flow
     * in this case
     */
    public function testRunTryWakeUpAfterTime()
    {
        $this->timerEvent = $this->getMockBuilder('PMSETimerEvent')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareResponse',
                    'checkIfUsesAnEventBasedGateway',
                    'checkIfExistEventBased',
                    'getCurrentTime',
                ]
            )
            ->getMock();

        $dateObject = new DateTime('2014-05-29 16:26:38');
        $date = $dateObject->getTimestamp();

        $this->timerEvent->expects($this->any())
            ->method('getCurrentTime')
            ->will($this->returnValue($date));

        $flowData = [
            'cas_due_date' => '2014-05-28 16:26:38',
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_previous' => 2,
        ];
        $bean = new stdClass();

        $this->timerEvent->expects($this->exactly(1))
            ->method('prepareResponse')
            ->with($flowData, 'ROUTE', 'UPDATE');

        $this->timerEvent->run($flowData, $bean, 'WAKE_UP');
    }

    public function testRunWithNoAction()
    {
        $this->timerEvent = $this->getMockBuilder('PMSETimerEvent')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareResponse',
                    'checkIfUsesAnEventBasedGateway',
                    'checkIfExistEventBased',
                    'getCurrentTime',
                    'retrieveDefinitionData',
                ]
            )
            ->getMock();

        $definition = [
            'evn_criteria' => '5',
            'evn_params' => 'minute',
        ];

        $this->timerEvent->expects($this->once())
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definition));

        $flowData = [
            'cas_due_date' => '2014-05-28 16:26:38',
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_previous' => 2,
            'bpmn_id' => '198273498jh9238j1s23',
            'id' => '2918379e8921uj98s12',
        ];
        $bean = new stdClass();

        $this->timerEvent->expects($this->exactly(1))
            ->method('prepareResponse');

        $this->timerEvent->run($flowData, $bean, '');
    }

    public function testRunWithNoEvnCriteria()
    {
        $this->timerEvent = $this->getMockBuilder('PMSETimerEvent')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareResponse',
                    'checkIfUsesAnEventBasedGateway',
                    'checkIfExistEventBased',
                    'getCurrentTime',
                    'retrieveDefinitionData',
                ]
            )
            ->getMock();

        $definition = [
            'evn_criteria' => '[]',
        ];

        $evaluatorMock = $this->getMockBuilder('PMSEEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(['evaluateExpression'])
            ->getMock();

        $evaluatorMock->expects($this->any())
            ->method('evaluateExpression')
            ->will($this->returnValue('2014-05-28T16:26:38-0000'));

        $this->timerEvent->setEvaluator($evaluatorMock);

        $this->timerEvent->expects($this->once())
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($definition));

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $caseFlowHandler->expects($this->exactly(1))
            ->method('retrieveBean');

        $beanHandler = $this->getMockBuilder('PMSEBeanHandler')
            ->disableOriginalConstructor()
            ->setMethods(['calculateDueDate'])
            ->getMock();

        $beanHandler->expects($this->exactly(0))
            ->method('calculateDueDate');


        $this->timerEvent->setBeanHandler($beanHandler);
        $this->timerEvent->setCaseFlowHandler($caseFlowHandler);

        $flowData = [
            'cas_due_date' => '2014-05-28 16:26:38',
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_previous' => 2,
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'ciweun9823jd238jd',
            'bpmn_id' => '198273498jh9238j1s23',
            'id' => '2918379e8921uj98s12',
        ];
        $bean = new stdClass();

        $this->timerEvent->expects($this->exactly(1))
            ->method('prepareResponse');

        $this->timerEvent->run($flowData, $bean, '');
    }
}
