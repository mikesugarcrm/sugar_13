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

require_once 'install/install_utils.php';

class ForecastTreeSeedDataTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $GLOBALS['db']->query("DELETE FROM forecast_tree WHERE hierarchy_type in ('users','products')");
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function testCreateForecastUserSeedData()
    {
        require_once 'install/seed_data/ForecastTreeSeedData.php';
        $forecastSeedData = new ForecastTreeSeedData();
        $forecastSeedData->populateUserSeedData();
        $results = $GLOBALS['db']->query("SELECT id, reports_to_id FROM users WHERE status = 'Active'");
        $users_data = [];

        while (($row = $GLOBALS['db']->fetchByAssoc($results))) {
            $users_data[$row['id']] = $row['reports_to_id'];
        }

        $tree_results = $GLOBALS['db']->query("SELECT user_id, parent_id FROM forecast_tree WHERE hierarchy_type = 'users'");
        $tree_data = [];
        while (($row = $GLOBALS['db']->fetchByAssoc($tree_results))) {
            $tree_data[$row['user_id']] = $row['parent_id'];
        }
        sort($users_data, SORT_STRING);
        sort($tree_data, SORT_STRING);

        //Assert that the users table entries match the users hierarchy_type entries in forecast_tree
        $this->assertEquals($users_data, $tree_data, 'Forecast tree data for users does not match report to structure of users table');

        $forecastSeedData->populateProductCategorySeedData();
        $product_categories_count = $GLOBALS['db']->getOne('SELECT count(id) AS total FROM product_categories where deleted = 0');
        $product_templates_count = $GLOBALS['db']->getOne('SELECT count(id) AS total FROM product_templates where deleted = 0');
        $tree_product_count = $GLOBALS['db']->getOne("SELECT count(id) AS total FROM forecast_tree WHERE hierarchy_type = 'products'");

        $this->assertEquals($product_categories_count + $product_templates_count, $tree_product_count, 'Forecast tree data for products does not match hierarchy structure of product_categories and product_templates table');
    }
}
