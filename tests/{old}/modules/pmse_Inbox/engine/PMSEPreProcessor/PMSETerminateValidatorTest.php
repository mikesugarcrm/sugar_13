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
use Sugarcrm\Sugarcrm\ProcessManager;

/**
 * @coversDefaultClass \PMSETerminateValidator
 */
class PMSETerminateValidatorTest extends TestCase
{
    private function getRequestObject()
    {
        $request = new PMSERequest();
        $request->setType('hook');
        $request->setArguments([]);
        $request->setCreateThread(false);
        $request->setExternalAction('');

        return $request;
    }

    private function getMockEvaluator($return = false)
    {
        $evaluator = $this->createPartialMock(
            'PMSEEvaluator',
            [
                'evaluateExpression',
                'condition',
            ]
        );

        $evaluator->method('evaluateExpression')->willReturn($return);
        $evaluator->method('condition')->willReturn('noop');

        return $evaluator;
    }

    private function runAssertions(PMSERequest $request, $result = '')
    {
        $this->assertSame($result, $request->getResult());
        $this->assertSame('VALID', $request->getStatus());
        $this->assertSame(true, $request->isValid());
    }

    public function testValidateRequest()
    {
        $tv = new \PMSETerminateValidator();
        $request = $tv->validateRequest($this->getRequestObject());

        // First assertion is the most basic... no bean, so invalid
        $this->assertFalse($request->isValid());

        // Second test covers everything else... have a bean. Literally all cases
        // where there is a bean means the request is valid, even if we match
        // terminate criteria since that is handled later down the line
        $request = $this->getRequestObject();
        $request->setBean(new \stdClass());
        $request->setFlowData(['evn_id' => 'foo']);
        $request = $tv->validateRequest($request);
        $this->runAssertions($request);
    }

    public function testValidateExpression()
    {
        $flowData = [];
        $tv = new \PMSETerminateValidator();

        // Set the evaluator into the validator
        $tv->setEvaluator($this->getMockEvaluator());

        // Set up the logger
        $logger = $this->createPartialMock('PMSELogger', ['log']);
        $logger->method('log')->willReturn(true);

        // Set the logger into the validator
        $tv->setLogger($logger);

        // Now we need our required elements
        $request = $this->getRequestObject();

        // Start testing, first with empty criteria
        $request = $tv->validateExpression('', ['evn_criteria' => ''], $request);
        $this->runAssertions($request);

        // Now test with empty array criteria
        $request = $tv->validateExpression('', ['evn_criteria' => '[]'], $request);
        $this->runAssertions($request);

        // Now test with real criteria and make sure the code knows this is an update
        $request->setArguments(['isUpdate' => true]);
        $flowData['evn_criteria'] = '[{"expOperator":"changes"}]';

        // We can use a stdClass for the bean here
        $request = $tv->validateExpression(new stdClass(), $flowData, $request);
        $this->runAssertions($request);

        // Now test the same scenarior, but with the evaluator returning true
        // This is still a valid request, but the result is different
        $tv->setEvaluator($this->getMockEvaluator(true));

        $request = $tv->validateExpression(new stdClass(), $flowData, $request);
        $this->runAssertions($request, 'TERMINATE_CASE');
    }
}
