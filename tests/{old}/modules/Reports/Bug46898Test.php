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

require_once 'modules/Reports/SavedReport.php';

/**
 * Bug #46898
 * Scheduled reports could not be sent to multiple users
 *
 * @group 46898
 * @author mgusev@sugarcrm.com
 */
class Bug46898Test extends TestCase
{
    public function testGetReportsToEmail()
    {
        $user1 = SugarTestUserUtilities::createAnonymousUser();
        $user2 = SugarTestUserUtilities::createAnonymousUser();

        $savedReports = new SavedReport();
        $savedReports->name = 'Bug46898Report1';
        $savedReports->save();

        $reportSchedule = new ReportSchedule();
        $schedule1 = $reportSchedule->save_schedule(false, $user1->id, $savedReports->id, false, 0, true, 'bug');
        $schedule2 = $reportSchedule->save_schedule(false, $user2->id, $savedReports->id, false, 0, true, 'bug');
        $GLOBALS['db']->query("UPDATE {$reportSchedule->table_name} SET next_run='2001-01-01 00:00:00' WHERE id='{$schedule1}'");
        $GLOBALS['db']->query("UPDATE {$reportSchedule->table_name} SET next_run='2001-01-01 00:00:00' WHERE id='{$schedule2}'");

        $actual = $reportSchedule->get_reports_to_email('', 'bug');

        $ids = [];
        foreach ($actual as $item) {
            $ids[] = $item['user_id'];
        }

        $savedReports->mark_deleted($savedReports->id);
        $user1->mark_deleted($user1->id);
        $user2->mark_deleted($user2->id);
        $reportSchedule->mark_deleted($schedule1);
        $reportSchedule->mark_deleted($schedule2);
        $GLOBALS['db']->commit();

        $this->assertEquals(2, count($ids));
        $this->assertContains($user1->id, $ids, 'User is missed in returned array');
        $this->assertContains($user2->id, $ids, 'User is missed in returned array');
    }
}
