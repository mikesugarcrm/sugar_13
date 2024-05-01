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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers ForecastWorksheet
 */
class ForecastWorksheetTest extends TestCase
{
    /**
     * @var SugarTestDatabaseMock
     */
    protected static $db;

    public static function setUpBeforeClass(): void
    {
        self::$db = SugarTestHelper::setUp('mock_db');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param array $methods
     * @return MockObject|ForecastWorksheet
     */
    protected function getMockWorksheet(array $methods = ['save', 'getBean'])
    {
        if (!in_array('save', $methods)) {
            $methods[] = 'save';
        }

        if (!in_array('getBean', $methods)) {
            $methods[] = 'getBean';
        }

        return $this->getMockBuilder('ForecastWorksheet')
            ->setMethods($methods)
            ->getMock();
    }

    public static function dataProviderSaveWorksheet()
    {
        return [
            [
                'RevenueLineItem',
                'RevenueLineItems',
                'likely_case',
            ],
            [
                'Opportunity',
                'Opportunities',
                'amount',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers       ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheet($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(['save'])
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';

        $worksheet = $this->getMockWorksheet(['ACLFieldAccess']);

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                ['best_case', 'write'],
                ['worst_case', 'write']
            )
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = 'test';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals($worksheet->best_case, $bean->best_case);
        $this->assertEquals($worksheet->worst_case, $bean->worst_case);
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers       ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheetDoesNotUpdateDateClosed($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(['save'])
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';
        $bean->date_closed = 'unit_test';

        $worksheet = $this->getMockWorksheet(['ACLFieldAccess']);

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                ['best_case', 'write'],
                ['worst_case', 'write']
            )
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = '';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals('unit_test', $bean->date_closed);
    }

    /**
     * @dataProvider dataProviderSaveWorksheet
     * @covers       ForecastWorksheet::saveWorksheet
     */
    public function testSaveWorksheetDoesNotOverwriteBestWorst($klass_name, $module_name, $likely_field)
    {
        $bean = $this->getMockBuilder($klass_name)
            ->setMethods(['save'])
            ->getMock();

        $bean->id = 'test_bean_1';
        $bean->worst_case = '60.000000';
        $bean->best_case = '60.000000';

        $worksheet = $this->getMockWorksheet(['ACLFieldAccess']);

        $worksheet->expects($this->exactly(2))
            ->method('ACLFieldAccess')
            ->withConsecutive(
                ['best_case', 'write'],
                ['worst_case', 'write']
            )
            ->willReturn(false);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with($module_name, $bean->id)
            ->willReturn($bean);

        $worksheet->parent_type = $module_name;
        $worksheet->parent_id = 'test_bean_1';
        $worksheet->likely_case = '50.000000';
        $worksheet->best_case = '50.000000';
        $worksheet->worst_case = '50.000000';
        $worksheet->date_closed = 'test';

        $worksheet->saveWorksheet(false);

        $this->assertEquals($worksheet->likely_case, $bean->$likely_field);
        $this->assertEquals('60.000000', $bean->best_case);
        $this->assertEquals('60.000000', $bean->worst_case);
    }

    /**
     * @covers ForecastWorksheet::setWorksheetArgs
     */
    public function testSetWorksheetArgs()
    {
        $args = [
            'likely_case' => '50.00',
            'best_case' => '50.00',
        ];
        $worksheet = $this->getMockWorksheet();
        $worksheet->setWorksheetArgs($args);
        $this->assertSame($args, $worksheet->args);
        foreach ($args as $key => $val) {
            $this->assertSame($val, $worksheet->$key);
        }
    }

    public static function dataProviderSaveRelatedOpportunity()
    {
        return [
            [
                false,
                [
                    'id' => 'test_opp_id',
                    'amount' => '50.000000',
                    'account_id' => 'test_account_id',
                ],
                true,
            ],
            [
                true,
                [
                    'id' => 'test_opp_id',
                    'amount' => '50.000000',
                    'account_id' => 'test_account_id',
                    'account_name' => 'Test Account',
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderSaveRelatedOpportunity
     * @covers       ForecastWorksheet::saveRelatedOpportunity
     * @param $isCommit
     * @param $opp_values
     */
    public function testSaveRelatedOpportunity($isCommit, $opp_values, $acc_check)
    {
        $worksheet = $this->getMockWorksheet(['retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow']);

        $mockOpp = $this->getMockBuilder('Opportunity')
            ->setMethods(['save'])
            ->getMock();

        foreach ($opp_values as $key => $val) {
            $mockOpp->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockOpp);

        if ($acc_check) {
            $worksheet->expects($this->once())
                ->method('getRelatedName')
                ->with('Accounts', $mockOpp->account_id)
                ->willReturn('Test Account');
        } else {
            $worksheet->expects($this->never())
                ->method('getRelatedName');
        }

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                [
                    'parent_type' => 'Opportunities',
                    'parent_id' => 'test_opp_id',
                    'draft' => ($isCommit === false) ? 1 : 0,
                    'deleted' => 0,
                ],
                true,
                false
            );

        $worksheet->saveRelatedOpportunity($mockOpp, $isCommit);
    }

    /**
     * @covers ForecastWorksheet::saveRelatedProduct
     */
    public function testSaveRelatedProduct()
    {
        $worksheet = $this->getMockWorksheet(['retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow']);

        $mockRli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $values = [
            'id' => 'test_rli_id',
            'account_name' => 'test_acc_name',
            'account_id' => 'test_acc_id',
            'opportunity_name' => 'opportunity_name',
            'opportunity_id' => 'opportunity_id',
            'product_template_name' => 'product_template_name',
            'product_template_id' => 'product_template_id',
            'category_name' => 'category_name',
            'category_id' => 'category_id',
        ];

        foreach ($values as $key => $val) {
            $mockRli->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockRli);

        $worksheet->expects($this->never())
            ->method('getRelatedName');

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                [
                    'parent_type' => 'RevenueLineItems',
                    'parent_id' => 'test_rli_id',
                    'draft' => 1,
                    'deleted' => 0,
                ],
                true,
                false
            );

        $worksheet->saveRelatedProduct($mockRli, false);
    }

    /**
     * @covers ForecastWorksheet::saveRelatedProduct
     */
    public function testSaveRelatedProductFetchedRelatedName()
    {
        $worksheet = $this->getMockWorksheet(['retrieve_by_string_fields', 'copyValues', 'getRelatedName', 'removeMigratedRow']);

        $mockRli = $this->getMockBuilder('RevenueLineItem')
            ->setMethods(['save'])
            ->getMock();

        $values = [
            'id' => 'test_rli_id',
            'account_id' => 'account_id',
            'opportunity_id' => 'opportunity_id',
            'product_template_id' => 'product_template_id',
            'category_id' => 'category_id',
        ];

        foreach ($values as $key => $val) {
            $mockRli->$key = $val;
        }

        $worksheet->expects($this->once())
            ->method('removeMigratedRow')
            ->with($mockRli);


        $worksheet->expects($this->exactly(4))
            ->method('getRelatedName')
            ->withConsecutive(
                ['Accounts', 'account_id'],
                ['Opportunities', 'opportunity_id'],
                ['ProductTemplates', 'product_template_id'],
                ['ProductCategories', 'category_id']
            )
            ->willReturn('Unit Test');

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                [
                    'parent_type' => 'RevenueLineItems',
                    'parent_id' => 'test_rli_id',
                    'draft' => 1,
                    'deleted' => 0,
                ],
                true,
                false
            );

        $worksheet->saveRelatedProduct($mockRli, false);
    }

    public function dataProviderCommitWorksheetReturnFalse()
    {
        return [
            [
                false,
                'test_timperiod_id',
            ],
            [
                true,
                null,
            ],
        ];
    }

    /**
     * @covers       ForecastWorksheet::commitWorksheet
     * @param $forecastSetup
     * @param $timePeriodId
     * @dataProvider dataProviderCommitWorksheetReturnFalse
     */
    public function testCommitWorksheetReturnFalse($forecastSetup, $timePeriodId)
    {
        Forecast::$settings = [
            'is_setup' => $forecastSetup,
        ];

        $worksheet = $this->getMockWorksheet();

        $count = intval($forecastSetup);

        $tp = $this->createPartialMock('Timeperiod', ['save']);
        $tp->id = $timePeriodId;

        $worksheet->expects($this->exactly($count))
            ->method('getBean')
            ->with('TimePeriods', $timePeriodId)
            ->willReturn($tp);

        $actual = $worksheet->commitWorksheet('test_user_id', $timePeriodId);

        $this->assertFalse($actual);
    }


    public static function dataProviderCommitWorksheet()
    {
        return [
            [
                'RevenueLineItems',
                'RevenueLineItem',
                'account_link',
            ],
            [
                'Opportunities',
                'Opportunity',
                'accounts',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCommitWorksheet
     * @covers       ForecastWorksheet::commitWorksheet
     * @param string $type
     * @param string $bean
     */
    public function testCommitWorksheet($type, $bean, $link_name)
    {
        Forecast::$settings = [
            'is_setup' => true,
            'forecast_by' => $type,
        ];

        $worksheet = $this->getMockWorksheet(
            [
                'getSugarQuery',
                'removeReassignedItems',
                'processWorksheetDataChunk',
                'createUpdateForecastWorksheetJob',
            ]
        );

        $worksheet->expects($this->once())
            ->method('removeReassignedItems')
            ->with('test_user_id', 'test_timeperiod_id', 1);

        $tp = $this->getMockBuilder('Timeperiod')->setMethods(['save'])->getMock();
        $tp->id = 'test_timeperiod_id';

        $sq = $this->getMockBuilder('SugarQuery')->setMethods(['execute'])->getMock();

        $bean = $this->getMockBuilder($bean)->setMethods(['save', 'load_relationship'])->getMock();
        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['buildJoinSugarQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $link2->expects($this->once())
            ->method('buildJoinSugarQuery')
            ->with($sq, ['joinTableAlias' => 'account']);

        $bean->expects($this->once())
            ->method('load_relationship')
            ->with($link_name)
            ->willReturn(true);

        $bean->$link_name = $link2;

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                ['TimePeriods', 'test_timeperiod_id'],
                [$type]
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $bean
            );

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn([
                ['one'],
                ['two'],
                ['three'],
                ['four'],
            ]);

        $worksheet->expects($this->once())
            ->method('processWorksheetDataChunk')
            ->with($type, [['one']]);

        $worksheet->expects($this->exactly(3))
            ->method('createUpdateForecastWorksheetJob')
            ->withConsecutive(
                [$type, [['two']]],
                [$type, [['three']]],
                [$type, [['four']]]
            );

        $actual = $worksheet->commitWorksheet('test_user_id', 'test_timeperiod_id', 1);

        $this->assertTrue($actual);
    }


    public static function dataProviderRemoveMigratedRow()
    {
        return [
            [
                true,
            ],
            [
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderRemoveMigratedRow
     * @covers       ForecastWorksheet::removeMigratedRow
     */
    public function testRemoveMigratedRow($hasMigrated)
    {
        $worksheet = $this->getMockWorksheet(['timeperiodHasMigrated', 'retrieve_by_string_fields']);

        $worksheet->fetched_row['date_closed'] = 'date_1';

        $bean = $this->getMockForAbstractClass('SugarBean', ['save']);
        $bean->fetched_row['date_closed'] = 'date_2';
        $bean->module_name = 'unit_test';
        $bean->id = 'unit_test_id';

        if ($hasMigrated) {
            $worksheet->expects($this->once())
                ->method('getBean')
                ->willReturn($worksheet);

            $worksheet->expects($this->once())
                ->method('retrieve_by_string_fields')
                ->with(
                    [
                        'parent_type' => 'unit_test',
                        'parent_id' => 'unit_test_id',
                        'draft' => 0,
                        'deleted' => 0,
                    ],
                    true,
                    false
                );

            $worksheet->expects($this->once())
                ->method('save');
        } else {
            $worksheet->expects($this->never())
                ->method('getBean');

            $worksheet->expects($this->never())
                ->method('retrieve_by_string_fields');

            $worksheet->expects($this->never())
                ->method('save');
        }

        $worksheet->expects($this->once())
            ->method('timeperiodHasMigrated')
            ->with('date_1', 'date_2')
            ->willReturn($hasMigrated);

        $actual = $worksheet->removeMigratedRow($bean);
        $this->assertSame($hasMigrated, $actual);
    }

    public function dataProviderCopyValues()
    {
        $bean = $this->createPartialMock('Opportunity', ['save', 'toArray']);
        $bean->amount = '50.000000';
        return [
            [
                [
                    ['likely_case' => 'amount'],
                ],
                [
                    'amount' => '50.000000',
                ],
            ],
            [
                [
                    'likely_case',
                ],
                [
                    'likely_case' => '50.000000',
                ],
            ],
            [
                [
                    ['likely_case' => 'amount'],
                ],
                $bean,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCopyValues
     * @covers       ForecastWorksheet::copyValues
     */
    public function testCopyValues($fields, $values)
    {
        if ($values instanceof SugarBean) {
            $values->expects($this->once())
                ->method('toArray')
                ->with()
                ->will($this->returnValue([
                    'amount' => '50.000000',
                ]));
        }

        $worksheet = $this->getMockWorksheet();
        $worksheet->copyValues($fields, $values);
        $this->assertEquals('50.000000', $worksheet->likely_case);
    }

    public static function dataProviderProcessWorksheetDataChunk()
    {
        return [
            [
                'RevenueLineItems',
                'RevenueLineItem',
                [
                    ['id' => 'test_rli'],
                ],
            ],
            [
                'Opportunities',
                'Opportunity',
                [
                    ['id' => 'test_opp'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderProcessWorksheetDataChunk
     * @covers       ForecastWorksheet::processWorksheetDataChunk
     */
    public function testProcessWorksheetDataChunk($type, $bean, $data)
    {
        $worksheet = $this->getMockWorksheet(['saveRelatedOpportunity', 'saveRelatedProduct']);
        $obj = $this->getMockBuilder($bean)
            ->setMethods(['save', 'loadFromRow'])
            ->getMock();

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                [$type],
                ['ForecastWorksheets']
            )
            ->willReturnOnConsecutiveCalls(
                $obj,
                $worksheet
            );

        $obj->expects($this->once())
            ->method('loadFromRow')
            ->with($data[0]);

        $method = ($type === 'Opportunities') ? 'saveRelatedOpportunity' : 'saveRelatedProduct';

        $worksheet->expects($this->once())
            ->method($method);

        $worksheet->processWorksheetDataChunk($type, $data);
    }

    /**
     * @covers ForecastWorksheet::createUpdateForecastWorksheetJob
     */
    public function testCreateUpdateForecastWorksheetJob()
    {
        $data = ['forecast_by' => 'unit_test', 'data' => []];
        $user_id = 'bar';
        $sj = $this->createMock('SchedulersJob');
        $jq = $this->createPartialMock('SugarJobQueue', ['submitJob']);
        $fw = $this->getMockWorksheet(['getJobQueue']);

        $jq->expects($this->once())
            ->method('submitJob')
            ->with($sj);

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($sj));

        $fw->expects($this->once())
            ->method('getJobQueue')
            ->will($this->returnValue($jq));

        SugarTestReflection::callProtectedMethod($fw, 'createUpdateForecastWorksheetJob', ['unit_test', [], $user_id]);

        $this->assertEquals('Update ForecastWorksheets', $sj->name);
        $this->assertEquals('class::SugarJobUpdateForecastWorksheets', $sj->target);
        $this->assertEquals(json_encode($data), $sj->data);
        $this->assertEquals(0, $sj->retry_count);
        $this->assertEquals($user_id, $sj->assigned_user_id);
    }

    /**
     * @covers ForecastWorksheet::getRelatedName
     */
    public function testGetRelatedNameReturnsEmpty()
    {
        self::$db->addQuerySpy(
            'accountQuery',
            '/my_test_id/',
            [
                [
                    'name' => 'My Test Account',
                ],
            ]
        );

        $forecast_worksheet = BeanFactory::newBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            ['Accounts', 'test_id']
        );
        $this->assertEmpty($return);
    }

    /**
     * @covers ForecastWorksheet::processRemoveChunk
     */
    public function testProcessRemoveChunk()
    {
        $fw = $this->createPartialMock('ForecastWorksheet', ['mark_deleted']);
        $bean = ['id' => 'foo'];

        $fw->expects($this->once())
            ->method('mark_deleted');

        $fw->processRemoveChunk([$bean]);
    }

    /**
     * @covers ForecastWorksheet::createRemoveReassignedJob
     */
    public function testCreateRemoveReassignedJob()
    {
        $data = ['foo'];
        $user_id = 'bar';
        $sj = $this->createMock('SchedulersJob');
        $jq = $this->createPartialMock('SugarJobQueue', ['submitJob']);
        $fw = $this->createPartialMock('ForecastWorksheet', ['getBean',
            'getJobQueue',
        ]);

        $jq->expects($this->once())
            ->method('submitJob')
            ->with($sj);

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($sj));

        $fw->expects($this->once())
            ->method('getJobQueue')
            ->will($this->returnValue($jq));

        $fw->createRemoveReassignedJob($data, $user_id);

        $this->assertEquals('Remove Reassigned Items', $sj->name);
        $this->assertEquals('class::SugarJobRemoveReassignedItems', $sj->target);
        $this->assertEquals(json_encode($data), $sj->data);
        $this->assertEquals(0, $sj->retry_count);
        $this->assertEquals($user_id, $sj->assigned_user_id);
    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotalsReturnFalse()
    {
        $worksheet = $this->getMockWorksheet();

        $tp = $this->createPartialMock('Timeperiod', ['save']);
        $tp->id = null;

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with('TimePeriods', 'test_timeperiod_id')
            ->willReturn($tp);

        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id');

        $this->assertFalse($actual);
    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotalsReturnsDefaultEmptyArrayWhenNoValuesAreFound()
    {
        $GLOBALS['current_user'] = $this->createPartialMock('User', ['save']);
        $GLOBALS['current_user']->id = 'current_user_id';

        $worksheet = $this->getMockWorksheet(['getSugarQuery', 'getTableName']);

        $tp = $this->createPartialMock('Timeperiod', ['save']);
        $tp->id = 'test_timeperiod_id';
        $tp->start_date_timestamp = '10000';
        $tp->end_date_timestamp = '10000';

        $worksheet->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('forecast_worksheets');

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                ['TimePeriods', 'test_timeperiod_id'],
                ['ForecastWorksheets']
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $worksheet
            );

        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn([]);

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);


        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id', 'unit_test');

        $expected = [
            'amount' => '0',
            'best_case' => '0',
            'worst_case' => '0',
            'overall_amount' => '0',
            'overall_best' => '0',
            'overall_worst' => '0',
            'timeperiod_id' => 'test_timeperiod_id',
            'lost_count' => '0',
            'lost_amount' => '0',
            'lost_best' => '0',
            'lost_worst' => '0',
            'won_count' => '0',
            'won_amount' => '0',
            'won_best' => '0',
            'won_worst' => '0',
            'included_opp_count' => 0,
            'total_opp_count' => 0,
            'includedClosedCount' => 0,
            'includedClosedAmount' => '0',
            'includedClosedBest' => '0',
            'includedClosedWorst' => '0',
            'pipeline_amount' => '0',
            'pipeline_opp_count' => 0,
            'closed_amount' => '0',
            'includedIdsInLikelyTotal' => [],
        ];

        $this->assertSame($expected, $actual);

        unset($GLOBALS['current_user']);
    }

    /**
     * @covers ForecastWorksheet::worksheetTotals
     */
    public function testWorksheetTotals()
    {
        $GLOBALS['current_user'] = $this->createPartialMock('User', ['save']);
        $GLOBALS['current_user']->id = 'current_user_id';

        Forecast::$settings = [
            'sales_stage_won' => ['Closed Won'],
            'sales_stage_lost' => ['Closed Lost'],
            'commit_stages_included' => ['Include'],
        ];

        $worksheet = $this->getMockWorksheet(['getSugarQuery', 'getTableName']);

        $tp = $this->createPartialMock('Timeperiod', ['save']);
        $tp->id = 'test_timeperiod_id';
        $tp->start_date_timestamp = '10000';
        $tp->end_date_timestamp = '10000';

        $worksheet->expects($this->atLeastOnce())
            ->method('getTableName')
            ->willReturn('forecast_worksheets');

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                ['TimePeriods', 'test_timeperiod_id'],
                ['ForecastWorksheets']
            )
            ->willReturnOnConsecutiveCalls(
                $tp,
                $worksheet
            );

        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();

        $sq->expects($this->once())
            ->method('execute')
            ->willReturn(
                [
                    [
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Closed Won',
                        'commit_stage' => 'Include',
                        'parent_id' => 'test_1',
                    ],
                    [
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Test',
                        'commit_stage' => 'Include',
                        'parent_id' => 'test_3',
                    ],
                    [
                        'likely_case' => '50',
                        'best_case' => '50',
                        'worst_case' => '50',
                        'base_rate' => '1',
                        'sales_stage' => 'Closed Lost',
                        'commit_stage' => 'Exclude',
                        'parent_id' => 'test_2',
                    ],
                ]
            );

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);


        $actual = $worksheet->worksheetTotals('test_timeperiod_id', 'test_user_id', 'unit_test');

        $expected = [
            'amount' => '50.000000',
            'best_case' => '50.000000',
            'worst_case' => '50.000000',
            'overall_amount' => '150.000000',
            'overall_best' => '150.000000',
            'overall_worst' => '150.000000',
            'timeperiod_id' => 'test_timeperiod_id',
            'lost_count' => 1,
            'lost_amount' => '50.000000',
            'lost_best' => '50.000000',
            'lost_worst' => '50.000000',
            'won_count' => 1,
            'won_amount' => '50.000000',
            'won_best' => '50.000000',
            'won_worst' => '50.000000',
            'included_opp_count' => 2,
            'total_opp_count' => 3,
            'includedClosedCount' => 1,
            'includedClosedAmount' => '50.000000',
            'includedClosedBest' => '50.000000',
            'includedClosedWorst' => '50.000000',
            'pipeline_amount' => '0',
            'pipeline_opp_count' => 0,
            'closed_amount' => '0',
            'includedIdsInLikelyTotal' => [
                0 => 'test_3',
            ],
        ];

        $this->assertSame($expected, $actual);

        unset($GLOBALS['current_user']);
    }
}
