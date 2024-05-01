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

class PMSEJobQueueHandlerTest extends TestCase
{
    public function testSubmitPMSEJob()
    {
        $jobQueueHandler = $this->getMockBuilder('PMSEJobQueueHandler')
            ->disableOriginalConstructor()
            ->setMethods(['filterData', 'getSchedulersJob', 'getSugarJobQueue', 'getCurrentUser'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug', 'info', 'error', 'warning'])
            ->getMock();

        $schedulersJobMock = $this->getMockBuilder('SchedulersJob')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $sugarJobQueueMock = $this->getMockBuilder('SugarJobQueue')
            ->disableOriginalConstructor()
            ->setMethods(['submitJob'])
            ->getMock();

        $mockJob = '12';

        $sugarJobQueueMock->expects($this->once())
            ->method('submitJob')
            ->will($this->returnValue($mockJob));

        // False in the first arg means no save
        $currentUserMock = SugarTestUserUtilities::createAnonymousUser(false, 0, ['id' => 'user01']);

        $jobQueueHandler->setLogger($loggerMock);
        $jobQueueHandler->method('getCurrentUser')->willReturn($currentUserMock);
        $jobQueueHandler->method('getSchedulersJob')->willReturn($schedulersJobMock);
        $jobQueueHandler->method('getSugarJobQueue')->willReturn($sugarJobQueueMock);

        $params = new stdClass();
        $params->id = 'params01';
        $params->data = [];

        $expectedJob = '12';

        $jobID = $jobQueueHandler->submitPMSEJob($params);
        $this->assertEquals($expectedJob, $jobID);
    }

    public function testExecuteRequest()
    {
        $jobQueueHandler = $this->getMockBuilder('PMSEJobQueueHandler')
            ->disableOriginalConstructor()
            ->setMethods(['filterData', 'preparePreProcessor'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug', 'info', 'error', 'warning'])
            ->getMock();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(['getInstance', 'processRequest'])
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->disableOriginalConstructor()
            ->setMethods(['setCreateThread', 'setExternalAction', 'setBean', 'setArguments'])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('processRequest')
            ->with($requestMock);

        $jobQueueHandler->setLogger($loggerMock);
        $jobQueueHandler->setPreProcessor($preProcessorMock);
        $jobQueueHandler->setRequest($requestMock);

        $params = new stdClass();
        $params->id = 'params01';
        $params->data = [];

        $jobQueueHandler->executeRequest($params);
    }

    public function testFilterData()
    {
        $jobQueueHandler = $this->getMockBuilder('PMSEJobQueueHandler')
            ->disableOriginalConstructor()
            ->setMethods(['preparePreProcessor'])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug', 'info', 'error', 'warning'])
            ->getMock();

        $jobQueueHandler->setLogger($loggerMock);

        $params = [
            'pro_id' => 'pro01',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => 'cas01',
            'some_data' => 'data',
            'additional_data' => 'data',
            'another_data' => 'data',
        ];

        $expectedData = [
            'pro_id' => 'pro01',
            'cas_id' => 1,
            'cas_index' => 2,
            'id' => 'cas01',
        ];

        $result = $jobQueueHandler->filterData($params);

        $this->assertEquals($expectedData, $result);
    }

    //put your tests code here
}
