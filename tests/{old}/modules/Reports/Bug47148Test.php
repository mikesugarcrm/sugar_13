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

class ChartDisplayMock47148 extends ChartDisplay
{
    /**
     * Overwrite this method to not actually run a report
     */
    public function setReporter(Report $reporter)
    {
        $this->reporter = $reporter;
    }

    public function get_row_remap($row)
    {
        return parent::get_row_remap($row);
    }
}

/**
 * Bug47148Test.php
 * Reporter has a big problem with big numbers
 * @ticket 47148
 */
class Bug47148Test extends TestCase
{
    private $backup = [];

    protected function setUp(): void
    {
        global $beanFiles, $beanList;
        require 'include/modules.php';
        $this->backup['do_thousands'] = $GLOBALS['do_thousands'] ?? false;
        $GLOBALS['do_thousands'] = true;
    }

    protected function tearDown(): void
    {
        $GLOBALS['do_thousands'] = $this->backup['do_thousands'];
    }

    public function testBigNumber()
    {
        // big number from database, it has to be string
        $expected = '1000000000000000';

        // define fake of row result
        $row = [
            'cells' => [
                0 => [
                    'val' => $expected,
                ],
            ],
        ];
        $row['count'] = count($row['cells']);

        // define fake of report
        $report = new Report();
        $report->chart_numerical_position = 0;
        $report->chart_header_row = [
            0 => [
                'label' => 'test',
                'column_key' => 0,
            ],
        ];
        $report->module = null;
        $report->report_def = [
            'group_defs' => [],
        ];

        $cdm = new ChartDisplayMock47148();
        $cdm->setReporter($report);

        $actual = $cdm->get_row_remap($row);
        $actual = $actual['numerical_value'] * 1000; // recovery of division by 1000 from get_row_remap function
        $actual = sprintf('%0.0f', $actual); // getting float as string

        $this->assertEquals($expected, $actual, 'Big number is not valid');
    }
}
