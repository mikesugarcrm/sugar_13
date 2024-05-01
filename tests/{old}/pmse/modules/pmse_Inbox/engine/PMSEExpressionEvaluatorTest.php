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

class PMSEExpressionEvaluatorTest extends TestCase
{
    /**
     * Sets up the test data, for example,
     *     opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        // The default timezone is set to phoenix because the server could
        // have a different timezone that triggers failures with the tests
        // already defined values.
        date_default_timezone_set('America/Phoenix');
    }

    /**
     * @covers PMSEExpressionEvaluator::executeDateSpanBCOp
     */
    public function testExecuteDateSpanBCOp()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $bcMock = $this->getMockBuilder('SugarBean')
            ->setMethods(['getIncrementedBusinessDatetime'])
            ->getMock();

        $now = new DateTime();
        $bcMock->expects($this->once()) // expect getIncrementedBusinessDatetime to be called once
        ->method('getIncrementedBusinessDatetime')
            ->willReturn($now->format(DateTimeInterface::ATOM));

        // to make BeanFactory::getBean inside executeDateSpanBCOp happy
        $bcMock->id = 'random_id';
        $bcMock->module_name = 'BusinessCenters';
        BeanFactory::registerBean($bcMock);

        $expressionEvaluatorMock->executeDateSpanBCOp($now, '+', '4bh', $bcMock->id);

        // verify that appropriate exception is thrown when it can't find the business center
        // and the exception code is correct
        $code = null;

        try {
            // pass in an invalid id, it should throw an exception
            $expressionEvaluatorMock->executeDateSpanBCOp(new DateTime(), '+', '4bh', 'invalid_id');
        } catch (PMSEExpressionEvaluationException $e) {
            $code = $e->getCode();
        }

