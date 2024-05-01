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

class PMSEProjectImporterTest extends TestCase
{
    /**
     * @var PMSEProjectImporter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = ProcessManager\Factory::getPMSEObject('PMSEProjectImporter');
    }

    /**
     * @covers PMSEProjectImporter::saveProjectData
     * @todo   Implement testSaveProjectData().
     */
    public function testSaveProjectDataWithName()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods([
                'getBean',
                'unsetCommonFields',
                'getNameWithSuffix',
                'saveProjectActivitiesData',
                'saveProjectEventsData',
                'saveProjectGatewaysData',
                'saveProjectElementsData',
                'saveProjectFlowsData',
                'processDefaultFlows',
            ])
            ->getMock();

        global $current_user;
        $current_user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $current_user->id = 'user01';

        $projectMock = $this->getMockBuilder('pmse_BpmProject')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $projectMock->object_name = 'Project';

        $projectImporterMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($projectMock));

        $projectData = [
            'prj_test' => 'Some Name',
            'prj_uid' => 'SomeStringChainOf32CharactersLng',
            'prj_name' => 'Project Name',
            'definition' => [
                'prj_name' => 'Project Name',
            ],
            'diagram' => [
                [
                    'activities' => [],
                    'events' => [],
                    'gateways' => [],
                    'documentation' => [],
                    'extension' => [],
                    'pools' => [],
                    'lanes' => [],
                    'participants' => [],
                    'artifacts' => [],
                    'data' => [],
                    'flows' => [],
                ],
            ],
            'dynaforms' => [],
        ];

        $projectImporterMock->setName('prj_test');
        $projectImporterMock->saveProjectData($projectData);
    }

    public function testSaveProjectDataWithoutName()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods([
                'getBean',
                'unsetCommonFields',
                'getNameWithSuffix',
                'saveProjectActivitiesData',
                'saveProjectEventsData',
                'saveProjectGatewaysData',
                'saveProjectElementsData',
                'saveProjectFlowsData',
                'processDefaultFlows',
            ])
            ->getMock();

        global $current_user;
        $current_user = $this->getMockBuilder('User')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $current_user->id = 'user01';

        $projectMock = $this->getMockBuilder('pmse_BpmProject')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $projectMock->object_name = 'Project';

        $projectImporterMock->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($projectMock));

        $projectData = [
            'prj_test' => 'Some Name',
            'prj_uid' => 'SomeStringChainOf32CharactersLng',
            'prj_name' => '',
            'definition' => [
                'prj_description' => 'Some Description',
            ],
            'diagram' => [
                [
                    'activities' => [],
                    'events' => [],
                    'gateways' => [],
                    'documentation' => [],
                    'extension' => [],
                    'pools' => [],
                    'lanes' => [],
                    'participants' => [],
                    'artifacts' => [],
                    'data' => [],
                    'flows' => [],
                ],
            ],
            'dynaforms' => [],
        ];

        $projectImporterMock->setName('prj_test');
        $projectImporterMock->saveProjectData($projectData);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectActivitiesData
     * @todo   Implement testSaveProjectActivitiesData().
     */
    public function testSaveProjectActivitiesData()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $activities = [
            [
                'act_name' => 'act01',
                'act_type' => 'TASK',
                'act_task_type' => 'USER',
                'act_description' => 'Some Description',
                'act_default_flow' => 'some flow',
            ],
            [
                'act_name' => 'act01',
                'act_type' => 'TASK',
                'act_task_type' => 'USER',
                'act_description' => 'Some Description',
                'act_default_flow' => 'some flow',
            ],
            [
                'act_name' => 'act02',
                'act_type' => 'TASK',
                'act_task_type' => 'SCRIPTTASK',
                'act_script_type' => 'ASSIGN',
                'act_description' => 'Some Description',
                'act_default_flow' => 'some flow',
            ],
            [
                'act_name' => 'act03',
                'act_type' => 'TASK',
                'act_task_type' => 'SCRIPTTASK',
                'act_script_type' => 'BUSINESS_RULE',
                'act_fields' => 'some_field',
                'act_description' => 'Some Description',
                'act_default_flow' => 'some flow',
            ],
            [
                'act_name' => 'act04',
                'act_type' => 'TASK',
                'act_task_type' => 'SCRIPTTASK',
                'act_script_type' => 'ASSIGN',
                'act_description' => 'Some Description',
                'act_default_flow' => 'some flow',
            ],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];
        $projectImporterMock->setSavedElements(['BpmRuleSet' => ['some_field' => 'fieldValue']]);
        $projectImporterMock->saveProjectActivitiesData($activities, $arrayKeys);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectEventsData
     * @todo   Implement testSaveProjectEventsData().
     */
    public function testSaveProjectEventsData()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $events = [
            [
                'evn_name' => 'evn01',
                'evn_message' => 'Some Message',
                'evn_default_flow' => 'some flow',
            ],
            [
                'evn_name' => 'evn01',
                'evn_message' => 'Some Message',
                'evn_default_flow' => 'some flow',
            ],
            [
                'evn_name' => 'evn02',
                'evn_type' => 'TASK',
                'evn_message' => 'Some Message',
                'evn_default_flow' => 'some flow',
            ],
            [
                'evn_name' => 'evn03',
                'evn_type' => 'TASK',
                'evn_fields' => 'some_field',
                'evn_message' => 'Some Message',
                'evn_default_flow' => 'some flow',
            ],
            [
                'evn_name' => 'evn04',
                'evn_type' => 'TASK',
                'evn_message' => 'Some Message',
                'evn_default_flow' => 'some flow',
            ],
        ];

        $relatedDependenciesMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(['processRelatedDependencies'])
            ->getMock();

        $relatedDependenciesMock->expects($this->exactly(5))
            ->method('processRelatedDependencies');

        $projectImporterMock->setDependenciesWrapper($relatedDependenciesMock);

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];
        $projectImporterMock->setSavedElements(['BpmRuleSet' => ['some_field' => 'fieldValue']]);
        $projectImporterMock->saveProjectEventsData($events, $arrayKeys);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectGatewaysData
     * @todo   Implement testSaveProjectGatewaysData().
     */
    public function testSaveProjectGatewaysData()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $gateways = [
            [
                'gat_name' => 'gat01',
                'gat_message' => 'Some Message',
                'gat_default_flow' => 'some flow',
            ],
            [
                'gat_name' => 'gat01',
                'gat_message' => 'Some Message',
                'gat_default_flow' => 'some flow',
            ],
            [
                'gat_name' => 'gat02',
                'gat_type' => 'TASK',
                'gat_message' => 'Some Message',
                'gat_default_flow' => 'some flow',
            ],
            [
                'gat_name' => 'gat03',
                'gat_type' => 'TASK',
                'gat_fields' => 'some_field',
                'gat_message' => 'Some Message',
                'gat_default_flow' => 'some flow',
            ],
            [
                'gat_name' => 'gat04',
                'gat_type' => 'TASK',
                'gat_message' => 'Some Message',
                'gat_default_flow' => 'some flow',
            ],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];
        $projectImporterMock->setSavedElements(['BpmRuleSet' => ['some_field' => 'fieldValue']]);
        $projectImporterMock->saveProjectGatewaysData($gateways, $arrayKeys);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectFlowsData
     * @todo   Implement testSaveProjectFlowsData().
     */
    public function testSaveProjectFlowsData()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(['changedUidElements'])
            ->getMock();

        $gateways = [

            [
                'flo_uid' => '01',
                'flo_name' => 'flo01',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA1',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB1',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '02',
                'flo_name' => 'flo02',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA2',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB2',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '03',
                'flo_name' => 'flo03',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA3',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB3',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '04',
                'flo_name' => 'flo04',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA4',
                'flo_element_origin_type' => 'Gateway',
                'flo_element_dest' => 'eleB4',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '05',
                'flo_name' => 'flo05',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA5',
                'flo_element_origin_type' => 'Gateway',
                'flo_element_dest' => 'eleB5',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
                'flo_condition' => '',
                'flo_is_inmediate' => true,
            ],
        ];

        $savedElements = [
            'BpmRuleSet' => [
                'some_field' => 'fieldValue',
            ],
            'Activity' => [
                'eleA1' => ['eleUidA1'],
                'eleA2' => ['eleUidA2'],
                'eleA3' => ['eleUidA3'],
            ],
            'Event' => [
                'eleB1' => ['eleUidB2'],
                'eleB3' => ['eleUidB3'],
                'eleB4' => ['eleUidB4'],
            ],
            'Gateway' => [
                'eleA4' => ['eleUidA4'],
                'eleA5' => ['eleUidA5'],
                'eleB2' => ['eleUidB2'],
                'eleB5' => ['eleUidB5'],
            ],
        ];

        $defaultFlowList = [
            '01' => [],
            '02' => [],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];

        $projectImporterMock->setSavedElements($savedElements);
        $projectImporterMock->setDefaultFlowList($defaultFlowList);
        $projectImporterMock->saveProjectFlowsData($gateways, $arrayKeys);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectFlowsData
     * @todo   Implement testSaveProjectFlowsData().
     */
    public function testSaveProjectFlowsDataWithChangedElements()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $gateways = [

            [
                'flo_uid' => '01',
                'flo_name' => 'flo01',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA1',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB1',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '02',
                'flo_name' => 'flo02',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA2',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB2',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '03',
                'flo_name' => 'flo03',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA3',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB3',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '04',
                'flo_name' => 'flo04',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA4',
                'flo_element_origin_type' => 'Gateway',
                'flo_element_dest' => 'eleB4',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '05',
                'flo_name' => 'flo05',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA5',
                'flo_element_origin_type' => 'Gateway',
                'flo_element_dest' => 'eleB5',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
                'flo_condition' => '',
                'flo_is_inmediate' => true,
            ],
        ];

        $savedElements = [
            'BpmRuleSet' => [
                'some_field' => 'fieldValue',
            ],
            'Activity' => [
                'eleA1' => ['eleUidA1'],
                'eleA2' => ['eleUidA2'],
                'eleAB2' => ['eleUidAB2'],
                'eleA3' => ['eleUidA3'],
                'eleAB3' => ['eleUidAB3'],
            ],
            'Event' => [
                'eleB1' => ['eleUidB2'],
                'eleB3' => ['eleUidB3'],
                'eleBC3' => ['eleUidBC3'],
                'eleB4' => ['eleUidB4'],
            ],
            'Gateway' => [
                'eleA4' => ['eleUidA4'],
                'eleA5' => ['eleUidA5'],
                'eleB2' => ['eleUidB2'],
                'eleBC2' => ['eleUidBC2'],
                'eleB5' => ['eleUidB5'],
            ],
        ];

        $defaultFlowList = [
            '01' => [],
            '02' => [],
        ];

        $changedUidElements = [
            'eleA2' => ['new_uid' => 'eleAB2'],
            'eleA3' => ['new_uid' => 'eleAB3'],
            'eleB2' => ['new_uid' => 'eleBC2'],
            'eleB3' => ['new_uid' => 'eleBC3'],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];

        $projectImporterMock->setChangedUidElements($changedUidElements);
        $projectImporterMock->setSavedElements($savedElements);
        $projectImporterMock->setDefaultFlowList($defaultFlowList);
        $projectImporterMock->saveProjectFlowsData($gateways, $arrayKeys);
    }

    /**
     * @covers PMSEProjectImporter::saveProjectElementsData
     */
    public function testSaveProjectElementsData()
    {
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $gateways = [

            [
                'flo_uid' => '01',
                'flo_name' => 'flo01',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA1',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB1',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '02',
                'flo_name' => 'flo02',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA2',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB2',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '03',
                'flo_name' => 'flo03',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA3',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB3',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
        ];

        $savedElements = [
            'BpmRuleSet' => [
                'some_field' => 'fieldValue',
            ],
            'Activity' => [
                'eleA1' => ['eleUidA1'],
                'eleA2' => ['eleUidA2'],
                'eleAB2' => ['eleUidAB2'],
                'eleA3' => ['eleUidA3'],
                'eleAB3' => ['eleUidAB3'],
            ],
            'Event' => [
                'eleB1' => ['eleUidB2'],
                'eleB3' => ['eleUidB3'],
                'eleBC3' => ['eleUidBC3'],
                'eleB4' => ['eleUidB4'],
            ],
            'Gateway' => [
                'eleA4' => ['eleUidA4'],
                'eleA5' => ['eleUidA5'],
                'eleB2' => ['eleUidB2'],
                'eleBC2' => ['eleUidBC2'],
                'eleB5' => ['eleUidB5'],
            ],
        ];

        $defaultFlowList = [
            '01' => [],
            '02' => [],
        ];

        $changedUidElements = [
            'eleA2' => ['new_uid' => 'eleAB2'],
            'eleA3' => ['new_uid' => 'eleAB3'],
            'eleB2' => ['new_uid' => 'eleBC2'],
            'eleB3' => ['new_uid' => 'eleBC3'],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];

        $projectImporterMock->setChangedUidElements($changedUidElements);
        $projectImporterMock->setSavedElements($savedElements);
        $projectImporterMock->setDefaultFlowList($defaultFlowList);
        $projectImporterMock->saveProjectElementsData($gateways, $arrayKeys, 'pmse_BpmnActivity', false, false, 'act_uid');
    }

    /**
     * @covers PMSEProjectImporter::saveProjectElementsData
     */
    public function testSaveProjectElementsDataWithBounds()
    {
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $gateways = [

            [
                'flo_uid' => '01',
                'flo_name' => 'flo01',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA1',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB1',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '02',
                'flo_name' => 'flo02',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA2',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB2',
                'flo_element_dest_type' => 'Gateway',
                'flo_default_flow' => 'some flow',
            ],
            [
                'flo_uid' => '03',
                'flo_name' => 'flo03',
                'flo_state' => 'CLOSED',
                'flo_element_origin' => 'eleA3',
                'flo_element_origin_type' => 'Activity',
                'flo_element_dest' => 'eleB3',
                'flo_element_dest_type' => 'Event',
                'flo_default_flow' => 'some flow',
            ],
        ];

        $savedElements = [
            'BpmRuleSet' => [
                'some_field' => 'fieldValue',
            ],
            'Activity' => [
                'eleA1' => ['eleUidA1'],
                'eleA2' => ['eleUidA2'],
                'eleAB2' => ['eleUidAB2'],
                'eleA3' => ['eleUidA3'],
                'eleAB3' => ['eleUidAB3'],
            ],
            'Event' => [
                'eleB1' => ['eleUidB2'],
                'eleB3' => ['eleUidB3'],
                'eleBC3' => ['eleUidBC3'],
                'eleB4' => ['eleUidB4'],
            ],
            'Gateway' => [
                'eleA4' => ['eleUidA4'],
                'eleA5' => ['eleUidA5'],
                'eleB2' => ['eleUidB2'],
                'eleBC2' => ['eleUidBC2'],
                'eleB5' => ['eleUidB5'],
            ],
        ];

        $defaultFlowList = [
            '01' => [],
            '02' => [],
        ];

        $changedUidElements = [
            'eleA2' => ['new_uid' => 'eleAB2'],
            'eleA3' => ['new_uid' => 'eleAB3'],
            'eleB2' => ['new_uid' => 'eleBC2'],
            'eleB3' => ['new_uid' => 'eleBC3'],
        ];

        $arrayKeys = ['prj_id' => 'prj01', 'pro_id' => 'pro01', 'dia_id' => 'dia01'];

        $projectImporterMock->setChangedUidElements($changedUidElements);
        $projectImporterMock->setSavedElements($savedElements);
        $projectImporterMock->setDefaultFlowList($defaultFlowList);
        $projectImporterMock->saveProjectElementsData($gateways, $arrayKeys, 'pmse_BpmnActivity', true, false, 'act_uid');
    }

    /**
     * @covers PMSEProjectImporter::processDefaultFlows
     * @todo   Implement testProcessDefaultFlows().
     */
    public function testProcessDefaultFlows()
    {
        // Remove the following lines when you implement this test.
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $defaultFlows = [
            [
                'bean' => 'pmse_BpmnFlow',
                'search_field' => 'some_search',
                'search_field_value' => 'some_field',
                'default_flow' => 'some_flow',
                'default_flow_field' => 'some_default',
            ],
        ];

        $projectImporterMock->setDefaultFlowList($defaultFlows);
        $projectImporterMock->processDefaultFlows();
    }
}
