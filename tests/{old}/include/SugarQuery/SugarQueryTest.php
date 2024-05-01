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
 * @coversDefaultClass SugarQuery
 */
class SugarQueryTest extends TestCase
{
    /**
     * Test subpanel joins
     *
     * FIXME: This unit test is not complete and primarily targets the fix for
     * BR-2039. SugarQuery also needs some refactoring for proper unit testing
     * as there are too many dependencies which cannot be properly injected
     * to mock and isolate the tests.
     *
     * @covers SugarQuery::joinSubPanel
     */
    public function testJoinSubpanel()
    {
        // Test settings
        $joinAlias = 'foobaralias';
        $linkName = 'bogus_link';
        $tableName = 'dummy';

        $joinParams = [
            'joinTableAlias' => $joinAlias,
            'joinType' => 'INNER',
            'ignoreRole' => false,
            'reverse' => true,
            'includeCustom' => true,
        ];

        // Link2 mock
        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['buildJoinSugarQuery'])
            ->getMock();

        $link->expects($this->once())
            ->method('buildJoinSugarQuery')
            ->with($this->anything(), $joinParams);

        // SugarBean mock
        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $bean->expects($this->any())
            ->method('load_relationship')
            ->will($this->returnValue(true));

        $bean->table_name = $tableName;
        $bean->$linkName = $link;

        // SugarQuery mock
        $query = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getJoinTableAlias'])
            ->getMock();

        $query->expects($this->once())
            ->method('getJoinTableAlias')
            ->with($linkName)
            ->will($this->returnValue($joinAlias));

        // Hack to satisfy the tests (no proper SugarQuery injection)
        $join = $this->getMockBuilder('SugarQuery_Builder_Join')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $join->query = $query;
        $query->join[$joinAlias] = $join;

        // Execute tests
        $query->joinSubPanel($bean, $linkName, []);
    }

    /**
     * @dataProvider dataProviderGetJoinOnField
     *
     * @param string $side
     * @param string $expected
     */
    public function testGetJoinOnField($side, $expected)
    {
        $q = $this->getMockBuilder('SugarQuery')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $link2 = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(['getRelationshipObject', 'getSide'])
            ->getMock();

        $rel = $this->getMockBuilder('M2MRelationship')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        SugarTestReflection::setProtectedValue($rel, 'def', [
            'join_key_rhs' => 'right_hand_side_id',
            'join_key_lhs' => 'left_hand_side_id',
        ]);

        $link2->expects($this->once())
            ->method('getRelationshipObject')
            ->willReturn($rel);

        $link2->expects($this->atLeastOnce())
            ->method('getSide')
            ->willReturn($side);

        $bean = $this->getMockBuilder('SugarBean')
            ->disableOriginalConstructor()
            ->setMethods(['load_relationship'])
            ->getMock();

        $bean->expects($this->once())
            ->method('load_relationship')
            ->willReturn(true);

        $bean->test_link = $link2;

        $q->from = $bean;

        $actual = SugarTestReflection::callProtectedMethod($q, 'getJoinOnField', ['test_link']);

        $this->assertEquals($expected, $actual);
    }

    public static function dataProviderGetJoinOnField()
    {
        return [
            ['RHS', 'left_hand_side_id'],
            ['LHS', 'right_hand_side_id'],
        ];
    }

    /**
     * @param string $message
     * @param null|string $customTableName
     * @param null|string $alias
     * @param string $expected
     *
     * @dataProvider providerTestGetCustomTableAlias
     */
    public function testGetCustomTableAlias(string $message, ?string $customTableName, ?string $alias, string $expected)
    {
        $bean = $this->getMockBuilder(SugarBean::class)
            ->disableOriginalConstructor()
            ->setMethods(['get_custom_table_name'])
            ->getMock();

        $bean->expects($this->any())
            ->method('get_custom_table_name')
            ->willReturn($customTableName);

        $sugarQuery = $this->getMockBuilder(SugarQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $result = $sugarQuery->getCustomTableAlias($bean, $alias);
        $this->assertSame($expected, $result, 'failed test case: ' . $message);
    }

    public function providerTestGetCustomTableAlias()
    {
        return [
            ['bean has custom table without alias', 'account_cstm', '', 'account_cstm'],
            ['bean has custom table with alias', 'account_cstm', 'acct', 'acct_cstm'],
            ['bean has no custom table but has alias', '', 'acct', 'acct_cstm'],
            ['bean has null custom table but has alias', null, 'acct', 'acct_cstm'],
            ['bean has custom table with null alias', 'account_cstm', null, 'account_cstm'],
        ];
    }

    /**
     * @covers ::orderBy
     */
    public function testOrderBy()
    {
        $field = 'test_field';
        $byNull = "(CASE WHEN $field IS NULL THEN 1 ELSE 0 END)";

        $sugarQuery = $this->getMockBuilder(SugarQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFromBean'])
            ->getMock();

        $sugarQuery->expects($this->any())
            ->method('getFromBean')
            ->willReturn(false);

        $sugarQuery->orderBy($field, 'DESC', true);
        $this->assertEquals(
            2,
            count($sugarQuery->order_by),
            "$byNull should be added to order by clause"
        );
        $this->assertEquals(
            $byNull,
            $sugarQuery->order_by[0]->column->field,
            "$byNull should be added to order by clause"
        );
        $this->assertEquals(
            'ASC',
            $sugarQuery->order_by[0]->direction,
            "direction should be ASC for $byNull"
        );
        $this->assertEquals(
            $field,
            $sugarQuery->order_by[1]->column->field,
            "$field should be added to order by clause"
        );
        $this->assertEquals(
            'DESC',
            $sugarQuery->order_by[1]->direction,
            "direction should be DESC for $field"
        );

        $sugarQuery->orderByReset();

        $sugarQuery->orderBy($field, 'ASC');
        $this->assertEquals(
            1,
            count($sugarQuery->order_by),
            "$byNull should not be added to order by clause"
        );
        $this->assertEquals(
            $field,
            $sugarQuery->order_by[0]->column->field,
            "$field should be added to order by clause"
        );
        $this->assertEquals(
            'ASC',
            $sugarQuery->order_by[0]->direction,
            "direction should be ASC for $field"
        );
    }
}
