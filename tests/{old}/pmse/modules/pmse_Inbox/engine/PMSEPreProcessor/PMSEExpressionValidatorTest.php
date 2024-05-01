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

class PMSEExpressionValidatorTest extends TestCase
{
    /**
     * @var PMSELogger
     */
    protected $loggerMock;

    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder('PMSELogger')
            ->disableOriginalConstructor()
            ->setMethods(['info', 'debug'])
            ->getMock();
    }

    public function testValidateRequest()
    {
        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(['validateExpression', 'validateParamsRelated'])
            ->getMock();

        $expressionValidatorMock->setLogger($this->loggerMock);

        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setFlowData(['evn_id' => 'NO_TERMINATE']);
        $request->setBean(new stdClass());

        $expressionValidatorMock->expects($this->once())
            ->method('validateExpression');
        $expressionValidatorMock->expects($this->once())
            ->method('validateParamsRelated')
            ->will($this->returnValue([]));

        $expressionValidatorMock->validateRequest($request);
    }

    public function testValidateExpressionEmpty()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $flowDataMock = ['evn_criteria' => '[]'];
        $beanMock = new stdClass();

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods([
                'getLogger',
            ])
            ->getMock();
        $expressionValidatorMock->setLevel(1);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(['evaluateExpression', 'condition'])
            ->getMock();

        $expressionValidatorMock->expects($this->any())
            ->method('getLogger')
            ->will($this->returnValue($this->loggerMock));
        $expressionValidatorMock->setEvaluator($expressionEvaluatorMock);

        $expressionValidatorMock->validateExpression($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }

    public function testValidateExpressionWithConditionTrue()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $flowDataMock = ['evn_criteria' => '[{1==1}]'];
        $beanMock = new stdClass();

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $expressionValidatorMock->setLevel(1);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(['evaluateExpression', 'condition'])
            ->getMock();

        $expressionEvaluatorMock->expects($this->once())
            ->method('evaluateExpression')
            ->will($this->returnValue(true));

        $expressionValidatorMock->setLogger($this->loggerMock);
        $expressionValidatorMock->setEvaluator($expressionEvaluatorMock);

        $expressionValidatorMock->validateExpression($beanMock, $flowDataMock, $request);
        $this->assertEquals(true, $request->isValid());
    }

    public function testValidateExpressionWithConditionFalse()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $flowDataMock = ['evn_criteria' => '[{1==1}]'];
        $beanMock = new stdClass();

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $expressionValidatorMock->setLevel(1);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(['evaluateExpression', 'condition'])
            ->getMock();

        $expressionEvaluatorMock->expects($this->once())
            ->method('evaluateExpression')
            ->will($this->returnValue(false));

        $expressionValidatorMock->setLogger($this->loggerMock);
        $expressionValidatorMock->setEvaluator($expressionEvaluatorMock);

        $expressionValidatorMock->validateExpression($beanMock, $flowDataMock, $request);
        $this->assertEquals(false, $request->isValid());
    }

    public function testValidateParamsRelatedWithoutRelationship()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setExternalAction('EVALUATE_MAIN_MODULE');
        $flowDataMock = ['evn_criteria' => '[{1==1}]', 'cas_sugar_module' => 'Leads', 'cas_sugar_object_id' => 'id'];
        $beanMock = new stdClass();
        $beanMock->id = 'id';
        $beanMock->module_name = 'Leads';

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expressionValidatorMock->setLogger($this->loggerMock);
        $result = $expressionValidatorMock->validateParamsRelated($beanMock, $flowDataMock, $request);
        $this->assertEquals([], $result);
    }

    public function testValidateParamsRelatedWithRelationship()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setExternalAction('EVALUATE_RELATED_MODULE');
        $flowDataMock = ['evn_criteria' => '[{1==1}]'];
        $flowDataMock['rel_process_module'] = 'PARENT_MODULE';
        $flowDataMock['cas_sugar_object_id'] = 'PARENT_ID';
        $flowDataMock['rel_element_relationship'] = 'MODULE_RELATIONSHIP';
        $flowDataMock['rel_element_module'] = 'MODULE_ELEMENT';
        $flowDataMock['cas_sugar_module'] = 'PARENT_MODULE';

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationships'])
            ->getMock();

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(['hasValidRelationship'])
            ->getMock();
        $expressionValidatorMock->expects($this->any())
            ->method('hasValidRelationship')
            ->will($this->returnValue(true));

        $paramsResult = [
            'replace_fields' => [
                'MODULE_RELATIONSHIP' => 'MODULE_ELEMENT',
            ],
        ];

        $expressionValidatorMock->setLogger($this->loggerMock);
        $result = $expressionValidatorMock->validateParamsRelated($beanMock, $flowDataMock, $request);
        $this->assertEquals($paramsResult, $result);
    }

    public function testValidateParamsRelatedWithInvalidRelationship()
    {
        $request = ProcessManager\Factory::getPMSEObject('PMSERequest');
        $request->setExternalAction('EVALUATE_RELATED_MODULE');
        $flowDataMock = ['evn_criteria' => '[{1==1}]'];
        $flowDataMock['rel_process_module'] = 'PARENT_MODULE';
        $flowDataMock['cas_sugar_object_id'] = 'PARENT_ID';
        $flowDataMock['rel_element_relationship'] = 'MODULE_RELATIONSHIP';
        $flowDataMock['rel_element_module'] = 'MODULE_ELEMENT';
        $flowDataMock['cas_sugar_module'] = 'PARENT_MODULE';

        $beanMock = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationships'])
            ->getMock();

        $expressionValidatorMock = $this->getMockBuilder('PMSEExpressionValidator')
            ->disableOriginalConstructor()
            ->setMethods(['hasValidRelationship'])
            ->getMock();
        $expressionValidatorMock->expects($this->any())
            ->method('hasValidRelationship')
            ->will($this->returnValue(false));

        $paramsResult = [];

        $expressionValidatorMock->setLogger($this->loggerMock);
        $result = $expressionValidatorMock->validateParamsRelated($beanMock, $flowDataMock, $request);
        $this->assertEquals($paramsResult, $result);
        $this->assertEquals(false, $request->isValid());
    }
}