        $this->assertEquals(PMSEExpressionEvaluator::getExceptionCode('NO_BUSINESS_CENTER'), $code);
    }

    /**
     * Test exception code accessors
     */
    public function testExceptionCodeAccessors()
    {
        $this->assertEquals(100, PMSEExpressionEvaluator::getExceptionCode('NO_BUSINESS_CENTER'));
        $this->assertTrue(PMSEExpressionEvaluator::hasExceptionCode('NO_BUSINESS_CENTER'));

        PMSEExpressionEvaluator::addExceptionCode('NEW_CODE', 123);
        $this->assertEquals(123, PMSEExpressionEvaluator::getExceptionCode('NEW_CODE'));

        $this->assertTrue(PMSEExpressionEvaluator::removeExceptionCode('NEW_CODE'));
        $this->assertFalse(PMSEExpressionEvaluator::hasExceptionCode('NEW_CODE'));
    }

    public function testEvaluateSingleElementZero()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "0",
                "expValue": 0
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 0;
        $expectedToken->expLabel = '0';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateSingleElement()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 2;
        $expectedToken->expLabel = '2';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateMultiply()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 8;
        $expectedToken->expLabel = '8';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateDivide()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "/",
                "expValue": "/"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 1;
        $expectedToken->expLabel = '1';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }


    public function testEvaluateAdd()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 7;
        $expectedToken->expLabel = '7';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateSubstract()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 1;
        $expectedToken->expLabel = '1';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateComplexMultiplySum()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 11;
        $expectedToken->expLabel = '11';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateComplexSumMultiply()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            }
        ]';

        $expression = json_decode($fixture);

        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 15;
        $expectedToken->expLabel = '15';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->evaluateExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testEvaluateComplexMultiplication()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "1",
                "expValue": 1
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 15;
        $expectedToken->expLabel = '15';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];

        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessExpression()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "7",
                "expValue": 7
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 63;
        $expectedToken->expLabel = '63';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }


    public function testProcessComplexExpression()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "7",
                "expValue": 7
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "5",
                "expValue": 5
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 30;
        $expectedToken->expLabel = '30';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessComplexRelationalExpressions()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "7",
                "expValue": 7
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "5",
                "expValue": 5
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "30",
                "expValue": 30
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessComplexExpressionFalseCondition()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "4",
                "expValue": 4
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "7",
                "expValue": 7
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "5",
                "expValue": 5
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "31",
                "expValue": 31
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = false;
        $expectedToken->expLabel = 'false';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     */
    public function testProcessExpressionFirstAmbiguousExpression()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "6",
                "expValue": 6
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "/",
                "expValue": "/"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "1",
                "expValue": 1
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 9;
        $expectedToken->expLabel = '9';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionSecondAmbiguousExpression()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "48",
                "expValue": 48
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "/",
                "expValue": "/"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "2",
                "expValue": 2
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "x",
                "expValue": "x"
            },
            {
                "expType": "GROUP",
                "expLabel": "(",
                "expValue": "("
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "9",
                "expValue": 9
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "number",
                "expLabel": "3",
                "expValue": 3
            },
            {
                "expType": "GROUP",
                "expLabel": ")",
                "expValue": ")"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = 288;
        $expectedToken->expLabel = '288';
        $expectedToken->expSubtype = 'number';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionString()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "ELEMENT",
                "expValue": "ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "ELEMENT",
                "expValue": "ELEMENT"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionDifferentString()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "ELEMENT",
                "expValue": "ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "!=",
                "expValue": "!="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "DIFERENT_ELEMENT",
                "expValue": "DIFERENT_ELEMENT"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionLogicOperators()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_ELEMENT",
                "expValue": "FIRST_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "!=",
                "expValue": "!="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_DIFERENT_ELEMENT",
                "expValue": "FIRST_DIFERENT_ELEMENT"
            },
            {
                "expType": "LOGIC",
                "expLabel": "AND",
                "expValue": "AND"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_ELEMENT",
                "expValue": "SECOND_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "!=",
                "expValue": "!="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_DIFERENT_ELEMENT",
                "expValue": "SECOND_DIFERENT_ELEMENT"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionLogicOperatorsAND()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_ELEMENT",
                "expValue": "FIRST_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_DIFERENT_ELEMENT",
                "expValue": "FIRST_DIFERENT_ELEMENT"
            },
            {
                "expType": "LOGIC",
                "expLabel": "AND",
                "expValue": "AND"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_ELEMENT",
                "expValue": "SECOND_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "!=",
                "expValue": "!="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_DIFERENT_ELEMENT",
                "expValue": "SECOND_DIFERENT_ELEMENT"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = false;
        $expectedToken->expLabel = 'false';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionLogicOperatorsOR()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_ELEMENT",
                "expValue": "FIRST_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_DIFERENT_ELEMENT",
                "expValue": "FIRST_DIFERENT_ELEMENT"
            },
            {
                "expType": "LOGIC",
                "expLabel": "OR",
                "expValue": "OR"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_ELEMENT",
                "expValue": "SECOND_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "!=",
                "expValue": "!="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "SECOND_DIFERENT_ELEMENT",
                "expValue": "SECOND_DIFERENT_ELEMENT"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionNOT()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_ELEMENT",
                "expValue": "FIRST_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_DIFERENT_ELEMENT",
                "expValue": "FIRST_DIFERENT_ELEMENT"
            },
            {
                "expType": "LOGIC",
                "expLabel": "OR",
                "expValue": "OR"
            },
            {
                "expType": "LOGIC",
                "expLabel": "NOT",
                "expValue": "NOT"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "bool",
                "expLabel": "false",
                "expValue": false
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionComplexNOT()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_ELEMENT",
                "expValue": "FIRST_ELEMENT"
            },
            {
                "expType": "COMPARISON",
                "expLabel": "==",
                "expValue": "=="
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "string",
                "expLabel": "FIRST_DIFERENT_ELEMENT",
                "expValue": "FIRST_DIFERENT_ELEMENT"
            },
            {
                "expType": "LOGIC",
                "expLabel": "OR",
                "expValue": "OR"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "bool",
                "expLabel": "false",
                "expValue": false
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = false;
        $expectedToken->expLabel = 'false';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionDATES()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "date",
                "expLabel": "2014-10-16 00:00:00",
                "expValue": "2014-10-16T00:00:00-07:00"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5d",
                "expValue": "5d"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = '2014-10-21T00:00:00-07:00';
        $expectedToken->expLabel = '2014-10-21 00:00:00';
        $expectedToken->expSubtype = 'date';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation 6/2x(1+2)
     * the result is the same
     */
    public function testProcessExpressionDATETIME()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "datetime",
                "expLabel": "2014-10-16 00:00:00",
                "expValue": "2014-10-16T00:00:00-07:00"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5d",
                "expValue": "5d"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = '2014-10-21T00:00:00-07:00';
        $expectedToken->expLabel = '2014-10-21 00:00:00';
        $expectedToken->expSubtype = 'date';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation
     * the result is the same
     */
    public function testProcessExpressionComplexDATES()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "date",
                "expLabel": "2014-05-16 00:00:00",
                "expValue": "2014-05-16T00:00:00-07:00"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5m",
                "expValue": "5m"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "4d",
                "expValue": "4d"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "4h",
                "expValue": "4h"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "1h",
                "expValue": "1h"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "20min",
                "expValue": "20min"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5min",
                "expValue": "5min"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expType = 'CONSTANT';
        $expectedToken->expValue = '2014-10-20T03:15:00-07:00';
        $expectedToken->expLabel = '2014-10-20 03:15:00';
        $expectedToken->expSubtype = 'date';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation
     * the result is the same
     */
    public function testProcessExpressionComplexDATETIME()
    {
        $fixture = '[
            {
                "expType": "CONSTANT",
                "expSubtype": "datetime",
                "expLabel": "2014-05-16 00:00:00",
                "expValue": "2014-05-16T00:00:00-07:00"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5m",
                "expValue": "5m"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "4d",
                "expValue": "4d"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "4h",
                "expValue": "4h"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "1h",
                "expValue": "1h"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "+",
                "expValue": "+"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "20min",
                "expValue": "20min"
            },
            {
                "expType": "ARITHMETIC",
                "expLabel": "-",
                "expValue": "-"
            },
            {
                "expType": "CONSTANT",
                "expSubtype": "timespan",
                "expLabel": "5min",
                "expValue": "5min"
            }
        ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expType = 'CONSTANT';
        $expectedToken->expValue = '2014-10-20T03:15:00-07:00';
        $expectedToken->expLabel = '2014-10-20 03:15:00';
        $expectedToken->expSubtype = 'date';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation
     * the result is the same
     */
    public function testProcessExpressionSampleCriteriaFALSE()
    {
        $fixture = '[
                        {
                            "expType": "GROUP",
                            "expLabel": "(",
                            "expValue": "("
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "GROUP",
                            "expLabel": ")",
                            "expValue": ")"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "AND",
                            "expValue": "AND"
                        },
                        {
                            "expType": "GROUP",
                            "expLabel": "(",
                            "expValue": "("
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "LOGIC",
                            "expLabel": "OR",
                            "expValue": "OR"
                        },
                        {
                            "expType": "CONSTANT",
                            "expValue": false,
                            "expSubtype": "boolean",
                            "expLabel": "false"
                        },
                        {
                            "expType": "GROUP",
                            "expLabel": ")",
                            "expValue": ")"
                        }
                    ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = false;
        $expectedToken->expLabel = 'false';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * The following tests test the evaluator against the operation
     * the result is the same
     */
    public function testProcessExpressionSampleCriteriaTRUE()
    {
        $fixture = '[
                        {
                          "expType": "GROUP",
                          "expLabel": "(",
                          "expValue": "("
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": true,
                          "expSubtype": "boolean",
                          "expLabel": "true"
                        },
                        {
                          "expType": "GROUP",
                          "expLabel": ")",
                          "expValue": ")"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "AND",
                          "expValue": "AND"
                        },
                        {
                          "expType": "GROUP",
                          "expLabel": "(",
                          "expValue": "("
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": true,
                          "expSubtype": "boolean",
                          "expLabel": "true"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        },
                        {
                          "expType": "LOGIC",
                          "expLabel": "OR",
                          "expValue": "OR"
                        },
                        {
                          "expType": "CONSTANT",
                          "expValue": false,
                          "expSubtype": "boolean",
                          "expLabel": "false"
                        }
                    ]';

        $expression = json_decode($fixture);
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedToken = new stdClass();
        $expectedToken->expValue = true;
        $expectedToken->expLabel = 'true';
        $expectedToken->expSubtype = 'boolean';
        $expectedToken->expType = 'CONSTANT';

        $expectedResult = [$expectedToken];
        $result = $expressionEvaluatorMock->processExpression($expression);

        $this->assertEquals($expectedResult, $result);
    }

    public function testProcessDateInterval()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $interval = '4d';
        $expected = 'P4D';

        $result = $expressionEvaluatorMock->processDateInterval($interval);
        $this->assertEquals($expected, $result->format('P%dD'));
    }

    public function testProcessDateIntervalMonths()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $interval = '5m';
        $expected = 'P5M';
        $result = $expressionEvaluatorMock->processDateInterval($interval);
        $this->assertEquals($expected, $result->format('P%mM'));
    }

    public function testExecuteDateDateOp()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $date1 = '2014-10-16T00:00:00-07:00';
        $operator = '-';
        $date2 = '2014-05-16T00:00:00-07:00';
        $expected = 'P5M';
        $result = $expressionEvaluatorMock->executeDateDateOp($date1, $operator, $date2);
        $this->assertEquals($expected, $result->format('P%mM'));
    }

    public function testExecuteDateSpanOp()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $date = '2014-10-16T00:00:00-07:00';
        $interval = '5m';
        $operator = '+';
        $expected = '2015-03-16 00:00:00';
        $result = $expressionEvaluatorMock->executeDateSpanOp($date, $operator, $interval);
        $this->assertEquals($expected, $result->format('Y-m-d H:i:s'));
        $operator = '-';
        $expected = '2014-05-16 00:00:00';
        $result = $expressionEvaluatorMock->executeDateSpanOp($date, $operator, $interval);
        $this->assertEquals($expected, $result->format('Y-m-d H:i:s'));
    }

    public function testExecuteSpanDateOp()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $date = '2014-10-16T00:00:00-07:00';
        $interval = '5m';
        $operator = '+';
        $expected = '2015-03-16 00:00:00';
        $result = $expressionEvaluatorMock->executeSpanDateOp($interval, $operator, $date);
        $this->assertEquals($expected, $result->format('Y-m-d H:i:s'));
    }

    public function testExecuteSpanSpanOp()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $interval1 = '5d';
        $interval2 = '4d';
        $operator = '+';
        $expected = 'P9D';
        $result = $expressionEvaluatorMock->executeSpanSpanOp($interval1, $operator, $interval2);
        $this->assertEquals($expected, $result->format('P%dD'));
    }

    public function testEvalEqualArrays()
    {
        $expressionEvaluatorMock = $this->getMockBuilder('PMSEExpressionEvaluator')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        // Compare two identical arrays
        $arr1 = [1, 2, 3, 4, 5, 6];
        $arr2 = [1, 2, 3, 4, 5, 6];
        $result = $expressionEvaluatorMock->evalEqualArrays($arr1, $arr2);
        $this->assertTrue($result);

        // Compare two arrays with same content but different content order
        $arr1 = [1, 2, 3, 4, 5, 6];
        $arr2 = [6, 2, 3, 1, 5, 4];
        $result = $expressionEvaluatorMock->evalEqualArrays($arr1, $arr2);
        $this->assertTrue($result);

        //Compare two arrays with some values in both of them
        $arr1 = [1, 2, 3, 4, 5, 6];
        $arr2 = [2, 4, 6, 8, 10];
        $result = $expressionEvaluatorMock->evalEqualArrays($arr1, $arr2);
        $this->assertFalse($result);

        // Compare two different arrays same length
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [6, 7, 8, 9, 0];
        $result = $expressionEvaluatorMock->evalEqualArrays($arr1, $arr2);
        $this->assertFalse($result);

        //Compare two different arrays, different length
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [6, 7, 8];
        $result = $expressionEvaluatorMock->evalEqualArrays($arr1, $arr2);
        $this->assertFalse($result);
    }
}
