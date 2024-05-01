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
use PHPUnit\Framework\MockObject\MockObject;

class AddTeamSecurityWhereClauseTest extends TestCase
{
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testAddTeamSecurityWhereClauseForRegularUser()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';

        $query = '';

        $visibility = new NormalizedTeamSecurity($bean);
        $visibility->addVisibilityFrom($query);

        $query = preg_replace("/[\t \n]+/", ' ', $query);

        $this->assertStringContainsString(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id ",
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserSpecifyTableAlias()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';

        $query = '';

        $visibility = new NormalizedTeamSecurity($bean);
        $visibility->setOptions([
            'table_alias' => 'myfoo',
        ]);
        $visibility->addVisibilityFrom($query);

        $query = preg_replace("/[\t \n]+/", ' ', $query);
        $this->assertStringContainsString(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_membershipsmyfoo ON tst.team_id = team_membershipsmyfoo.team_id AND team_membershipsmyfoo.user_id = '{$GLOBALS['current_user']->id}' AND team_membershipsmyfoo.deleted=0 group by tst.team_set_id) myfoo_tf on myfoo_tf.team_set_id = myfoo.team_set_id ",
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseForRegularUserWithJoinTeamsParameterTrue()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';

        $query = '';

        $visibility = new NormalizedTeamSecurity($bean);
        $visibility->setOptions([
            'join_teams' => true,
        ]);
        $visibility->addVisibilityFrom($query);

        $query = preg_replace("/[\t \n]+/", ' ', $query);
        $this->assertStringContainsString(
            "INNER JOIN (select tst.team_set_id from team_sets_teams tst INNER JOIN team_memberships team_memberships ON tst.team_id = team_memberships.team_id AND team_memberships.user_id = '{$GLOBALS['current_user']->id}' AND team_memberships.deleted=0 group by tst.team_set_id) foo_tf on foo_tf.team_set_id = foo.team_set_id INNER JOIN teams ON teams.id = team_memberships.team_id AND teams.deleted=0 ",
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseWhenRowLevelSecurityIsDisabled()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->disable_row_level_security = true;
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseWhenModuleIsWorkflow()
    {
        $bean = new SugarBean();
        $bean->module_dir = 'WorkFlow';
        $bean->table_name = 'workflow';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
        );
    }

    public function testAddTeamSecurityWhereClauseForAdmin()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $bean->addVisibilityStrategy('TeamSecurity');
        $query = '';

        $bean->add_team_security_where_clause($query);

        $this->assertEquals(
            '',
            $query
        );
    }

    /**
     * @ticket 26772
     */
    public function testAddTeamSecurityWhereClauseForAdminForModule()
    {
        global $current_user;

        /** @var User|MockObject $current_user */
        $current_user = $this->createPartialMock('User', ['isAdminForModule']);
        $current_user->expects($this->atLeastOnce())
            ->method('isAdminForModule')
            ->with('Foo')
            ->willReturn(true);

        $bean = new SugarBean();
        $bean->module_dir = 'Foo';
        $bean->table_name = 'foo';
        $query = '';
        $bean->addVisibilityStrategy('TeamSecurity');

        $bean->add_team_security_where_clause($query);

        $this->assertEquals('', $query);
    }
}
