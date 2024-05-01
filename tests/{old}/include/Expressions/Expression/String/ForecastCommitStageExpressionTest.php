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

class ForecastCommitStageExpressionTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        Forecast::$settings = [];
    }

    public static function evaluateDataProvider()
    {
        $binary_values = [
            'include' => [
                'min' => 70,
                'max' => 100,
            ],
            'exclude' => [
                'min' => 0,
                'max' => 69,
            ],
        ];

        $buckets_values = [
            'include' => [
                'min' => 85,
                'max' => 100,
            ],
            'upside' => [
                'min' => 70,
                'max' => 84,
            ],
            'exclude' => [
                'min' => 0,
                'max' => 69,
            ],
        ];

        $custom_values = [
            'include' => [
                'min' => 85,
                'max' => 100,
            ],
            'cstm_value' => [
                'min' => 70,
                'max' => 84,
            ],
            'exclude' => [
                'min' => 0,
                'max' => 69,
            ],
        ];

        return [
            [
                50,
                'exclude',
                'show_binary',
                $binary_values,
            ],
            [
                72,
                'include',
                'show_binary',
                $binary_values,
            ],
            [
                85,
                'include',
                'show_buckets',
                $buckets_values,
            ],
            [
                72,
                'upside',
                'show_buckets',
                $buckets_values,
            ],
            [
                50,
                'exclude',
                'show_buckets',
                $buckets_values,
            ],
            [
                74,
                'cstm_value',
                'show_custom_buckets',
                $custom_values,
            ],
        ];
    }


    /**
     * @dataProvider evaluateDataProvider
     * @param $probability
     * @param $expected
     * @param $range_type
     * @param array $ranges
     * @throws Exception
     */
    public function testEvaluate($probability, $expected, $range_type, array $ranges)
    {
        Forecast::$settings = [
            'is_setup' => 1,
            'forecast_ranges' => $range_type,
            "{$range_type}_ranges" => $ranges,
        ];

        $expr = "forecastCommitStage($probability)";
        $result = Parser::evaluate($expr)->evaluate();

        $this->assertSame($expected, $result);
    }
}
