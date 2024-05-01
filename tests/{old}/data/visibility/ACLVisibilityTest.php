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

class ACLVisibilityTest extends TestCase
{
    /**
     * @var string
     */
    protected $module = 'Accounts';

    /**
     * @var User
     */
    protected $user;

    /**
     * @var TeamSet
     */
    protected $teamSet;

    /**
     * @var Team
     */
    protected $team;

    /**
     * @var SugarBean
     */
    protected $bean;

    /**
     * @var Lead Bean
     */
    protected $leadBean;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);
        $this->user = SugarTestUserUtilities::createAnonymousUser();

        $this->team = SugarTestTeamUtilities::createAnonymousTeam();
        $this->teamSet = BeanFactory::newBean('TeamSets');
        $this->teamSet->addTeams([$this->team->id]);

        $this->bean = $this->getMockBuilder('Account')
            ->setMethods(['loadVisibility'])
            ->disableOriginalConstructor()
            ->getMock();

        $beanVisibility = new BeanVisibility(
            BeanFactory::newBean($this->module),
            // The ACLVisibility is added in constructor.
            []
        );
        $this->bean->expects($this->any())->method('loadVisibility')->will(
            $this->returnValue($beanVisibility)
        );
        $this->bean->__construct();

        $this->team->add_user_to_team($this->user->id);

        SugarTestAccountUtilities::setCreatedAccount([$this->bean->id]);

        // Mocks Leads Bean
        $this->leadBean = SugarTestLeadUtilities::createLead();
    }

    protected function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        $this->teamSet->mark_deleted($this->teamSet->id);
        SugarTestTeamUtilities::removeAllCreatedAnonymousTeams();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    /**
     * Owner visibility should be applied via ACLVisibility.
     */
    public function testOwnerVisibilityList()
    {
        $aclData = [];
        $aclData['module']['list']['aclaccess'] = ACL_ALLOW_OWNER;
        ACLAction::setACLData($this->user->id, $this->module, $aclData);

        $this->assertFalse($this->isBeanAvailable());

        // Owner.
        $this->bean->assigned_user_id = $this->user->id;
        $this->bean->save();

        $this->assertTrue($this->isBeanAvailable());
    }

    /**
     * TBA visibility should be applied by ACLVisibility.
     */
    public function testTBAVisibilityList()
    {
        $aclData = [];
        $aclData['module']['list']['aclaccess'] = ACL_ALLOW_SELECTED_TEAMS;
        ACLAction::setACLData($this->user->id, $this->module, $aclData);

        $this->assertFalse($this->isBeanAvailable());

        // TeamBasedACL
        $this->bean->acl_team_set_id = $this->teamSet->id;
        $this->bean->save();

        $this->assertTrue($this->isBeanAvailable());
    }

    /**
     * Check possibility to receive the bean.
     * @return boolean
     */
    protected function isBeanAvailable()
    {
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $this->bean->disable_row_level_security = false;
        $record = $this->bean->retrieve();

        $GLOBALS['current_user'] = $oldCurrentUser;
        return $record ? true : false;
    }


    /**
     * Check table alias is applied to where query statement
     * for TBA visibility
     */
    public function testTableAliasForTBAVisibility()
    {
        $aclData = [];
        $oldCurrentUser = $GLOBALS['current_user'];
        $GLOBALS['current_user'] = $this->user;

        $aclData['module']['list']['aclaccess'] = ACL_ALLOW_SELECTED_TEAMS;
        ACLAction::setACLData($this->user->id, 'Leads', $aclData);

        // TeamBasedACL
        $this->leadBean->acl_team_set_id = $this->teamSet->id;
        $this->leadBean->save();

        $bv = new ACLVisibility($this->leadBean);
        $options = ['table_alias' => 'l1'];
        $query = '';
        $bv->setOptions($options)->addVisibilityWhere($query);

        $GLOBALS['current_user'] = $oldCurrentUser;

        $this->assertStringContainsString('l1.assigned_user_id', $query);
        $this->assertStringContainsString('l1.acl_team_set_id', $query);
    }
}
