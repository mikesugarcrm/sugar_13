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
 * Class ForecastManagerWorksheetTest
 * @coversDefaultClass ForecastManagerWorksheet
 */
class ForecastManagerWorksheetTest extends TestCase
{
    /**
     * @var Forecast
     */
    protected static $forecast;

    /**
     * @var Timeperiod
     */
    protected static $timeperiod;

    /**
     * @var User
     */
    protected static $manager;

    /**
     * @var Quota
     */
    protected static $topLevelManager_quota;

    /**
     * @var User
     */
    protected static $user;

    /**
     * @var User
     */
    protected static $topLevelManager;

    /**
     * @var Quota
     */
    protected static $user_quota;

    /**
     * @var Quota
     */
    protected static $manager_quota;

    /**
     * @var Forecast
     */
    protected static $forecastBackup;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig();

        self::$timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        self::$topLevelManager = SugarTestUserUtilities::createAnonymousUser();

        self::$topLevelManager_quota = SugarTestQuotaUtilities::createQuota(1000);
        self::$topLevelManager_quota->user_id = self::$topLevelManager->id;
        self::$topLevelManager_quota->quota_type = 'Direct';
        self::$topLevelManager_quota->timeperiod_id = self::$timeperiod->id;
        self::$topLevelManager_quota->save();

        $rollup_quota_manager = SugarTestQuotaUtilities::createQuota(1000);
        $rollup_quota_manager->user_id = self::$topLevelManager->id;
        $rollup_quota_manager->quota_type = 'Rollup';
        $rollup_quota_manager->timeperiod_id = self::$timeperiod->id;
        $rollup_quota_manager->save();

        self::$manager = SugarTestUserUtilities::createAnonymousUser(false);
        self::$manager->reports_to_id = self::$topLevelManager->id;
        self::$manager->save();

        self::$manager_quota = SugarTestQuotaUtilities::createQuota(1000);
        self::$manager_quota->user_id = self::$manager->id;
        self::$manager_quota->quota_type = 'Direct';
        self::$manager_quota->timeperiod_id = self::$timeperiod->id;
        self::$manager_quota->save();

        $rollup_quota = SugarTestQuotaUtilities::createQuota(2000);
        $rollup_quota->user_id = self::$manager->id;
        $rollup_quota->quota_type = 'Rollup';
        $rollup_quota->timeperiod_id = self::$timeperiod->id;
        $rollup_quota->save();


        self::$user = SugarTestUserUtilities::createAnonymousUser(false);
        self::$user->reports_to_id = self::$manager->id;
        self::$user->save();

        self::$user_quota = SugarTestQuotaUtilities::createQuota(600);
        self::$user_quota->user_id = self::$user->id;
        self::$user_quota->quota_type = 'Direct';
        self::$user_quota->timeperiod_id = self::$timeperiod->id;
        self::$user_quota->save();

        $rollup_quota_user = SugarTestQuotaUtilities::createQuota(600);
        $rollup_quota_user->user_id = self::$user->id;
        $rollup_quota_user->quota_type = 'Rollup';
        $rollup_quota_user->timeperiod_id = self::$timeperiod->id;
        $rollup_quota_user->save();

        self::$forecast = SugarTestForecastUtilities::createForecast(self::$timeperiod, self::$user);

        $GLOBALS['current_user'] = self::$manager;
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestForecastUtilities::tearDownForecastConfig();

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestQuotaUtilities::removeAllCreatedQuotas();

