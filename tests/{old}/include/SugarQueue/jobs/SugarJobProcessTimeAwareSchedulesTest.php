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
 * @coversDefaultClass SugarJobProcessTimeAwareSchedules
 */
class SugarJobProcessTimeAwareSchedulesTest extends TestCase
{
    private $timeAwareTableName = 'time_aware_schedules';

    public function setUp(): void
    {
        $this->createMockSchedules();
    }

    public function tearDown(): void
    {
        $this->removeMockSchedules();
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $mockJob = $this->getMockBuilder('SugarJobProcessTimeAwareSchedules')
            ->onlyMethods(['processSchedules', 'deleteProcessedSchedules'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockJobObj = $this->getMockBuilder('SchedulersJob')
            ->onlyMethods(['succeedJob'])
            ->disableOriginalConstructor()
            ->getMock();
        SugarTestReflection::setProtectedValue($mockJob, 'job', $mockJobObj);

        $expiredSchedules = SugarTestReflection::callProtectedMethod($mockJob, 'retrieveExpiredSchedules');

        $mockJob->expects($this->once())
            ->method('processSchedules')
            ->with($expiredSchedules);
        $mockJob->expects($this->once())
            ->method('deleteProcessedSchedules');
        $mockJobObj->expects($this->once())
            ->method('succeedJob');

        $mockJob->run(null);
    }

    /**
     * @covers ::retrieveExpiredSchedules
     */
    public function testRetrieveExpiredSchedules()
    {
        $mockJob = $this->getMockBuilder('SugarJobProcessTimeAwareSchedules')
            ->disableOriginalConstructor()
            ->getMock();

        $result = SugarTestReflection::callProtectedMethod($mockJob, 'retrieveExpiredSchedules');
        $this->assertEquals(2, safeCount($result));
        $this->assertEquals('33333333-3333-3333-3333-333333333333', $result[0]['id']);
    }

    /**
     * @covers ::processSchedules
     */
    public function testProcessSchedules()
    {
        $mockJob = $this->getMockBuilder('SugarJobProcessTimeAwareSchedules')
            ->onlyMethods(['processRecalculationSchedule'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSchedules = $this->getMockScheduleData();

        $mockJob->expects($this->exactly(3))
            ->method('processRecalculationSchedule');

        $result = SugarTestReflection::callProtectedMethod($mockJob, 'processSchedules', [$mockSchedules]);
        $this->assertEquals([
            '11111111-1111-1111-1111-111111111111',
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
        ], $result);
    }

    /**
     * Adds mock Time-Aware Schedules data to the DB for testing
     */
    protected function createMockSchedules()
    {
        $mockSchedules = $this->getMockScheduleData();
        foreach ($mockSchedules as $mockSchedule) {
            $qb = DBManagerFactory::getConnection()->createQueryBuilder();
            $qb->insert($this->timeAwareTableName)
                ->values(
                    [
                        'id' => $qb->createPositionalParameter($mockSchedule['id']),
                        'next_run' => $qb->createPositionalParameter($mockSchedule['next_run']),
                        'type' => $qb->createPositionalParameter($mockSchedule['type']),
                        'module' => $qb->createPositionalParameter($mockSchedule['module']),
                        'bean_id' => $qb->createPositionalParameter($mockSchedule['bean_id']),
                        'deleted' => $qb->createPositionalParameter($mockSchedule['deleted']),
                    ]
                )
                ->execute();
        }
    }

    /**
     * Test data for mock Time-Aware Schedules
     *
     * @return array[]
     */
    protected function getMockScheduleData()
    {
        return [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'next_run' => (new SugarDateTime('+10 days'))->asDb(),
                'type' => 'recalculation',
                'module' => 'mockModule',
                'bean_id' => 'mockBeanId',
                'deleted' => '0',
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'next_run' => (new SugarDateTime('-15 days'))->asDb(),
                'type' => 'recalculation',
                'module' => 'mockModule',
                'bean_id' => 'mockBeanId',
                'deleted' => '0',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'next_run' => (new SugarDateTime('-20 days'))->asDb(),
                'type' => 'recalculation',
                'module' => 'mockModule',
                'bean_id' => 'mockBeanId',
                'deleted' => '0',
            ],
        ];
    }

    /**
     * Removes any mock Time-Aware Schedules created for testing from the DB
     */
    protected function removeMockSchedules()
    {
        $qb = DBManagerFactory::getConnection()->createQueryBuilder();
        $qb->delete($this->timeAwareTableName)
            ->where($qb->expr()->eq('module', $qb->createPositionalParameter('mockModule')))
            ->execute();
    }
}
