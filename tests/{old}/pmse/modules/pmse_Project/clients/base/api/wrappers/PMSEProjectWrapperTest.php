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

class PMSEProjectWrapperTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    public $mockRelatedDependency;
    protected $projectWrapper;
    protected $mockProject;
    protected $mockProcess;
    protected $mockDiagram;
    protected $mockProcessDefinition;
    protected $mockActivityBean;
    protected $mockArtifactBean;
    protected $mockEventBean;
    protected $mockGatewayBean;
    protected $mockFlowBean;
    protected $mockParticipantBean;
    protected $mockLaneBean;
    protected $mockLanesetBean;
    protected $mockDataBean;
    protected $mockActivityDefinitionBean;
    protected $mockEventDefinitionBean;
    protected $mockBoundBean;
    protected $mockDynaformBean;
    //    protected $mockApi;
    protected $args;

    public static function setUpBeforeClass(): void
    {
        //        require_once 'modules/ProcessMaker/ADAMWrapperProject.php';
    }

    protected function setUp(): void
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['getBean'])
            ->getMock();

        // $this->mockProject = $this->createMock('BpmnProject');
        $this->mockProject = $this->getMockBuilder('pmse_BpmnProject')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['get_full_list', '_get', '_put', '_post', '_delete', 'retrieve_by_string_fields', 'save', 'getPrimaryFieldUID'])
            ->getMock();

        $this->mockDiagram = $this->getMockBuilder('pmse_BpmnDiagram')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockProcess = $this->getMockBuilder('pmse_BpmnProcess')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockProcessDefinition = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockActivityBean = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockArtifactBean = $this->getMockBuilder('pmse_BpmnArtifact')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockEventBean = $this->getMockBuilder('pmse_BpmnEvent')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockGatewayBean = $this->getMockBuilder('pmse_BpmnGateway')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockFlowBean = $this->getMockBuilder('pmse_BpmnFlow')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockParticipantBean = $this->getMockBuilder('pmse_BpmnParticipant')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->mockLaneBean = $this->getMockBuilder('pmse_BpmnLane')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockLanesetBean = $this->getMockBuilder('pmse_BpmnLaneset')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDataBean = $this->getMockBuilder('pmse_BpmnData')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockActivityDefinitionBean = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockEventDefinitionBean = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockBoundBean = $this->getMockBuilder('pmse_BpmnBound')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockDynaformBean = $this->getMockBuilder('pmse_BpmDynaForm')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $this->mockRelatedDependency = $this->getMockBuilder('pmse_BpmRelatedDependency')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->getMock();
        $this->projectWrapper->setProject($this->mockProject);
        $this->projectWrapper->setDiagram($this->mockDiagram);
        $this->projectWrapper->setProcess($this->mockProcess);
        $this->projectWrapper->setProcessDefinition($this->mockProcessDefinition);
        $this->projectWrapper->setActivityBean($this->mockActivityBean);
        $this->projectWrapper->setEventBean($this->mockEventBean);
        $this->projectWrapper->setArtifactBean($this->mockArtifactBean);
        //        $this->projectWrapper->setDataBean($this->mockDataBean);
        $this->projectWrapper->setFlowBean($this->mockFlowBean);
        $this->projectWrapper->setGatewayBean($this->mockGatewayBean);
        //        $this->projectWrapper->setLaneBean($this->mockLaneBean);
        //        $this->projectWrapper->setLanesetBean($this->mockLanesetBean);
        //        $this->projectWrapper->setParticipantBean($this->mockParticipantBean);
        //        $this->projectWrapper->setDynaformBean($this->mockDynaformBean);
        //        $this->projectWrapper->setRelatedDependencyBean($this->mockRelatedDependency);
        $this->projectWrapper->setActivityDefinitionBean($this->mockActivityDefinitionBean);
        $this->projectWrapper->setEventDefinitionBean($this->mockEventDefinitionBean);
        $this->projectWrapper->setBoundBean($this->mockBoundBean);
        //        $this->mockApi = $this->getMockBuilder('ServiceBase')
        //                ->disableOriginalConstructor()
        //                ->setMethods(null)
        //                ->getMock();
        $this->args = [];
    }

    public function testGetDiagram()
    {
        $this->assertInstanceOf('pmse_BpmnDiagram', $this->projectWrapper->getDiagram());
    }

    public function testGetProcess()
    {
        $this->assertInstanceOf('pmse_BpmnProcess', $this->projectWrapper->getProcess());
    }

    public function testGetProcessDefinition()
    {
        $this->assertInstanceOf('pmse_BpmProcessDefinition', $this->projectWrapper->getProcessDefinition());
    }

    public function testRetrieveProject()
    {
        // First Test
        $this->mockProject->id = 1;
        $this->mockProject->name = 'project_name';
        $this->mockProject->description = 'project_desc';
        $this->mockProject->prj_uid = '2193798123';
        $this->mockProject->fetched_row = [
            'id' => 1,
            'name' => 'project_name',
            'description' => 'project_desc',
            'prj_uid' => '2193798123',
        ];
        $this->mockProject->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockProject));

        $this->mockDiagram->id = 1;
        $this->mockDiagram->dia_uid = '2737981231';
        $this->mockDiagram->fetched_row = [
            'id' => 1,
            'prj_uid' => '2737981231',
        ];

        $this->mockProcess->id = 1;
        $this->mockProcess->pro_uid = '2737909787';
        $this->mockProcess->fetched_row = [
            'id' => 1,
            'prj_uid' => '2737909787',
        ];
        $this->mockProcess->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockProcess));

        $this->mockProcessDefinition->id = 1;
        $this->mockProcessDefinition->pro_uid = '2737919822';
        $this->mockProcessDefinition->fetched_row = [
            'id' => 1,
            'prj_uid' => '2737919822',
            'pro_locked_variables' => '',
            'pro_terminate_variables' => '',
        ];
        $this->mockProcessDefinition->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockProcessDefinition));

        //$this->mockApi = $this->createMock('ServiceBase');
        $this->args = ['id' => '2193798123'];

        $result = $this->projectWrapper->retrieveProject($this->args['id']);
        $this->assertIsArray($result);
        $this->assertEquals(2, count($result));

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('project', $result);
        $this->assertNotEmpty($result['project']);
    }

    public function testRetrieveProjectInexistent()
    {
        // First Test
        $this->mockProject->id = 1;
        $this->mockProject->name = 'project_name';
        $this->mockProject->description = 'project_desc';
        $this->mockProject->prj_uid = '2193798123';
        $this->mockProject->fetched_row = [
            'id' => 1,
            'name' => 'project_name',
            'description' => 'project_desc',
            'prj_uid' => '2193798123',
        ];
        $this->mockProject->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue(false));

        $args = ['id' => 'pro01'];

        $result = $this->projectWrapper->retrieveProject($args['id']);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
    }

