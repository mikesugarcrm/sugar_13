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

include_once 'modules/Categories/Category.php';

/**
 * Test for Categories module
 */
class CategoriesTest extends TestCase
{
    /**
     * All created bean ids.
     *
     * @var array
     */
    public static $beanIds = [];

    /**
     * Root node
     *
     * @var CategoryMock $root
     */
    public static $root;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user', [true, true]);
        $root = SugarTestCategoryUtilities::createRootBean();
        self::$root = $root;
    }

    protected function tearDown(): void
    {
        self::$root = null;
        SugarTestCategoryUtilities::removeAllCreatedBeans();
        SugarTestHelper::tearDown();
    }

    /**
     * Test retrieve a valid query object using Category::getQuery method.
     */
    public function testGetQuery()
    {
        $bean = new CategoryMock();
        $this->assertInstanceOf('SugarQuery', $bean->getQueryMock());
    }

    /**
     * Test retrieve a valid tree data using Category::getTreeData method.
     */
    public function testGetTreeData()
    {
        $bean = new CategoryMock();
        $this->assertIsArray($bean->getTreeDataMock('test'));
    }

    /**
     * Test update category data using Category::update method.
     */
    public function testUpdate()
    {
        $db = DBManagerFactory::getInstance();
        $expected = 'TestUpdateCategoryName' . random_int(0, mt_getrandmax());

        SugarTestReflection::callProtectedMethod(
            self::$root,
            'update',
            [
                ['name = ?'],
                ' id = ? ',
                [$expected, self::$root->id],
            ]
        );

        $root = BeanFactory::retrieveBean('Categories', self::$root->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals($expected, $root->name);
    }

    /**
     * Test make new root category data using Category::saveAsRoot method.
     */
    public function testSaveAsRoot()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $this->assertTrue($bean->lft == 1);
        $this->assertTrue($bean->rgt == 2);
        $this->assertTrue($bean->lvl == 0);
        $this->assertTrue($bean->root == $bean->id);
    }

    /**
     * Test retrieve a valid data using Category::isRoot method.
     */
    public function testIsRoot()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $this->assertTrue($bean->isRoot());
    }

    /**
     * Test shifting of node indexes using Category::shiftLeftRight method.
     */
    public function testShiftLeftRight()
    {
        $bean = new CategoryMock();
        $bean->name = 'SugarCategoryRoot' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean($bean->saveAsRoot());

        $bean->shiftLeftRightMock(2, 2);
        $bean = BeanFactory::retrieveBean('Categories', $bean->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals(
            ['1', '4'],
            [$bean->lft, $bean->rgt]
        );
    }

    /**
     * Test adding new node using Category::addNode method.
     */
    public function testAddNode()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $this->assertTrue($subnode->lvl == 1);
        $this->assertTrue($subnode->lft == 2);
        $this->assertTrue($subnode->rgt == 3);
        $this->assertFalse($subnode->isRoot());
    }

    /**
     * Test throwing an Exception during adding existing node using Category::addNode method.
     */
    public function testAddExistingNodeException()
    {
        $subnode = new CategoryMock();
        $subnode->id = create_guid();
        $this->expectException(Exception::class);
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test throwing an Exception during adding deleted node using Category::addNode method.
     */
    public function testAddDeletedNodeException()
    {
        $subnode = new CategoryMock();
        $subnode->deleted = 1;
        $this->expectException(Exception::class);
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test throwing an Exception during adding node to deleted node using Category::addNode method.
     */
    public function testAddNodeToDeletedException()
    {
        self::$root->deleted = 1;
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());

        $this->expectException(Exception::class);
        self::$root->addNodeMock($subnode, 2, 1);
    }

    /**
     * Test retrieve a valid tree data using Category::getTree method.
     */
    public function testGetTree()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $tree = self::$root->getTree();

        $this->assertIsArray($tree);
        $node = current($tree);
        $this->assertTrue(array_key_exists('children', $node));
        $this->assertTrue(array_key_exists('root', $node));
        $this->assertEquals(self::$root->id, $node['root']);
        $this->assertIsArray($node['children']);
    }

    /**
     * Test retrieve a valid children data using Category::getChildren method.
     */
    public function testGetChildren()
    {
        $this->assertIsArray(self::$root->getChildren());
        $this->assertIsArray(self::$root->getChildren(1));
    }

    /**
     * Test retrieve a valid next sibling of node using Category::getNextSibling method.
     */
    public function testGetNextSibling()
    {
        $this->assertNull(self::$root->getNextSibling());

        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode2 = new CategoryMock();
        $subnode2->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode2, 2, 1));

        $subnode = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $subnode2 = BeanFactory::retrieveBean('Categories', $subnode2->id, [
            'use_cache' => false,
        ]);

        $result = $subnode2->getNextSibling();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertEquals($subnode->id, $result['id']);
    }

    /**
     * Test retrieve a valid previous sibling of node using Category::getPrevSibling method.
     */
    public function testGetPrevSibling()
    {
        $this->assertNull(self::$root->getPrevSibling());

        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode2 = new CategoryMock();
        $subnode2->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode2, 2, 1));

        $subnode = BeanFactory::retrieveBean('Categories', $subnode->id, [
            'use_cache' => false,
        ]);

        $subnode2 = BeanFactory::retrieveBean('Categories', $subnode2->id, [
            'use_cache' => false,
        ]);

        $result = $subnode->getPrevSibling();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertEquals($subnode2->id, $result['id']);
    }

    /**
     * Test retrieve a valid parents of node using Category::getParents method.
     */
    public function testGetParents()
    {
        $this->assertIsArray(self::$root->getParents());
        $this->assertIsArray(self::$root->getParents(1));
    }

    /**
     * Test retrieve a valid data using Category::isDescendantOf method.
     */
    public function testIsDescendantOf()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $root = BeanFactory::retrieveBean('Categories', self::$root->id, [
            'use_cache' => false,
        ]);

        $this->assertTrue($subnode->isDescendantOf($root));
        $this->assertFalse($root->isDescendantOf($subnode));
    }

    /**
     * Test moving node in tree using Category::moveNode method.
     */
    public function testMoveNode()
    {
        $subnode = new CategoryMock();
        $subnode->name = 'SugarCategory' . random_int(0, mt_getrandmax());
        SugarTestCategoryUtilities::addCreatedBean(self::$root->addNodeMock($subnode, 2, 1));

        $subnode->moveNodeMock(self::$root, 2, 1);
        $root = BeanFactory::retrieveBean('Categories', self::$root->id, [
            'use_cache' => false,
        ]);

        $this->assertEquals($root->id, $subnode->root);
        $this->assertEquals($root->lft + 1, $subnode->lft);
        $this->assertEquals($root->rgt - 1, $subnode->rgt);
    }

    /**
     * Test deleting node using Category::mark_deleted method.
     */
    public function test_mark_deleted()
    {
        $result = self::$root->mark_deleted(self::$root->id);
        $this->assertNull($result);
    }
}
