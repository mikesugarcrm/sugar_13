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
 * @covers Report Drillthru Visibility
 */
class ReportDrillthruVisibilityTest extends TestCase
{
    protected $teamSet1;
    protected $teamSet2;
    protected $account;
    protected $report;

    protected function setUp(): void
    {
        $user = SugarTestHelper::setUp('current_user');

        $team1 = SugarTestTeamUtilities::createAnonymousTeam();
        $team2 = SugarTestTeamUtilities::createAnonymousTeam();

        $team1->add_user_to_team($user->id);

        $this->teamSet1 = BeanFactory::newBean('TeamSets');
        $this->teamSet1->addTeams([$team1->id]);

        $this->teamSet2 = BeanFactory::newBean('TeamSets');
        $this->teamSet2->addTeams([$team2->id]);

        // account assigned to teamSet1
        $account = SugarTestAccountUtilities::createAccount('', ['name' => 'RPTTESTACCOUNT']);
        $account->team_id = $team1->id;
        $account->team_set_id = $this->teamSet1->id;
        $account->save();
        $this->account = $account;

        // account assigned to teamSet2
        $account = SugarTestAccountUtilities::createAccount('', ['name' => 'RPTTESTACCOUNT']);
        $account->team_id = $team2->id;
        $account->team_set_id = $this->teamSet2->id;
        $account->save();

        // create a report with filter Accounts -> Name equals RPTTESTACCOUNT to simulate drillthru on accounts:name
        // @codingStandardsIgnoreStart
        $def = '{"display_columns":[],"module":"Accounts","group_defs":[],"summary_columns":[{"name":"count","label":"Count","field_type":"","group_function":"count","table_key":"self"}],"report_name":"test","chart_type":"none","do_round":1,"chart_description":"","numerical_chart_column":"self:count","numerical_chart_column_type":"","assigned_user_id":"seed_will_id","report_type":"summary","full_table_list":{"self":{"value":"Accounts","module":"Accounts","label":"Accounts","dependents":[]}},"filters_def":{"Filter_1":{"operator":"AND","0":{"name":"name","table_key":"self","qualifier_name":"equals","input_name0":"RPTTESTACCOUNT","input_name1":"on"}}}}';
        // @codingStandardsIgnoreEnd
        $this->report = new Report($def);
    }

    protected function tearDown(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $this->teamSet1->mark_deleted($this->teamSet1->id);
        $this->teamSet2->mark_deleted($this->teamSet2->id);
        SugarTestHelper::tearDown();
    }

    public function testGetRecordIds()
    {
        $recordIds = $this->report->getRecordIds();
        $this->assertEquals(1, safeCount($recordIds), 'Should return 1 account');
        $this->assertEquals($this->account->id, $recordIds[0], 'Should return accounts that current user can access to');
    }

    public function testGetRecordCount()
    {
        $count = $this->report->getRecordCount();
        $this->assertEquals(1, $count, 'Should return 1');
    }
}
