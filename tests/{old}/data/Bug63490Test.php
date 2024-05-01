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
 * @ticket 63490
 */
class Bug63490Test extends TestCase
{
    /**
     * @var SugarBean
     */
    private static $bean;

    public static function setUpBeforeClass(): void
    {
        self::$bean = new SugarBean();
        self::$bean->table_name = 'bean';
        self::$bean->field_defs = [
            'id' => [],
            'name' => [],
        ];
    }

    /**
     * @param string $input
     * @param string $expected
     * @param bool $suppress_table_name
     * @param array $field_map
     *
     * @dataProvider correctProvider
     */
    public function testCorrectColumns(
        $input,
        $expected,
        $suppress_table_name = false,
        $field_map = []
    ) {

        $actual = self::$bean->process_order_by(
            $input,
            null,
            $suppress_table_name,
            $field_map
        );
        $this->assertStringContainsString($expected, $actual);

        // Test order stability column
        $stability = $suppress_table_name ? 'id' : 'bean.id';
        if (!self::$bean->db->supports('order_stability')) {
            $msg = 'Missing ORDER BY stability column';
            $this->assertStringContainsString($stability, $actual, $msg);
        } else {
            $msg = 'Unexpected ORDER BY stability column';
            $this->assertStringNotContainsString($stability, $actual, $msg);
        }
    }

    /**
     * @param string $input
     *
     * @dataProvider incorrectProvider
     */
    public function testIncorrectColumns($input)
    {
        $actual = self::$bean->process_order_by($input);
        $this->assertStringNotContainsString($input, $actual);
    }

    /**
     * @param string $input
     *
     * @dataProvider duplicateProvider
     */
    public function testNoDuplicates($input)
    {
        $actual = self::$bean->process_order_by($input);
        $count = substr_count($actual, 'bean.id');
        $this->assertEquals(1, $count, 'There must be exactly one occurrence of bean.id in ORDER BY');
    }

    public static function correctProvider()
    {
        return [
            // contains table anme
            ['bean.name DESC', 'bean.name DESC'],
            // existing field is accepted
            ['name', 'bean.name'],
            // valid order is accepted
            ['name asc', 'bean.name asc'],
            // order is case-insensitive
            ['name DeSc', 'bean.name DeSc'],
            // any white spaces are accepted
            ["\tname\t\nASC\n\r", 'bean.name ASC'],
            // invalid order is ignored
            ['name somehow', 'bean.name'],
            // everything after the first white space considered order
            ['name desc asc', 'bean.name'],
            // $suppress_table_name usage
            ['name', 'name', true],
            // $relate_field_map usage
            [
                'name desc',
                'first_name desc, last_name desc',
                false,
                [
                    'name' => ['first_name', 'last_name'],
                ],
            ],
        ];
    }

    public static function incorrectProvider()
    {
        return [
            // non-existing field is removed
            ['title'],
            // non-existing field is removed together with order
            ['title asc'],
            // non-existing field with table name is removed
            ['bean.title'],
        ];
    }

    public static function duplicateProvider()
    {
        return [
            ['id'],
            ['id asc'],
            ['id desc'],
        ];
    }
}
