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

/**
 * SugarMathTest
 *
 * unit tests for math library
 */
class SugarMathTest extends TestCase
{
    private $sugarMath;

    public function testOperations()
    {
        $result = SugarMath::init(2, 2)->div(0);
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init(2, 2)->div('a');
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init('0', 2)->div('');
        $this->assertEquals('0.00', $result);


        $result = SugarMath::init(2, 2)->mod(0);
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init(2, 2)->mod('a');
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init('0', 2)->mod('');
        $this->assertEquals('0.00', $result);


        $result = SugarMath::init('0', 2)->powmod(0, 'xx');
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init(2, 2)->powmod(0, 0);
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init(2, 2)->powmod('', 0);
        $this->assertEquals('0.00', $result);

        $result = SugarMath::init('0', 2)->powmod('-2', '');
        $this->assertEquals('0.00', $result);
    }

    /**
     * test instance type of new instantiation
     *
     * @group math
     * @access public
     */
    public function testInstanceType()
    {
        // test default instance
        $math = new SugarMath();
        $this->assertInstanceOf('SugarMath', $math);

        // test default static instance
        $math = SugarMath::init();
        $this->assertInstanceOf('SugarMath', $math);
    }

    /**
     * test __toString brings back result
     *
     * @group math
     * @access public
     */
    public function testToString()
    {
        // test __toString brings back value.
        $math = new SugarMath(100);
        $this->assertEquals(100, sprintf('%s', $math));

        // test static instance
        $math = SugarMath::init(100);
        $this->assertEquals(100, sprintf('%s', $math));
    }

    /**
     * test setting value and getting result
     *
     * @dataProvider setGetValueProvider
     * @param mixed $result
     * @param mixed $setVal the value to set
     * @param int $scale the math precision to use
     * @group math
     * @access public
     */
    public function testSetGetValue($result, $setVal, $scale)
    {
        $this->assertSame($result, SugarMath::init($setVal, $scale)->result());
    }

    /**
     * get/set value data provider
     *
     * @group math
     * @access public
     */
    public function setGetValueProvider()
    {
        return [
            ['100', '100', 0],
            ['100.011', '100.011', 3],
            ['100.0000000000000001', '100.0000000000000001', 16],
            ['-100', '-100', 0],
            ['-100.011', '-100.011', 3],
            ['-100.0000000000000001', '-100.0000000000000001', 16],
            // strings or numbers should work the same,
            // so long as precision isn't
            // outside the range of a double
            ['100', 100, 0],
            ['100.011', 100.011, 3],
            ['-100', -100, 0],
            ['-100.011', -100.011, 3],
        ];
    }

    /**
     * test setting value and getting scale value
     *
     * @dataProvider setGetScaleProvider
     * @param mixed $setVal the value to set
     * @group math
     * @access public
     */
    public function testSetGetScale($setVal)
    {
        $this->assertEquals($setVal, SugarMath::init(0, $setVal)->getScale());
    }

    /**
     * get/set value data provider
     *
     * @group math
     * @access public
     */
    public function setGetScaleProvider()
    {
        return [
            [0],
            [1],
            [100],
            [100000],
            ['0'],
            ['1'],
            ['100'],
            ['100000'],
        ];
    }

    /**
     * test chained math operations
     *
     * @group math
     * @access public
     */
    public function testChainedOperations()
    {
        $math = SugarMath::init(10)->pow(2)->mod(3);
        $this->assertEquals(1, $math->result());
        // common test where PHP fails from rounding error
        $this->assertEquals(8, floor(SugarMath::init(0.1)->add(0.7)->mul(10)->result()));
        $this->assertEquals(1, SugarMath::init(10)->add(5)->sub(5)->mul(10)->div(10)->pow(2)->mod(3)->result());
    }

    /**
     * test powmod() math operations
     *
     * @group math
     * @access public
     */
    public function testPowModOperations()
    {
        $math = SugarMath::init(10, 0)->powmod(2, 3);
        $this->assertEquals(1, $math->result());
    }

