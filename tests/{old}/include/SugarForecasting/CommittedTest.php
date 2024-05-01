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
 * @coversDefaultClass SugarForecasting_Committed
 */
class CommittedTest extends TestCase
{
    /**
     * @covers ::adjustWorksheetForArguments
     * @dataProvider providerTestAdjustWorksheetForArguments
     */
    public function testAdjustWorksheetForArguments($args, $worksheetTotals, $fieldExt, $expected)
    {
        $mockCommitted = $this->getMockBuilder(SugarForecasting_Committed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = SugarTestReflection::callProtectedMethod(
            $mockCommitted,
            'adjustWorksheetForArguments',
            [
                $args,
                $worksheetTotals,
                $fieldExt,
            ]
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * Provider for testAdjustWorksheetForArguments
     *
     * @return array[]
     */
    public function providerTestAdjustWorksheetForArguments()
    {
        // args, worksheet totals, field extension, expected result
        return [
            // Test no forecast arguments with sales rep forecast
            [
                [],
                [
                    'best_case' => 789.00,
                    'likely_case' => 456.00,
                    'worst_case' => 123.00,
                ],
                '_case',
                [
                    'best_case' => 789.00,
                    'likely_case' => 456.00,
                    'worst_case' => 123.00,
                ],
            ],
            // Test likely_case in arguments with sales rep forecast
            [
                [
                    'likely_case' => 999.00,
                ],
                [
                    'best_case' => 789.00,
                    'likely_case' => 456.00,
                    'worst_case' => 123.00,
                ],
                '_case',
                [
                    'best_case' => 789.00,
                    'likely_case' => 999.00,
                    'worst_case' => 123.00,
                ],
            ],
            // Test likely_case in arguments with manager forecast
            [
                [
                    'likely_case' => 111.00,
                ],
                [
                    'best_case' => 123.00,
                    'best_adjusted' => 456.00,
                    'likely_case' => 789.00,
                    'likely_adjusted' => 101112.00,
                    'worst_case' => 131415.00,
                    'worst_adjusted' => 161718.00,
                ],
                '_adjusted',
                [
                    'best_case' => 123.00,
                    'best_adjusted' => 456.00,
                    'likely_case' => 789.00,
                    'likely_adjusted' => 111.00,
                    'worst_case' => 131415.00,
                    'worst_adjusted' => 161718.00,
                ],
            ],
        ];
    }
}
