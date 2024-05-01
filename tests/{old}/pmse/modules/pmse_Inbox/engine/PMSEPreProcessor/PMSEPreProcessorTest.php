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

class PMSEPreProcessorTest extends TestCase
{
    protected $loggerMock;

    /** @var array fields for related dependency objects created during testing */
    private $relatedDependencyFields = [
        'rel_element_id' => 'abc123',
        'deleted' => 0,
        'pro_status' => 'ACTIVE',
        'evn_type' => 'START',
        'evn_module' => 'Accounts',
        'evn_params' => 'new',
    ];

    /** @var array fields for bpm project objects created during testing */
    private $bpmProjectFields = [
        'new_with_id' => 1,
        'prj_status' => 'ACTIVE',
        'prj_module' => 'Accounts',
        'deleted' => 0,
    ];

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PSMELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();
    }

    /**
     * Removes the initial test configurations for each test, for example:
     *     close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        SugarTestBpmUtilities::removeAllCreatedBpmObjects();
        SugarTestHelper::tearDown();
    }

    public function testGetFlowDataListDirect()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = ['idFlow' => '282', 'id' => '676', 'cas_id' => '191'];
        $request->setType('direct');
        $request->setArguments($arguments);

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(['getFlowById'])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('getFlowById')
            ->will($this->returnValue([true]));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals([true], $result);
    }

    public function testGetFlowDataListQueue()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = ['idFlow' => '282', 'id' => '676', 'cas_id' => '191'];
        $request->setArguments($arguments);
        $request->setType('queue');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(['getFlowById'])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('getFlowById')
            ->will($this->returnValue([true]));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals([true], $result);
    }

    public function testGetFlowDataListEngine()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = ['idFlow' => '282', 'id' => '676', 'cas_id' => '191'];
        $request->setArguments($arguments);
        $request->setType('engine');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(['getFlowsByCasId'])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('getFlowsByCasId')
            ->will($this->returnValue([true]));

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals([true], $result);
    }

    public function testGetFlowDataListInvalid()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $arguments = ['idFlow' => '282', 'id' => '676', 'cas_id' => '191'];
        $request->setArguments($arguments);
        $request->setType('invalid_type');

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $preProcessorMock->getFlowDataList($request);

        $this->assertEquals([], $result);
    }

    public function testGetAllEvents()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods([
                'getModuleName',
            ])
            ->getMock();

        $beanMock->expects($this->once())
            ->method('getModuleName')
            ->will($this->returnValue('Accounts'));

        $beanMock->id = 'T1234';

        $beanMock->db = DBManagerFactory::getInstance();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods([
                'getValidLinks',
            ])
            ->getMock();

        $preProcessorMock->expects($this->once())
            ->method('getValidLinks')
            ->will($this->returnValue([]));

        $preProcessorMock->getAllEvents($beanMock);
    }

    /**
     * @covers PMSEPreProcessor::buildLinkedObjectIdQuery
     */
    public function testBuildLinkedObjectIdQuery()
    {
        $beanId = 'case1234';
        $quotedId = DBManagerFactory::getInstance()->quoted($beanId);
        $linkName = 'link1234';
        $idField = 'id1234';
        $idQuery = 'query1234';
        $dummyFrom = DBManagerFactory::getInstance()->getFromDummyTable();
        $expected = "$idField IN ($idQuery UNION ALL SELECT $quotedId id $dummyFrom)";
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();
        $bean->expects($this->once())
            ->method('load_relationship')
            ->with($linkName)
            ->will($this->returnValue(true));
        $bean->id = $beanId;
        $bean->db = DBManagerFactory::getInstance();
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['getQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $link2->expects($this->atLeastOnce())
            ->method('getQuery')
            ->willReturn($idQuery);
        $bean->$linkName = $link2;

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $result = SugarTestReflection::callProtectedMethod(
            $preProcessorMock,
            'buildLinkedObjectIdQuery',
            [$bean, $idField, [$linkName]]
        );
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array array of project run order values
     */
    public function runOrderProvider()
    {
        return [
            [
                'runOrders' => [1, 2, 3, 4],
            ],
            [
                'runOrders' => [null, 4, 2, 3, 1],
            ],
            [
                'runOrders' => [4, 1, 1, 1],
            ],
            [
                'runOrders' => [null, null, null, 1, 2, 3],
            ],
        ];
    }

    /**
     * @dataProvider runOrderProvider
     */
    public function testGetAllEventsSortedByRunOrder($runOrders)
    {
        $account = SugarTestAccountUtilities::createAccount();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        foreach ($runOrders as $index => $value) {
            $projectFields = array_merge($this->bpmProjectFields, ['prj_run_order' => $value]);
            $project = SugarTestBpmUtilities::createBpmObject('Project', 'id' . strval($index), $projectFields);

            $relatedDepFields = array_merge($this->relatedDependencyFields, ['prj_id' => $project->id]);
            SugarTestBpmUtilities::createBpmObject('BpmRelatedDependency', '', $relatedDepFields);
        }

        $results = $preProcessorMock->getAllEvents($account);

        for ($i = 0; $i < count($results) - 1; $i++) {
            $lhval = $results[$i]['prj_run_order'];
            $rhval = $results[$i + 1]['prj_run_order'];
            if ($lhval === $rhval) {
                $this->assertLessThanOrEqual($results[$i + 1]['date_entered'], $results[$i]['date_entered']);
            } elseif ($rhval === null) {
                $this->assertNotNull($lhval);
            } else {
                $this->assertLessThanOrEqual($rhval, $lhval);
            }
        }
    }

    public function testProcessRequestValidRequest()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->setMethods([
                'getBean',
            ])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($beanMock));

        $requestMock->setBean($beanMock);

        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->validate();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods([
                'getFlowDataList',
                'processFlowData',
                'processBean',
                'setSubjectData',
            ])
            ->getMock();
        SugarTestReflection::setProtectedValue($preProcessorMock, 'executedFlowIds', []);

        $flowData = [
            [
                'bpmn_type' => 'bpmEvent',
                'bpmn_id' => 'event_0',
                'cas_id' => 1,
                // Added so that subject data setter doesn't die
                'evn_id' => 'foo',
                'pro_id' => 'bar',
                'prj_run_order' => 1,
            ],
        ];

        // So that the proper method calls are made in the test
        $resultRequest->setFlowData($flowData);

        $preProcessorMock->expects($this->any())
            ->method('processBean')
            ->will($this->returnValue($beanMock));

        $preProcessorMock->expects($this->any())
            ->method('getFlowDataList')
            ->will($this->returnValue($flowData));

        $preProcessorMock->expects($this->any())
            ->method('processFlowData')
            ->will($this->returnValue($flowData[0]));

        $preProcessorMock->expects($this->once())
            ->method('setSubjectData')
            ->with($this->equalTo($flowData));

        $validatorMock = $this->getMockBuilder('PMSEValidator')
            ->disableOriginalConstructor()
            ->setMethods(['validateRequest'])
            ->getMock();

        $validatorMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue($resultRequest));

        $executerMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['runEngine'])
            ->getMock();

        $executerMock->expects($this->once())
            ->method('runEngine');

        $preProcessorMock->setExecuter($executerMock);
        $preProcessorMock->setLogger($this->loggerMock);
        $preProcessorMock->setValidator($validatorMock);

        $preProcessorMock->processRequest($requestMock);
    }

    public function testProcessRequestInvalidRequest()
    {
        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock = $this->getMockBuilder('PMSERequest')
            ->setMethods([
                'getBean',
            ])
            ->getMock();

        $requestMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($beanMock));

        $requestMock->setBean($beanMock);

        $resultRequest = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $resultRequest->invalidate();

        $preProcessorMock = $this->getMockBuilder('PMSEPreProcessor')
            ->disableOriginalConstructor()
            ->setMethods([
                'getFlowDataList',
                'processFlowData',
                'processBean',
                'setSubjectData',
            ])
            ->getMock();
        SugarTestReflection::setProtectedValue($preProcessorMock, 'executedFlowIds', []);

        $flowData = [
            [
                'bpmn_type' => 'bpmEvent',
                'bpmn_id' => 'event_0',
                'cas_id' => 1,
                // Added so that subject data setter doesn't die
                'evn_id' => 'foo',
                'pro_id' => 'bar',
                'prj_run_order' => 1,
            ],
        ];
        $preProcessorMock->expects($this->any())
            ->method('processBean')
            ->will($this->returnValue($beanMock));

        $preProcessorMock->expects($this->any())
            ->method('getFlowDataList')
            ->will($this->returnValue($flowData));

        $preProcessorMock->expects($this->any())
            ->method('processFlowData')
            ->will($this->returnValue($flowData[0]));

        $preProcessorMock->expects($this->never())
            ->method('setSubjectData');

        $validatorMock = $this->getMockBuilder('PMSEValidator')
            ->disableOriginalConstructor()
            ->setMethods(['validateRequest'])
            ->getMock();

        $validatorMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue($resultRequest));

        $executerMock = $this->getMockBuilder('PMSEExecuter')
            ->disableOriginalConstructor()
            ->setMethods(['runEngine'])
            ->getMock();

        $preProcessorMock->setExecuter($executerMock);
        $preProcessorMock->setLogger($this->loggerMock);
        $preProcessorMock->setValidator($validatorMock);

        $preProcessorMock->processRequest($requestMock);
    }
}
