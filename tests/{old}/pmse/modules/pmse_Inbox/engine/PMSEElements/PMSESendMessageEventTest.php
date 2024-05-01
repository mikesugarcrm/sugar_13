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

class PMSESendMessageEventTest extends TestCase
{
    /**
     * @var type
     */
    protected $loggerMock;

    /**
     * @var PMSEElement
     */
    protected $sendMessageEvent;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug', 'warning', 'error'])
            ->getMock();
    }

    public function testRunResumingExecution()
    {
        $this->sendMessageEvent = $this->getMockBuilder('PMSESendMessageEvent')
            ->onlyMethods(['prepareResponse', 'sendEmail'])
            ->getMock();

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->onlyMethods(['queueEmail', 'setFlowData'])
            ->getMock();

        $this->sendMessageEvent->setEmailHandler($emailHandler);

        $this->sendMessageEvent->expects($this->once())
            ->method('prepareResponse');

        $this->sendMessageEvent->expects($this->once())
            ->method('sendEmail');

        $this->sendMessageEvent->getEmailHandler()->expects($this->once())
            ->method('setFlowData');

        $flowData = [];
        $bean = new stdClass();

        $this->sendMessageEvent->run($flowData, $bean, 'RESUME_EXECUTION');
    }

    public function testRunNormal()
    {
        $this->sendMessageEvent = $this->getMockBuilder('PMSESendMessageEvent')
            ->disableOriginalConstructor()
            ->onlyMethods(['prepareResponse', 'sendEmail'])
            ->getMock();

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->onlyMethods(['queueEmail', 'setFlowData'])
            ->getMock();

        $this->sendMessageEvent->setEmailHandler($emailHandler);

        $this->sendMessageEvent->expects($this->once())
            ->method('prepareResponse');

        $this->sendMessageEvent->getEmailHandler()->expects($this->once())
            ->method('setFlowData');

        $this->sendMessageEvent->getEmailHandler()->expects($this->once())
            ->method('queueEmail');

        $flowData = [];
        $bean = new stdClass();

        $this->sendMessageEvent->run($flowData, $bean, '');
    }

    public function testSendEmail()
    {
        $this->sendMessageEvent = $this->getMockBuilder('PMSESendMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean', 'getCachedEmail'])
            ->getMock();

        $eventDefinition = $this->getMockBuilder('PMSEEventDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $eventDefinition->evn_criteria = [];
        $eventDefinition->evn_params = json_encode([]);

        $this->sendMessageEvent->setEventDefinitionBean($eventDefinition);

        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['processEmailsFromJson', 'sendTemplateEmail'])
            ->getMock();

        $emailHandler->expects($this->exactly(1))
            ->method('processEmailsFromJson');

        $emailHandler->expects($this->exactly(1))
            ->method('sendTemplateEmail')
            ->will($this->returnValue([
                'result' => ['successful sending'],
            ]));

        $this->sendMessageEvent->setCaseFlowHandler($caseHandler);
        $this->sendMessageEvent->setEmailHandler($emailHandler);

        $flowData = [
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'anc7832jd2387hd23',
            'bpmn_id' => 'aosijdi9qwdj',
            'id' => 'caseidja9823ju89d',
        ];

        $this->sendMessageEvent->sendEmail($flowData);
    }

    public function testSendEmailErrorInfo()
    {
        $this->sendMessageEvent = $this->getMockBuilder('PMSESendMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $eventDefinition = $this->getMockBuilder('PMSEEventDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $eventDefinition->evn_criteria = [];
        $eventDefinition->evn_params = json_encode([]);

        $this->sendMessageEvent->setEventDefinitionBean($eventDefinition);

        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();
        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['processEmailsFromJson', 'sendTemplateEmail'])
            ->getMock();

        $emailHandler->expects($this->exactly(1))
            ->method('processEmailsFromJson');

        $emailHandler->expects($this->exactly(1))
            ->method('sendTemplateEmail')
            ->will($this->returnValue([
                'result' => [],
                'ErrorInfo' => 'Some Error happened',
            ]));

        $this->sendMessageEvent->setCaseFlowHandler($caseHandler);
        $this->sendMessageEvent->setEmailHandler($emailHandler);

        $flowData = [
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'anc7832jd2387hd23',
            'bpmn_id' => 'aosijdi9qwdj',
            'id' => 'caseidja9823ju89d',
        ];

        $this->sendMessageEvent->setLogger($this->loggerMock);

        $this->sendMessageEvent->sendEmail($flowData);
    }

    public function testSendEmailErrorMessage()
    {
        $this->sendMessageEvent = $this->getMockBuilder('PMSESendMessageEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $eventDefinition = $this->getMockBuilder('PMSEEventDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve'])
            ->getMock();

        $eventDefinition->evn_criteria = [];
        $eventDefinition->evn_params = json_encode([]);

        $this->sendMessageEvent->setEventDefinitionBean($eventDefinition);

        $caseHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['retrieveBean'])
            ->getMock();

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['processEmailsFromJson', 'sendTemplateEmail'])
            ->getMock();

        $emailHandler->expects($this->exactly(1))
            ->method('processEmailsFromJson');

        $emailHandler->expects($this->exactly(1))
            ->method('sendTemplateEmail')
            ->will($this->returnValue([
                'result' => ['something'],
                'ErrorMessage' => 'Some Error happened again',
            ]));

        $this->sendMessageEvent->setCaseFlowHandler($caseHandler);
        $this->sendMessageEvent->setEmailHandler($emailHandler);

        $flowData = [
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'anc7832jd2387hd23',
            'bpmn_id' => 'aosijdi9qwdj',
            'id' => 'caseidja9823ju89d',
        ];

        $this->sendMessageEvent->setLogger($this->loggerMock);

        $this->sendMessageEvent->sendEmail($flowData);
    }
}
