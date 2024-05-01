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

class SugarForecasting_Chart_ManagerTest extends TestCase
{
    /**
     * @var SugarForecasting_Chart_Manager
     */
    protected $obj;

    protected function setUp(): void
    {
        $this->obj = $this->getMockBuilder('SugarForecasting_Chart_Manager')
            ->setMethods(['getForecastConfig', 'getTimeperiod', 'getRollupQuota', 'getModuleLanguage'])
            ->setConstructorArgs([[]])
            ->getMock();

        $this->obj->expects($this->atLeastOnce())
            ->method('getForecastConfig')
            ->will(
                $this->returnValue(
                    [
                        'show_worksheet_worst' => 0,
                        'show_worksheet_best' => 1,
                        'show_worksheet_likely' => 1,
                    ]
                )
            );

        $tp_mock = $this->createPartialMock('TimePeriod', ['save']);
        $tp_mock->name = 'Q2 2012';

        $this->obj->expects($this->atLeastOnce())
            ->method('getTimeperiod')
            ->will($this->returnValue($tp_mock));

        $this->obj->expects($this->atLeastOnce())
            ->method('getModuleLanguage')
            ->will(
                $this->returnValue(
                    [
                        'LBL_CHART_FORECAST_FOR' => 'Test {0}',
                    ]
                )
            );

        $this->obj->expects($this->atLeastOnce())
            ->method('getRollupQuota')
            ->will($this->returnValue(50.00));


        $data_array = [
            [
                'id' => 'wkst_test_1',
                'user_id' => 'test_1',
                'name' => 'Test 1',
                'best_case' => 50.00,
                'best_case_adjusted' => 60.00,
                'likely_case' => 40.00,
                'likely_case_adjusted' => 50.00,
                'worst_case' => 30.00,
                'worst_case_adjusted' => 40.00,
                'base_rate' => 1,
                'currency_id' => '-99',
            ],
            [
                'id' => 'wkst_test_2',
                'user_id' => 'test_2',
                'name' => 'Test 2',
                'best_case' => 55.00,
                'best_case_adjusted' => 65.00,
                'likely_case' => 45.00,
                'likely_case_adjusted' => 55.00,
                'worst_case' => 35.00,
                'worst_case_adjusted' => 45.00,
                'base_rate' => 1,
                'currency_id' => '-99',
            ],
            [
                'id' => 'wkst_test_3',
                'user_id' => 'test_3',
                'name' => 'Test 3',
                'best_case' => 57.00,
                'best_case_adjusted' => 67.00,
                'likely_case' => 47.00,
                'likely_case_adjusted' => 57.00,
                'worst_case' => 37.00,
                'worst_case_adjusted' => 47.00,
                'base_rate' => 1,
                'currency_id' => '-99',
            ],
            [
                'id' => '',
                'user_id' => 'test_4',
                'name' => 'Test 4',
                'best_case' => 0,
                'best_case_adjusted' => 0,
                'likely_case' => 0,
                'likely_case_adjusted' => 0,
                'worst_case' => 0,
                'worst_case_adjusted' => 0,
                'base_rate' => 1,
                'currency_id' => '-99',
            ],
        ];

        // set the data
        SugarTestReflection::setProtectedValue($this->obj, 'dataArray', $data_array);
    }

    public function testDataContainsAllUsers()
    {
        $data = $this->obj->process();
        $this->assertEquals(4, safeCount($data['data']));
    }

    public function testNameIsSet()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['title']);
        $this->assertEquals('Test Q2 2012', $data['title']);
    }

    public function testQuotaIsSet()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['quota']);
        $this->assertEquals(50.00, $data['quota']);
    }

    public function testWorstNotInData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['data']);
        $this->assertNotContains('worst', array_keys($data['data'][0]));
        $this->assertNotContains('worst_adjusted', array_keys($data['data'][0]));
    }

    public function testBestInData()
    {
        $data = $this->obj->process();
        $this->assertNotEmpty($data['data']);
        $this->assertContains('best', array_keys($data['data'][0]));
        $this->assertContains('best_adjusted', array_keys($data['data'][0]));
    }
}
