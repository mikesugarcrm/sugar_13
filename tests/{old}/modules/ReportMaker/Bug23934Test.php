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

class Bug23934Test extends TestCase
{
    private $reportId = 'ad832c9b-59be-bf94-9b8d-4cdab4d3f1e8';

    protected function setUp(): void
    {
        // If only there were some sort of mechanism that handled queries for us...
        $sql = "INSERT INTO report_maker
                (
                    id, deleted, date_entered, date_modified,
                    modified_user_id, created_by, name, title, report_align,
                    description, scheduled, team_id, team_set_id
                )
                VALUES
                (
                    '{$this->reportId}', 0, '2010-11-10 15:05:52', '2010-11-10 15:05:52',
                    '1', '1', '6 month Sales Pipeline Report', '6 month Sales Pipeline Report', 'center',
                    'Opportunities over the next 6 months broken down by month and type', 0, '1', '1'
                )";
        $GLOBALS['db']->query($sql);

        // ... where we could just set some values and do something like $thing->save()...
        $sql = "INSERT INTO report_schedules
                (
                  id, user_id, report_id,
                  date_start, next_run, active,
                  time_interval, date_modified, schedule_type, deleted
                )
                VALUES
                (
                  '728f6b40-2f41-01c6-6e13-4cdac466c862', '1', '{$this->reportId}',
                  '2010-11-09 18:00:00', '2010-11-11 10:00:00', 1,
                   3600, NULL, 'ent', 0
                )";
        $GLOBALS['db']->query($sql);
    }

    public function testGetEntReportsToEmail()
    {
        $rs = new ReportSchedule();
        $results = $rs->get_ent_reports_to_email('1');
        $this->assertArrayHasKey($this->reportId, $results, 'Report Maker Id does not exist in the results to email.');
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("DELETE FROM report_maker WHERE id = '{$this->reportId}'", 'Unable to cleanup Bug 23934 Test');
        $GLOBALS['db']->query("DELETE FROM report_schedules WHERE id = '728f6b40-2f41-01c6-6e13-4cdac466c862'", 'Unable to cleanup Bug 23934 Test');
    }
}
