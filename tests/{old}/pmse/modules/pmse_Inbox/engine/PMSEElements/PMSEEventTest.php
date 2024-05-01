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
use Doctrine\DBAL\Connection;

class PMSEEventTest extends TestCase
{
    /**
     * @var type
     */
    protected $caseFlowHandlerMock;

    /**
     * @var type
     */
    protected $flowMock;

    /**
     * @var type
     */
    protected $caseFlowMock;

    /**
     * @var type
     */
    protected $gatewayMock;

    /**
     * @var PMSEElement
     */
    protected $event;

    /**
     * @var array threads for test
     */
    protected static $threads = [];

    public static function setUpBeforeClass(): void
    {
        // test threads for testCheckIfExistEventBased
        $thread1 = BeanFactory::newBean('pmse_BpmThread');
        $thread1->cas_id = 1;
        $thread1->cas_thread_index = 1;
        $thread1->cas_thread_parent = 1;
        $thread1->cas_flow_index = 1;
        $thread1->save();
        self::$threads[] = $thread1->id;

        $thread2 = BeanFactory::newBean('pmse_BpmThread');
        $thread2->cas_id = 1;
        $thread2->cas_thread_index = 2;
        $thread2->cas_thread_parent = 1;
        $thread2->cas_flow_index = 1;
        $thread2->save();
        self::$threads[] = $thread2->id;
    }

    public static function tearDownAfterClass(): void
    {
        if (count(self::$threads)) {
            $conn = DBManagerFactory::getConnection();
            $query = 'DELETE FROM pmse_bpm_thread WHERE id IN (?)';
            $conn->executeUpdate($query, [self::$threads], [Connection::PARAM_STR_ARRAY]);
        }
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->flowMock = $this->getMockBuilder('pmse_BpmnFlow')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['get_list', 'retrieve_by_string_fields'])
            ->getMock();

        $this->caseFlowMock = $this->getMockBuilder('pmse_BpmFlow')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['get_list', 'retrieve_by_string_fields'])
            ->getMock();

        $this->caseFlowHandlerMock = $this->getMockBuilder('PMSECaseFlowHandler')
            ->disableOriginalConstructor()
            ->setMethods(['closeThreadByThreadIndex', 'retrieveBean', 'closeFlow'])
            ->getMock();

        $this->gatewayMock = $this->getMockBuilder('pmse_BpmnGateway')
            ->disableAutoload()
            ->setMethods(['retrieve_by_string_fields', 'get_list'])
            ->getMock();
    }

    public function testCheckIfUsesAnEventBasedGatewayIfFound()
    {
        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(['retrieveBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = [
            'list' => [
                $beanCase,
            ],
        ];

        $this->caseFlowMock->expects($this->any())
            ->method('get_list')
            ->will($this->returnValue($resultCaseMock));

        $flowBean = new stdClass();
        $flowBean->flo_element_origin_type = 'bpmnGateway';
        $flowBean->flo_element_origin = 'abc890';
        $resultFlowMock = [
            'list' => [
                $flowBean,
            ],
        ];

        $this->flowMock->expects($this->any())
            ->method('get_list')
            ->will($this->returnValue($resultFlowMock));


        $gatewayBean = new stdClass();
        $gatewayBean->gat_type = 'EVENTBASED';

        $this->caseFlowHandlerMock->expects($this->exactly(3))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmFlow',
                ],
                [
                    'pmse_BpmnFlow',
                ],
                [
                    'pmse_BpmnGateway',
                    'abc890',
                ]
            )
            ->willReturnOnConsecutiveCalls($this->caseFlowMock, $this->flowMock, $gatewayBean);

        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);

        $casID = 1;
        $casIndexPrevious = 1;

        $result = $this->event->checkIfUsesAnEventBasedGateway($casID, $casIndexPrevious);
        $expected = true;

        $this->assertEquals($expected, $result);
    }

    public function testCheckIfUsesAnEventBasedGatewayIfNotFound()
    {
        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(['retrieveBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = [
            'list' => [
                $beanCase,
            ],
        ];

        $this->caseFlowMock->expects($this->any())
            ->method('get_list')
            ->will($this->returnValue($resultCaseMock));

        $flowBean = new stdClass();
        $flowBean->flo_element_origin_type = 'bpmnGateway';
        $flowBean->flo_element_origin = 'abc890';
        $resultFlowMock = [
            'list' => [
                $flowBean,
            ],
        ];

        $this->flowMock->expects($this->any())
            ->method('get_list')
            ->will($this->returnValue($resultFlowMock));


        $gatewayBean = new stdClass();
        $gatewayBean->gat_type = 'EXCLUSIVE';

        $this->caseFlowHandlerMock->expects($this->exactly(3))
            ->method('retrieveBean')
            ->withConsecutive(
                [
                    'pmse_BpmFlow',
                ],
                [
                    'pmse_BpmnFlow',
                ],
                [
                    'pmse_BpmnGateway',
                    'abc890',
                ]
            )
            ->willReturnOnConsecutiveCalls($this->caseFlowMock, $this->flowMock, $gatewayBean);

        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);

        $casID = 1;
        $casIndexPrevious = 1;

        $result = $this->event->checkIfUsesAnEventBasedGateway($casID, $casIndexPrevious);
        $expected = false;

        $this->assertEquals($expected, $result);
    }

//
    public function testCheckIfExistEventBased()
    {
        $this->event = $this->getMockBuilder('PMSEEvent')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $beanCase = new stdClass();
        $beanCase->cas_id = 1;
        $beanCase->cas_index = 1;
        $beanCase->cas_previous = 1;
        $beanCase->bpmn_type = 'bpmnFlow';
        $beanCase->bpmn_id = 'j89s239s823d';

        $resultCaseMock = [
            'list' => [
                $beanCase,
            ],
        ];

        $this->caseFlowMock->expects($this->atLeastOnce())
            ->method('get_list')
            ->will($this->returnValue($resultCaseMock));

        $this->caseFlowMock->expects($this->atLeastOnce())
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue($beanCase));

        $this->caseFlowMock->cas_id = 1;
        $this->caseFlowMock->cas_index = 1;
        $this->caseFlowMock->cas_previous = 0;
        $this->caseFlowMock->bpmn_type = 'bpmnFlow';
        $this->caseFlowMock->bpmn_id = 'j89s239s823d';

        $this->caseFlowHandlerMock->expects($this->atLeastOnce())
            ->method('retrieveBean')
            ->will($this->returnValue($this->caseFlowMock));

        $dbHandlerMock = $this->getMockBuilder('DBHandler')
            ->setMethods(['quoted', 'getConnection'])
            ->getMock();

        $this->caseFlowHandlerMock->expects($this->exactly(2))
            ->method('closeThreadByThreadIndex');

        $this->event->setCaseFlowHandler($this->caseFlowHandlerMock);
        $this->event->setDbHandler($dbHandlerMock);

        $casId = 1;
        $casIndex = 5;
        $isEventBased = true;

        $dbHandlerMock->expects($this->any())
            ->method('quoted')
            ->willReturnCallback(function ($str) {
                return $GLOBALS['db']->quoted($str);
            });
        $dbHandlerMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->will($this->returnValue($GLOBALS['db']->getConnection()));

        $expected = true;
        $result = $this->event->checkIfExistEventBased($casId, $casIndex, $isEventBased);
        $this->assertEquals($expected, $result);
    }
}
