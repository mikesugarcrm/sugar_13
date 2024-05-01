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

namespace Sugarcrm\SugarcrmTestsUnit\inc\SugarCharts;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \SugarChart
 */
class SugarChartTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['app_strings'] = ['LBL_THOUSANDS_SYMBOL' => 'K'];
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['app_strings']);
    }

    public function processDataGroupProvider()
    {
        return [
            ['137734.745309', true, '137.73', '137.73K'],
            ['137734.745309', false, '137734.74530973', '137734.745309'],
        ];
    }

    /**
     * @covers       ::processDataGroup
     * @dataProvider processDataGroupProvider
     *
     * @param string $label
     * @param boolean $thousands
     * @param string $thousands
     * @param string $formattedNumber
     * @param string $expected
     */
    public function testProcessDataGroup($label, $thousands, $formattedNumber, $expected)
    {
        $chart = $this->getMockBuilder(\SugarChart::class)
            ->disableOriginalConstructor()
            ->setMethods(['formatNumber', 'tab', 'tabValue',])
            ->getMock();

        $chart->method('formatNumber')->willReturn($formattedNumber);
        $chart->method('tab')->willReturn('');
        $chart->method('tabValue')
            ->willReturnCallback(function ($tag, $value, $depth) {
                return $value;
            });

        $chart->chart_properties['thousands'] = $thousands;
        $val = $chart->processDataGroup(1, '', '', $label, '');

        $this->assertSame($expected, $val);
    }
}
