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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SugarACLUsers
 */
class SugarACLUsersTest extends TestCase
{
    /**
     * @var array
     */
    private $sugarConfigBackup = [];

    private bool $userTabEdit;
    private bool $userPinnedEdit;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        global $sugar_config;
        $this->sugarConfigBackup = $sugar_config;

        $tabController = new TabController();
        $this->userTabEdit = $tabController->get_users_can_edit();
        $this->userPinnedEdit = $tabController->get_users_pinned_modules();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        global $sugar_config;
        $sugar_config = $this->sugarConfigBackup;

        $tabController = new TabController();
        $tabController->set_users_can_edit($this->userTabEdit);
        $tabController->set_users_pinned_modules($this->userPinnedEdit);

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testCheckAccess.
     *
     * @return array
     * @see SugarACLUsersTest::testCheckAccess
     */
    public static function checkAccessDataProvider()
    {
        return [
            // We can get it only from Users/Employees module
            ['Accounts', 'view', false, [], [], false],
            // Let the other modules decide about acl access
            ['Users', 'team_security', false, [], [], true],
            // Regular user in any way should have access to export functionality
            // if both disable_export & admin_export_only are not checked
            [
                'Users',
                'export',
                false,
                [],
                ['disable_export' => false, 'admin_export_only' => false],
                true,
            ],
            // Regular user should have access to export functionality in Employees module
            // if both disable_export & admin_export_only are not checked
            [
                'Employees',
                'export',
                false,
                [],
                ['disable_export' => false, 'admin_export_only' => false],
                true,
            ],
            // Regular user doesn't have access to export functionality if admin_export_only is true
            [
                'Employees',
                'export',
                false,
                [],
                ['disable_export' => false, 'admin_export_only' => true],
                false,
            ],
            // Admin user has access to export
            [
                'Users',
                'export',
                true,
                [],
                ['disable_export' => false, 'admin_export_only' => true],
                true,
            ],
            // Admin user doesn't have access to export if disable_export is true
            [
                'Users',
                'export',
                true,
                [],
                ['disable_export' => true, 'admin_export_only' => true],
                false,
            ],
            // This is another way to disable yourself
            ['Users', 'field', false, ['action' => 'edit', 'field' => 'status'], [], false],
            ['Users', 'field', false, ['action' => 'massupdate', 'field' => 'status'], [], false],
            ['Users', 'field', false, ['action' => 'delete', 'field' => 'status'], [], false],
        ];
    }

    /**
     * Test some common cases for check access method
     *
     * @dataProvider checkAccessDataProvider
     * @covers       SugarACLUsers::CheckAccess
     * @param string $module Module name
     * @param string $view View name
     * @param bool $isAdmin
     * @param array $context
     * @param array $config additional values
     * @param bool $expected Expected result
     */
    public function testCheckAccess($module, $view, $isAdmin, $context, $config, $expected)
    {
        SugarTestHelper::setUp('current_user', [true, $isAdmin]);
        global $current_user;
        $context['bean'] = $current_user;

        // Set config parameters
        global $sugar_config;
        foreach ($config as $key => $value) {
            $sugar_config[$key] = $value;
        }

        /** @var SugarACLUsers|MockObject $acl */
        $acl = $this->createPartialMock('SugarACLUsers', ['doesSystemHaveOtherActiveAdmins']);
        $acl->expects($this->any())->method('doesSystemHaveOtherActiveAdmins')->willReturn(true);

        $result = $acl->checkAccess($module, $view, $context);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testAccessToDelete.
     *
     * @return array
     * @see SugarACLUsersTest::testAccessToDelete
     */
    public static function accessToDeleteDataProvider()
    {
        return [
            // You can not delete yourself even if you are admin and system doesn't have other active admins
            [true, true, false, false],
            // You can delete yourself even if you admin and system has other active admins
            [true, true, true, true],
            // You can delete somebody if you admin
            [true, false, true, true],
            // You can not delete yourself (regular user)
            [false, true, true, false],
            // You can not delete somebody (regular user)
            [false, false, true, false],
        ];
    }

    /**
     * Check specific cases related to delete action
     *
     * @dataProvider accessToDeleteDataProvider
     * @covers       SugarACLUsers::CheckAccess
     * @param bool $isAdmin
     * @param bool $isMyself Expected result for myselfCheck method
     * @param bool $anotherAdmins Does system have another active admins?
     * @param bool $expected Expected result
     */
    public function testAccessToDelete($isAdmin, $isMyself, $anotherAdmins, $expected)
    {
        SugarTestHelper::setup('current_user', [true, $isAdmin]);
        global $current_user;

        /** @var SugarACLUsers|MockObject $acl */
        $acl = $this->createPartialMock('SugarACLUsers', ['myselfCheck', 'doesSystemHaveOtherActiveAdmins']);
        $acl->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));
        $acl->expects($this->any())->method('doesSystemHaveOtherActiveAdmins')->willReturn($anotherAdmins);

