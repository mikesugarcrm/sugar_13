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

class QuotesSeedDataTest extends TestCase
{
    protected $quote_name;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        global $sugar_demodata;
        $sugar_demodata['company_name_array'] = [];
        $query = 'SELECT * FROM accounts';
        $results = $GLOBALS['db']->limitQuery($query, 0, 10, true, 'Error retrieving Accounts');
        while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
            $sugar_demodata['company_name_array'][] = $row['name'];
        }

        $sugar_demodata['users'][0]['id'] = $GLOBALS['current_user']->id;

        $this->quote_name = 'Test Quote ' . time();

        $sugar_demodata['quotes_seed_data']['quotes'][0] = [
            'name' => $this->quote_name,
            'quote_stage' => 'Draft',
            'date_quote_expected_closed' => '04/30/2012',
            'description' => 'This is a test that should contain one product group with two products and a total of three items',


            'bundle_data' => [
                0 => [
                    'bundle_name' => 'Group 1',
                    'bundle_stage' => 'Draft',
                    'comment' => 'Three Computers',
                    'products' => [
                        1 => ['name' => 'TK 1000 Desktop', 'quantity' => '1'],
                        2 => ['name' => 'TK m30 Desktop', 'quantity' => '2'],
                    ],
                ],
            ],
        ];
    }

    protected function tearDown(): void
    {
        $sql = "SELECT * FROM quotes WHERE name = '{$this->quote_name}'";
        $results = $GLOBALS['db']->query($sql);
        $quote_id = '';

        while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
            $quote_id = $row['id'];
        }

        $sql = "DELETE FROM quotes WHERE id = '{$quote_id}'";
        $GLOBALS['db']->query($sql);

        $sql = "DELETE FROM products WHERE quote_id = '{$quote_id}'";
        $GLOBALS['db']->query($sql);

        $bundle_id = '';
        $sql = "SELECT bundle_id FROM product_bundle_quote WHERE quote_id = '{$quote_id}'";

        $results = $GLOBALS['db']->query($sql);
        while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
            $bundle_id = $row['bundle_id'];

            $sql = "DELETE FROM product_bundle_product WHERE bundle_id = '{$bundle_id}'";
            $GLOBALS['db']->query($sql);

            $sql = "DELETE FROM product_bundle_quote WHERE bundle_id = '{$bundle_id}'";
            $GLOBALS['db']->query($sql);
        }

        if (!empty($bundle_id)) {
            $sql = "SELECT note_id FROM product_bundle_note WHERE bundle_id = '{$bundle_id}'";
            $results = $GLOBALS['db']->query($sql);
            while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
                $note_id = $row['note_id'];

                $sql = "DELETE FROM product_bundle_notes WHERE id = '{$note_id}'";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM product_bundle_note WHERE bundle_id = '{$bundle_id}'";
            $GLOBALS['db']->query($sql);
        }

        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        SugarTestHelper::tearDown();
    }

    public function testCreateSeedQuotes()
    {
        require_once 'install/seed_data/quotes_SeedData.php';
        $sql = "SELECT * FROM quotes WHERE name = '{$this->quote_name}'";
        $results = $GLOBALS['db']->query($sql);
        $quote_created = false;
        while ($row = $GLOBALS['db']->fetchByAssoc($results)) {
            $quote_created = true;
        }

        $this->assertTrue($quote_created);
    }
}
