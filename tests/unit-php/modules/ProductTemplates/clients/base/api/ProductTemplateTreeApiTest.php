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

namespace Sugarcrm\SugarcrmTestsUnit\modules\ProductTemplates\clients\base\api;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \ProductTemplateTreeApi
 */
class ProductTemplateTreeApiTest extends TestCase
{
    protected function setUp(): void
    {
        \SugarAutoLoader::load('../../modules/ProductTemplates/clients/base/api/ProductTemplateApi.php');
        \SugarAutoLoader::load('../../include/SugarObjects/SugarConfig.php');
    }

    /**
     * @covers ::registerApiRest
     */
    public function testRegisterApiRest()
    {
        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $compare = [
            'tree' => [
                'reqType' => 'GET',
                'path' => ['ProductTemplates', 'tree',],
                'pathVars' => ['module', 'type',],
                'method' => 'getTemplateTree',
                'shortHelp' => 'Returns a filterable tree structure of all Product Templates and Product Categories',
                'longHelp' => 'modules/ProductTemplates/clients/base/api/help/tree.html',
            ],
            'filterTree' => [
                'reqType' => 'POST',
                'path' => ['ProductTemplates', 'tree',],
                'pathVars' => ['module', 'type',],
                'method' => 'getTemplateTree',
                'shortHelp' => 'Returns a filterable tree structure of all Product Templates and Product Categories',
                'longHelp' => 'modules/ProductTemplates/clients/base/api/help/tree.html',
            ],
        ];

        $returnVal = $mock->registerApiRest();
        $this->assertEquals($compare, $returnVal);
    }

    /**
     * @covers ::generateNewLeaf
     * @dataProvider generateNewLeafProvider
     */
    public function testGenerateNewLeaf($type, $state)
    {
        $id = 'foo';
        $name = 'bar';
        $total = 10;
        $index = 0;

        $returnObj = new \stdClass();
        $returnObj->id = $id;
        $returnObj->type = $type;
        $returnObj->data = $name;
        $returnObj->state = $state;
        $returnObj->index = $index;

        $node = [
            'id' => 'foo',
            'name' => 'bar',
            'type' => $type,
        ];

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $leaf = TestReflection::callProtectedMethod($mock, 'generateNewLeaf', [$node, $index]);

        $this->assertEquals($returnObj, $leaf);
    }

    public function generateNewLeafProvider()
    {
        return [
            [
                'category', 'closed',
                'product', '',
            ],
        ];
    }

    /**
     * @covers ::getFilteredTreeData
     */
    public function testGetFilteredTreeData()
    {
        $filter = '%foo%';
        $unionFilter = 'and name like ? ';

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getTreeData'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getTreeData')
            ->with($unionFilter, $unionFilter, [$filter, $filter]);

