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
 * @group ApiTests
 */
class SugarBeanApiHelperTest extends TestCase
{
    public $bean;
    public $beanApiHelper;

    public $oldDate;
    public $oldTime;

    public $roles = [];
    public $serviceMock;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        // Mocking out SugarBean to avoid having to deal with any dependencies other than those that we need for this test
        $mock = $this->createMock('SugarBean');
        $mock->expects($this->any())
            ->method('ACLFieldAccess')
            ->will($this->returnValue(true));
        $mock->expects($this->any())
            ->method('ACLAccess')
            ->will($this->returnValue(true));
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->module_name = 'Test';
        $mock->module_dir = 'Test';
        $mock->field_defs = [
            'testInt' => [
                'type' => 'int',
            ],
            'testDecimal' => [
                'type' => 'decimal',
            ],
            'testBool' => [
                'type' => 'bool',
            ],
        ];
        $this->bean = $mock;
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
        $this->beanApiHelper = $this->getApiHelperMock();
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['sugar_config']['exclude_notifications'][$this->bean->module_dir])) {
            unset($GLOBALS['sugar_config']['exclude_notifications'][$this->bean->module_dir]);
        }
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    protected function getApiHelperMock($platformNotifications = true)
    {
        $helperMock = $this->getMockBuilder(SugarBeanApiHelper::class)
            ->setConstructorArgs([$this->serviceMock])
            ->onlyMethods(['platformNotificationsEnabled'])
            ->getMock();
        $helperMock->method('platformNotificationsEnabled')->will($this->returnValue($platformNotifications));
        return $helperMock;
    }

    /**
     * @dataProvider providerFunction
     */
    public function testFormatForApi($fieldName, $fieldValue, $expectedFormattedValue, $message)
    {
        $this->bean->$fieldName = $fieldValue;

        $data = $this->beanApiHelper->formatForApi($this->bean);

        $this->assertArrayHasKey($fieldName, $data, $message);
        $this->assertSame($expectedFormattedValue, $data[$fieldName], $message);
    }

    public function providerFunction()
    {
        return [
            ['testInt', '', null, 'Bug 57507 regression: expected formatted value for a null int type to be NULL'],
            ['testDecimal', '', null, 'Bug 59692 regression: expected formatted value for a null decimal type to be NULL'],
            ['testInt', '1', 1, "Int type conversion of '1' failed"],
            ['testDecimal', '1', 1.0, "Decimal type conversion of '1' failed"],
            ['testInt', 1.0, 1, 'Int type conversion of 1.0 failed'],
            ['testDecimal', 1, 1.0, 'Decimal type conversion of 1 failed'],
            ['testInt', '0', 0, "Int type conversion of '0' failed"],
            ['testDecimal', '0', 0.0, "Decimal type conversion of '0' failed"],
            ['testInt', 0.0, 0, 'Int type conversion of 0.0 failed'],
            ['testDecimal', 0, 0.0, 'Decimal type conversion of 0 failed'],
            ['testBool', 1, true, '1 should be true'],
            ['testBool', 0, false, '0 should be false'],
            ['testBool', true, true, 'true should be true'],
            ['testBool', false, false, 'false should be false'],
            ['testBool', 'true', true, 'true string should be true'],
            ['testBool', 'false', false, 'false string should be false'],
        ];
    }

    public function testFormatForApiDeleted()
    {
        $bean = BeanFactory::newBean('Accounts');

        $bean->deleted = 1;
        $bean->name = 'Mr. Toad';
        $bean->assigned_user_id = 'seed_toad_id';

        $data = $this->beanApiHelper->formatForApi($bean, ['name', 'deleted']);

        $this->assertArrayNotHasKey('name', $data, 'Did not strip name from a deleted record');
        $this->assertArrayNotHasKey('assigned_user_id', $data, "Did not strip assigned_user_id from a deleted record when we didn't request it");
        $this->assertArrayHasKey('deleted', $data, 'Did not add the deleted flag to a deleted record');


        $this->serviceMock->user->is_admin = true;
        $data = $this->beanApiHelper->formatForApi($bean, ['name', 'deleted', 'assigned_user_id']);

        $this->assertArrayNotHasKey('name', $data, 'Did not strip name from a deleted record');
        $this->assertArrayHasKey('assigned_user_id', $data, 'Did not fill in assigned_user_id from a deleted record when we did request it');
        $this->assertArrayHasKey('deleted', $data, 'Did not add the deleted flag to a deleted record');

        $this->serviceMock->user->is_admin = false;
        $data = $this->beanApiHelper->formatForApi($bean, ['name', 'deleted', 'assigned_user_id']);

        $this->assertArrayNotHasKey('name', $data, 'Did not strip name from a deleted record');
        $this->assertArrayNotHasKey('assigned_user_id', $data, 'Did not strip the assigned_user_id from a deleted record when requested by a non-admin');
        $this->assertArrayHasKey('deleted', $data, 'Did not add the deleted flag to a deleted record');
    }

    public function testJsonFieldSave()
    {
        $userPrefs = BeanFactory::newBean('UserPreferences');
        $userPrefs->field_defs['contents']['custom_type'] = 'json';

        $submittedData = [
            'contents' => ['abcd' => '1234', 'cdef' => 5678],
        ];

        $this->beanApiHelper->populateFromApi($userPrefs, $submittedData);

        $this->assertEquals($userPrefs->contents, json_encode($submittedData['contents']));
    }

    public function testListWithOwnerAccess()
    {
        // create role that is all fields read only
        $role = SugarTestACLUtilities::createRole('SUGARBEANAPIHELPER - UNIT TEST ' . create_guid(), ['Meetings'], ['access', 'list', 'view'], ['view']);

        SugarTestACLUtilities::setupUser($role);

        // create a meeting not owned by current user
        $meeting = BeanFactory::newBean('Meetings');
        $meeting->name = 'SugarBeanApiHelperTest Meeting';
        $meeting->assigned_user_id = '1';
        $meeting->id = create_guid();

        // verify I can format the bean for the api and I can see the name and id;
        $data = $this->beanApiHelper->formatForApi($meeting);
        $this->assertEquals($meeting->id, $data['id'], "ID Doesn't Match");
    }

    public function testListCertainFieldsNoAccess()
    {
        // create role that is all fields read only
        $this->roles[] = $role = SugarTestACLUtilities::createRole('SUGARBEANAPIHELPER - UNIT TEST ' . create_guid(), ['Accounts'], ['access', 'list', 'view'], ['view']);

        if (!($GLOBALS['current_user']->check_role_membership($role->name))) {
            $GLOBALS['current_user']->load_relationship('aclroles');
            $GLOBALS['current_user']->aclroles->add($role);
            $GLOBALS['current_user']->save();
        }

        $id = $GLOBALS['current_user']->id;
        $GLOBALS['current_user'] = BeanFactory::getBean('Users', $id);

        // set the name field as Read Only
        ACLField::setAccessControl('Accounts', $role->id, 'website', -99);

        unset($_SESSION['ACL']);

        ACLField::loadUserFields('Accounts', 'Account', $GLOBALS['current_user']->id, true);

        // create a meeting not owned by current user
        $account = BeanFactory::newBean('Accounts');
        $account->name = 'SugarBeanApiHelperTest Meeting';
        $account->assigned_user_id = '1';
        $account->id = create_guid();

        $data = $this->beanApiHelper->formatForApi($account, ['id', 'name', 'website'], ['action' => 'view']);

        $this->assertNotEmpty($data['id'], 'no id was passed back');
    }

    public function updateFieldOwnerReadOwnerWrite()
    {
        $data = [];
        // set the test field as owner read owner write directly in the session
        $_SESSION['ACL'][$GLOBALS['current_user']->id]['Test']['fields']['testInt'] = 40;
        $data['testInt'] = 4;
        $data['assigned_user_id'] = 'not_me';
        $this->beanApiHelper->populateFromApi($this->bean, $data);
        $this->assertEquals($this->bean->testInt, 4);
        $this->assertEquals($this->bean->assigned_user_id, 'not_me');
    }

    protected function createRole($name, $allowedModules, $allowedActions, $ownerActions = [])
    {
        $role = new ACLRole();
        $role->name = $name;
        $role->description = $name;
        $role->save();
        $GLOBALS['db']->commit();

        $roleActions = $role->getRoleActions($role->id);

        foreach ($roleActions as $moduleName => $actions) {
            // enable allowed modules
            if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
            } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
            } else {
                foreach ($actions as $action => $actionName) {
                    if (isset($actions[$action]['access']['id'])) {
                        $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                    }
                }
            }

            if (in_array($moduleName, $allowedModules)) {
                foreach ($actions['module'] as $actionName => $action) {
                    if (in_array($actionName, $ownerActions)) {
                        $aclAllow = ACL_ALLOW_OWNER;
                    } elseif (in_array($actionName, $allowedActions)) {
                        $aclAllow = ACL_ALLOW_ALL;
                    } else {
                        $aclAllow = ACL_ALLOW_NONE;
                    }
                    $role->setAction($role->id, $action['id'], $aclAllow);
                }
            }
        }

        return $role;
    }

    public function testCheckNotify()
    {
        $GLOBALS['sugar_config']['exclude_notifications'][$this->bean->module_dir] = true;
        $this->assertEquals(false, $this->beanApiHelper->checkNotify($this->bean), 'Should not check_notify if exclude_notifications == true');

        $GLOBALS['sugar_config']['exclude_notifications'][$this->bean->module_dir] = false;
        $this->bean->assigned_user_id = 'user1';
        $this->bean->fetched_row['assigned_user_id'] = 'user2';
        $this->assertEquals(true, $this->beanApiHelper->checkNotify($this->bean), 'Should check_notify for new assigned user');

        $this->bean->fetched_row['assigned_user_id'] = 'user1';
        $this->assertEquals(false, $this->beanApiHelper->checkNotify($this->bean), "Should not check_notify if assigned user doesn't change");

        $GLOBALS['sugar_config']['exclude_notifications'][$this->bean->module_dir] = false;
        $this->bean->assigned_user_id = 'user1';
        $this->bean->fetched_row['assigned_user_id'] = 'user2';
        $this->beanApiHelper = $this->getApiHelperMock(false);
        $this->assertEquals(false, $this->beanApiHelper->checkNotify($this->bean), 'Should not notify if platform is disabled');
    }
}