        SugarTestHelper::tearDown();
    }

    public function setUp(): void
    {
        // Save the original data of the forecast
        self::$forecastBackup = self::$forecast->toArray();
    }

    public function tearDown(): void
    {
        // Restore the original forecast data
        self::$forecast->fromArray(self::$forecastBackup);

        // Clear any ForecastManagerWorksheets created after each test
        $userIds = [self::$topLevelManager->id, self::$manager->id, self::$user->id];
        $db = DBManagerFactory::getInstance();
        $db->query('DELETE FROM forecast_manager_worksheets WHERE user_id IN (\'' . implode("', '", $userIds) . '\')');
    }

    /**
     * Sets up a draft ForecastManagerWorksheet
     */
    public function createDraftManagerWorksheet()
    {
        // Create a new ForecastManagerWorksheet
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');

        // Rollup data from reportee forecasts
        $this->assertTrue($worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray()));
    }

    /**
     * Sets up a committed ForecastManagerWorksheet
     */
    public function createCommittedManagerWorksheet()
    {
        // Create a new ForecastManagerWorksheet
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');

        // Rollup data from reportee forecasts to the manager's forecast
        $this->assertTrue($worksheet->reporteeForecastRollUp(self::$user, self::$forecast->toArray()));

        // Commit the manager's forecast
        $this->assertTrue($worksheet->commitManagerForecast(self::$manager, self::$timeperiod->id));
    }

    public function loadManagerWorksheet($assignedUserId, $userId, $draft, $deleted)
    {
        // Return the worksheet. We use retrieve_by_string_fields here as
        // the ForecastManagerWorksheet has been built around the use of the
        // fill_in_additional_detail_fields function
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');
        return $worksheet->retrieve_by_string_fields(
            [
                'assigned_user_id' => $assignedUserId,
                'user_id' => $userId,
                'draft' => $draft,
                'deleted' => $deleted,
            ]
        );
    }

    /**
     * @group forecasts
     * @covers ::reporteeForecastRollUp
     */
    public function testReporteeForecastRollUpSavesCorrectValues()
    {
        // Create a draft manager worksheet
        $this->createDraftManagerWorksheet();
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 1, 0);

        // Assert the the worksheet exists
        $this->assertNotNull($worksheet, 'User Draft Forecast Manager Worksheet Not Found');

        // Assert that the worksheet is a draft
        $this->assertEquals(1, $worksheet->draft);

        // Assert that the draft worksheet has the correct currency_id and base_rate
        $this->assertNotEmpty($worksheet->currency_id);
        $this->assertEquals('-99', $worksheet->currency_id);
        $this->assertNotEmpty($worksheet->base_rate);
        $this->assertEquals(1, $worksheet->base_rate);

        // Assert that the draft worksheet has the correct user IDs
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals(self::$manager->id, $worksheet->assigned_user_id);

        // Assert that the draft worksheet's adjusted case values equal the standard case values
        $this->assertEquals(
            [$worksheet->likely_case, $worksheet->best_case, $worksheet->worst_case],
            [$worksheet->likely_case_adjusted, $worksheet->best_case_adjusted, $worksheet->worst_case_adjusted]
        );

        // Assert that the quota was pulled from the Quotas table
        $this->assertEquals(600, $worksheet->quota);

        // Assert that show history log is true
        $this->assertEquals(1, $worksheet->show_history_log);
    }

    /**
     * @group forecasts
     * @covers ::reporteeForecastRollUp
     */
    public function testReporteeForecastRollUpDoesNotCreateCommittedVersion()
    {
        // Create a draft manager worksheet
        $this->createDraftManagerWorksheet();

        // Assert that a committed version was not created in the process
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 0, 0);
        $this->assertNull($worksheet);
    }

    /**
     * @group forecasts
     * @covers ::commitManagerForecast
     */
    public function testCommitManagerForecastSavesCorrectValues()
    {
        // Create a committed ForecastManagerWorksheet
        $this->createCommittedManagerWorksheet();

        // Assert that the committed worksheet exists
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 0, 0);
        $this->assertNotNull($worksheet, 'User Committed Forecast Manager Worksheet Not Found');

        // Assert that the committed worksheet has the correct user IDs
        $this->assertEquals(self::$user->id, $worksheet->user_id);
        $this->assertEquals(self::$manager->id, $worksheet->assigned_user_id);

        // Assert that the committed worksheet is not marked as a draft
        $this->assertEquals(0, $worksheet->draft);
    }

    /**
     * @group forecasts
     * @covers ::commitManagerForecast
     */
    public function testCommitRecalculatesManagerDirectQuota()
    {
        // Create a committed ForecastManagerWorksheet
        $this->createCommittedManagerWorksheet();

        // Get the direct quota for the manager
        $quota = BeanFactory::newBean('Quotas');
        $quota->retrieve_by_string_fields(
            [
                'timeperiod_id' => self::$timeperiod->id,
                'user_id' => self::$manager->id,
                'committed' => 1,
                'quota_type' => 'Direct',
                'deleted' => 0,
            ]
        );

        // Assert that the quota has the correct amount
        $this->assertEquals(1400, $quota->amount);
    }

    /**
     * @group forecasts
     * @covers ::reporteeForecastRollUp
     */
    public function testUserCommitsUpdatesMangerDraftAndUpdatesCommittedVersion()
    {
        // Create a committed ForecastManagerWorksheet
        $this->createCommittedManagerWorksheet();
        $originalCommitedWorksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 0, 0);

        // Adjust the forecast and update the manager's draft
        self::$forecast->best_case += 100;
        $this->createDraftManagerWorksheet();

        // Reload the committed ForecastManagerWorksheet so we can compare it with the original
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 0, 0);

        // Make sure that the best case on the new committed version still equals the original value
        $this->assertEquals(self::$forecast->best_case, $worksheet->best_case);

        // SFA-787: Make sure that the date_modified didn't get updated since a reportee committed and not a manager
        $this->assertEquals($originalCommitedWorksheet->date_modified, $worksheet->date_modified);
    }


    /**
     * @group forecasts
     * @covers ::commitManagerForecast
     */
    public function testShowHistoryLogIsZeroWhenAdjustedColumnIsChanged()
    {
        // Create a committed ForecastManagerWorksheet
        $this->createCommittedManagerWorksheet();

        // Change an "adjusted" column on the draft record
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 1, 0);
        $worksheet->likely_case_adjusted = SugarMath::init($worksheet->likely_case_adjusted)->add(100)->result();
        $worksheet->save();

        // Reload the draft record
        $worksheet = $this->loadManagerWorksheet(self::$manager->id, self::$user->id, 1, 0);

        // Make sure that we are not showing the history log
        $this->assertEquals(0, $worksheet->show_history_log);
    }

    /**
     * @group forecasts
     * @covers ::commitManagerForecast
     */
    public function testCommitManagerForecastReturnsFalseWhenUserNotAManager()
    {
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');
        $return = $worksheet->commitManagerForecast(self::$user, self::$timeperiod->id);
        $this->assertFalse($return);
    }

    /**
     * @group forecasts
     * @covers ::recalcUserQuota
     */
    public function testManagerQuotaReCalcWorks()
    {
        // From the data created when the class was started, the manager had a rollup quota of 2000, direct 1000,
        // and the user had a quota of 600, so, it should return 1400 as that is the difference
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');

        $new_mgr_quota = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'recalcUserQuota',
            [
                self::$manager->id,
                self::$timeperiod->id,
            ]
        );

        $this->assertEquals(1400, $new_mgr_quota, '');
    }

    /**
     * @group forecasts
     * @covers ::recalcUserQuota
     */
    public function testManagerQuotaNoRecalc()
    {
        // From the data created when the class was started, the manager had a quota of 1000
        // and the user had a quota of 600. We are going to set the manager direct to 4000, so
        // that the total is 4600 (2600 over the Rollup of 2000).  It should NOT recalc at that point.
        $worksheet = BeanFactory::newBean('ForecastManagerWorksheets');
        self::$manager_quota->amount = 4000;
        self::$manager_quota->save();
        $new_mgr_quota = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'recalcUserQuota',
            [
                self::$manager->id,
                self::$timeperiod->id,
            ]
        );

        $this->assertEquals(4000, $new_mgr_quota, '');
    }

    /**
     * @covers ::getQuota
     */
    public function testGetQuota()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['getBean', 'save'])
            ->getMock();

        $quota = $this->getMockBuilder('Quota')
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $params = [
            'timeperiod_id' => 'test_timeperiod',
            'user_id' => 'test_user_id',
            'committed' => 1,
            'quota_type' => 'test_quota_type',
            'deleted' => 0,
        ];

        $quota->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with($params);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->willReturn($quota);

        SugarTestReflection::callProtectedMethod(
            $worksheet,
            'getQuota',
            [
                $params['user_id'],
                $params['timeperiod_id'],
                $params['quota_type'],
            ]
        );
    }

    /**
     * @covers ::commitQuota
     */
    public function testCommitQuota()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['getQuota', 'save'])
            ->getMock();

        $quota = $this->getMockBuilder('Quota')
            ->setMethods(['save'])
            ->getMock();

        $quota->expects($this->once())
            ->method('save');

        $worksheet->expects($this->once())
            ->method('getQuota')
            ->with('test_user_id', 'test_timeperiod_id', 'test_quota_type')
            ->willReturn($quota);

        $quota = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'commitQuota',
            [
                '50000.000000',
                'test_user_id',
                'test_timeperiod_id',
                'test_quota_type',
            ]
        );

        $this->assertEquals('50000.000000', $quota->amount);
        $this->assertEquals(1, $quota->committed);
    }

    /**
     * @covers ::rollupDraftToCommittedWorksheet
     */
    public function testRollupDraftToCommittedWorksheetReturnFalse()
    {
        $mockManagerWorksheetOne = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save', 'toArray'])
            ->getMock();
        $mockManagerWorksheetOne->user_id = 'test_user_id';
        $mockManagerWorksheetOne->assigned_user_id = 'test_user_id';
        $mockManagerWorksheetOne->timeperiod_id = 'test_timeperiod_id';

        $mgrWorksheetBean = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $mgrWorksheetBean->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                [
                    'user_id' => 'test_user_id',
                    'assigned_user_id' => 'test_user_id',
                    'timeperiod_id' => 'test_timeperiod_id',
                    'draft' => 0,
                    'deleted' => 0,
                ]
            )
            ->willReturn(false);

        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['getBean', 'save'])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('getBean')
            ->willReturn($mgrWorksheetBean);

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'rollupDraftToCommittedWorksheet',
            [
                $mockManagerWorksheetOne,
                [],
            ]
        );

        $this->assertFalse($actual);
    }

    public static function dataProviderRollupDraftToCommittedWorksheet()
    {
        return [
            [
                [],
                [
                    'likely_case',
                    'best_case',
                    'worst_case',
                ],
            ],
            [
                ['likely_case'],
                [
                    'likely_case',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderRollupDraftToCommittedWorksheet
     * @covers ::rollupDraftToCommittedWorksheet
     */
    public function testRollupDraftToCommittedWorksheet($copyMap, $copyMapExpected)
    {
        $mockManagerWorksheetOne = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save', 'toArray'])
            ->getMock();
        $mockManagerWorksheetOne->user_id = 'test_user_id';
        $mockManagerWorksheetOne->assigned_user_id = 'test_user_id';
        $mockManagerWorksheetOne->timeperiod_id = 'test_timeperiod_id';

        $mockManagerWorksheetOne->expects($this->once())
            ->method('toArray')
            ->willReturn([]);

        $mgrWorksheetBean = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save', 'retrieve_by_string_fields'])
            ->getMock();

        $mgrWorksheetBean->expects($this->once())
            ->method('save');

        $mgrWorksheetBean->id = 'unittest_id';

        $mgrWorksheetBean->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->with(
                [
                    'user_id' => 'test_user_id',
                    'assigned_user_id' => 'test_user_id',
                    'timeperiod_id' => 'test_timeperiod_id',
                    'draft' => 0,
                    'deleted' => 0,
                ]
            )
            ->willReturn($mgrWorksheetBean);


        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['getBean', 'save', 'copyValues'])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('getBean')
            ->willReturn($mgrWorksheetBean);

        $worksheet->expects($this->once())
            ->method('copyValues')
            ->with($copyMapExpected, [], $mgrWorksheetBean);

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'rollupDraftToCommittedWorksheet',
            [
                $mockManagerWorksheetOne,
                $copyMap,
            ]
        );

        $this->assertTrue($actual);
    }

    public function dataProviderCopyValues()
    {
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
        ];
    }

    /**
     * @dataProvider dataProviderCopyValues
     * @covers ::copyValues
     */
    public function testCopyValues($fields, $values)
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save'])
            ->getMock();

        SugarTestReflection::callProtectedMethod(
            $worksheet,
            'copyValues',
            [
                $fields,
                $values,
            ]
        );

        $this->assertEquals('50.000000', $worksheet->likely_case);
    }

    /**
     * @covers ForecastManagerWorksheet::assignQuota
     */
    public function testAssignQuotaReturnFalseWhenUserIsNotManager()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods(['save', 'isUserManager'])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('isUserManager')
            ->with('test_user_id')
            ->willReturn(false);

        $actual = $worksheet->assignQuota('test_user_id', 'test_timeperiod_id');

        $this->assertFalse($actual);
    }

    /**
     * @covers ForecastManagerWorksheet::assignQuota
     */
    public function testAssignQuota()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'getBean',
                'isUserManager',
                'getSugarQuery',
                'fixTopLevelManagerQuotaRollup',
                '_assignQuota',
                'rollupDraftToCommittedWorksheet',
            ])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('isUserManager')
            ->with('test_user_id')
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('fixTopLevelManagerQuotaRollup')
            ->with('test_user_id', 'test_timeperiod_id');

        $sqOne = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();
        $sqTwo = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();
        $sqExecute = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute', 'union', 'addQuery'])
            ->getMock();

        $sqExecute->expects($this->once())
            ->method('execute')
            ->willReturn([
                [
                    'id' => 'test_worksheet_id1',
                    'user_id' => 'test_user_id1',
                    'quota' => '500.000000',
                ],
                [
                    'id' => 'test_worksheet_id2',
                    'user_id' => 'test_user_id',
                    'quota' => '1500.000000',
                ],
            ]);

        $sqExecute->expects($this->once())
            ->method('union')
            ->with($sqOne)
            ->willReturn($sqExecute);

        $sqExecute->expects($this->once())
            ->method('addQuery')
            ->with($sqTwo);

        $worksheet->expects($this->exactly(3))
            ->method('getSugarQuery')
            ->willReturnOnConsecutiveCalls(
                $sqOne,
                $sqTwo,
                $sqExecute
            );

        $worksheet->expects($this->exactly(2))
            ->method('_assignQuota')
            ->withConsecutive(
                ['500.000000', 'Rollup', 'test_user_id1', 'test_timeperiod_id', false],
                ['1500.000000', 'Direct', 'test_user_id', 'test_timeperiod_id', true]
            );

        $mockWorksheetOne = $this->createPartialMock('ForecastManagerWorksheet', ['save']);
        $mockWorksheetTwo = $this->createPartialMock('ForecastManagerWorksheet', ['save']);

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                ['ForecastManagerWorksheets', 'test_worksheet_id1'],
                ['ForecastManagerWorksheets', 'test_worksheet_id2']
            )
            ->willReturnOnConsecutiveCalls(
                $mockWorksheetOne,
                $mockWorksheetTwo
            );


        $worksheet->expects($this->exactly(2))
            ->method('rollupDraftToCommittedWorksheet')
            ->withConsecutive(
                [$mockWorksheetOne, ['quota']],
                [$mockWorksheetTwo, ['quota']]
            );


        $actual = $worksheet->assignQuota('test_user_id', 'test_timeperiod_id');

        $this->assertTrue($actual);
    }

    /**
     * @covers ForecastManagerWorksheet::_assignQuota
     */
    public function test_AssignQuotaDoesNotUseActivityStreams()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'commitQuota',
                'recalcQuotas',
                'getActivityQueueManager',
            ])
            ->getMock();

        $worksheet->expects($this->never())
            ->method('getActivityQueueManager');

        $worksheet->expects($this->once())
            ->method('commitQuota')
            ->with('5000.000000', 'test_user_id', 'test_timeperiod_id', 'test_type');

        $worksheet->expects($this->once())
            ->method('recalcQuotas')
            ->with('test_user_id', 'test_timeperiod_id', true);

        SugarTestReflection::callProtectedMethod(
            $worksheet,
            '_assignQuota',
            [
                '5000.000000',
                'test_type',
                'test_user_id',
                'test_timeperiod_id',
                false,
            ]
        );
    }

    public static function dataProvider_assignQuota()
    {
        return [
            [
                '50.000000',
                '60.000000',
                [
                    'isUpdate' => true,
                    'dataChanges' => [
                        'amount' => [
                            'field_name' => 'amount',
                            'field_type' => 'currency',
                            'before' => '50.000000',
                            'after' => '60.000000',
                        ],
                    ],
                ],
            ],
            [
                '',
                '60.000000',
                [
                    'isUpdate' => false,
                    'dataChanges' => [
                        'amount' => [
                            'field_name' => 'amount',
                            'field_type' => 'currency',
                            'before' => '',
                            'after' => '60.000000',
                        ],
                    ],
                ],
            ],
            [
                '50.000000',
                '50.000000',
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider_assignQuota
     * @covers       ForecastManagerWorksheet::_assignQuota
     * @param string $current_quota
     * @param string $new_quota
     * @param array $expectedActivityMessage
     */
    public function test_AssignQuota($current_quota, $new_quota, $expectedActivityMessage)
    {
        SugarAutoLoader::load('modules/ActivityStream/Activities/ActivityQueueManager.php');
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'getQuota',
                'commitQuota',
                'recalcQuotas',
                'getActivityQueueManager',
            ])
            ->getMock();

        $currentQuotaBean = $this->getMockBuilder('Quota')
            ->setMethods(['save'])
            ->getMock();
        $currentQuotaBean->amount = $current_quota;

        $worksheet->expects($this->once())
            ->method('getQuota')
            ->with('test_user_id', 'test_timeperiod_id', 'test_type')
            ->willReturn($currentQuotaBean);

        $commitQuotaBean = $this->getMockBuilder('Quota')
            ->setMethods(['save'])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('commitQuota')
            ->with($new_quota, 'test_user_id', 'test_timeperiod_id', 'test_type')
            ->willReturn($commitQuotaBean);

        $worksheet->expects($this->once())
            ->method('recalcQuotas')
            ->with('test_user_id', 'test_timeperiod_id', true)
            ->willReturn($new_quota);

        if (!empty($expectedActivityMessage)) {
            $mockAQM = $this->getMockBuilder('ActivityQueueManager')
                ->setMethods(['eventDispatcher'])
                ->getMock();

            $mockAQM->expects($this->once())
                ->method('eventDispatcher')
                ->with($commitQuotaBean, 'after_save', $expectedActivityMessage);

            $worksheet->expects($this->once())
                ->method('getActivityQueueManager')
                ->willReturn($mockAQM);
        } else {
            $worksheet->expects($this->never())
                ->method('getActivityQueueManager');
        }

        SugarTestReflection::callProtectedMethod(
            $worksheet,
            '_assignQuota',
            [
                $new_quota,
                'test_type',
                'test_user_id',
                'test_timeperiod_id',
                true,
            ]
        );
    }

    /**
     * @covers ::worksheetTotals
     */
    public function testWorksheetTotalsReturnFalseWithInvalidTimeperiod()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'getBean',
            ])
            ->getMock();


        $tpMock = $this->createPartialMock('TimePeriod', ['save']);

        $worksheet->expects($this->once())
            ->method('getBean')
            ->with('TimePeriods', 'test_timeperiod_id')
            ->willReturn($tpMock);

        $this->assertFalse($worksheet->worksheetTotals('test_user_id', 'test_timeperiod_id'));
    }

    public static function dataProviderWorksheetTotals()
    {
        return [
            [
                [],
                [
                    'quota' => '0',
                    'best_case' => '0',
                    'best_adjusted' => '0',
                    'likely_case' => '0',
                    'likely_adjusted' => '0',
                    'worst_case' => '0',
                    'worst_adjusted' => '0',
                    'included_opp_count' => 0,
                    'pipeline_opp_count' => 0,
                    'pipeline_amount' => '0',
                    'closed_amount' => '0',
                ],
            ],
            [
                [
                    [
                        'base_rate' => '1.000000',
                        'quota' => '5.000000',
                        'best_case' => '5.000000',
                        'best_case_adjusted' => '5.000000',
                        'likely_case' => '5.000000',
                        'likely_case_adjusted' => '5.000000',
                        'worst_case' => '5.000000',
                        'worst_case_adjusted' => '5.000000',
                        'closed_amount' => '5.000000',
                        'opp_count' => 1,
                        'pipeline_opp_count' => 1,
                        'pipeline_amount' => '5.000000',
                        'closed_amount' => '0.000000',
                    ],
                ],
                [
                    'quota' => '5.000000',
                    'best_case' => '5.000000',
                    'best_adjusted' => '5.000000',
                    'likely_case' => '5.000000',
                    'likely_adjusted' => '5.000000',
                    'worst_case' => '5.000000',
                    'worst_adjusted' => '5.000000',
                    'included_opp_count' => 1,
                    'pipeline_opp_count' => 1,
                    'pipeline_amount' => '5.000000',
                    'closed_amount' => '0.000000',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWorksheetTotals
     * @covers ::worksheetTotals
     * @param array $queryReturn
     * @param array $expected
     */
    public function testWorksheetTotals($queryReturn, $expected)
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'getBean',
                'getSugarQuery',
            ])
            ->getMock();


        $tpMock = $this->createPartialMock('TimePeriod', ['save']);
        $tpMock->id = 'test_timeperiod_id';

        $mockSQ = $this->getMockBuilder('SugarQuery')
            ->setMethods(['execute'])
            ->getMock();

        $mockSQ->expects($this->once())
            ->method('execute')
            ->willReturn($queryReturn);

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($mockSQ);

        $worksheet->expects($this->exactly(2))
            ->method('getBean')
            ->withConsecutive(
                ['TimePeriods', 'test_timeperiod_id'],
                ['ForecastManagerWorksheets']
            )
            ->willReturnOnConsecutiveCalls(
                $tpMock,
                $worksheet
            );

        $actual = $worksheet->worksheetTotals('test_user_id', 'test_timeperiod_id');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::updateManagerWorksheetQuota
     */
    public function testUpdateManagerWorksheetQuotaReturnFalseIfUserIsNotManager()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'isUserManager',
            ])
            ->getMock();

        $worksheet->expects($this->once())
            ->method('isUserManager')
            ->with('test_user_id')
            ->willReturn(false);

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'updateManagerWorksheetQuota',
            [
                'test_user_id',
                'test_timeperiod_id',
                '50.000000',
                false,
            ]
        );

        $this->assertFalse($actual);
    }

    public static function dataProviderUpdateManagerWorksheetQuota()
    {
        return [
            [
                null,
                true,
                '50.000000',
                '60.000000',
                true,
            ],
            // committed row found, but quota is the same
            [
                true,
                false,
                '50.000000',
                '50.000000',
                false,
            ],
            // committed row found
            [
                true,
                false,
                '50.000000',
                '60.000000',
                true,
            ],
            // committed row not found
            [
                null,
                false,
                '50.000000',
                '60.000000',
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderUpdateManagerWorksheetQuota
     * @covers ::updateManagerWorksheetQuota
     */
    public function testUpdateManagerWorksheetQuota($retrieve_return, $isDraft, $worksheetQuota, $quota, $expected)
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'isUserManager',
                'getBean',
                'retrieve_by_string_fields',
            ])
            ->getMock();

        $userBean = $this->createPartialMock('User', ['save']);

        $worksheet->expects($this->once())
            ->method('isUserManager')
            ->with('test_user_id')
            ->willReturn(true);

        $worksheet->expects($this->atLeastOnce())
            ->method('getBean')
            ->withConsecutive(
                ['ForecastManagerWorksheets'],
                ['Users', 'test_user_id']
            )
            ->willReturnOnConsecutiveCalls(
                $worksheet,
                $userBean
            );

        $worksheet->expects($this->once())
            ->method('retrieve_by_string_fields')
            ->willReturn($retrieve_return);

        if ($expected === true) {
            $worksheet->expects($this->once())
                ->method('save');
        }

        $worksheet->quota = $worksheetQuota;

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'updateManagerWorksheetQuota',
            [
                'test_user_id',
                'test_timeperiod_id',
                $quota,
                $isDraft,
            ]
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::getManagerQuota
     */
    public function testGetManagerQuota()
    {
        $db = new SugarTestDatabaseMock();
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
            ])
            ->getMock();

        $db->addQuerySpy('get_manager_quota', '/union all/', [
            ['amount' => '50.00', 'id' => 'test_id_1'],
        ]);

        $worksheet->db = $db;

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'getManagerQuota',
            [
                'test_user_id',
                'test_timeperiod_id',
            ]
        );

        $expected = [
            'amount' => '50.00',
            'id' => 'test_id_1',
        ];

        $this->assertSame($expected, $actual);
    }

    public static function dataProviderGetQuotaSum()
    {
        return [
            [
                [
                    ['amount' => '50.00'],
                ],
                '50.00',
            ],
            [
                [],
                0,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetQuotaSum
     * @covers ::getQuotaSum
     */
    public function testGetQuotaSum($rows, $expected)
    {
        $db = new SugarTestDatabaseMock();
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
            ])
            ->getMock();

        $db->addQuerySpy('get_quota_sum', '/sum\(q\.amount\)/', $rows);

        $worksheet->db = $db;

        $actual = SugarTestReflection::callProtectedMethod(
            $worksheet,
            'getQuotaSum',
            [
                'test_user_id',
                'test_timeperiod_id',
            ]
        );

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers ::setWorksheetArgs
     */
    public function testSetWorksheetArgs()
    {
        $args = [
            'likely_case' => '50.00',
            'best_case' => '50.00',
        ];

        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
            ])
            ->getMock();

        $worksheet->setWorksheetArgs($args);

        $this->assertSame($args, $worksheet->args);

        foreach ($args as $key => $val) {
            $this->assertSame($val, $worksheet->$key);
        }
    }

    /**
     * @covers ::fixTopLevelManagerQuotaRollup
     */
    public function testFixTopLevelManagerQuotaRollup()
    {
        $worksheet = $this->getMockBuilder('ForecastManagerWorksheet')
            ->setMethods([
                'save',
                'isTopLevelManager',
                'getSugarQuery',
                'commitQuota',
            ])
            ->getMock();

        $sq = $this->getMockBuilder('SugarQuery')
            ->setMethods(['getOne'])
            ->getMock();
        $sq->expects($this->once())
            ->method('getOne')
            ->willReturn('50.000000');

        $worksheet->expects($this->once())
            ->method('getSugarQuery')
            ->willReturn($sq);

        $worksheet->expects($this->once())
            ->method('isTopLevelManager')
            ->willReturn(true);

        $worksheet->expects($this->once())
            ->method('commitQuota')
            ->with('50.000000', 'test_user_id', 'test_timeperiod_id', 'Rollup');

        SugarTestReflection::callProtectedMethod(
            $worksheet,
            'fixTopLevelManagerQuotaRollup',
            [
                'test_user_id',
                'test_timeperiod_id',
            ]
        );
    }
}
