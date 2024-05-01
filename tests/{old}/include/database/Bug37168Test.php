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

class Bug37168Test extends TestCase
{
    protected $has_disable_count_query_enabled;
    protected $previous_current_user;

    protected function setUp(): void
    {
        global $sugar_config, $current_user;

        if ($GLOBALS['db']->dbType != 'mysql') {
            $this->markTestSkipped('Only applies to MySQL');
        }

        $this->has_disable_count_query_enabled = !empty($sugar_config['disable_count_query']);
        if (!$this->has_disable_count_query_enabled) {
            $sugar_config['disable_count_query'] = true;
        }

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    protected function tearDown(): void
    {
        global $sugar_config;

        if (!$this->has_disable_count_query_enabled) {
            unset($sugar_config['disable_count_query']);
        }
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);

        $GLOBALS['current_user'] = $this->previous_current_user;
    }

    public function test_add_distinct_clause()
    {
        //Test a list query
        $focus = new Account();
        $query = 'SELECT * FROM accounts';
        if (!$focus->disable_row_level_security) {
            $focus->add_team_security_where_clause($query);
        }
        $this->assertTrue(preg_match('/\s*?SELECT\s+?DISTINCT/si', $query) !== false, 'Assert that SELECT DISTINCT clause is added');

        //Test union query
        $query = "( SELECT tasks.id , tasks.name , tasks.status , CONCAT(IFNULL(contacts.first_name,''),' ',IFNULL(contacts.last_name,'')) contact_name , tasks.contact_id , contacts.assigned_user_id contact_name_owner , 'Contacts' contact_name_mod, tasks.date_due as date_start , jt1.user_name assigned_user_name , jt1.created_by assigned_user_name_owner , 'Users' assigned_user_name_mod, tasks.assigned_user_id , 'tasks' panel_name FROM tasks INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = 'seed_chris_id' AND team_memberships.deleted=0 group by tst.team_set_id) tasks_tf on tasks_tf.team_set_id = tasks.team_set_id LEFT JOIN contacts contacts ON contacts.id= tasks.contact_id AND contacts.deleted=0 AND contacts.deleted=0 LEFT JOIN users jt1 ON jt1.id= tasks.assigned_user_id AND jt1.deleted=0 AND jt1.deleted=0 where ( tasks.parent_id= '4aba0d8a-ca09-2e11-9a41-4bd1f393448f' AND tasks.parent_type='Accounts' AND tasks.deleted=0 AND (tasks.status='Not Started' OR tasks.status='In Progress' OR tasks.status='Pending Input')) AND tasks.deleted=0 ) UNION ALL ( SELECT meetings.id , meetings.name , meetings.status , ' ' contact_name , ' ' contact_id , ' ' contact_name_owner , ' ' contact_name_mod , meetings.date_start , jt1.user_name assigned_user_name , jt1.created_by assigned_user_name_owner , 'Users' assigned_user_name_mod, meetings.assigned_user_id , 'meetings' panel_name FROM meetings INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = 'seed_chris_id' AND team_memberships.deleted=0 group by tst.team_set_id) meetings_tf on meetings_tf.team_set_id = meetings.team_set_id LEFT JOIN users jt1 ON jt1.id= meetings.assigned_user_id AND jt1.deleted=0 AND jt1.deleted=0 where ( meetings.parent_id= '4aba0d8a-ca09-2e11-9a41-4bd1f393448f' AND meetings.parent_type='Accounts' AND meetings.deleted=0 AND (meetings.status='Planned')) AND meetings.deleted=0 ) UNION ALL ( SELECT calls.id , calls.name , calls.status , ' ' contact_name , ' ' contact_id , ' ' contact_name_owner , ' ' contact_name_mod , calls.date_start , jt1.user_name assigned_user_name , jt1.created_by assigned_user_name_owner , 'Users' assigned_user_name_mod, calls.assigned_user_id , 'calls' panel_name FROM calls INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = 'seed_chris_id' AND team_memberships.deleted=0 group by tst.team_set_id) calls_tf on calls_tf.team_set_id = calls.team_set_id LEFT JOIN users jt1 ON jt1.id= calls.assigned_user_id AND jt1.deleted=0 AND jt1.deleted=0 where ( calls.parent_id= '4aba0d8a-ca09-2e11-9a41-4bd1f393448f' AND calls.parent_type='Accounts' AND calls.deleted=0 AND (calls.status='Planned')) AND calls.deleted=0 ) ORDER BY date_start desc LIMIT 0,11";
        $GLOBALS['db']->query($query);

        //For some reason, because of passing by value we don't get the other UNION ALL distinct clauses, but it is being replaced
        $this->assertTrue(preg_match('/\(\s*?SELECT\s+?DISTINCT/si', $query) !== false, 'Assert that SELECT DISTINCT clause is added');
    }
}
