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
 * @coversDefaultClass ProductTemplateTreeApi
 */
class ProductTemplateTreeApiTest extends TestCase
{
    /**
     * @var SugarBean Stores a parent Product Category bean created for testing
     */
    private $parentCategory = null;

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->setUpProductTreeData();
    }

    /**
     * @inheritdoc
     */
    public function tearDown(): void
    {
        $this->cleanUpProductTreeData();
    }

    /**
     * @covers ::getTreeDataWithArray
     */
    public function testGetTreeDataWithArray()
    {
        $mock = $this->getMockBuilder('ProductTemplateTreeApi')
            ->disableOriginalConstructor()
            ->getMock();

        // Get the result of calling the Product Template Tree API with the parent Product Category as the root
        $getTreeDataInput = [
            'ProductCategories' => [
                'parent_id' => $this->parentCategory->id,
            ],
            'ProductTemplates' => [
                'category_id' => $this->parentCategory->id,
            ],
        ];
        $result = SugarTestReflection::callProtectedMethod($mock, 'getTreeDataWithArray', [$getTreeDataInput]);

        // Assert that the correct number of records was returned
        $this->assertEquals(5, safeCount($result));

        // Assert that the records are in the correct order. Should be sorted by:
        // 1. Categories before Products
        // 2. List order in ascending order
        // 3. Alphabetical order
        $this->assertEquals('Cat3', $result[0]['name']);
        $this->assertEquals('Cat1', $result[1]['name']);
        $this->assertEquals('Cat2', $result[2]['name']);
        $this->assertEquals('TiedCat2', $result[3]['name']);
        $this->assertEquals('Prod1', $result[4]['name']);
    }

    /**
     * Adds any data needed for testing the Product Template tree
     */
    private function setUpProductTreeData()
    {
        // Create any needed Product Categories
        $this->parentCategory = SugarTestProductCategoryUtilities::createProductCategory(null, [
            'name' => 'ParentCat',
        ]);
        SugarTestProductCategoryUtilities::createProductCategory(null, [
            'name' => 'Cat1',
            'list_order' => 1,
            'parent_id' => $this->parentCategory->id,
        ]);
        SugarTestProductCategoryUtilities::createProductCategory(null, [
            'name' => 'Cat2',
            'list_order' => 2,
            'parent_id' => $this->parentCategory->id,
        ]);
        SugarTestProductCategoryUtilities::createProductCategory(null, [
            'name' => 'TiedCat2',
            'list_order' => 2,
            'parent_id' => $this->parentCategory->id,
        ]);
        SugarTestProductCategoryUtilities::createProductCategory(null, [
            'name' => 'Cat3',
            'list_order' => 0,
            'parent_id' => $this->parentCategory->id,
        ]);

        // Create any needed Product Templates
        SugarTestProductTemplatesUtilities::createProductTemplate(null, [
            'name' => 'Prod1',
            'category_id' => $this->parentCategory->id,
            'active_status' => 'Active',
        ]);
    }

    /**
     * Removes any data created for testing the Product Template tree
     */
    private function cleanUpProductTreeData()
    {
        SugarTestProductTemplatesUtilities::removeAllCreatedProductTemplate();
        SugarTestProductCategoryUtilities::removeAllCreatedProductCategories();
    }
}
