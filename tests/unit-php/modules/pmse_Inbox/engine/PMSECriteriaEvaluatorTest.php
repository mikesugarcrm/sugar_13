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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;
use stdClass;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \PMSECriteriaEvaluator
 */
class PMSECriteriaEvaluatorTest extends TestCase
{
    /**
     * @covers ::isCriteriaToken
     * @dataProvider isCriteriaTokenProvider
     */
    public function testIsCriteriaToken(stdClass $token, $expect)
    {
        $criteriaEvaluatorMock = $this->getMockBuilder('\PMSECriteriaEvaluator')
            // The original constructor instantiates the PMSEExpressionEvaluator class by using
            // ProcessManager\Factory::getPMSEObject. Easiest way to avoid that is not running it.
            // We don't need to mock any methods out as other methods of the class aren't called.
            ->setMethods(null)
            ->getMock();

        $result = $criteriaEvaluatorMock->isCriteriaToken($token);
        $this->assertEquals($expect, $result);
    }

    public function isCriteriaTokenProvider()
    {
        $o1 = (object)['expType' => 'USER_IDENTITY'];
        $o2 = (object)['expType' => 'CONSTANT'];

        return [
            ['token' => $o1, 'expect' => true],
            ['token' => $o2, 'expect' => false],
        ];
    }

    /**
     * @covers ::evaluateCriteriaToken
     * @dataProvider evaluateCriteriaTokenSubtypeProvider
     */
    public function testEvaluateCriteriaToken(stdClass $expression, $operator, $expect)
    {
        // Since the evaluateCriteriaToken method uses the PMSEExpressionEvaluator
        // class, we mock it out.
        $expressionEvaluatorMock = $this->createMock('\PMSEExpressionEvaluator');

        $expressionEvaluatorMock->expects($this->once())
            ->method('routeFunctionOperator')
            // Note that the getSubtype function is mocked out below this.
            ->with($operator, $expression->currentValue[0], $expression->expOperator, $expression->expValue, $expression->expSubtype)
            // I just return this because the rest of the function doesn't need it and it's easy to deal with.
            ->willReturn('Goatman');

        $expressionEvaluatorMock->expects($this->once())
            ->method('processTokenAttributes')
            ->willReturnArgument(0);

        $mock = $this->getMockBuilder('\PMSECriteriaEvaluator')
            ->disableOriginalConstructor()
            // getSubtype is called by evaluateCriteriaToken so we mock it to avoid testing it.
            ->setMethods(['getSubtype'])
            ->getMock();

        // Because $expressionEvaluator is protected, we need reflection to set it.
        TestReflection::setProtectedValue($mock, 'expressionEvaluator', $expressionEvaluatorMock);

        $mock->expects($this->once())
            ->method('getSubtype')
            ->with($expression)
            ->willReturn($expect);

        $mock->evaluateCriteriaToken($expression);
    }

    public function evaluateCriteriaTokenSubtypeProvider()
    {
        $o1 = (object)[
            'expType' => 'CONTROL',
            'expSubtype' => 'string',
            'expLabel' => 'Task # 4 >= Approved',
            'expOperator' => 'major_equals_than',
            'expValue' => 'Approved',
            'currentValue' => ['$expValue'],
            'expField' => '17842861053ee3573bcb7a4046264057',
        ];

        $o2 = (object)[
            'expType' => 'CONTROL',
            'expSubtype' => null,
            'expLabel' => 'Task # 4 >= Approved',
            'expOperator' => 'major_equals_than',
            'expValue' => 'Approved',
            'currentValue' => ['$expValue'],
            'expField' => '17842861053ee3573bcb7a4046264057',
        ];
        return [
            [
                'expression' => $o1,
                'operator' => 'relation',
                'expect' => 'string',
            ],
            [
                'expression' => $o2,
                'operator' => 'relation',
                'expect' => null,
            ],
        ];
    }

    /**
     * @covers ::evaluateCriteriaTokenList
     */
    public function testEvaluateCriteriaTokenList()
    {
        // Since we don't actually use evaluateCriteriaToken in a unit test, the
        // data does not have to be appropriate for it.
        $expression = [
            (object)['expType' => 'goat1'],
            (object)['expType' => 'goat2'],
            (object)['expType' => 'goat3'],
            (object)['expType' => 'goat4'],
            (object)['expType' => 'goat5'],
            (object)['expType' => 'goat6'],
        ];

        $criteriaEvaluatorMock = $this->getMockBuilder('PMSECriteriaEvaluator')
            ->disableOriginalConstructor()
            // Mock out other functions of the class that are called
            ->setMethods(['evaluateCriteriaToken', 'isCriteriaToken'])
            ->getMock();

        $criteriaEvaluatorMock->expects($this->exactly(3))
            ->method('evaluateCriteriaToken')
            // I just return this because it's easy to validate.
            ->willReturn('boggle');

        $criteriaEvaluatorMock->expects($this->exactly(6))
            ->method('isCriteriaToken')
            // The order of true/false will effect how the output is ordered because of the mock above.
            ->will($this->onConsecutiveCalls(true, false, true, true, false, false));

        $expectedList = '[
                        "boggle",
                        {
                            "expType": "goat2"
                        },
                        "boggle",
                        "boggle",
                        {
                            "expType": "goat5"
                        },
                        {
                            "expType": "goat6"
                        }
                    ]';

        $expectedList = json_decode($expectedList);

        $result = $criteriaEvaluatorMock->evaluateCriteriaTokenList($expression);

        $this->assertEquals($expectedList, $result);
    }
}
