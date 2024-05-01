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


class SugarTestProductCategoryUtilities
{
    private static $createdProductCategories = [];

    private function __construct()
    {
    }

    public static function createProductCategory($id = '', $fields = [])
    {
        $time = random_int(0, mt_getrandmax());
        $name = 'SugarProductCategory';
        $product_category = BeanFactory::newBean('ProductCategories');
        $product_category->name = $name . $time;

        if (!empty($id)) {
            $product_category->new_with_id = true;
            $product_category->id = $id;
        }

        foreach ($fields as $field => $value) {
            $product_category->$field = $value;
        }

        $product_category->save();
        self::$createdProductCategories[] = $product_category;
        return $product_category;
    }

    public static function setCreatedProductCategory($product_category_ids)
    {
        foreach ($product_category_ids as $product_category_id) {
            $product_category_id = new ProductCategory();
            $product_category->id = $product_category_id;
            self::$createdProductCategories[] = $product_category;
        }
    }

    public static function removeAllCreatedProductCategories()
    {
        $db = DBManagerFactory::getInstance();
        $conditions = implode(',', array_map([$db, 'quoted'], self::getCreatedProductCategoryIds()));
        if (!empty($conditions)) {
            $db->query('DELETE FROM product_categories WHERE id IN (' . $conditions . ')');
        }

        self::$createdProductCategories = [];
    }

    public static function getCreatedProductCategoryIds()
    {
        $product_category_ids = [];
        foreach (self::$createdProductCategories as $product_category) {
            $product_category_ids[] = $product_category->id;
        }
        return $product_category_ids;
    }
}
