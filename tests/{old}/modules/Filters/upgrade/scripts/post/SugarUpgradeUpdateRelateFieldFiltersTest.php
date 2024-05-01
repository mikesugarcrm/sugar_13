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
 * @coversDefaultClass \SugarUpgradeUpdateRelateFieldFilters
 */
class SugarUpgradeUpdateRelateFieldFiltersTest extends TestCase
{
    /**
     * UpgradeScript object to test
     * @var UpgradeScript
     */
    private $ug;

    /**
     * SugarBean needed to test with
     * @var SugarBean
     */
    private $bean;

    /**
     * Field defs for the bean to test cases with
     * @var array
     */
    private $defs = [
        'field_a' => [
            'type' => 'relate',
        ],
        'field_b' => [
            'type' => 'relate',
        ],
        'field_x_c' => [
            'source' => 'custom_fields',
            'type' => 'id',
        ],
        'field_y_c' => [
            'source' => 'custom_fields',
            'type' => 'id',
        ],
    ];

    protected function setUp(): void
    {
        // We need a log way down the line for this
        SugarTestHelper::setup('log', ['name' => 'unit-php']);

        // We need a current user for the upgrader
        $cu = SugarTestHelper::setup('current_user', ['save' => false, 'is_admin' => 1]);

        // Load the upgrader
        $this->ug = (new TestUpgrader($cu))->getScript('post', '5_UpdateRelateFieldFilters');

        // Load the test Empty bean because we need a SugarBean
        $this->bean = BeanFactory::getBean('Empty');
        $this->bean->field_defs = $this->defs;
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public function convertFilterProvider()
    {
        return [
            // Test case one matched string value
            [
                'json' => '[{"field_b":"baz"}]',
                'expect' => '[{"field_b":{"$equals":"baz"}}]',
            ],
            // Test case one matched array value
            [
                'json' => '[{"field_a":["id1"]}]',
                'expect' => '[{"field_a":{"$equals":"id1"}}]',
            ],
            // Test case many matched values
            [
                'json' => '[{"field_b":["id4","id3","id2"]}]',
                'expect' => '[{"field_b":{"$equals":"id4"}}]',
            ],
            // No relate fields, nothing to do
            [
                'json' => '[{"a_id":["a1"]},{"a_id":["a2"]},{"o_t":{"$in":["Boo"]}}]',
                'expect' => '[{"a_id":["a1"]},{"a_id":["a2"]},{"o_t":{"$in":["Boo"]}}]',
            ],
            // Already formatted, nothing to do
            [
                'json' => '[{"field_b":{"$equals":"id3"}}]',
                'expect' => '[{"field_b":{"$equals":"id3"}}]',
            ],
            // Not formatted but doesn't pass criteria, nothing to do
            [
                'json' => '[{"field_b":{"$foo":["id3","id6"]}}]',
                'expect' => '[{"field_b":{"$foo":["id3","id6"]}}]',
            ],
            // Custom relate field, array value
            [
                'json' => '[{"field_x_c":["id8"]}]',
                'expect' => '[{"field_x_c":{"$equals":"id8"}}]',
            ],
            // Custom relate field, many matched values
            [
                'json' => '[{"field_y_c":["id7","id6","id9"]}]',
                'expect' => '[{"field_y_c":{"$equals":"id7"}}]',
            ],
        ];
    }

    /**
     * Tests convertFilter
     * @param string $json JSON string of data
     * @param string $expect Expected return
     * @covers       SugarUpgradeUpdateRelateFieldFilters::convertFilter
     * @dataProvider convertFilterProvider
     */
    public function testConvertFilter(string $json, string $expect)
    {
        $actual = $this->ug->convertFilter($json, $this->bean);
        $this->assertSame($expect, $actual);
    }
}
