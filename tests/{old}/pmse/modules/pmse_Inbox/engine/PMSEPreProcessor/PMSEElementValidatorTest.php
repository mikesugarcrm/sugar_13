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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

class PMSEElementValidatorTest extends TestCase
{
    public function eventValidatorTypeProvider()
    {
        return [
            [
                'eventType' => 'START',
                'eventMethod' => 'validateStartEvent',
            ],
            [
                'eventType' => 'INTERMEDIATE',
                'eventMethod' => 'validateIntermediateEvent',
            ],
        ];
    }

    /**
     * Tests START and INTERMEDIATE event validation
     * @param string $eventType The type to test
     * @param string $eventMethod The method for the type
     * @dataProvider eventValidatorTypeProvider
     */
    public function testValidateRequestForEvent($eventType, $eventMethod)
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods([
                'processExternalAction',
                'processCreateThread',
                'validateStartEvent',
                'validateIntermediateEvent',
                'getLogger',
            ])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug', 'info'])
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->disableOriginalConstructor()
            ->setMethods(['getFlowData', 'getBean', 'setExternalAction', 'setCreateThread'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getFlowData')
            ->will($this->returnValue([
                'evn_type' => $eventType,
                'rel_element_module' => 'Accounts',
                'rel_process_module' => 'Accounts',
                'rel_element_relationship' => '',
            ]));

        $bean = BeanFactory::getBean('Accounts');
        $requestMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($bean));

        $elementValidatorMock->expects($this->once())
            ->method($eventMethod);

        $elementValidatorMock->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($loggerMock));

