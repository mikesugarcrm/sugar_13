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

class PipelineChartApiTest extends TestCase
{
    /**
     * @var array
     */
    private static $reportee;

    /**
     * @var array
     */
    protected static $manager;

    /**
     * @var array
     */
    protected static $manager2;

    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var array
     */
    protected static $managerData;

    /**
     * @var Administration
     */
    protected static $admin;

    /**
     * @var ServiceBase
     */
    protected $service;


    protected function setUp(): void
    {
        $this->service = $this->createPartialMock(
            'ServiceBase',
            ['execute', 'handleException']
        );
    }

    /**
     * Utility Method to setup a mock Pipeline API
     *
     * @param Array $methods
     * @return PipelineChartApi
     */
    protected function getMockPipelineApi(array $methods = ['loadBean'])
    {
        $api = $this->getMockBuilder('PipelineChartApi')
            ->setMethods($methods)
            ->getMock();

        return $api;
    }

    public function testNotFoundExceptionThrownWithInvalidModule()
    {
        $api = $this->getMockPipelineApi();

        $this->expectException(SugarApiExceptionNotFound::class);
        $api->pipeline($this->service, ['module' => 'MyInvalidModule']);
    }

    public function testNotAuthorizedThrownWhenACLAccessDenied()
    {
        $api = $this->getMockPipelineApi(['loadBean']);

        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save', 'ACLAccess'])
            ->getMock();

        $rli->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(false));

        $api->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($rli));

        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $api->pipeline($this->service, ['module' => 'RevenueLineItems']);
    }

    public function testBuildQueryContainsAmountField()
    {
        $api = $this->getMockPipelineApi();
        $tp = $this->getMockBuilder('TimePeriod')
            ->setMethods(['save'])
            ->getMock();
        $tp->start_date_timestamp = 1;
        $tp->end_date_timestamp = 2;

        $seed = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $user = $this->getMockBuilder('User')
            ->setMethods(['save'])
            ->getMock();
        $user->id = 'test';

        $this->service->user = $user;

        $sq = SugarTestReflection::callProtectedMethod(
            $api,
            'buildQuery',
            [$this->service, $seed, $tp, 'likely_case', 'user']
        );
        /* @var $sq SugarQuery */
        $sql = $sq->compile()->getSQL();

        $this->assertStringContainsString('likely_case', $sql);
    }

    public function testPipelineReturnsCorrectData()
    {
        $api = $this->getMockPipelineApi(['getForecastSettings', 'buildQuery', 'loadBean', 'getTimeperiod']);
        $rli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save', 'ACLAccess'])
            ->getMock();

        $rli->expects($this->once())
            ->method('ACLAccess')
            ->with('view')
            ->will($this->returnValue(true));

        $GLOBALS['current_language'] = 'en_us';
        $rli->module_name = 'RevenueLineItems';

        $api->expects($this->once())
            ->method('loadBean')
            ->will($this->returnValue($rli));

        $api->expects($this->once())
            ->method('getForecastSettings')
            ->will(
                $this->returnValue(
                    [
                        'sales_stage_won' => ['Closed Won'],
                        'sales_stage_lost' => ['Closed Lost'],
                        'is_setup' => 0,
                    ]
                )
            );

        /**
         * 'Prospecting' => 'Prospecting',
         * 'Qualification' => 'Qualification',
         */

        $data = [
            [
                'id' => 'test1',
                'sales_stage' => 'Prospecting',
                'likely_case' => '100.00',
                'base_rate' => '1.0',
            ],
            [
                'id' => 'test2',
                'sales_stage' => 'Prospecting',
                'likely_case' => '150.00',
                'base_rate' => '1.0',
            ],
            [
                'id' => 'test3',
                'sales_stage' => 'Qualification',
                'likely_case' => '100.00',
                'base_rate' => '1.0',
            ],
            [
                'id' => 'test4',
                'sales_stage' => 'Qualification',
                'likely_case' => '150.00',
                'base_rate' => '1.0',
            ],
        ];


        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();
        $sq->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($data));

        $api->expects($this->once())
            ->method('buildQuery')
            ->will($this->returnValue($sq));

        $api->expects($this->once())
            ->method('getTimeperiod')
            ->will($this->returnValue(''));

        $data = $api->pipeline(
            $this->service,
            [
                'module' => 'RevenueLineItems',
                'timeperiod_id' => '',
            ]
        );

        // check the properties
        $this->assertEquals('500.000000', $data['properties']['total']);

        // lets check the data, there should be two
        $this->assertEquals(2, safeCount($data['data']));

        // each item should be 250 and have 2 items
        foreach ($data['data'] as $item) {
            $this->assertEquals(2, $item['count']);
            $this->assertEquals(250, $item['values'][0]['value']);
        }
    }
}
