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

require_once 'modules/ACLActions/actiondefs.php';

class TeamBasedACLVisibilityTest extends TestCase
{
    /**
     * @var TeamBasedACLConfigurator
     */
    protected $tbaConfig;

    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var TeamSet
     */
    protected $teamSet;

    /**
     * @var Team
     */
    protected $team;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var SugarBean
     */
    protected $bean;

    /**
     * @var boolean
     */
    protected $tbaGlobal;

    /**
     * @var boolean
     */
    protected $tbaModule;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);
        $this->tbaConfig = new TeamBasedACLConfigurator();
        $this->tbaGlobal = $this->tbaConfig->isEnabledGlobally();
        $this->tbaModule = $this->tbaConfig->isEnabledForModule($this->module);

        $this->tbaConfig->setGlobal(true);
        $this->tbaConfig->setForModule($this->module, true);

        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams([$this->team->id]);

        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $this->bean = SugarTestAccountUtilities::createAccount();
        $this->bean->addVisibilityStrategy('TeamBasedACLVisibility');
        $this->bean->acl_team_set_id = $this->teamSet->id;
        $this->bean->save();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $this->teamSet->mark_deleted($this->teamSet->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        $this->tbaConfig->setForModule($this->module, $this->tbaModule);
        $this->tbaConfig->setGlobal($this->tbaGlobal);
        SugarTestHelper::tearDown();
    }

    public function testVisibleRecord()
    {
        $this->team->add_user_to_team($this->user->id);
        $this->assertTrue($this->isBeanAvailableUsingFrom());
        $this->assertTrue($this->isBeanAvailableUsingWhere());
    }

    public function testInvisibleRecord()
    {
        $this->team->remove_user_from_team($this->user->id);
        $this->assertFalse($this->isBeanAvailableUsingFrom());
        $this->assertFalse($this->isBeanAvailableUsingWhere());
    }

    /**
     * Owner should have full access despite team membership.
     */
    public function testOwnerPassVisibility()
    {
        $this->bean->assigned_user_id = $this->user->id;
        $this->bean->save();
        $this->team->remove_user_from_team($this->user->id);

        $this->assertTrue($this->isBeanAvailableUsingWhere());
        $this->assertTrue($this->isBeanAvailableUsingFrom());
    }

    /**
     * Test that visibility affects implicitly assigned users.
     * Original user should receive a record that assigned to new user's private team.
     */
    public function testImplicitTeamMembership()
    {
        $newUser = SugarTestUserUtilities::createAnonymousUser();
        $privateTeamSet = BeanFactory::newBean('TeamSets');
        $this->bean->acl_team_set_id = $privateTeamSet->addTeams([$newUser->getPrivateTeamID()]);
        $this->bean->save();

        $this->assertFalse($this->isBeanAvailableUsingFrom());
        $this->assertFalse($this->isBeanAvailableUsingWhere());

        // The user will appear in new user's private team.
        // If the user reported to another one he would get to the new user's team as well.
        $newUser->reports_to_id = $this->user->id;
        $newUser->save();

        $this->assertTrue($this->isBeanAvailableUsingFrom());
        $this->assertTrue($this->isBeanAvailableUsingWhere());
    }

    /**
     * The ACL should not depend on other visibilities.
     * @dataProvider teamVisibilityProvider
     */
    public function testIsolatedTeamSecurity($visibilities, $inTeam, $isVisible)
    {
        $this->bean = $this->getMockBuilder('Account')
            ->setMethods(['loadVisibility'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->bean->expects($this->any())->method('loadVisibility')->will(
            $this->returnCallback(function () use ($visibilities) {
                return new BeanVisibility($this->bean, $visibilities);
            })
        );

        $this->bean->__construct();
        $this->bean->team_id = $this->team->id;
        $this->bean->team_set_id = $this->teamSet->id;
        $this->bean->acl_team_set_id = $this->teamSet->id;
        $this->bean->save();
        SugarTestAccountUtilities::setCreatedAccount([$this->bean->id]);

        if ($inTeam) {
            $this->team->add_user_to_team($this->user->id);
        } else {
            $this->team->remove_user_from_team($this->user->id);
        }
        if ($isVisible) {
            $this->assertTrue($this->isBeanAvailableUsingWhere());
        } else {
            $this->assertFalse($this->isBeanAvailableUsingWhere());
        }
    }

    public function teamVisibilityProvider()
    {
        return [
            // List of Visibilities.
            // Is a current user in bean's teams.
            // Is a record visible.
            [['TeamSecurity' => true], true, true],
            [['TeamSecurity' => true], false, false],
            [['TeamBasedACLVisibility' => true], true, true],
            [['TeamBasedACLVisibility' => true], false, false],
            [[], true, true],
            [[], false, true],
        ];
    }

    /**
     * Test that admin access does NOT affect TBA.
     * @dataProvider accessProvider
     */
    public function testAdminAccessTeamCheck($access)
    {
        $aclData = [];
        $action = 'view';
        $expectedAccess = ACL_ALLOW_SELECTED_TEAMS;

        $this->bean->team_id = $this->team->id;
        $this->bean->team_set_id = $this->teamSet->id;
        $this->bean->acl_team_set_id = $this->teamSet->id;
        $this->bean->save();

        $aclData['module'][$action]['aclaccess'] = $expectedAccess;
        $aclData['module']['admin']['aclaccess'] = $access;
        ACLAction::setACLData($this->user->id, $this->bean->module_dir, $aclData);

        $actualAccess = ACLAction::getUserAccessLevel($this->user->id, $this->bean->module_dir, $action);
        $this->assertEquals($expectedAccess, $actualAccess);
    }

    public function accessProvider()
    {
        return [
            [ACL_ALLOW_ADMIN],
            [ACL_ALLOW_ADMIN_DEV],
        ];
    }

    /**
     * Check possibility to retrieve a record with visibility's FROM part only.
     * @return boolean
     */
    protected function isBeanAvailableUsingFrom()
    {
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $sq = new SugarQuery();
        $sq->select(['id']);
        $sq->from($this->bean);
        $sq->where()->equals('id', $this->bean->id);
        $result = $sq->execute();

        $GLOBALS['current_user'] = $oldCurrentUser;

        return empty($result) ? false : true;
    }

    /**
     * Check possibility to retrieve a record with visibility's WHERE part only.
     * @return boolean
     */
    protected function isBeanAvailableUsingWhere()
    {
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $this->bean->disable_row_level_security = false;
        $record = $this->bean->retrieve();
        $this->bean->disable_row_level_security = true;

        $GLOBALS['current_user'] = $oldCurrentUser;
        return $record ? true : false;
    }
}