        $elementValidatorMock->validateRequest($requestMock);
    }

    public function testValidateRequestNoValidElement()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods([
                'processExternalAction',
                'processCreateThread',
                'validateStartEvent',
                'validateIntermediateEvent',
                'getLogger',
            ])
            ->getMock();

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug', 'info'])
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->disableOriginalConstructor()
            ->setMethods(['getFlowData', 'getBean', 'setBean', 'setExternalAction', 'setCreateThread'])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getFlowData')
            ->will($this->returnValue(['evn_type' => 'NO_VALID']));

        $bean = BeanFactory::getBean('Accounts');
        $requestMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($bean));

        $elementValidatorMock->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($loggerMock));

        $result = $elementValidatorMock->validateRequest($requestMock);
        $this->assertEquals($requestMock, $result);
    }

    public function testIdentifyElementStatusIfRunning()
    {
        $mockData = ['cas_id' => 1, 'cas_index' => 2];
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $elementValidatorMock->identifyElementStatus($mockData);
        $this->assertEquals('RUNNING', $result);
    }

    public function testIdentifyElementStatusIfNew()
    {
        $mockData = [];
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $elementValidatorMock->identifyElementStatus($mockData);
        $this->assertEquals('NEW', $result);
    }

    public function testIdentifyEventActionIfNotRelated()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Leads';
        $mockData = ['rel_process_module' => 'Leads',
            'rel_element_relationship' => 'Leads',
            'rel_element_module' => 'Leads',
        ];
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $elementValidatorMock->identifyEventAction($mockData);

        $this->assertEquals('EVALUATE_MAIN_MODULE', $result);
    }

    public function testIdentifyEventActionIfRelated()
    {
        $bean = new stdClass();
        $bean->module_dir = 'Notes';
        $mockData = ['rel_process_module' => 'Leads',
            'rel_element_relationship' => 'leads_notes',
            'rel_element_module' => 'Notes',
        ];
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $elementValidatorMock->identifyEventAction($mockData);

        $this->assertEquals('EVALUATE_RELATED_MODULE', $result);
    }

    public function testProcessExternalActionIfRunning()
    {
        $bean = new stdClass();
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['identifyElementStatus', 'identifyEventAction'])
            ->getMock();

        $elementValidatorMock->expects($this->once())
            ->method('identifyElementStatus')
            ->will($this->returnValue('RUNNING'));

        $mockData = ['evn_type' => 'INTERMEDIATE'];

        $elementValidatorMock->expects($this->once())
            ->method('identifyEventAction');

        $elementValidatorMock->processExternalAction($mockData);
    }

    public function testProcessExternalActionIfNotRunning()
    {
        $bean = new stdClass();
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['identifyElementStatus', 'identifyEventAction'])
            ->getMock();

        $elementValidatorMock->expects($this->any())
            ->method('identifyElementStatus')
            ->will($this->returnValue(false));

        $mockData = ['evn_type' => 'START'];
        $result = $elementValidatorMock->processExternalAction($mockData);
        $this->assertEquals(false, $result);
    }

    public function testProcessCreateThreadIfNew()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['identifyElementStatus'])
            ->getMock();

        $elementValidatorMock->expects($this->once())
            ->method('identifyElementStatus')
            ->will($this->returnValue('NEW'));

        $flowData = [];
        $result = $elementValidatorMock->processCreateThread($flowData);
        $this->assertEquals(true, $result);
    }

    public function testProcessCreateThreadIfNotNew()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['identifyElementStatus'])
            ->getMock();

        $elementValidatorMock->expects($this->once())
            ->method('identifyElementStatus')
            ->will($this->returnValue('INTERMEDIATE'));

        $flowData = [];
        $result = $elementValidatorMock->processCreateThread($flowData);
        $this->assertEquals(false, $result);
    }

    public function testIsCaseDuplicated()
    {
        $beanMock = new stdClass();
        $beanMock->id = 'bean123';
        $beanMock->module_name = 'Leads';
        $flowData = ['pro_id' => 'pro123'];

        $elementValidator = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['getSugarQueryObject', 'getLogger', 'getBeanFlow'])
            ->getMock();

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $sugarQueryObjectMock = $this->getMockBuilder('SugarQuery')
            ->setMethods(['from', 'distinct', 'where', 'equals', 'query', 'getOne'])
            ->getMock();

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->any())
            ->method('equals')
            ->will($this->returnValue($sugarQueryObjectMock));

        $arrayDupli = ['id' => '999000'];

        $sugarQueryObjectMock->expects($this->any())
            ->method('getOne')
            ->will($this->returnValue(null));

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $elementValidator->expects($this->any())
            ->method('getBeanFlow')
            ->will($this->returnValue($beanMock));

        $elementValidator->expects($this->any())
            ->method('getSugarQueryObject')
            ->will($this->returnValue($sugarQueryObjectMock));

        $elementValidator->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($loggerMock));

        $result = $elementValidator->isCaseDuplicated($beanMock, $flowData);
        $this->assertEquals(true, $result);
    }

    public function testIsCaseDuplicatedNot()
    {
        $beanMock = new stdClass();
        $beanMock->id = 'bean123';
        $beanMock->module_name = 'Leads';
        $flowData = ['pro_id' => 'pro123'];

        $elementValidator = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['getSugarQueryObject', 'getLogger', 'getBeanFlow'])
            ->getMock();

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $sugarQueryObjectMock = $this->getMockBuilder('SugarQuery')
            ->setMethods(['from', 'distinct', 'where', 'equals', 'query', 'getOne'])
            ->getMock();

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('from')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->atLeastOnce())
            ->method('where')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->any())
            ->method('equals')
            ->will($this->returnValue($sugarQueryObjectMock));

        $sugarQueryObjectMock->expects($this->any())
            ->method('getOne')
            ->will($this->returnValue(false));

        $loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $elementValidator->expects($this->any())
            ->method('getBeanFlow')
            ->will($this->returnValue($beanMock));

        $elementValidator->expects($this->any())
            ->method('getSugarQueryObject')
            ->will($this->returnValue($sugarQueryObjectMock));

        $elementValidator->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($loggerMock));

        $result = $elementValidator->isCaseDuplicated($beanMock, $flowData);
        $this->assertEquals(false, $result);
    }

    public function testValidateStartEventNewRecord()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRecord', 'isCaseDuplicated'])
            ->getMock();

        $elementValidatorMock->expects($this->once())
            ->method('isNewRecord')
            ->will($this->returnValue(true));

        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');

        $flowDataMock = ['evn_params' => 'new'];
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }

    public function testValidateStartEventUpdatedRecord()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRecord', 'isCaseDuplicated'])
            ->getMock();

        $elementValidatorMock->expects($this->atLeastOnce())
            ->method('isNewRecord')
            ->will($this->returnValue(false));

        $elementValidatorMock->expects($this->once())
            ->method('isCaseDuplicated')
            ->will($this->returnValue(false));

        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');

        $flowDataMock = ['evn_params' => 'updated'];
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }

    public function testValidateStartEventNewUpdatedRecord()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRecord', 'isCaseDuplicated'])
            ->getMock();

        $elementValidatorMock->expects($this->atLeastOnce())
            ->method('isNewRecord')
            ->will($this->returnValue(false));

        $elementValidatorMock->expects($this->exactly(2))
            ->method('isCaseDuplicated')
            ->will($this->returnValue(false));

        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');

        $flowDataMock = ['evn_params' => 'newfirstupdated'];
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());

        $flowDataMock = ['evn_params' => 'newallupdates'];
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }

    public function testValidateStartEventFailed()
    {
        $elementValidatorMock = $this->getMockBuilder('PMSEElementValidator')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRecord', 'isCaseDuplicated'])
            ->getMock();

        $elementValidatorMock->expects($this->atLeastOnce())
            ->method('isNewRecord')
            ->will($this->returnValue(false));

        $elementValidatorMock->expects($this->once())
            ->method('isCaseDuplicated')
            ->will($this->returnValue(true));

        $beanMock = new stdClass();
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');

        $flowDataMock = ['evn_params' => 'updated'];
        $elementValidatorMock->validateStartEvent($beanMock, $flowDataMock, $request);
        $this->assertEquals(false, $request->isValid());
    }
}
