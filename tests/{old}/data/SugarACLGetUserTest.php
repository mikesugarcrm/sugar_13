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
 * Test class for SugarACL getUserActions
 */
class SugarACLGetUserTest extends TestCase
{
    protected $bean;

    protected function setUp(): void
    {
        SugarACL::$acls = [];
        if (!$this->bean) {
            $this->bean = $this->getTestMock();
        }
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('ACLStatic');
        $GLOBALS['beanList']['test'] = 'test';
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        SugarACL::$acls = [];
        unset($GLOBALS['dictionary'][$this->bean->object_name]);
    }

    public function getTestMock()
    {
        $bean = $this->getMockBuilder('MockSugarBeanACLGU')->disableOriginalConstructor()->getMock();
        $bean->model_name = 'test';
        $bean->object_name = 'test';
        $bean->module_dir = 'test';
        $bean->expects($this->any())->method('bean_implements')->will($this->returnValue(true));

        return $bean;
    }

    public function modulesAccess()
    {
        return [
            ['Users', ['access' => true, 'view' => true, 'import' => false]],
            ['Accounts', ['access' => true, 'view' => true, 'import' => true, 'massupdate' => true]],
            ['test', ['access' => true, 'view' => true, 'import' => true]],
        ];
    }

    /**
     * @dataProvider modulesAccess
     *
     * @param string $module
     * @param array $expected
     */
    public function testGetAccess($module, $expected)
    {
        $access = SugarACL::getUserAccess($module);
        foreach ($expected as $action => $expvalue) {
            $this->assertEquals($access[$action], $expvalue, "Action $action for module $module should be: " . var_export($expvalue, true));
        }
    }

    public function testAccessDenied()
    {
        $acldata = [];
        // override module access
        $acldata['module']['access']['aclaccess'] = ACL_ALLOW_DISABLED;
        ACLAction::setACLData($GLOBALS['current_user']->id, 'Accounts', $acldata);
        $access = SugarACL::getUserAccess('Accounts');
        foreach (SugarACL::$all_access as $action => $value) {
            $this->assertFalse($access[$action], "Action $action should be set to false");
        }
    }

    public function testReadOnlyACL()
    {
        SugarACL::loadACLs('test');
        SugarACL::$acls['test'][] = new TestACLReadOnly();
        $access = SugarACL::getUserAccess('test');
        $this->assertFalse($access['edit']);
    }
}

class TestACLReadOnly extends SugarACLStrategy
{
    public function checkAccess($module, $view, $context)
    {
        return $view != 'edit';
    }
}

class MockSugarBeanACLGU extends SugarBean
{
    // do not let the mock kill defaultACLs function
    final public function defaultACLs()
    {
        return parent::defaultACLs();
    }
}
