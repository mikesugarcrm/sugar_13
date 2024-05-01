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

class One2OneBeanRelationshipTest extends TestCase
{
    public function testbuildJoinSugarQuery()
    {
        $relDef = [
            'name' => 'products_revenuelineitems',
            'lhs_module' => 'Products',
            'lhs_table' => 'products',
            'lhs_key' => 'revenuelineitem_id',
            'rhs_module' => 'RevenueLineItems',
            'rhs_table' => 'revenue_line_items',
            'rhs_key' => 'id',
            'relationship_type' => 'one-to-one',
        ];
        $rel = new One2OneBeanRelationship($relDef);

        /* @var $product Product */
        $product = $this->createPartialMock('Product', ['save']);
        $product->id = 'unit_test_id';

        $link2 = $this->getMockBuilder('Link2')
            ->setMethods(['getSide', 'getRelatedModuleName', 'getFocus'])
            ->disableOriginalConstructor()
            ->getMock();
        $link2->expects($this->any())
            ->method('getSide')
            ->will($this->returnValue(REL_RHS));
        $link2->expects($this->never())
            ->method('getFocus');
        $sq = new SugarQuery();
        $sq->select('id');
        $sq->from(BeanFactory::newBean('RevenueLineItems'));

        /** @var Link2 $link2 */
        $ret = $rel->buildJoinSugarQuery($link2, $sq, ['ignoreRole' => true]);

        /** @var SugarQuery_Builder_Join $ret */
        $condition = $ret->on->conditions[0];
        $this->assertEquals('revenue_line_items', $condition->field->table);
        $this->assertEquals('id', $condition->field->field);
        $this->assertEquals('products', $condition->values->table);
        $this->assertEquals('revenuelineitem_id', $condition->values->field);
    }

    /**
     * @covers One2OneBeanRelationship::getType
     */
    public function testGetType()
    {
        $relationship = $this->getMockBuilder('One2OneBeanRelationship')
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals(REL_TYPE_ONE, $relationship->getType(REL_LHS));
        $this->assertEquals(REL_TYPE_ONE, $relationship->getType(REL_RHS));
    }
}
