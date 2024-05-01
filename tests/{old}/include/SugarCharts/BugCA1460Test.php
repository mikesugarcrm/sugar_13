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

/**
 * Bug CA-1460 - Date fields not sorted cronologically
 *
 * @ticket CA-1460
 */
class BugCA1460Test extends TestCase
{
    /**
     * @var \chartjsReports|mixed
     */
    public $chartJsReports;

    protected function setUp(): void
    {
        $this->chartJsReports = new \chartjsReports();

        $this->chartJsReports->super_set_data = [
            'October 2022' => [
                'group_base_text' => 'October 2022',
                'raw_value' => '2022-10',
            ],
            'November 2022' => [
                'group_base_text' => 'November 2022',
                'raw_value' => '2022-11',
            ],
            'December 2022' => [
                'group_base_text' => 'December 2022',
                'raw_value' => '2022-12',
            ],
        ];
    }

    protected function tearDown(): void
    {
        unset($this->chartJsReports);
    }

    /**
     * Test sort
     *
     * @param string $firstDate
     * @param string $secondDate
     * @param int $expected
     *
     * @dataProvider providerTestSort
     */
    public function testSort($firstDate, $secondDate, $expected)
    {
        $datesToSort = [$firstDate, $secondDate];
        $datesSorted = TestReflection::callProtectedMethod($this->chartJsReports, 'runDateSort', $datesToSort);

        $this->assertEquals($expected, $datesSorted);
    }

    /**
     * Sort provider
     */
    public function providerTestSort()
    {
        return [
            [
                'October 2022',
                'November 2022',
                -1,
            ],
            [
                'October 2022',
                'October 2022',
                0,
            ],
            [
                'November 2022',
                'October 2022',
                1,
            ],
        ];
    }
}