        TestReflection::callProtectedMethod($mock, 'getFilteredTreeData', ['foo']);
    }

    /**
     * @dataProvider getRootedTreeDataProvider
     * @covers ::getRootedTreeData
     */
    public function testGetRootededTreeData($root)
    {
        $union1Root = '';
        $union2Root = '';

        if ($root == null) {
            $union1Root = 'and parent_id is null ';
            $union2Root = 'and category_id is null ';
            $params = [];
        } else {
            $union1Root = 'and parent_id = ? ';
            $union2Root = 'and category_id = ? ';
            $params = [$root, $root];
        }

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getTreeData'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->once())
            ->method('getTreeData')
            ->with($union1Root, $union2Root, $params);

        TestReflection::callProtectedMethod($mock, 'getRootedTreeData', [$root]);
    }

    public function getRootedTreeDataProvider()
    {
        return [
            ['foo'],
            [null],
        ];
    }

    /**
     * @covers ::getTreeData
     */
    public function testGetTreeData()
    {
        $union1Filter = 'foo';
        $union2Filter = 'bar';
        $filter1 = 'foo1';
        $filter2 = 'bar1';
        $params = [$filter1, $filter2];

        $q = "select id, name, 'category' as type from product_categories " .
            'where deleted = 0 ' .
            $union1Filter .
            'union all ' .
            "select id, name, 'product' as type from product_templates " .
            'where deleted = 0 ' .
            $union2Filter .
            'order by type, name';

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getDBConnection', 'getDBInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbConnectionMock = $this->getMockBuilder('mockProductTemplateTreeApiTestDBConnection')
            ->setMethods(['executeQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $dbConnectionMock->expects($this->any())
            ->method('executeQuery')
            ->with($q, $this->anything())
            ->will($this->returnValue(new mockProductTemplateTreeApiTestDBConnection()));

        $mock->expects($this->any())
            ->method('getDBConnection')
            ->will($this->returnValue($dbConnectionMock));

        TestReflection::callProtectedMethod(
            $mock,
            'getTreeData',
            [$union1Filter, $union2Filter, $params]
        );

        //because simply testing that a function is called with params isn't enough, we MUST assert something ಠ_ಠ
        $this->assertEquals(1, 1);
    }

    public function getTreeDataProvider()
    {
        return [
            ['', '', null],
            ['and name like ? ', 'and (pc.name like ? or pt.name like ?) ', 'foo'],
        ];
    }

    /**
     * @dataProvider getTemplateTreeProvider
     * @covers ::getTemplateTree
     */
    public function testGetTemplateTree($args, $filteredTreeCallCount, $rootedTreeCallCount, $index, $id, $data, $total)
    {
        //build tree data
        $treeData = [];
        for ($i = 1; $i <= 100; $i++) {
            $treeData[] = [
                'id' => $i,
                'name' => 'foo_' . $i,
                'type' => 'category',
            ];
        }

        for ($i = 101; $i <= 200; $i++) {
            $treeData[] = [
                'id' => $i,
                'name' => 'bar_' . $i,
                'type' => 'product',
            ];
        }

        $mock = $this->getMockBuilder('\ProductTemplateTreeApi')
            ->setMethods(['getTreeDataWithFilter', 'getTreeDataWithRoot', 'getSugarConfig', 'checkCategoryContainsProduct', 'checkAccess'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects($this->exactly($filteredTreeCallCount))
            ->method('getTreeDataWithFilter')
            ->with(array_key_exists('filter', $args) ? $args['filter'] : '')
            ->will($this->returnValue($treeData));

        if (array_key_exists('root', $args)) {
            $mock->expects($this->any())
                ->method('getTreeDataWithRoot')
                ->with($args['root'])
                ->will($this->returnValue([$treeData[$args['root'] - 1]]));
        } else {
            $mock->expects($this->any())
                ->method('getTreeDataWithRoot')
                ->with('')
                ->will($this->returnValue($treeData));
        }

        $mock->expects($this->any())
            ->method('checkCategoryContainsProduct')
            ->willReturn(true);

        $sugarConfigMock = $this->getMockBuilder('\SugarConfig')
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $sugarConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue(20));

        $mock->expects($this->any())
            ->method('getSugarConfig')
            ->will($this->returnValue($sugarConfigMock));

        $results = $mock->getTemplateTree(new SugarApiProductTemplateTreeApiTestServiceMock(), $args);

        if ($data) {
            $this->assertEquals($id, $results['records'][0]->id);
            $this->assertEquals($index, $results['records'][0]->index);
        } else {
            $this->assertEquals([], $results['records']);
        }

        $this->assertEquals($total, safeCount($results['records']));
    }

    public function getTemplateTreeProvider()
    {
        return [
            [
                ['filter' => 'foo'],
                1,
                0,
                0,
                1,
                true,
                20,
            ],
            [
                ['root' => 1],
                0,
                1,
                0,
                1,
                true,
                1,
            ],
            [
                ['filter' => 'foo', 'offset' => 100],
                1,
                0,
                100,
                101,
                true,
                20,
            ],
            [
                ['offset' => 201],
                0,
                1,
                100,
                101,
                false,
                0,
            ],
            [
                ['max_num' => 50],
                0,
                1,
                0,
                1,
                true,
                20,
            ],
            [
                ['max_num' => -1],
                0,
                1,
                0,
                1,
                true,
                20,
            ],
        ];
    }
}

class mockProductTemplateTreeApiTestDBConnection
{
    public function executeQuery()
    {
    }

    public function fetchAllAssociative()
    {
    }
}

class SugarApiProductTemplateTreeApiTestServiceMock extends \ServiceBase
{
    public function execute()
    {
    }

    protected function handleException(\Throwable $exception)
    {
    }
}
