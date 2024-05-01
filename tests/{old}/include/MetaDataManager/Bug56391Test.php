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
 * Bug 56391 - ACL's used in the MetadataManager were the static ones.  Have switched to use the SugarACL methods
 */
class Bug56391Test extends TestCase
{
    /**
     * @var mixed[]|mixed|array<string, string>
     */
    public $accounts;
    /**
     * @var mixed[]
     */
    public $roles;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        $this->accounts = [];
        SugarACL::resetACLs();
        SugarTestHelper::setUp('ACLStatic');

        // Create one more active admin
        SugarTestUserUtilities::createAnonymousUser(true, 1);
    }

    protected function tearDown(): void
    {
        foreach ($this->accounts as $account_id) {
            $GLOBALS['db']->query("DELETE FROM accounts WHERE id = '{$account_id}'");
        }
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * Test Users Module
     *
     * @group Bug56391
     */
    public function testUsersModule()
    {
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access
        $expected_result = [
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        ];
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);
    }


    /**
     * Test Users Module Fields
     *
     * @group Bug56391
     */
    public function testUsersModuleFields()
    {
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access
        $expected_result = [
            'user_name' => ['write' => 'no', 'create' => 'no'],
            'user_hash' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'system_generated_password' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'pwd_last_changed' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'authenticate_id' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'sugar_login' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'external_auth_only' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'status' => ['write' => 'no', 'create' => 'no'],
            'show_on_employees' => ['read' => 'no', 'write' => 'no', 'create' => 'no'],
            'portal_only' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'employee_status' => ['write' => 'no', 'create' => 'no'],
            'is_group' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'title' => ['write' => 'no', 'create' => 'no',],
            'department' => ['write' => 'no', 'create' => 'no',],
            'reports_to_id' => ['write' => 'no', 'create' => 'no',],
            'reports_to_name' => ['write' => 'no', 'create' => 'no',],
            'reports_to_link' => ['write' => 'no', 'create' => 'no',],
            'is_admin' => ['write' => 'no', 'create' => 'no',],
            'last_login' => ['read' => 'no', 'write' => 'no', 'create' => 'no',],
            'license_type' => ['write' => 'no', 'create' => 'no'],
        ];
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        $acls = $acls['fields'];

        $this->assertEquals($expected_result, $acls);
    }

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModule()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        $mm = MetaDataManager::getManager();
        // because the user is not an admin the user should only have view and list access

        $expected_result = [];
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_result, $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Users Module as Admin
     *
     * @group Bug56391
     */
    public function testUsersAsAdminModuleForSelf()
    {
        // set current user as an admin
        $GLOBALS['current_user']->is_admin = 1;
        $GLOBALS['current_user']->save();
        $mm = MetaDataManager::getManager();

        // current user can do all action on self because system has another active admins
        $acls = $mm->getAclForModule('Users', $GLOBALS['current_user'], $GLOBALS['current_user']);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals([], $acls);

        // remove admin
        $GLOBALS['current_user']->is_admin = 0;
        $GLOBALS['current_user']->save();
    }

    /**
     * Test Module Access
     *
     * Set 5 modules to have specific actions and verify them
     *
     * @group Bug56391
     */
    public function testModuleAccess()
    {
        $modules = ['Accounts', 'Contacts', 'Contracts', 'Opportunities', 'Leads'];
        // user can view, list, delete, and export
        $expected_result = [
            'admin' => 'no',
            'developer' => 'no',
            'create' => 'no',
            'edit' => 'no',
            'delete' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        ];

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, ['access', 'view', 'list', 'export']);
        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();
        foreach ($modules as $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            // not checking fields right now
            unset($acls['fields']);
            $this->assertEquals($expected_result, $acls, "failed at module:$module");
        }
    }


    /**
     * Test Field Access
     *
     * Set a field on accounts to be not readable, writeable, or editable
     *
     * @group Bug56391
     */
    public function testFieldAccess()
    {
        $modules = ['Accounts'];
        // user can view, list, delete, and export
        $expected_result = [
            'fields' => [
                'website' => [
                    'read' => 'no',
                    'write' => 'no',
                    'create' => 'no',
                ],
                'dri_workflow_template_id' => [
                    'write' => 'no',
                    'create' => 'no',
                    'license' => 'no',
                ],
                'dri_workflow_template_name' => [
                    'write' => 'no',
                    'create' => 'no',
                    'license' => 'no',
                ],
            ],
            'admin' => 'no',
            'developer' => 'no',
            'delete' => 'no',
        ];

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, [
            'access', 'create', 'view', 'list', 'edit', 'import', 'export', 'massupdate',
        ]);

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'website', -99);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();
        foreach ($modules as $module) {
            $acls = $mm->getAclForModule($module, $GLOBALS['current_user']);
            unset($acls['_hash']);
            $this->assertEquals($expected_result, $acls);
        }
    }

    /**
     * Test Owner Access
     *
     * Test if Edit = Owner that we can not edit a bean that is not owned by the current user.
     *
     * @group Bug56391
     */
    public function testModuleOwnerAccess()
    {
        $expected_bean_result = [];
        $modules = ['Accounts'];


        $expected_bean_result['access'] = [
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        ];

        $this->roles[] = $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, [
            'access', 'view', 'list', 'edit', 'delete', 'export',
        ], ['edit', 'delete']);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    /**
     * Test Owner Create Access
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateAccess()
    {
        $expected_bean_result = [];
        $modules = ['Accounts'];


        $expected_bean_result['access'] = [
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        ];


        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, [
            'access', 'view', 'list', 'edit', 'delete', 'export',
        ], ['create', 'edit']);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::newBean('Accounts'));
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    /**
     * Test Owner Create Access 2
     *
     * Test if Create = Owner that we can create a bean.
     *
     * @group Bug56391
     */
    public function testModuleOwnerCreateNewWithIdAccess()
    {
        $expected_bean_result = [];
        $modules = ['Accounts'];


        $expected_bean_result['access'] = [
            'admin' => 'no',
            'developer' => 'no',
            'import' => 'no',
            'massupdate' => 'no',
        ];


        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, ['access', 'view', 'list', 'edit', 'delete', 'export'], ['create', 'edit']);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $account = BeanFactory::newBean('Accounts');
        $account->new_with_id = true;
        $account->id = create_guid();
        $account->name = 'Tis Awesome';

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], $account);
        unset($acls['_hash']);
        // not checking fields right now
        unset($acls['fields']);

        $this->assertEquals($expected_bean_result['access'], $acls, 'Access Failed');
    }

    public function testModuleFieldOwnerAccess()
    {
        $modules = ['Accounts'];

        $expected_bean_result = [
            'field_access' => [
                'dri_workflow_template_id' => [
                    'create' => 'no',
                    'write' => 'no',
                    'license' => 'no',
                ],
                'dri_workflow_template_name' => [
                    'create' => 'no',
                    'write' => 'no',
                    'license' => 'no',
                ],
            ],
        ];

        $account = BeanFactory::newBean('Accounts');
        $account->name = 'Unit Test ' . create_guid();
        $account->assigned_user_id = $GLOBALS['current_user']->id;
        $account->save();
        $this->accounts['access'] = $account->id;

        unset($account);

        $role = SugarTestACLUtilities::createRole('UNIT TEST ' . create_guid(), $modules, [
            'access', 'view', 'list', 'edit', 'delete', 'export',
        ], ['edit']);

        SugarTestACLUtilities::createField($role->id, 'Accounts', 'name', ACL_READ_OWNER_WRITE);

        SugarTestACLUtilities::setupUser($role);
        SugarTestHelper::clearACLCache();

        $mm = MetaDataManager::getManager();

        $acls = $mm->getAclForModule('Accounts', $GLOBALS['current_user'], BeanFactory::getBean('Accounts', $this->accounts['access']));
        unset($acls['_hash']);

        $fields = $acls['fields'];
        unset($acls['fields']);
        $this->assertEquals($expected_bean_result['field_access'], $fields, 'Field Access Failed');
    }
}