//    public function testNotFoundGetProject()
//    {
//        // Second Test
//        $this->mockProject->expects($this->exactly(1))
//                ->method('retrieve_by_string_fields')
//                ->with($this->isType('array'))
//                ->will($this->returnValue(null));
//        $this->mockApi = $this->createMock('ServiceBase');
//        $this->args = array('id' => '2193798123');
//        $result = $this->projectWrapper->_get($this->args);
//        $this->assertIsArray($result);
//        $this->assertEquals(1, count($result));
//        $this->assertArrayHasKey('success', $result);
//        $this->assertArrayNotHasKey('project', $result);
//    }

    public function testUpdateProject()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'updateDiagram', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProject->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockProject));
        $this->mockProject->id = 1;
        $this->mockProject->prj_uid = '2193798123';
        $this->mockProject->fetched_row = [
            'id' => 1,
            'prj_uid' => '2193798123',
        ];

        $this->mockProcess->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockProcess));
        $this->mockProcess->id = 1;

        $this->mockDiagram->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockDiagram));
        $this->mockDiagram->id = 1;

        $this->projectWrapper->setProject($this->mockProject);
        $this->projectWrapper->setDiagram($this->mockDiagram);
        $this->projectWrapper->setProcess($this->mockProcess);
        $this->projectWrapper->setProcessDefinition($this->mockProcessDefinition);

        $this->args = ['id' => '2193798123', 'flows' => [], 'data' => []];

        $result = $this->projectWrapper->updateProject(1, $this->args);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(true, $result['success']);
    }

    public function testUpdateProjectInexistent()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'updateDiagram', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockProject->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue(null));
        $this->mockProject->id = 1;
        $this->mockProject->prj_uid = '2193798123';
        $this->mockProject->fetched_row = [
            'id' => 1,
            'prj_uid' => '2193798123',
        ];

        $this->projectWrapper->setProject($this->mockProject);

        $this->args = ['id' => '2193798123', 'flows' => [], 'data' => []];

        $result = $this->projectWrapper->updateProject(1, $this->args);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals(false, $result['success']);
    }

    public function testGetProjectDiagram()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods([
                'getBean',
                'getSelectRows',
                'sanitizeKeyFields',
                'getElementUid',
                'getEntityUid',
                'initWrapper',
            ])
            ->getMock();

        $prjID = 1;
        $this->mockDiagram->id = 1;
        $this->mockDiagram->dia_uid = '2737981231';
        $this->mockDiagram->fetched_row = [
            'id' => 1,
            'prj_id' => 1,
            'prj_uid' => '2737981231',
        ];

        $this->mockDiagram->expects($this->atLeastOnce())
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockDiagram));

        $this->projectWrapper->expects($this->at(0))
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        [
                            'act_default_flow' => '1276',
                            'bou_uid' => '123asd',
                            'name' => 'some activity name',
                        ],
                    ],
                ]
            ));

        $this->mockFlowBean->expects($this->atLeastOnce())
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue($this->mockFlowBean));
        $this->mockFlowBean->flo_uid = '12as';

        $this->projectWrapper->expects($this->at(2))
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        [
                            'evn_attached_to' => '4123asd',
                            'evn_cancel_activity' => '456qwe',
                            'evn_activity_ref' => '789rty',
                            'bou_uid' => '123asd',
                            'name' => 'some event name',
                        ],
                    ],
                ]
            ));


        $this->projectWrapper->expects($this->at(4))
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        [
                            'gat_default_flow' => '789rty',
                            'bou_uid' => '123asd',
                            'name' => 'some gateway name',
                        ],
                    ],
                ]
            ));

        $this->projectWrapper->expects($this->at(6))
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        [
                            'bou_uid' => '123asd',
                            'name' => 'some element name',
                        ],
                    ],
                ]
            ));

        $this->projectWrapper->expects($this->at(8))
            ->method('getSelectRows')
            ->will($this->returnValue(
                [
                    'rowList' => [
                        [
                            'bou_uid' => '123asd',
                            'name' => 'some element name',
                            'flo_element_origin_type' => 'bpmnEvent',
                            'flo_element_origin' => 'event01',
                            'flo_element_dest_type' => 'bpmnActivity',
                            'flo_element_dest' => 'act01',
                            'flo_state' => 'ACTIVE',
                        ],
                    ],
                ]
            ));

        $this->projectWrapper->expects($this->at(9))
            ->method('sanitizeKeyFields')
            ->will($this->returnValue(
                [
                    'bou_uid' => '123asd',
                    'name' => 'some element name',
                    'flo_element_origin_type' => 'bpmnEvent',
                    'flo_element_origin' => 'event01',
                    'flo_element_dest_type' => 'bpmnActivity',
                    'flo_element_dest' => 'act01',
                    'flo_state' => 'ACTIVE',
                ]
            ));


        $this->projectWrapper->setProject($this->mockProject);
        $this->projectWrapper->setDiagram($this->mockDiagram);
        $this->projectWrapper->setProcess($this->mockProcess);
        $this->projectWrapper->setProcessDefinition($this->mockProcessDefinition);
        $this->projectWrapper->setActivityBean($this->mockActivityBean);
        $this->projectWrapper->setEventBean($this->mockEventBean);
        $this->projectWrapper->setArtifactBean($this->mockArtifactBean);
        $this->projectWrapper->setFlowBean($this->mockFlowBean);
        $this->projectWrapper->setGatewayBean($this->mockGatewayBean);
        $this->projectWrapper->setActivityDefinitionBean($this->mockActivityDefinitionBean);
        $this->projectWrapper->setEventDefinitionBean($this->mockEventDefinitionBean);
        $this->projectWrapper->setBoundBean($this->mockBoundBean);

        $result = $this->projectWrapper->getProjectDiagram($prjID);
        $this->assertIsArray($result);
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey('activities', $result[0]);
        $this->assertArrayHasKey('flows', $result[0]);
        $this->assertArrayHasKey('artifacts', $result[0]);
        $this->assertArrayHasKey('events', $result[0]);
        $this->assertArrayHasKey('gateways', $result[0]);
    }

    public function testGetProjectDiagramIfDoesntExist()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods([
                'getBean',
                'getSelectRows',
                'sanitizeKeyFields',
                'getElementUid',
                'getEntityUid',
                'initWrapper',
            ])
            ->getMock();

        $this->mockDiagram->expects($this->atLeastOnce())
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'))
            ->will($this->returnValue(null));

        $this->projectWrapper->setProject($this->mockProject);
        $this->projectWrapper->setDiagram($this->mockDiagram);
        $this->projectWrapper->setProcess($this->mockProcess);
        $this->projectWrapper->setProcessDefinition($this->mockProcessDefinition);
        $this->projectWrapper->setActivityBean($this->mockActivityBean);
        $this->projectWrapper->setEventBean($this->mockEventBean);
        $this->projectWrapper->setArtifactBean($this->mockArtifactBean);
        $this->projectWrapper->setFlowBean($this->mockFlowBean);
        $this->projectWrapper->setGatewayBean($this->mockGatewayBean);
        $this->projectWrapper->setActivityDefinitionBean($this->mockActivityDefinitionBean);
        $this->projectWrapper->setEventDefinitionBean($this->mockEventDefinitionBean);
        $this->projectWrapper->setBoundBean($this->mockBoundBean);

        $prjID = 'someID';

        $result = $this->projectWrapper->getProjectDiagram($prjID);
        $this->assertIsArray($result);
        $this->assertEquals(2, count($result));
    }

    public function testUpdateDiagramCreateFlows()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBean',
                    'getClassEntity',
                    'getEntityData',
                    'getSelectRows',
                    'sanitizeKeyFields',
                    'getElementUid',
                    'getEntityUid',
                    'updateDiagramElements',
                    'initWrapper',
                ]
            )
            ->getMock();

        $diagramArray = [
            'flows' => [
                [
                    'action' => 'create',
                    'flo_element_origin_type' => 'bpmnActivity',
                    'flo_element_origin' => 'act001',
                    'flo_element_dest_type' => 'bpmnActivity',
                    'flo_element_dest' => 'act002',
                    'flo_state' => [
                        ['x' => 12.23, 'y' => 12.24],
                        ['x' => 29.54, 'y' => 29.55],
                    ],
                ],
            ],
            'gateways' => [],
            'activities' => [],
            'events' => [],
        ];

        $originMock = [
            'bean' => 'BpmnActivity',
            'uid_field' => 'act_uid',
        ];

        $originBeanMock = $this->getMockBuilder('BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $originBeanMock->id = 'act001';

        $this->projectWrapper->expects($this->any())
            ->method('getEntityData')
            ->will($this->returnValue('SomeEntityData'));

        $this->projectWrapper->expects($this->at(5))
            ->method('getClassEntity')
            ->will($this->returnValue($originMock));

        $this->projectWrapper->expects($this->at(6))
            ->method('getBean')
            ->will($this->returnValue($originBeanMock));

        $this->projectWrapper->expects($this->at(7))
            ->method('getClassEntity')
            ->will($this->returnValue($originMock));

        $destinationBeanMock = $this->getMockBuilder('BpmnEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $destinationBeanMock->id = 'evn001';

        $this->projectWrapper->expects($this->at(8))
            ->method('getBean')
            ->will($this->returnValue($destinationBeanMock));

        $flowBeanMock = $this->getMockBuilder('pmse_BpmFlowBean')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $flowBeanMock->id = 'mockFlow01';

        $this->projectWrapper->setFlowBean($flowBeanMock);

        $keysArray = [];

        $this->projectWrapper->updateDiagram($diagramArray, $keysArray);
    }

    public function testUpdateDiagramUpdateFlows()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBean',
                    'getClassEntity',
                    'getEntityData',
                    'getSelectRows',
                    'sanitizeKeyFields',
                    'getElementUid',
                    'getEntityUid',
                    'updateDiagramElements',
                    'initWrapper',
                ]
            )
            ->getMock();

        $diagramArray = [
            'flows' => [
                [
                    'action' => 'update',
                    'flo_element_origin_type' => 'bpmnActivity',
                    'flo_element_origin' => 'act001',
                    'flo_element_dest_type' => 'bpmnActivity',
                    'flo_element_dest' => 'act002',
                    'flo_state' => [
                        ['x' => 12.23, 'y' => 12.24],
                        ['x' => 29.54, 'y' => 29.55],
                    ],
                ],
            ],
            'gateways' => [],
            'activities' => [],
            'events' => [],
        ];

        $originBeanMock = $this->getMockBuilder('BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $originBeanMock->id = 'act001';

        $this->projectWrapper->expects($this->any())
            ->method('getEntityData')
            ->will($this->returnValue('SomeEntityData'));

        $this->projectWrapper->expects($this->at(6))
            ->method('getBean')
            ->will($this->returnValue($originBeanMock));

        $destinationBeanMock = $this->getMockBuilder('BpmnEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $destinationBeanMock->id = 'evn001';

        $this->projectWrapper->expects($this->at(8))
            ->method('getBean')
            ->will($this->returnValue($destinationBeanMock));

        $keysArray = [];

        $this->projectWrapper->updateDiagram($diagramArray, $keysArray);
    }

    public function testUpdateDiagramGateways()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBean',
                    'getClassEntity',
                    'getEntityData',
                    'getSelectRows',
                    'sanitizeKeyFields',
                    'getElementUid',
                    'getEntityUid',
                    'updateDiagramElements',
                ]
            )
            ->getMock();

        $diagramArray = [
            'flows' => [],
            'gateways' => [
                [
                    'gat_default_flow' => 'abc123',
                    'gat_direction' => 'CONVERGING',
                ],
                [
                    'gat_default_flow' => '',
                    'gat_direction' => 'DIVERGING',
                ],
            ],
            'activities' => [],
            'events' => [],
        ];

        $this->projectWrapper->expects($this->any())
            ->method('getEntityData')
            ->will($this->returnValue('SomeEntityData'));

        $this->mockFlowBean->expects($this->once())
            ->method('retrieve_by_string_fields');

        $this->projectWrapper->setFlowBean($this->mockFlowBean);

        $keysArray = [];

        $this->projectWrapper->updateDiagram($diagramArray, $keysArray);
    }

    public function testUpdateDiagramActivities()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBean',
                    'getClassEntity',
                    'getEntityData',
                    'getSelectRows',
                    'sanitizeKeyFields',
                    'getElementUid',
                    'getEntityUid',
                    'updateDiagramElements',
                ]
            )
            ->getMock();

        $diagramArray = [
            'flows' => [],
            'gateways' => [],
            'activities' => [
                [
                    'act_default_flow' => 'abc123',
                ],
                [
                    'act_default_flow' => '',
                ],
            ],
            'events' => [],
        ];

        $this->projectWrapper->expects($this->any())
            ->method('getEntityData')
            ->will($this->returnValue('SomeEntityData'));

        $this->mockFlowBean->expects($this->once())
            ->method('retrieve_by_string_fields');

        $this->projectWrapper->setFlowBean($this->mockFlowBean);

        $keysArray = [];

        $this->projectWrapper->updateDiagram($diagramArray, $keysArray);
    }

    public function testUpdateDiagramEvents()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getBean',
                    'getClassEntity',
                    'getEntityData',
                    'getSelectRows',
                    'sanitizeKeyFields',
                    'getElementUid',
                    'getEntityUid',
                    'updateDiagramElements',
                    'initWrapper',
                ]
            )
            ->getMock();

        $diagramArray = [
            'flows' => [],
            'gateways' => [],
            'activities' => [],
            'events' => [
                [
                    'evn_attached_to' => 'abc123',
                ],
                [
                    'evn_attached_to' => '',
                ],
            ],
        ];

        $this->projectWrapper->expects($this->any())
            ->method('getEntityData')
            ->will($this->returnValue('SomeEntityData'));

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $activityMock->id = 'act01';
        $activityMock->act_uid = 'actUid01';

        $tmpObjectMock = $this->getMockBuilder('pmse_BpmnTmpObject')
            ->disableOriginalConstructor()
            ->setMethods(['getPrimaryFieldName'])
            ->getMock();
        $tmpObjectMock->expects($this->exactly(2))
            ->method('getPrimaryFieldName')
            ->will($this->returnValue('act_uid'));

        $activityMock->expects($this->exactly(2))
            ->method('retrieve_by_string_fields')
            ->will($this->returnValue($tmpObjectMock));

        $this->projectWrapper->expects($this->exactly(2))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->setFlowBean($this->mockFlowBean);

        $keysArray = [];

        $this->projectWrapper->updateDiagram($diagramArray, $keysArray);
    }

    public function testGetEntityData()
    {
        $fixtureArray = [
            [
                'inputData' => ['key' => 'activities'],
                'outputData' => [
                    'bean' => 'BpmnActivity',
                    'bean_object' => 'pmse_BpmnActivity',
                    'bound_element' => 'bpmnActivity',
                    'uid_field' => 'act_uid',
                    'element_name' => 'act_name',
                ],
            ],
            [
                'inputData' => ['key' => 'artifacts'],
                'outputData' => [
                    'bean' => 'BpmnArtifact',
                    'bean_object' => 'pmse_BpmnArtifact',
                    'bound_element' => 'bpmnArtifact',
                    'uid_field' => 'art_uid',
                    'element_name' => 'art_name',
                ],
            ],
            [
                'inputData' => ['key' => 'gateways'],
                'outputData' => [
                    'bean' => 'BpmnGateway',
                    'bean_object' => 'pmse_BpmnGateway',
                    'bound_element' => 'bpmnGateway',
                    'uid_field' => 'gat_uid',
                    'element_name' => 'gat_name',
                ],
            ],
            [
                'inputData' => ['key' => 'events'],
                'outputData' => [
                    'bean' => 'BpmnEvent',
                    'bean_object' => 'pmse_BpmnEvent',
                    'bound_element' => 'bpmnEvent',
                    'uid_field' => 'evn_uid',
                    'element_name' => 'evn_name',
                ],
            ],
            [
                'inputData' => ['key' => 'flows'],
                'outputData' => [
                    'bean' => 'BpmnFlow',
                    'bean_object' => 'pmse_BpmnFlow',
                    'bound_element' => 'bpmnFlow',
                    'uid_field' => 'flo_uid',
                    'element_name' => 'flo_name',
                ],
            ],
            [
                'inputData' => ['key' => 'pools'],
                'outputData' => [
                    'bean' => 'BpmnLaneset',
                    'bean_object' => 'pmse_BpmnLaneset',
                    'bound_element' => 'bpmnLaneset',
                    'uid_field' => 'lns_uid',
                ],
            ],
            [
                'inputData' => ['key' => 'lanes'],
                'outputData' => [
                    'bean' => 'BpmnLane',
                    'bean_object' => 'pmse_BpmnLane',
                    'bound_element' => 'bpmnLane',
                    'uid_field' => 'lan_uid',
                ],
            ],
            [
                'inputData' => ['key' => 'data'],
                'outputData' => [
                    'bean' => 'BpmnData',
                    'bean_object' => 'pmse_BpmnData',
                    'bound_element' => 'bpmnData',
                    'uid_field' => 'dat_uid',
                ],
            ],
            [
                'inputData' => ['key' => 'participants'],
                'outputData' => [
                    'bean' => 'BpmnParticipant',
                    'bean_object' => 'pmse_BpmnParticipant',
                    'bound_element' => 'bpmnParticipant',
                    'uid_field' => 'par_uid',
                ],
            ],
        ];
        foreach ($fixtureArray as $fixture) {
            $this->assertEquals($fixture['outputData'], $this->projectWrapper->getEntityData($fixture['inputData']['key']));
        }
    }

    public function testGetEntityUid()
    {
        $fixtureArray = [
            [
                'inputData' => 'bpmnActivity',
                'outputData' => ['bean' => 'pmse_BpmnActivity', 'uid' => 'act_uid'],
            ],
            [
                'inputData' => 'bpmnGateway',
                'outputData' => ['bean' => 'pmse_BpmnGateway', 'uid' => 'gat_uid'],
            ],
            [
                'inputData' => 'bpmnEvent',
                'outputData' => ['bean' => 'pmse_BpmnEvent', 'uid' => 'evn_uid'],
            ],
            [
                'inputData' => 'bpmnFlow',
                'outputData' => ['bean' => 'pmse_BpmnFlow', 'uid' => 'flo_uid'],
            ],
            [
                'inputData' => 'bpmnLaneset',
                'outputData' => 'id',
            ],
            [
                'inputData' => 'bpmnLane',
                'outputData' => 'id',
            ],
            [
                'inputData' => 'bpmnData',
                'outputData' => 'id',
            ],
            [
                'inputData' => 'bpmnParticipant',
                'outputData' => 'id',
            ],
            [
                'inputData' => 'bpmnArtifact',
                'outputData' => ['bean' => 'pmse_BpmnArtifact', 'uid' => 'art_uid'],
            ],

        ];
        foreach ($fixtureArray as $fixture) {
            $this->assertEquals($fixture['outputData'], $this->projectWrapper->getEntityUid($fixture['inputData']));
        }
    }

    public function testGetElementUid()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getPrimaryFieldName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDynaformBean->expects($this->exactly(1))
            ->method('retrieve_by_string_fields')
            ->with($this->isType('array'));
        //                ->will($this->returnValue(null));
        $this->mockDynaformBean->dyn_uid = '2737981231';

        $this->projectWrapper->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($this->mockDynaformBean));

        //        $this->projectWrapper->setBeanFactory($this->mockBeanFactory);
        $this->assertEquals('2737981231', $this->projectWrapper->getElementUid('2737981231', 'BpmDynaForm', 'dyn_uid'));
    }

    public function testGetDefaultDynaformView()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getSelectRows'])
            ->disableOriginalConstructor()
            ->getMock();

        $rowList = [
            'rowList' => [
                [
                    'dyn_uid' => '2737981231',
                    'flo_element_dest' => '123',
                    'flo_element_origin_type' => 'bpmnActivity',
                    'flo_element_dest_type' => 'bpmnEvent',
                    'flo_state' => '{}',
                    'bou_uid' => '123asd',
                ],
            ],
        ];

        $this->projectWrapper->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($rowList));

        $this->assertEquals('EditView', $this->projectWrapper->getDefaultDynaformView('2737981231'));
    }

    public function testGetClassEntity()
    {
        $fixtureArray = [
            [
                'inputData' => 'BpmnActivity',
                'outputData' => ['bean' => 'pmse_BpmnActivity', 'uid_field' => 'act_uid'],
            ],
            [
                'inputData' => 'BpmnGateway',
                'outputData' => ['bean' => 'pmse_BpmnGateway', 'uid_field' => 'gat_uid'],
            ],
            [
                'inputData' => 'BpmnEvent',
                'outputData' => ['bean' => 'pmse_BpmnEvent', 'uid_field' => 'evn_uid'],
            ],
            [
                'inputData' => 'BpmnArtifact',
                'outputData' => ['bean' => 'pmse_BpmnArtifact', 'uid_field' => 'art_uid'],
            ],

        ];
        foreach ($fixtureArray as $fixture) {
            $this->assertEquals($fixture['outputData'], $this->projectWrapper->getClassEntity($fixture['inputData']));
        }
    }

    public function testUpdateDiagramElementsCreateActivity()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getSelectRows', 'create', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $activityMock->act_task_type = 'USERTASK';
        $activityMock->act_type = 'TASK';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue('elem01'));

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(5))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnActivity',
            'element_name' => 'act_name',
            'bean' => 'BpmnActivity',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'create',
            'act_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsCreateGateway()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getSelectRows', 'create', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnGateway')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $activityMock->act_task_type = 'USERTASK';
        $activityMock->act_type = 'TASK';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue('elem01'));

        $definitionMock = $this->getMockBuilder('pmse_BpmGatewayDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(4))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnGateway',
            'element_name' => 'gat_name',
            'bean' => 'BpmnGateway',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'create',
            'gat_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsCreateEvent()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getSelectRows', 'create', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnEvent')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $activityMock->evn_type = 'CATCH';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue('elem01'));

        $definitionMock = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->projectWrapper->expects($this->at(4))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnEvent',
            'element_name' => 'evn_name',
            'bean' => 'BpmnEvent',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'create',
            'evn_name' => 'Task #1',
            'evn_message' => 'Leads',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
            'Leads',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsUpdate()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getPrimaryFieldName', 'update', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $activityMock->act_task_type = 'USERTASK';
        $activityMock->act_type = 'TASK';
        $activityMock->id = 'act01';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';
        $activityMock->fetched_row = [
            'act_task_type' => 'USERTASK',
            'act_type' => 'TASK',
            'pro_id' => 'pro01',
            'prj_id' => 'prj01',
            'id' => 'act01',
            'act_uit' => 'actUid01',
        ];

        $activityMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('update')
            ->will($this->returnValue('act01'));

        $this->projectWrapper->expects($this->at(3))
            ->method('getPrimaryFieldName')
            ->will($this->returnValue('id'));


        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $boundMock->dia_id = 'dia01';

        $this->projectWrapper->expects($this->at(4))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnActivity',
            'bound_element' => 'bpmnActivity',
            'element_name' => 'act_name',
            'uid_field' => 'act_uid',
            'bean' => 'BpmnActivity',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'update',
            'act_uid' => 'actUid01',
            'act_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsRemoveActivity()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getPrimaryFieldName', 'delete', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $activityMock->act_task_type = 'USERTASK';
        $activityMock->act_type = 'TASK';
        $activityMock->id = 'act01';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';
        $activityMock->fetched_row = [
            'act_task_type' => 'USERTASK',
            'act_type' => 'TASK',
            'pro_id' => 'pro01',
            'prj_id' => 'prj01',
            'id' => 'act01',
            'act_uit' => 'actUid01',
        ];

        $activityMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('getPrimaryFieldName')
            ->will($this->returnValue('id'));

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));


        $this->projectWrapper->expects($this->at(4))
            ->method('delete')
            ->will($this->returnValue('act01'));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $boundMock->dia_id = 'dia01';

        $this->projectWrapper->expects($this->at(5))
            ->method('delete')
            ->will($this->returnValue(true));

        $this->projectWrapper->expects($this->at(6))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnActivity',
            'bound_element' => 'bpmnActivity',
            'element_name' => 'act_name',
            'uid_field' => 'act_uid',
            'bean' => 'BpmnActivity',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'remove',
            'act_uid' => 'actUid01',
            'act_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsRemoveEvent()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getPrimaryFieldName', 'delete', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock = $this->getMockBuilder('pmse_BpmnEvent')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $eventMock->evn_type = 'CATCH';
        $eventMock->id = 'evn01';
        $eventMock->pro_id = 'pro01';
        $eventMock->prj_id = 'prj01';
        $eventMock->fetched_row = [
            'evn_type' => 'CATCH',
            'pro_id' => 'pro01',
            'prj_id' => 'prj01',
            'id' => 'evn01',
            'evn_uid' => 'evnUid01',
        ];

        $eventMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($eventMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('getPrimaryFieldName')
            ->will($this->returnValue('id'));

        $definitionMock = $this->getMockBuilder('pmse_BpmEventDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields', 'delete'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));


        $this->projectWrapper->expects($this->at(4))
            ->method('delete')
            ->will($this->returnValue(true));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $boundMock->dia_id = 'dia01';

        $this->projectWrapper->expects($this->at(5))
            ->method('delete')
            ->will($this->returnValue(true));

        $this->projectWrapper->expects($this->at(6))
            ->method('getBean')
            ->will($this->returnValue($boundMock));


        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnEvent',
            'bound_element' => 'bpmnEvent',
            'element_name' => 'evn_name',
            'uid_field' => 'evn_uid',
            'bean' => 'BpmnEvent',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'remove',
            'evn_uid' => 'evnUid01',
            'evn_name' => 'Event #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsRemoveGateway()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'getPrimaryFieldName', 'delete', 'getDefaultDynaformView', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $activityMock = $this->getMockBuilder('pmse_BpmnActivity')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $activityMock->act_task_type = 'USERTASK';
        $activityMock->act_type = 'TASK';
        $activityMock->id = 'act01';
        $activityMock->pro_id = 'pro01';
        $activityMock->prj_id = 'prj01';
        $activityMock->fetched_row = [
            'act_task_type' => 'USERTASK',
            'act_type' => 'TASK',
            'pro_id' => 'pro01',
            'prj_id' => 'prj01',
            'id' => 'act01',
            'act_uit' => 'actUid01',
        ];

        $activityMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($activityMock));

        $this->projectWrapper->expects($this->at(2))
            ->method('getPrimaryFieldName')
            ->will($this->returnValue('id'));

        $definitionMock = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($definitionMock));


        $this->projectWrapper->expects($this->at(4))
            ->method('delete')
            ->will($this->returnValue('act01'));

        $boundMock = $this->getMockBuilder('pmse_BpmnBound')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $boundMock->dia_id = 'dia01';

        $this->projectWrapper->expects($this->at(4))
            ->method('getBean')
            ->will($this->returnValue($boundMock));

        $this->mockProcessDefinition->pro_module = 'Leads';

        $entityData = [
            'bean_object' => 'pmse_bpmnActivity',
            'bound_element' => 'bpmnActivity',
            'element_name' => 'act_name',
            'uid_field' => 'act_uid',
            'bean' => 'BpmnActivity',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'remove',
            'act_uid' => 'actUid01',
            'act_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }

    public function testUpdateDiagramElementsDefault()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->projectWrapper->expects($this->once())
            ->method('getBean');

        $entityData = [
            'bean_object' => 'pmse_bpmnActivity',
            'bound_element' => 'bpmnActivity',
            'element_name' => 'act_name',
            'uid_field' => 'act_uid',
            'bean' => 'BpmnActivity',
        ];

        $keysArray = [
            'pro_id' => 'pro01',
            'prj_uid' => 'prj01',
            'dia_id' => 'dia01',
        ];

        $elementArray = [
            'action' => 'randomAction',
            'act_uid' => 'actUid01',
            'act_name' => 'Task #1',
            'bou_x' => 12.8278,
            'bou_y' => 11.2222,
            'bou_width' => 200,
            'bou_height' => 50,
            'bou_container' => 'bpmnDiagram',
        ];

        global $beanList;

        $beanList = [
            'Meetings',
        ];

        $this->projectWrapper->updateDiagramElements($entityData, $keysArray, $elementArray);
    }


    public function testUpdateProcessDefinition()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(['getBean', 'notify', 'initWrapper'])
            ->disableOriginalConstructor()
            ->getMock();

        $processDefinitionMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $processDefinitionMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(1))
            ->method('getBean')
            ->will($this->returnValue($processDefinitionMock));

        $diagramMock = $this->getMockBuilder('pmse_BpmnDiagram')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();

        $diagramMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(2))
            ->method('getBean')
            ->will($this->returnValue($diagramMock));

        $processMock = $this->getMockBuilder('pmse_BpmnProcess')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields', 'save'])
            ->getMock();


        $processMock->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->will($this->returnSelf());

        $this->projectWrapper->expects($this->at(3))
            ->method('getBean')
            ->will($this->returnValue($processMock));

        $this->projectWrapper->expects($this->at(4))
            ->method('notify');

        $args = [
            'record' => 'someRecord01',
        ];

        $this->projectWrapper->updateProcessDefinition($args);
    }

    public function testAttachDetachNotify()
    {
        $this->projectWrapper = $this->getMockBuilder('PMSEProjectWrapper')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->projectWrapper->setObservers([]);

        $observerMock = $this->getMockBuilder('PMSEObserver')
            ->disableOriginalConstructor()
            ->setMethods(['update'])
            ->getMock();

        $observerMock->expects($this->once())
            ->method('update');

        $this->projectWrapper->attach($observerMock);
        $this->projectWrapper->notify();
        $this->projectWrapper->detach($observerMock);
        $this->projectWrapper->notify();
    }
}
