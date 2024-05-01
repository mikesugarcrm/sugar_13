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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ReportSchedules;

use DBManager;
use PHPUnit\Framework\TestCase;
use ReportSchedule;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ReportSchedule
 */
class ReportScheduleTest extends TestCase
{
    /**
     * @covers ::getQuery
     */
    public function testGetQueryPro()
    {
        $this->assertStringContainsString('reportschedules_users', $this->getQuery('pro'));
    }

    /**
     * @covers ::getQuery
     */
    public function testGetQueryEnt()
    {
        $this->assertStringNotContainsString('reportschedules_users', $this->getQuery('ent'));
    }

    /**
     * @covers ::getQuery
     */
    private function getQuery(string $scheduleType)
    {
        $schedule = $this->createMock(ReportSchedule::class);
        $schedule->db = $this->createMock(DBManager::class);

        return TestReflection::callProtectedMethod($schedule, 'getQuery', ['test_id', $scheduleType]);
    }
}
