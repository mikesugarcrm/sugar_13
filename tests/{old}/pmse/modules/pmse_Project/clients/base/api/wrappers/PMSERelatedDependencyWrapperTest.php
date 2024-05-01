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

class PMSERelatedDependencyWrapperTest extends TestCase
{
    protected $loggerMock;

    protected $relatedModuleMock;

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug', 'warning', 'error'])
            ->getMock();

        $this->relatedModuleMock = $this->getMockBuilder('PMSERelatedModule')
            ->disableOriginalConstructor()
            ->setMethods(['getRelatedModuleName'])
            ->getMock();
    }

    public function testProcessRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'processEventCriteria',
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('info');

        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $relatedDepWrapperMock->expects($this->once())
            ->method('processEventCriteria');
        $relatedDepWrapperMock->expects($this->once())
            ->method('removeRelatedDependencies');
        $relatedDepWrapperMock->expects($this->once())
            ->method('createRelatedDependencies');

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $eventData = ['evn_criteria' => 'Some Criteria'];

        $relatedDepWrapperMock->processRelatedDependencies($eventData);
    }

    public function testProcessEventCriteriaNonEmpty()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                    'getBean',
                    'getRelatedElementModule',
                ]
            )
            ->getMock();

        $processDefinitionMock = $this->getMockBuilder('psme_BpmProcessDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $processDefinitionMock->pro_module = 'Leads';
        $processDefinitionMock->pro_status = 'ACTIVE';
        $processDefinitionMock->pro_locked_variables = 'locked01, locked02';
        $processDefinitionMock->pro_terminate_variables = 'terminate01, terminate02';

        $relatedDepWrapperMock->expects($this->atLeastOnce())
            ->method('getBean')
            ->will($this->returnValue($processDefinitionMock));

        $this->loggerMock->expects($this->once())
            ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '[]';

        $eventData = [
            'id' => 'event01',
            'evn_behavior' => 'CATCH',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        ];
        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
    }

    public function testProcessEventCriteriaEmpty()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                    'getBean',
                    'getRelatedElementModule',
                ]
            )
            ->getMock();

        $processDefinitionMock = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $processDefinitionMock->pro_module = 'Leads';
        $processDefinitionMock->pro_status = 'ACTIVE';
        $processDefinitionMock->pro_locked_variables = 'locked01, locked02';
        $processDefinitionMock->pro_terminate_variables = 'terminate01, terminate02';

        $relatedDepWrapperMock->expects($this->atLeastOnce())
            ->method('getBean')
            ->will($this->returnValue($processDefinitionMock));

        $this->loggerMock->expects($this->once())
            ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '['
            . '{'
            . '"expType" : "MODULE",'
            . '"expModule" : "Leads"'
            . '},'
            . '{'
            . '"expType" : "MODULE",'
            . '"expModule" : "Leads"'
            . '}'
            . ']';

        $eventData = [
            'id' => 'event01',
            'evn_behavior' => 'CATCH',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        ];

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
    }

    public function testProcessEventCriteriaThrowEvent()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                    'getBean',
                    'getRelatedElementModule',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $eventCriteria = '['
            . '{'
            . '"expType" : "MODULE",'
            . '"expModule" : "Leads"'
            . '},'
            . '{'
            . '"expType" : "MODULE",'
            . '"expModule" : "Leads"'
            . '}'
            . ']';

        $eventData = [
            'id' => 'event01',
            'evn_behavior' => 'TRHOW',
            'pro_id' => 'pro01',
            'evn_type' => 'START_EVENT',
            'rel_element_module' => 'Notes',
        ];

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $result = $relatedDepWrapperMock->processEventCriteria($eventCriteria, $eventData);
        $this->assertEmpty($result);
    }

    public function testGetRelatedElementModuleIfModulesAreTheSame()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                    'getBean',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('debug');

        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Leads';

        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $relatedDepWrapperMock->getRelatedElementModule($tmpObject, $tmpCriteria);
    }

    public function testGetRelatedElementModuleIfModulesAreDifferent()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'removeRelatedDependencies',
                    'createRelatedDependencies',
                    'getBean',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('debug');
        $this->relatedModuleMock->expects($this->any())
            ->method('getRelatedModuleName');

        $relationshipMock = $this->getMockBuilder('Relationship')
            ->disableOriginalConstructor()
            ->setMethods(['get_other_module'])
            ->getMock();
        $relationshipMock->db = new stdClass();

        $relatedDepWrapperMock->setRelationship($relationshipMock);

        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Notes';

        $relatedDepWrapperMock->setLogger($this->loggerMock);
        $relatedDepWrapperMock->setRelatedModule($this->relatedModuleMock);

        $relatedDepWrapperMock->getRelatedElementModule($tmpObject, $tmpCriteria);
    }

    public function testRemoveRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRelatedDependency',
                    'getBean',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('debug');

        $relatedDependencyMock = $this->getMockBuilder('Relationship')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $relatedDependencyMock->db = new stdClass();

        $elementMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();
        $elementMock->deleted = 0;

        $relatedDependencyMock->expects($this->exactly(2))
            ->method('retrieve_by_string_fields')
            ->willReturnOnConsecutiveCalls($elementMock, false);

        $relatedDepWrapperMock->expects($this->once())
            ->method('getRelatedDependency')
            ->will($this->returnValue($relatedDependencyMock));

        $tmpObject = new stdClass();
        $tmpObject->rel_process_module = 'Leads';
        $tmpCriteria = new stdClass();
        $tmpCriteria->expModule = 'Notes';

        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $eventData = ['id' => 'event01', 'pro_id' => 'pro01'];
        $relatedDepWrapperMock->removeRelatedDependencies($eventData);
    }

    public function testCreateRelatedDependencies()
    {
        $relatedDepWrapperMock = $this->getMockBuilder('PMSERelatedDependencyWrapper')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getRelatedDependency',
                    'getBean',
                ]
            )
            ->getMock();

        $this->loggerMock->expects($this->once())
            ->method('debug');

        $relatedDependencyMock = $this->getMockBuilder('Relationship')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $relatedDependencyMock->expects($this->atLeastOnce())
            ->method('save');

        $relatedDepWrapperMock->expects($this->atLeastOnce())
            ->method('getBean')
            ->will($this->returnValue($relatedDependencyMock));

        $relatedDepWrapperMock->setLogger($this->loggerMock);

        $resultData = [
            [
                'id' => 'event01',
                'pro_id' => 'pro01',
            ],
            [
                'id' => 'event02',
                'pro_id' => 'pro02',
            ],
        ];
        $relatedDepWrapperMock->createRelatedDependencies($resultData);
    }
}
