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

require_once 'modules/ProductTemplates/TreeData.php';

/**
 * Bug #51792
 * Product Category "sort" field not respected when adding product to Quote
 *
 * @ticket 51792
 */
class Bug51792Test extends TestCase
{
    private $category1;
    private $category2;
    private $category3;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');

        $this->category1 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->category1->name = $this->category1->name . '_1';
        $this->category1->list_order = 3;
        $this->category1->save();

        $this->category2 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->category2->name = $this->category2->name . '_2';
        $this->category2->list_order = 2;
        $this->category2->save();

        $this->category3 = SugarTestProductCategoryUtilities::createProductCategory();
        $this->category3->name = $this->category3->name . '_3';
        $this->category3->list_order = 1;
        $this->category3->save();
    }

    protected function tearDown(): void
    {
        SugarTestProductCategoryUtilities::removeAllCreatedProductCategories();
        SugarTestHelper::tearDown();
    }

    /**
     * @group 51792
     */
    public function testOrder()
    {
        $labels = [$this->category1->name, $this->category2->name, $this->category3->name];
        $indexes = [];

        $catalogtree = new Tree('productcatalog');
        $catalogtree->set_param('module', 'ProductTemplates');
        $nodes = get_categories_and_products(null);

        $this->assertNotEmpty($nodes, 'Error retrieving data');

        foreach ($nodes as $index => $node) {
            $indexes[$node->_label] = $index;
        }

        foreach ($labels as $label) {
            $this->assertArrayHasKey($label, $indexes, 'Test-created Product Category is not in the result list');
        }

        $this->assertLessThan($indexes[$this->category2->name], $indexes[$this->category3->name]);
        $this->assertLessThan($indexes[$this->category1->name], $indexes[$this->category2->name]);
    }
}
