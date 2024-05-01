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

class PMSETerminateEventTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\PMSETerminateEvent|mixed
     */
    public $endEvent;
    /**
     * @var PMSEElement
     */
    protected $roundRobin;

    public function testRun()
    {
        $this->endEvent = $this->getMockBuilder('PMSETerminateEvent')
            ->setMethods(['prepareResponse', 'closeCase'])
            ->disableOriginalConstructor()
            ->getMock();

        $caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods([
                'closeThreadByCaseIndex',
                'closeCase',
                'terminateCaseFlow',
                'retrieveSugarQueryObject',
                'setCloseStatusForThisThread',
                'closeThreadByThreadIndex',
                'retrieveBean',
            ])
            ->getMock();

        $sugarQueryMock = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'from', 'where', 'equals', 'execute'])
            ->getMock();

        $bean = $this->getMockBuilder('SugarBean')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $caseFlowHandlerMock->expects($this->once())
            ->method('retrieveBean')
            ->will($this->returnValue($bean));

        $caseFlowHandlerMock->expects($this->once())
            ->method('retrieveSugarQueryObject')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
            ->method('where')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->exactly(2))
            ->method('equals')
            ->will($this->returnValue($sugarQueryMock));

        $sugarQueryMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue([
                [
                    'cas_thread_index' => 1,
                ],
            ]));

        $this->endEvent->setCaseFlowHandler($caseFlowHandlerMock);

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 2,
        ];

        $this->endEvent->run($flowData, $bean, '');
    }
}