    /**
     * test sqrt() math operations
     *
     * @dataProvider sqrtOperationsProvider
     * @param mixed $result
     * @param mixed $initVal
     * @param int $scale
     * @group math
     * @access public
     */
    public function testSqrtOperations($result, $initVal, $scale)
    {
        $math = SugarMath::init($initVal, $scale)->sqrt();
        $this->assertEquals($result, $math->result());
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function sqrtOperationsProvider()
    {
        return [
            [3, 9, 0],
            [9.1651, 84, 4],
            [10, 100, 0],
        ];
    }


    /**
     * test comp() math operations
     *
     * @dataProvider compOperationsProvider
     * @param mixed $initVal the initial value to compare from
     * @param mixed $compVal the value to compare to
     * @param int $result the expected result
     * @group math
     * @access public
     */
    public function testCompOperations($initVal, $compVal, $result)
    {
        $math = SugarMath::init($initVal);
        $this->assertEquals($result, $math->comp($compVal));
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function compOperationsProvider()
    {
        return [
            [100, 100, 0],
            [100, 99, 1],
            [100, 101, -1],
        ];
    }

    /**
     * test expression engine empty expressions
     *
     * @group math
     * @access public
     */
    public function testExpressionsEmpty()
    {
        // empty expression
        $this->assertEquals(0, SugarMath::init()->exp('')->result());
        $this->assertEquals(0, SugarMath::init()->exp('()')->result());
    }

    /**
     * test operations where PHP will normally create rounding errors, or fail on long precision
     *
     * @dataProvider longPrecisionOperationsProvider
     * @param mixed $initVal
     * @param string $method the operator to apply
     * @param mixed $opVal
     * @param mixed $result
     * @param int $scale
     * @group math
     * @access public
     */
    public function testLongPrecisionOperations($initVal, $method, $opVal, $result, $scale)
    {
        // 50 digits, 50 decimals, adding
        $math = SugarMath::init($initVal, $scale)->$method($opVal);
        $this->assertSame((string)$result, (string)$math->result());
    }

    /**
     * comp operations data provider
     *
     * @group math
     * @access public
     */
    public static function longPrecisionOperationsProvider()
    {
        return [
            [
                '99999999999999999999999999999999999999999999999999',
                'add',
                '100000000000000000000000000000000000000000000000000.99999999999999999999999999999999999999999999999991',
                '199999999999999999999999999999999999999999999999999.99999999999999999999999999999999999999999999999991',
                50,
            ],
            [
                '99999999999999999999999999999999999999999999999999',
                'add',
                '100000000000000000000000000000000000000000000000000.99999999999999999999999999999999999999999999999991',
                '199999999999999999999999999999999999999999999999999.9999999999999999999999999999999999999999999999999',
                49,
            ],
            [
                '99999999999999999999999999999999999999999999999999',
                'add',
                '100000000000000000000000000000000000000000000000000.99999999999999999999999999999999999999999999999999',
                '199999999999999999999999999999999999999999999999999.9999999999999999999999999999999999999999999999999',
                49,
            ],
        ];
    }

    /**
     * expression engine data provider
     *
     * @group math
     * @access public
     */
    public static function expressionsProvider()
    {
        return [
            ['3.00', '1+2', null, null],
            ['11.00', '1+2*3+4', null, null],
            ['13.00', '(1+2)*3+4', null, null],
            ['21.00', '(1+2)*(3+4)', null, null],
            ['147.00', '(1+2)*(3+4)^2', null, null],
            ['441.00', '((1+2)*(3+4))^2', null, null],
            ['30.25', '(3 * 2 - (4 / 8)) ^ 2', null, null],
            ['3.33', '10/3', null, 2],
            ['3.3333', '10/3', null, 4],
            ['3.3333333333333333333333333', '10/3', null, 25],
            ['3.33', '10/?', [3], 2],
            ['3.33', '?/?', [10, 3], 2],
            ['200.00', '(?+?)*10', [10, 10], null],
            ['1.00', '10%3', null, null],
            ['2', '?/?', [10, 6], 0],
            ['1.7', '?/?', [10, 6], 1],
            ['1.67', '?/?', [10, 6], 2],
            ['1.667', '?/?', [10, 6], 3],
            ['1.6667', '?/?', [10, 6], 4],
            ['1.66667', '?/?', [10, 6], 5],
            ['1.666667', '?/?', [10, 6], 6],
            ['1.6666667', '?/?', [10, 6], 7],
            ['802.458090', '?/?*?', ['1000', '1.246171', '1.0'], 6],
        ];
    }

    /**
     * test expression engine computations
     *
     * @dataProvider roundProvider
     * @param mixed $result the expected result of the computation
     * @param string $value the value to round
     * @param int $scale the math precision to use
     * @group math
     * @access public
     */
    public function testRound($result, $value, $scale)
    {
        $math = SugarMath::init(0, $scale);
        $this->assertSame($result, $math->round($value));
    }

    /**
     * expression engine data provider
     *
     * @group math
     */
    public static function roundProvider()
    {
        return [
            ['-500.000000', '-500.0000000', 6],
            ['3.354999999', '3.354999999', 9],
            ['3.35500000', '3.354999999', 8],
            ['3.3550000', '3.354999999', 7],
            ['3.355000', '3.354999999', 6],
            ['3.35500', '3.354999999', 5],
            ['3.3550', '3.354999999', 4],
            ['3.355', '3.354999999', 3],
            ['3.35', '3.354999999', 2],
            ['3.4', '3.354999999', 1],
            ['3', '3.354999999', 0],
        ];
    }


    /**
     * test setValue exceptions on class
     *
     * @dataProvider setValueExceptionsProvider
     *
     * @param mixed $val the value to pass
     * @group math
     * @access public
     */
    public function testSetValueExceptions($val)
    {
        $math = new SugarMath();

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('must be numeric');
        $math->setValue($val);
    }

    /**
     * setValue exceptions data provider
     *
     * @group math
     * @access public
     */
    public function setValueExceptionsProvider()
    {
        return [
            ['foo'],
            ['10,00.30'],
            ['10.20.30'],
            ['$10'],
            ['10,00'],
        ];
    }

    /**
     * test setScale exceptions on class
     *
     * @dataProvider setScaleExceptionsProvider
     *
     * @param mixed $val the value to pass
     * @group math
     * @access public
     */
    public function testSetScaleExceptions($val)
    {
        $math = new SugarMath();

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('scale must be a positive integer');
        $math->setScale($val);
    }

    /**
     * setScale exceptions data provider
     *
     * @group math
     * @access public
     */
    public function setScaleExceptionsProvider()
    {
        return [
            [10.44],
            [-10.44],
            [-2],
        ];
    }


    /**
     * test expression exceptions
     *
     * @dataProvider nonStringExpressionExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonStringExpressionExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('expression must be a string');
        $math->exp($exp, $args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonStringExpressionExceptionsProvider()
    {
        return [
            [100, null, null],
        ];
    }

    /**
     * test non-array args exceptions
     *
     * @dataProvider nonArrayArgsExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonArrayArgsExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('expression args must be an array');
        $math->exp($exp, $args)->result();
    }

    /**
     * arg exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonArrayArgsExceptionsProvider()
    {
        return [
            ['1+2', 100, null, 'non-array args should be caught'],
        ];
    }

    /**
     * test scale exceptions
     *
     * @dataProvider scaleExceptionsProvider
     *
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testScaleExceptions($scale)
    {
        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('scale must be a positive integer');

        new SugarMath(0, $scale);
    }

    /**
     * scale exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function scaleExceptionsProvider()
    {
        return [
            [-99],
        ];
    }

    /**
     * test non-matching parenthesis exceptions
     *
     * @dataProvider nonMatchingParenthesisExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonMatchingParenthesisExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('parenthesis mismatch');
        $math->exp($exp, $args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonMatchingParenthesisExceptionsProvider()
    {
        return [
            ['((1+2)', null, null],
            ['(1+2))', null, null],
        ];
    }

    /**
     * test non-numeric args exceptions
     *
     * @dataProvider nonNumericArgsExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testNonNumericArgsExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('arguments must be numeric');
        $math->exp($exp, $args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonNumericArgsExceptionsProvider()
    {
        return [
            ['1+?', ['abc'], null],
            ['1+?', ['abc'], null],
        ];
    }

    /**
     * test invalid expressions exceptions
     *
     * @dataProvider nonInvalidExpressionsExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testInvalidExpressionsExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('invalid expression syntax');
        $math->exp($exp, $args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonInvalidExpressionsExceptionsProvider()
    {
        return [
            ['1+2* abc(3+4)', null, null],
        ];
    }

    /**
     * test groups operators expressions exceptions
     *
     * @dataProvider nonGroupedOperatorsExpressionsExceptionsProvider
     *
     * @param string $exp string the expression
     * @param array $args array the argument array for the expression
     * @param int $scale the decimal precision to use
     * @group math
     * @access public
     */
    public function testGroupedOperatorsExpressionExceptions($exp, $args, $scale)
    {
        $math = new SugarMath(0, $scale);

        $this->expectException(SugarMath_Exception::class);
        $this->expectExceptionMessage('grouped operators error');
        $math->exp($exp, $args)->result();
    }

    /**
     * non-string exceptions data provider
     *
     * @group math
     * @access public
     */
    public static function nonGroupedOperatorsExpressionsExceptionsProvider()
    {
        return [
            ['1+*2', null, null],
        ];
    }
}
