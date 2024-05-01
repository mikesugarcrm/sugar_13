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

require_once 'include/utils.php';

class DropdownListItemsTest extends TestCase
{
    protected static $custFilePath = 'custom/include/required_list_items.php';

    public static function setUpBeforeClass(): void
    {
        // Create a custom file that adds a new list and overrides the current
        $testItems = [
            'test_list_1' => [
                'list_item_0',
                'list_item_1',
                'list_item_2',
            ],
            'sales_stage_dom' => [
                'Prospecting',
            ],
        ];
        sugar_mkdir(dirname(self::$custFilePath), null, true);
        write_array_to_file('app_list_strings_required', $testItems, self::$custFilePath);
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$custFilePath);
    }

    public function testGetRequiredDropdownListItems()
    {
        $items = getRequiredDropdownListItems();

        // Asserts we have known items
        $this->assertArrayHasKey('sales_stage_dom', $items, 'sales_stage_key was missing from the list of items');
        $this->assertArrayHasKey('test_list_1', $items, 'test_list_1 was missing from the list of items');

        // Tests overriding the OOTB required sales stage list
        $this->assertCount(1, $items['sales_stage_dom'], 'sales_stage_dome should have 1 item only');
    }

    public function testGetRequiredDropdownListItemsByDDL()
    {
        $items = getRequiredDropdownListItemsByDDL('test_list_1');

        $this->assertCount(3, $items, 'test_list_1 should contain only 3 items');
    }
}
