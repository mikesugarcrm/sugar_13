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

namespace Sugarcrm\SugarcrmTestsUnit\modules\Reports;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \SavedReport
 */
class SavedReportTest extends TestCase
{
    /**
     * @covers ::deleteSchedules
     */
    public function testDeleteSchedules()
    {
        $savedReport = $this->createPartialMock('\SavedReport', [
            'load_relationship',
        ]);
        $savedReport->expects($this->once())
            ->method('load_relationship')
            ->will($this->returnValue(true));

        $reportSchedule = $this->createPartialMock('\ReportSchedule', [
            'mark_deleted',
        ]);
        $reportSchedule->expects($this->once())
            ->method('mark_deleted');
        $link = $this->createPartialMock('\Link2', [
            'getBeans',
        ]);
        $link->expects($this->once())
            ->method('getBeans')
            ->will($this->returnValue([$reportSchedule]));
        $savedReport->reportschedules = $link;
        TestReflection::callProtectedMethod($savedReport, 'deleteSchedules');
    }
}
