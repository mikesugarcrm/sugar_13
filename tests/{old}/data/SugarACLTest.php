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
 * Test class for SugarACL.
 */
class SugarACLTest extends TestCase
{
    protected $bean;

    /**
     * @covers SugarACL::loadACLs
     */
    public function aclProvider()
    {
        return [
            [1, ['SugarACLStatic'], ['SugarACLStatic' => true]], //ACL
            [0, [], ['SugarACLStatic' => false]],
            [0, [], []], //nothing
        ];
    }

    protected function setUp(): void
    {
        SugarACL::resetACLs();
        if (!$this->bean) {
            $this->bean = $this->getTestMock();
        }
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('ACLStatic');
        SugarTestHelper::setUp('current_user');
        $GLOBALS['beanList']['test'] = 'test';
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
        $GLOBALS['dictionary'][$this->bean->object_name]['acls'] = [];
        SugarACL::resetACLs();
    }

    public function getTestMock()
    {
        $bean = $this->getMockBuilder('MockSugarBeanACL')->disableOriginalConstructor()->getMock();
        $bean->model_name = 'test';
        $bean->object_name = 'test';
        $bean->module_dir = 'test';
        $bean->expects($this->any())->method('bean_implements')->will($this->returnValue(true));

        return $bean;
    }


    /**
     * @param $count
     * @param $classes
     * @param $config
     * @dataProvider aclProvider
     */
    public function testLoadACLs($count, $classes, $config)
    {
        $GLOBALS['dictionary'][$this->bean->object_name]['acls'] = $config;
        SugarACL::resetACLs();
        $acls = SugarACL::loadACLs($this->bean->object_name, ['bean' => $this->bean]);

        $this->assertEquals($count, count($acls));

        sort($acls);
        sort($classes);
        foreach ($classes as $key => $class) {
            $this->assertInstanceOf($class, $acls[$key]);
        }
    }

    /**
     * @covers SugarACL::moduleSupportsACL
     */
    public function testModuleSupportsACL()
    {
        SugarACL::$acls = ['test' => true];
        $this->assertTrue(SugarACL::moduleSupportsACL('test'));
    }

    /**
     * @covers SugarACL::checkAccess
     */
    public function testCheckAccess()
    {
        $acl1 = $this->createMock('SugarACLStatic');
        $acl1->expects($this->exactly(3))->method('checkAccess')->with('test', 'test2')->will($this->returnValue(false));
        SugarACL::$acls['test'] = [$acl1];

        $this->assertFalse(SugarACL::checkAccess('test', 'test2'));

        $acl2 = $this->createMock('SugarACLStatic');
        $acl2->expects($this->exactly(2))->method('checkAccess')->with('test', 'test2')->will($this->returnValue(true));
        SugarACL::$acls['test'] = [$acl2];

        $this->assertTrue(SugarACL::checkAccess('test', 'test2'));

        SugarACL::$acls['test'] = [$acl1, $acl2];

        $this->assertFalse(SugarACL::checkAccess('test', 'test2'));

        SugarACL::$acls['test'] = [$acl2, $acl1];

        $this->assertFalse(SugarACL::checkAccess('test', 'test2'));
    }

    /**
     * @covers SugarACL::disabledModuleList
     */
    public function testDisabledModuleList()
    {
        $acl1 = $this->createMock('SugarACLStatic');
        $acl1->expects($this->exactly(2))->method('checkAccess')->will($this->returnValue(false));
        SugarACL::$acls['test1'] = [$acl1];

        $acl2 = $this->createMock('SugarACLStatic');
        $acl2->expects($this->exactly(2))->method('checkAccess')->will($this->returnValue(true));
        SugarACL::$acls['test2'] = [$acl2];

        $this->assertEquals([], SugarACL::disabledModuleList(['test1', 'test2'], 'test'));

        $this->assertEquals(['test1' => 'test1'], SugarACL::disabledModuleList(['test1', 'test2'], 'test', true));

        $this->assertEquals(['test1' => 'test1'], SugarACL::disabledModuleList(['test1' => 'test1', 'test2' => 'test2'], 'test'));
    }

    public function testCheckField()
    {
        $acl2 = $this->createMock('SugarACLStatic');
        $acl2->expects($this->exactly(1))->method('checkAccess')->with('test', 'field', ['field' => 'myfield', 'action' => 'myaction'])->will($this->returnValue(true));
        SugarACL::$acls['test'] = [$acl2];

        $this->assertTrue(SugarACL::checkField('test', 'myfield', 'myaction'));
    }

    /**
     * @covers SugarACL::filterModuleList
     */
    public function testFilterModuleList()
    {
        $acl1 = $this->createMock('SugarACLStatic');
        $acl1->expects($this->exactly(2))->method('checkAccess')->will($this->returnValue(true));
        SugarACL::$acls['test1'] = [$acl1];

        $acl2 = $this->createMock('SugarACLStatic');
        $acl2->expects($this->exactly(2))->method('checkAccess')->will($this->returnValue(false));
        SugarACL::$acls['test2'] = [$acl2];

        $this->assertEquals(['test1', 'test2'], SugarACL::filterModuleList(['test1', 'test2'], 'test'));

        $this->assertEquals(['test1'], SugarACL::filterModuleList(['test1', 'test2'], 'test', true));

        $this->assertEquals(['test1' => 'test1'], SugarACL::filterModuleList(['test1' => 'test1', 'test2' => 'test2'], 'test'));
    }

    /**
     * @covers SugarACL::listFilter
     */
    public function testListFilter()
    {
        $list = [];

        $this->assertNull(SugarACL::listFilter('test', $list));

        $list = ['test1', 'test2', 'test3', 'prefix_test4'];

        $this->assertEmpty(SugarACL::listFilter('test', $list));
    }

    public function testSetACL()
    {
        $acct = BeanFactory::newBean('Accounts');
        $this->assertTrue($acct->ACLAccess('edit'));

        $rejectacl = $this->createMock('SugarACLStatic');
        $rejectacl->expects($this->any())->method('checkAccess')->will($this->returnValue(false));
        SugarACL::setACL('Accounts', [$rejectacl]);
        $this->assertFalse($acct->ACLAccess('edit'));
    }

    /**
     * @param array $access_list
     * @param boolean $expected
     *
     * @dataProvider massUpdateProvider
     * @covers       SugarACL::getUserAccess
     */
    public function testMassUpdateDependsOnEdit(array $access_list, $expected)
    {
        $acl = new SugarACL();
        $access = $acl->getUserAccess('Accounts', $access_list);
        $this->assertEquals($expected, $access['massupdate'], 'MassUpdate access is incorrect');
    }

    public static function massUpdateProvider()
    {
        return [
            [
                [
                    'massupdate' => false,
                ],
                false,
            ],
            [
                [
                    'massupdate' => true,
                ],
                true,
            ],
            [
                [
                    'massupdate' => true,
                    'edit' => true,
                ],
                true,
            ],
            [
                [
                    'massupdate' => true,
                    'edit' => false,
                ],
                false,
            ],
        ];
    }
}

class MockSugarBeanACL extends SugarBean
{
    // do not let the mock kill defaultACLs function
    final public function defaultACLs()
    {
        return parent::defaultACLs();
    }
}
