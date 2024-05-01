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

class SugarTestProductUtilities
{
    private static $createdProducts = [];

    private function __construct()
    {
    }

    public static function createProduct($id = '', $values = [])
    {
        $time = random_int(0, mt_getrandmax());
        $product = BeanFactory::newBean('Products');

        $values = array_merge([
            'currency_id' => '-99',
            'name' => 'SugarProduct' . $time,
            'tax_class' => 'Taxable',
            'cost_price' => '100.00',
            'list_price' => '100.00',
            'discount_price' => '100.00',
            'quantity' => '100',
            'best_case' => '100.00',
            'likely_case' => '80.00',
            'worst_case' => '50.00',
        ], $values);

        foreach ($values as $property => $value) {
            $product->$property = $value;
        }

        if (!empty($id)) {
            $product->new_with_id = true;
            $product->id = $id;
        }
        $product->save();
        self::$createdProducts[] = $product;
        return $product;
    }

    public static function setCreatedProduct($product_ids)
    {
        foreach ($product_ids as $product_id) {
            $product = new Product();
            $product->id = $product_id;
            self::$createdProducts[] = $product;
        } // foreach
    } // fn

    public static function removeAllCreatedProducts()
    {
        $db = DBManagerFactory::getInstance();
        $product_ids = self::getCreatedProductIds();
        $db->query("DELETE FROM products WHERE id IN ('" . implode("', '", $product_ids) . "')");
        $db->query("DELETE FROM products_audit WHERE parent_id IN ('" . implode("', '", $product_ids) . "')");
        $db->query("DELETE FROM forecast_worksheets WHERE parent_type = 'Products' and parent_id IN ('" . implode("', '", $product_ids) . "')");
    }

    public static function getCreatedProductIds()
    {
        $product_ids = [];
        foreach (self::$createdProducts as $product) {
            $product_ids[] = $product->id;
        }
        return $product_ids;
    }
}