        // You can not delete yourself even if you admin
        $result = $acl->checkAccess('Users', 'delete', ['bean' => $current_user]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testAccessToEditYourself.
     *
     * @return array
     * @see SugarACLUsersTest::testAccessToEditYourself
     */
    public static function accessToEditDataProvider()
    {
        return [
            // You can edit youself
            [false, true, true],
            // You can not edit somebody if you are not admin
            [false, false, false],
            // You can edit user profile if you are admin
            [true, false, true],
            // You can edit user profile if you are admin
            [true, true, true],
        ];
    }

    /**
     * Check specific cases related to edit action
     *
     * @dataProvider accessToEditDataProvider
     * @covers       SugarACLUsers::CheckAccess
     * @param bool $isAdmin
     * @param bool $isMyself Expected result for myselfCheck method
     * @param bool $expected Expected result
     */
    public function testAccessToEditYourself($isAdmin, $isMyself, $expected)
    {
        SugarTestHelper::setup('current_user', [true, $isAdmin]);
        global $current_user;

        /** @var SugarACLUsers|MockObject $acl */
        $acl = $this->createPartialMock('SugarACLUsers', ['myselfCheck', 'doesSystemHaveOtherActiveAdmins']);
        $acl->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));
        $acl->expects($this->any())->method('doesSystemHaveOtherActiveAdmins')->willReturn(true);

        $result = $acl->checkAccess('Users', 'edit', ['bean' => $current_user]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test functionality of comparing some user with current signed in user
     * @covers SugarACLUsers::myselfCheck
     */
    public function testMyselfCheck()
    {
        $current_user = SugarTestHelper::setup('current_user', [true, false]);

        $acl = new SugarACLUsers();

        /** @var User|MockObject $bean */
        $bean = $this->createMock(User::class);

        // Expected result - false if bean id and current_user id are not equal
        $this->assertFalse($acl->myselfCheck($bean, $current_user));

        // Expected result - true if bean id and current_user id are equal
        $bean->id = $current_user->id;
        $this->assertTrue($acl->myselfCheck($bean, $current_user));
    }

    /**
     * Data provider for testCheckFieldList.
     *
     * @covers SugarACLUsersTest::testCheckFieldList
     * @return array
     */
    public static function checkFieldListDataProvider()
    {
        return [
            // no_access_fields && no_edit_fields have already been checked in testAccessToFields
            [false, 'Users', ['status', 'employee_status'], 'edit', [], true, [false, false]],
            [false, 'Users', ['status', 'employee_status'], 'massupdate', [], true, [false, false]],
            [false, 'User', ['status', 'employee_status'], 'delete', [], true, [false, false]],
            [true, 'Users', ['user_hash', 'password'], 'field', [], false, [true, true]],
            [false, 'Users', ['user_hash', 'password'], 'field', [], false, [false, false]],
        ];
    }

    /**
     * Test field list check
     *
     * @dataProvider checkFieldListDataProvider
     * @covers       SugarACLUsers::checkFieldList
     * @param bool $isAdmin
     * @param string $module
     * @param array $field_list
     * @param string $action
     * @param array $context
     * @param bool $isMyself
     * */
    public function testCheckFieldList($isAdmin, $module, $field_list, $action, $context, $isMyself, $expected)
    {
        SugarTestHelper::setup('current_user', [true, $isAdmin]);

        /** @var SugarACLUsers|MockObject $acl_class */
        $acl_class = $this->createPartialMock('SugarACLUsers', ['myselfCheck']);
        $acl_class->expects($this->once())
            ->method('myselfCheck')
            ->will($this->returnValue($isMyself));

        // You can not delete yourself even if you admin
        $result = $acl_class->checkFieldList($module, $field_list, $action, $context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers ::getFieldListAccess
     * @dataProvider providerTestGetFieldListAccessPreferenceFields
     * @throws Exception
     */
    public function testGetFieldListAccessPreferenceFields($tabEditAccess, $pinEditAccess)
    {
        SugarTestHelper::setup('current_user');
        global $current_user;

        // Temporarily make user admin so we can set admin settings
        $current_user->is_admin = true;
        $tabController = new TabController();
        $tabController->set_users_can_edit($tabEditAccess);
        $tabController->set_users_pinned_modules($pinEditAccess);
        $current_user->is_admin = false;

        $mockAcl = $this->getMockBuilder(SugarACLUsers::class)
            ->onlyMethods([])
            ->getMock();
        $fieldList = [
            'user_tabs' => 'user_tabs',
            'number_pinned_modules' => 'number_pinned_modules',
        ];

        $results = $mockAcl->getFieldListAccess('Users', $fieldList, []);
        $this->assertEquals([
            'user_tabs' => $tabEditAccess ? SugarACL::ACL_READ_WRITE : SugarACL::ACL_READ_ONLY,
            'number_pinned_modules' => $pinEditAccess ? SugarACL::ACL_READ_WRITE : SugarACL::ACL_READ_ONLY,
        ], $results);
    }

    /**
     * Provider for testGetFieldListAccessPreferenceFields
     * @return array
     */
    public function providerTestGetFieldListAccessPreferenceFields()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }
}
