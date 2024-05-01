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

class PMSEBusinessRuleTest extends TestCase
{
    /**
     * @var type
     */
    protected $loggerMock;

    /**
     * @var PMSEElement
     */
    protected $businessRule;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug', 'warning'])
            ->getMock();
    }

    public function testRunIfBRExists()
    {
        $this->businessRule = $this->getMockBuilder('PMSEBusinessRule')
            ->disableOriginalConstructor()
            ->setMethods([
                'prepareResponse',
                'retrieveDefinitionData',
                'getCurrentUser',
                'getBusinessRuleReader',
                'retrieveHistoryData',
            ])
            ->getMock();

        $this->businessRule->setLogger($this->loggerMock);

        $bpmnElement = [
            'id' => 'di92j3892dj',
        ];

        $this->businessRule->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($bpmnElement));

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['retrieveBean', 'saveFormAction'])
            ->getMock();

        $definition = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $definition->act_fields = '298uj9sd812';
        $definition->pro_id = 'd8923dj982398d';
        $definition->act_field_module = 'Leads';

        $processDefinition = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $processDefinition->pro_module = 'Leads';
        $processDefinition->pro_id = 'd8923dj982398d';

        $dbHandler = $this->getMockBuilder('DBHandler')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbHandler->expects($this->exactly(1))
            ->method('Query')
            ->will($this->returnValue([]));

        $row = [
            'name' => 'Some Rule',
            'rst_definition' => base64_encode('Some Array'),
            'rst_type' => 'SINGLE',
            'rst_source_definition' => base64_encode('Some Array'),
        ];

        $dbHandler->expects($this->exactly(1))
            ->method('fetchByAssoc')
            ->will($this->returnValue($row));

        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(['save'])
            ->getMock();

        $beanMock->field_defs = [
            'first_field' => 'value',
            'second_field' => 'value',
            'third_field' => 'value',
            'fourth_field' => 'value',
        ];
        $beanMock->name = 'Some Name';
        $beanMock->first_field = 'value';
        $beanMock->second_field = 'value';
        $beanMock->third_field = 'value';

        $caseFlowHandler->method('retrieveBean')
            ->willReturnOnConsecutiveCalls($definition, $processDefinition, $beanMock);

        $ruleReader = $this->getMockBuilder('PMSERuleReader')
            ->setMethods(['parseRuleSetJSON'])
            ->getMock();

        $ruleResult = [
            'log' => 'Some Log Message',
            'return' => 'Return response for a BR evaluation',
            'newAppData' => [
                'first_field' => 'Business Rule orders change this field',
            ],
        ];

        $ruleReader->expects($this->once())
            ->method('parseRuleSetJSON')
            ->will($this->returnValue($ruleResult));

        $this->businessRule->expects($this->exactly(1))
            ->method('getBusinessRuleReader')
            ->will($this->returnValue($ruleReader));

        $historyData = $this->getMockBuilder('PMSEHistoryData')
            ->setMethods(['savePredata', 'savePostData', 'getLog'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->businessRule->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyData));

        $currentUser = new stdClass();
        $currentUser->id = '2389734';

        $this->businessRule->expects($this->exactly(1))
            ->method('getCurrentUser')
            ->will($this->returnValue($currentUser));

        $this->businessRule->setCaseFlowHandler($caseFlowHandler);
        $this->businessRule->setDbHandler($dbHandler);

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_sugar_object_id' => 'mnedwij8923d2',
            'bpmn_id' => 'di92j3892dj',
        ];

        $bean = new stdClass();

        $this->businessRule->run($flowData, $bean, '');
    }

    public function testRunIfBRExistsButBeanHasNotName()
    {
        $this->businessRule = $this->getMockBuilder('PMSEBusinessRule')
            ->disableOriginalConstructor()
            ->setMethods([
                'prepareResponse',
                'retrieveDefinitionData',
                'getCurrentUser',
                'getBusinessRuleReader',
                'retrieveHistoryData',
            ])
            ->getMock();

        $this->businessRule->setLogger($this->loggerMock);

        $bpmnElement = [
            'id' => 'di92j3892dj',
        ];

        $this->businessRule->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($bpmnElement));

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['retrieveBean', 'saveFormAction'])
            ->getMock();

        $definition = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $definition->act_fields = '298uj9sd812';
        $definition->pro_id = 'd8923dj982398d';
        $definition->act_field_module = 'Leads';

        $processDefinition = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->disableOriginalConstructor()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();

        $processDefinition->pro_module = 'Leads';
        $processDefinition->pro_id = 'd8923dj982398d';

        $dbHandler = $this->getMockBuilder('DBHandler')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $dbHandler->expects($this->exactly(1))
            ->method('Query')
            ->will($this->returnValue([]));

        $row = [
            'name' => 'Some Rule',
            'rst_definition' => base64_encode('Some Array'),
            'rst_type' => 'SINGLE',
            'rst_source_definition' => base64_encode('Some Array'),
        ];

        $dbHandler->expects($this->exactly(1))
            ->method('fetchByAssoc')
            ->will($this->returnValue($row));

        $beanMock = $this->getMockBuilder('SugarBean')
            ->setMethods(['save'])
            ->getMock();

        $beanMock->field_defs = [
            'first_field' => 'value',
            'second_field' => 'value',
            'third_field' => 'value',
            'fourth_field' => 'value that has not a correspondent field',
        ];
        $beanMock->first_field = 'value';
        $beanMock->second_field = 'value';
        $beanMock->third_field = 'value';

        $caseFlowHandler->method('retrieveBean')
            ->willReturnOnConsecutiveCalls($definition, $processDefinition, $beanMock);

        $ruleReader = $this->getMockBuilder('PMSERuleReader')
            ->setMethods(['parseRuleSetJSON'])
            ->getMock();

        $ruleResult = [
            'log' => 'Some Log Message',
            'return' => 'Return response for a BR evaluation',
            'newAppData' => [
                'first_field' => 'Business Rule orders change this field',
            ],
        ];

        $ruleReader->expects($this->once())
            ->method('parseRuleSetJSON')
            ->will($this->returnValue($ruleResult));

        $this->businessRule->expects($this->exactly(1))
            ->method('getBusinessRuleReader')
            ->will($this->returnValue($ruleReader));

        $historyData = $this->getMockBuilder('PMSEHistoryData')
            ->setMethods(['savePredata', 'savePostData', 'getLog'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->businessRule->expects($this->exactly(1))
            ->method('retrieveHistoryData')
            ->will($this->returnValue($historyData));

        $currentUser = new stdClass();
        $currentUser->id = '2389734';

        $this->businessRule->expects($this->exactly(1))
            ->method('getCurrentUser')
            ->will($this->returnValue($currentUser));

        $this->businessRule->setCaseFlowHandler($caseFlowHandler);
        $this->businessRule->setDbHandler($dbHandler);

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_sugar_object_id' => 'mnedwij8923d2',
            'bpmn_id' => 'di92j3892dj',
        ];

        $bean = new stdClass();

        $this->businessRule->run($flowData, $bean, '');
    }

    public function testRunIfBRNotDefined()
    {
        $this->businessRule = $this->getMockBuilder('PMSEBusinessRule')
            ->disableOriginalConstructor()
            ->setMethods(['prepareResponse', 'retrieveDefinitionData', 'getCurrentUser'])
            ->getMock();

        $this->businessRule->setLogger($this->loggerMock);

        $bpmnElement = [
            'id' => 'di92j3892dj',
        ];

        $this->businessRule->expects($this->exactly(1))
            ->method('retrieveDefinitionData')
            ->will($this->returnValue($bpmnElement));

        $caseFlowHandler = $this->getMockBuilder('PMSECaseFlowHandler')
            ->setMethods(['retrieveBean', 'saveFormAction'])
            ->getMock();

        $definition = $this->getMockBuilder('pmse_BpmActivityDefinition')
            ->disableAutoload()
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $definition->act_fields = '298uj9sd812';
        $definition->pro_id = 'd8923dj982398d';
        $definition->act_field_module = 'Leads';

        $processDefinition = $this->getMockBuilder('pmse_BpmProcessDefinition')
            ->setMethods(['retrieve_by_string_fields'])
            ->getMock();
        $processDefinition->pro_module = 'Leads';

        $caseFlowHandler->method('retrieveBean')
            ->willReturnOnConsecutiveCalls($definition, $processDefinition);

        $dbHandler = $this->geTMockBuilder('DBHandler')
            ->setMethods(['Query', 'fetchByAssoc'])
            ->getMock();

        $this->businessRule->setCaseFlowHandler($caseFlowHandler);
        $this->businessRule->setDbHandler($dbHandler);

        $flowData = [
            'cas_id' => 1,
            'cas_index' => 3,
            'cas_sugar_object_id' => 'mnedwij8923d2',
            'bpmn_id' => 'di92j3892dj',
        ];

        $bean = new stdClass();

        $this->businessRule->run($flowData, $bean, '');
    }
}
