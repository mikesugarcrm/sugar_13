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
use Sugarcrm\Sugarcrm\ACL\Cache as AclCacheInterface;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class ACLActionsTest extends TestCase
{
    protected $roles = [];

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        $this->roles[0] = new ACLRole();
        $this->roles[0]->name = 'role0';
        $this->roles[0]->save();

        $this->roles[1] = new ACLRole();
        $this->roles[1]->name = 'role1';
        $this->roles[1]->save();

        $this->roles[2] = new ACLRole();
        $this->roles[2]->name = 'role2';
        $this->roles[2]->save();

        $aclActions = $this->roles[0]->getRoleActions($this->roles[0]->id);
        $this->roles[0]->setAction($this->roles[0]->id, $aclActions['Accounts']['module']['edit']['id'], ACL_ALLOW_NONE);

        $aclActions = $this->roles[1]->getRoleActions($this->roles[1]->id);
        $this->roles[1]->setAction($this->roles[1]->id, $aclActions['Accounts']['module']['edit']['id'], ACL_ALLOW_DEFAULT);

        $aclActions = $this->roles[2]->getRoleActions($this->roles[2]->id);
        $this->roles[2]->setAction($this->roles[2]->id, $aclActions['Accounts']['module']['edit']['id'], ACL_FIELD_DEFAULT);

        $this->roles[0]->load_relationship('users');
        $this->roles[1]->load_relationship('users');
        $this->roles[2]->load_relationship('users');
        $this->roles[0]->users->add($GLOBALS['current_user']);
        $this->roles[1]->users->add($GLOBALS['current_user']);
        $this->roles[2]->users->add($GLOBALS['current_user']);
        ACLActionExtended::unsetACLs();
    }

    protected function tearDown(): void
    {
        $GLOBALS['db']->query("delete from acl_roles_users where role_id IN ('{$this->roles[0]->id}', '{$this->roles[1]->id}', '{$this->roles[2]->id}')");
        $GLOBALS['db']->query("delete from acl_roles_actions where role_id IN ('{$this->roles[0]->id}', '{$this->roles[1]->id}', '{$this->roles[2]->id}')");
        $GLOBALS['db']->query("delete from acl_roles where id IN ('{$this->roles[0]->id}', '{$this->roles[1]->id}', '{$this->roles[2]->id}')");
        ACLActionExtended::unsetACLs();
        SugarTestHelper::tearDown();
    }

    public function testGetUserActions()
    {
        Container::getInstance()->get(AclCacheInterface::class)->clearAll();
        $actions = ACLAction::getUserActions($GLOBALS['current_user']->id, true);
        $this->assertEquals(ACL_ALLOW_NONE, $actions['Accounts']['module']['edit']['aclaccess'], 'aclaccess should be: ' . ACL_ALLOW_NONE);
        $this->assertEquals(false, $actions['Accounts']['module']['edit']['isDefault'], 'aclaccess should be overridden.');
    }

    /**
     * @dataProvider providerActions
     * @depends      testGetUserActions
     */
    public function testKeepMostRestrictiveActions($actions, $expected)
    {
        Container::getInstance()->get(AclCacheInterface::class)->clearAll();
        $restrictive = SugarTestReflection::callProtectedMethod('ACLAction', 'keepMostRestrictiveActions', [$actions]);
        $this->assertEquals($expected, $restrictive['same action other role']['access_override'], 'aclaccess should be: ' . $expected);
    }

    public function providerActions()
    {
        return [
            [
                [
                    [
                        'user_id' => 'least restrictive',
                        'action_id' => 'same action other role',
                        'access_override' => 99,
                    ],
                    [
                        'user_id' => 'less restrictive',
                        'action_id' => 'same action other role',
                        'access_override' => 1,
                    ],
                    [
                        'user_id' => 'most restrictive',
                        'action_id' => 'same action other role',
                        'access_override' => -99,
                    ],
                ],
                -99,
            ],
        ];
    }

    public function testGetUserActionsExtended()
    {
        $_ = ACLAction::getUserActions($GLOBALS['current_user']->id, true, 'Contacts');
        $actions = ACLAction::getUserActions($GLOBALS['current_user']->id);
        $this->assertEquals(ACL_ALLOW_NONE, $actions['Accounts']['module']['edit']['aclaccess'], 'aclaccess should be: ' . ACL_ALLOW_NONE);
        $this->assertEquals(false, $actions['Accounts']['module']['edit']['isDefault'], 'aclaccess should be overridden.');
    }
}

class ACLActionExtended extends ACLAction
{
    public static function unsetACLs()
    {
        parent::$acls = [];
    }
}
