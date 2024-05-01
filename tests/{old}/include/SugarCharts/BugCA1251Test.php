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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\Types;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarTestHelper;

/**
 * Bug CA-1251 - Wrong total for charts
 *
 * @ticket CA-1251
 */
class BugCA1251Test extends TestCase
{
    /**
     * @var User
     */
    protected $currentUser;

    /**
     * Keep a backup of variables to restore them on tearDown
     * @var array
     */
    private $backup = [];

    protected function setUp(): void
    {
        SugarTestHelper::init();
        SugarTestHelper::setUp('current_user');

        $this->backup['do_thousands'] = array_key_exists('do_thousands', $GLOBALS) ? $GLOBALS['do_thousands'] : false;
        $this->currentUser = $GLOBALS['current_user'];
        $this->currentUser->setPreference('default_number_grouping_seperator', '.');
        $this->currentUser->setPreference('default_decimal_seperator', ',');
        $this->currentUser->setPreference('default_currency_significant_digits', 2);
    }

    protected function tearDown(): void
    {
        $GLOBALS['do_thousands'] = $this->backup['do_thousands'];
        SugarTestHelper::tearDown();
    }

    /**
     * Test bug with fix
     *
     * @param array $data
     * @param array $expectedData
     *
     * @dataProvider providerTestTotal
     */
    public function testTotalSuccess($data, $expectedData)
    {
        $ignoreFix = false;
        $round = true;

        $GLOBALS['do_thousands'] = false;
        $this->testTotal($data, $expectedData, !$round, $ignoreFix);

        $GLOBALS['do_thousands'] = true;
        $this->testTotal($data, $expectedData, $round, $ignoreFix);
    }

    /**
     * Test with bug without fix
     *
     * @param array $data
     * @param array $expectedData
     *
     * @dataProvider providerTestTotal
     */
    public function testTotalFailed($data, $expectedData)
    {
        $ignoreFix = true;
        $round = true;

        $GLOBALS['do_thousands'] = false;
        $this->testTotal($data, $expectedData, !$round, $ignoreFix);


        $GLOBALS['do_thousands'] = true;
        $this->testTotal($data, $expectedData, $round, $ignoreFix);
    }

    /**
     * Test the logic for both cases, fixed/unfixed
     *
     * @param array $data
     * @param array $expectedData
     * @param bool $round
     * @param bool $ignoreFix
     */
    protected function testTotal($data, $expectedData, $round, $ignoreFix)
    {
        $mockedSummaryMethods = ['get_total'];

        if ($ignoreFix === true) {
            $mockedSummaryMethods[] = 'isChartColumnCurrency';
        }

        $mockChartDisplay = $this->getReporterMock('\ChartDisplay', $mockedSummaryMethods);
        $mockReport = $this->getReporterMock('\Report');


        TestReflection::setProtectedValue($mockReport, 'report_def', $data['reportDef']);
        TestReflection::setProtectedValue($mockReport, 'all_fields', $data['all_fields']);
        TestReflection::setProtectedValue($mockChartDisplay, 'reporter', $mockReport);

        if ($ignoreFix === true) {
            $mockChartDisplay->expects($this->any())
                ->method('isChartColumnCurrency')
                ->willReturn(false);
        }

        $total = $round ? $data['get_total']['round'] : $data['get_total']['no_round'];

        $mockChartDisplay->expects($this->any())
            ->method('get_total')
            ->willReturn($total);

        TestReflection::callProtectedMethod($mockChartDisplay, 'parseChartTitle');

        $chartTitle = TestReflection::getProtectedValue($mockChartDisplay, 'chartTitle');

        if ($ignoreFix) {
            $expectedTitle = $round ? $expectedData['round']['wrongTitle'] : $expectedData['no_round']['wrongTitle'];

            $this->assertEquals($chartTitle, $expectedTitle);
        } else {
            $expectedTitle = $round ? $expectedData['round']['expectedTitle'] : $expectedData['no_round']['expectedTitle'];

            $this->assertEquals($chartTitle, $expectedTitle);
        }
    }

    /**
     * Data for testTotalSuccess/testTotalFailed
     */
    public function providerTestTotal()
    {
        return [
            [
                [
                    'reportDef' => [
                        'numerical_chart_column' => 'self:likely_case:sum',
                    ],
                    'all_fields' => [
                        'self:likely_case' => [
                            'type' => 'currency',
                        ],
                    ],
                    'chart_header_row' => ['test'],
                    'get_total' => [
                        'no_round' => '619637.9388563333',
                        'round' => '620',
                    ],
                ],
                [
                    'no_round' => [
                        'expectedTitle' => 'Total is $619,637.94',
                        'wrongTitle' => 'Total is 619,637.9388563333',
                    ],
                    'round' => [
                        'expectedTitle' => 'Total is $620K',
                        'wrongTitle' => 'Total is 620K',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $mockPath
     * @param null|array $methods
     * @return \Summary
     */
    protected function getReporterMock($mockPath, $methods = null)
    {
        return $this->getMockBuilder($mockPath)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
