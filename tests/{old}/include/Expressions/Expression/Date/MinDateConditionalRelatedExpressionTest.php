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

class MinDateConditionalRelatedExpressionTest extends TestCase
{
    public static function dataProviderEvaluate()
    {
        // Each data set consists of:
        // 1. A list of [date_closed, product_type, renewable] fields for related RLIs
        // 2. The expected result of the expression
        return [
            [
                // Test all RLIs satisfying all conditions
                [
                    [self::convertToDBFormat('2019-01-01', 'Y-m-d'), 'Existing Business', 1],
                    [self::convertToDBFormat('2020-01-01', 'Y-m-d'), 'Existing Business', 1],
                    [self::convertToDBFormat('2021-01-01', 'Y-m-d'), 'Existing Business', 1],
                ],
                self::convertToDBFormat('2019-01-01', 'Y-m-d'),
            ],
            [
                // Test an RLI not satisfying condition 1
                [
                    [self::convertToDBFormat('2019-12-20', 'Y-m-d'), 'Existing Business', 1],
                    [self::convertToDBFormat('2019-12-18', 'Y-m-d'), 'New Business', 1],
                    [self::convertToDBFormat('2019-12-19', 'Y-m-d'), 'Existing Business', 1],
                ],
                self::convertToDBFormat('2019-12-19', 'Y-m-d'),
            ],
            [
                // Test an RLI not satisfying condition 2
                [
                    [self::convertToDBFormat('2019-10-01', 'Y-m-d'), 'Existing Business', 0],
                    [self::convertToDBFormat('2019-11-01', 'Y-m-d'), 'Existing Business', 1],
                    [self::convertToDBFormat('2019-12-01', 'Y-m-d'), 'Existing Business', 1],
                ],
                self::convertToDBFormat('2019-11-01', 'Y-m-d'),
            ],
            [
                // Test an RLI not satisfying either condition
                [
                    [self::convertToDBFormat('2019-01-01', 'Y-m-d'), 'New Business', 0],
                    [self::convertToDBFormat('2019-01-02', 'Y-m-d'), 'Existing Business', 1],
                    [self::convertToDBFormat('2019-01-03', 'Y-m-d'), 'Existing Business', 1],
                ],
                self::convertToDBFormat('2019-01-02', 'Y-m-d'),
            ],
            [
                // Test no RLIs satisfying any of the conditions
                [
                    [self::convertToDBFormat('2019-01-02', 'Y-m-d'), 'New Business', 0],
                    [self::convertToDBFormat('2020-03-04', 'Y-m-d'), 'New Business', 0],
                    [self::convertToDBFormat('2021-05-06', 'Y-m-d'), 'New Business', 0],
                ],
                '',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderEvaluate
     */
    public function testEvaluate($rliData, $expected)
    {
        // Create an account as the base module
        $account = $this->getMockBuilder('Account')
            ->setMethods(['save'])
            ->getMock();

        // Mock the revenuelineitems link of the account
        $link2 = $this->getMockBuilder('Link2')
            ->setConstructorArgs(['revenuelineitems', $account])
            ->setMethods(['getBeansForSugarLogic'])
            ->getMock();
        $account->revenuelineitems = $link2;

        // Create the mock related RLIs
        $rliSet = array_map([$this, 'createRelatedRLI'], $rliData);

        // Set up the mock revenuelineitems link to return the mock related RLIs
        $link2->expects($this->any())
            ->method('getBeansForSugarLogic')
            ->will($this->returnValue($rliSet));

        // Create the expression to find the minimum date_closed date value among
        // the account's related RLIs where type is "Existing Business" and
        // renewable is true
        $expr = 'rollupConditionalMinDate(
        $revenuelineitems,
        "date_closed",
        createList("product_type","renewable"),
        createList("Existing Business","1")
        )';

        // Evaluate the expression to see if it returns the expected result
        $result = Parser::evaluate($expr, $account)->evaluate();
        $this->assertEquals($expected, $result);
    }

    /**
     * Test helper function to create an RLI
     * @param array $rliData array containing the test data for a single RLI
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createRelatedRLI($rliData)
    {
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();
        $rli->date_closed = $rliData[0];
        $rli->product_type = $rliData[1];
        $rli->renewable = $rliData[2];
        return $rli;
    }

    /**
     * Test helper function to convert the test dates into proper database format
     * @param string $date the date string to convert
     * @param string $format the format of the test date string
     * @return string the original date string formatted into DB format
     */
    public static function convertToDBFormat($date, $format)
    {
        return SugarDateTime::createFromFormat($format, $date)->setTime(0, 0, 0)->asDbDate(false);
    }
}
