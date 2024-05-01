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

class Bug46448AutoFillTest extends TestCase
{
    private $user;
    private $aclRolesIds = [];
    private $aclRoles2Users = [];

    public function testAutoFill()
    {
        $Account = new Account();
        populateFromPost('', $Account);
        $this->assertEquals($Account->assigned_user_id, $this->user->id);
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        require_once 'include/formbase.php';
        SugarTestHelper::setUp('current_user', [true]);
        $user = $GLOBALS['current_user'];
        $this->user = $user;

        $aclFields = [
            ['module' => 'Accounts', 'name' => 'assigned_user_name', 'access' => ACL_READ_ONLY],
            ['module' => 'Accounts', 'name' => 'team_name', 'access' => ACL_READ_ONLY],
        ];
        $role = $this->createAclRole($aclFields);
        $this->connectAclRoles2Users($role, $user);
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $this->removeAllCreatedAclRoles();
        $this->removeAllConnectAclRoles2Users();
    }

    private function createAclRole($fields = [])
    {
        $AclRole = new ACLRole();

        $time = random_int(0, mt_getrandmax());
        $roleId = 'SugarACLRole';

        $AclRole->name = $roleId . $time;
        $AclRole->description = $roleId . $time;
        $AclRole->modified_user_id = '1';
        $AclRole->created_by = 1;
        $AclRole->date_entered = $time;
        $AclRole->date_modified = $time;
        $AclRole->save();

        $this->aclRolesIds[] = $AclRole->id;

        foreach ($fields as $fld) {
            ACLField::setAccessControl($fld['module'], $AclRole->id, $fld['name'], $fld['access']);
        }

        return $AclRole;
    }

    private function removeAllCreatedAclRoles()
    {
        if (is_array($this->aclRolesIds) && count($this->aclRolesIds)) {
            $AclRole = new ACLRole();
            $qr = 'DELETE FROM ' . $AclRole->table_name
                . ' WHERE id IN (\'' . implode("', '", $this->aclRolesIds) . '\')';
            $GLOBALS['db']->query($qr);

            $ACLField = new ACLField();
            $qr = 'DELETE FROM ' . $ACLField->table_name
                . ' WHERE role_id IN (\'' . implode("', '", $this->aclRolesIds) . '\')';
            $GLOBALS['db']->query($qr);
        }
    }

    private function connectAclRoles2Users($AclRole, $User = null)
    {
        $userId = null;
        if (is_null($User)) {
            $userId = $GLOBALS['current_user'];
        } elseif ($User instanceof User) {
            $userId = $User->id;
        } elseif (is_scalar($User)) {
            $userId = $User;
        } else {
            throw new Exception('Unsupported User');
        }

        $aclRoleId = null;
        if ($AclRole instanceof ACLRole) {
            $aclRoleId = $AclRole->id;
        } elseif (is_scalar($AclRole)) {
            $aclRoleId = $User;
        } else {
            throw new Exception('Unsupported AclRole');
        }

        $id = create_guid();
        $insQR = "INSERT into acl_roles_users(id,user_id,role_id, date_modified) values('" . $id . "','" . $userId . "','" . $aclRoleId . "', " . $GLOBALS['db']->convert("'" . $GLOBALS['timedate']->nowDb() . "'", 'datetime') . ')';
        $GLOBALS['db']->query($insQR);
        $this->aclRoles2Users[] = $id;

        return $id;
    }

    private function removeAllConnectAclRoles2Users()
    {
        if (is_array($this->aclRoles2Users) && count($this->aclRoles2Users)) {
            $qr = 'DELETE FROM acl_roles_users WHERE id IN (\'' . implode("', '", $this->aclRoles2Users) . '\')';
            $GLOBALS['db']->query($qr);
            // var_dump($qr);
        }
    }
}
