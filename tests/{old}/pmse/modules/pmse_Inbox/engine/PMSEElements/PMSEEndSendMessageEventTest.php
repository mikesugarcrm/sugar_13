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

class PMSEEndSendMessageEventTest extends TestCase
{
    /**
     * @var PMSEElement
     */
    protected $endSendMessageEvent;

    public function testRun()
    {
        $this->endSendMessageEvent = $this->getMockBuilder('PMSEEndSendMessageEvent')
            ->setMethods(['prepareResponse', 'closeCase', 'hasMultipleOpenThreads'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->endSendMessageEvent->expects($this->once())
            ->method('hasMultipleOpenThreads')
            ->will($this->returnValue(true));

        $emailHandler = $this->getMockBuilder('PMSEEmailHandler')
            ->disableOriginalConstructor()
            ->setMethods(['queueEmail'])
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['closeThreadByCaseIndex', 'closeCase'])
            ->getMock();

        $emailHandler->expects($this->once())
            ->method('queueEmail');

        $bean = new stdClass();
        $this->endSendMessageEvent->setCaseFlowHandler($caseFlowHandlerMock);
        $this->endSendMessageEvent->setEmailHandler($emailHandler);

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 2,
            'cas_previous' => 1,
            'bpmn_id' => 'deuh823dj23',
            'cas_sugar_module' => 'Leads',
            'cas_sugar_object_id' => 'ajsdioajodisa2',
        ];

        $this->endSendMessageEvent->run($flowData, $bean, '');
    }
}
