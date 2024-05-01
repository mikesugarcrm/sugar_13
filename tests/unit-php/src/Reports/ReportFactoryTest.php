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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\ReportFactory;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Reports\ReportFactory
 */
class ReportFactoryTest extends TestCase
{
    /**
     * @covers ::getReport
     */
    public function testGetReport()
    {
        $reportsFactory = ReportFactory::getReport('detailed_summary', [
            'record' => 'TestRecordID',
            'group_filters' => [],
            'use_saved_filters' => false,
        ], true);

        $this->assertSame(get_class($reportsFactory), \Sugarcrm\Sugarcrm\Reports\Types\SummaryDetails::class);
    }
}
