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

class OracleManagerTest extends TestCase
{
    /** @var OracleManager */
    private $db;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    protected function setUp(): void
    {
        $this->db = new OracleManager();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->db->quote($string), "''dog eat ");
    }

    public function testArrayQuote()
    {
        $string = ["'dog eat "];
        $this->db->arrayQuote($string);
        $this->assertEquals($string, ["''dog eat "]);
    }

    public function providerConvert()
    {
        $returnArray = [
            [
                ['foo', 'nothing'],
                'foo',
            ],
            [
                ['foo', 'date'],
                "to_date(foo, 'YYYY-MM-DD')",
            ],
            [
                ['foo', 'time'],
                "to_date(foo, 'HH24:MI:SS')",
            ],
            [
                ['foo', 'datetime'],
                "to_date(foo, 'YYYY-MM-DD HH24:MI:SS')",
            ],
            [
                ['foo', 'datetime', [1, 2, 3]],
                "to_date(foo, 'YYYY-MM-DD HH24:MI:SS',1,2,3)",
            ],
            [
                ['foo', 'today'],
                'sysdate',
            ],
            [
                ['foo', 'left'],
                'LTRIM(foo)',
            ],
            [
                ['foo', 'left', [1, 2, 3]],
                'LTRIM(foo,1,2,3)',
            ],
            [
                ['foo', 'date_format'],
                "TO_CHAR(foo, 'YYYY-MM-DD')",
            ],
            [
                ['foo', 'date_format', ["'%Y-%m'"]],
                "TO_CHAR(foo, 'YYYY-MM')",
            ],
            [
                ['foo', 'date_format', [1, 2, 3]],
                "TO_CHAR(foo, 'YYYY-MM-DD')",
            ],
            [
                ['foo', 'time_format'],
                "TO_CHAR(foo,'HH24:MI:SS')",
            ],
            [
                ['foo', 'time_format', [1, 2, 3]],
                'TO_CHAR(foo,1,2,3)',
            ],
            [
                ['foo', 'IFNULL'],
                "NVL(foo,'')",
            ],
            [
                ['foo', 'IFNULL', [1, 2, 3]],
                'NVL(foo,1,2,3)',
            ],
            [
                ['foo', 'CONCAT'],
                'foo',
            ],
            [
                ['foo', 'CONCAT', [1, 2, 3]],
                'foo||1||2||3',
            ],
            [
                ['foo', 'text2char'],
                'to_char(foo)',
            ],
            [
                ['foo', 'length'],
                'LENGTH(foo)',
            ],
            [
                ['foo', 'month'],
                "TO_CHAR(foo, 'MM')",
            ],
            [
                ['foo', 'quarter'],
                "TO_CHAR(foo, 'Q')",
            ],
            [
                ['foo', 'add_date', [1, 'day']],
                '(foo + 1)',
            ],
            [
                ['foo', 'add_date', [2, 'week']],
                '(foo + 2*7)',
            ],
            [
                ['foo', 'add_date', [3, 'month']],
                'ADD_MONTHS(foo, 3)',
            ],
            [
                ['foo', 'add_date', [4, 'quarter']],
                'ADD_MONTHS(foo, 4*3)',
            ],
            [
                ['foo', 'add_date', [5, 'year']],
                'ADD_MONTHS(foo, 5*12)',
            ],
            [
                ['1.23', 'round', [6]],
                'round(1.23, 6)',
            ],
            [
                ['date_created', 'date_format', ['%v']],
                "TO_CHAR(date_created, 'IW')",
            ],
        ];
        return $returnArray;
    }

    /**
     * @ticket 33283
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array([$this->db, 'convert'], $parameters));
    }

    /**
     * @ticket 33283
     */
    public function testConcat()
    {
        $ret = $this->db->concat('foo', ['col1', 'col2', 'col3']);
        $this->assertEquals("LTRIM(RTRIM(NVL(foo.col1,'')||' '||NVL(foo.col2,'')||' '||NVL(foo.col3,'')))", $ret);
    }

    public function providerFromConvert()
    {
        $returnArray = [
            [
                ['foo', 'nothing'],
                'foo',
            ],
            [
                ['2009-01-01 12:00:00', 'date'],
                '2009-01-01',
            ],
            [
                ['2009-01-01 12:00:00', 'time'],
                '12:00:00',
            ],
        ];

        return $returnArray;
    }

    /**
     * @ticket 33283
     * @dataProvider providerFromConvert
     */
    public function testFromConvert(
        array $parameters,
        $result
    ) {

        $this->assertEquals(
            $result,
            $this->db->fromConvert($parameters[0], $parameters[1])
        );
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'OracleManager should not have order_stability capability';
        $this->assertFalse($this->db->supports('order_stability'), $msg);
    }

    /**
     * Test checks correct detection of type of vardef if type and dbType are different
     */
    public function testIsNullableTypeDetection()
    {
        $vardef = [
            'type' => 'someMagicType',
            'dbType' => 'text',
        ];
        /** @var MockObject|OracleManager $db */
        $db = $this->createPartialMock(get_class($this->db), ['getFieldType', 'isTextType']);
        $db->expects($this->atLeastOnce())->method('getFieldType')->with($this->equalTo($vardef))->will($this->returnValue($vardef['dbType']));
        $db->expects($this->atLeastOnce())->method('isTextType')->with($this->equalTo($vardef['dbType']))->will($this->returnValue(true));
        SugarTestReflection::callProtectedMethod($db, 'isNullable', [$vardef]);
    }

    public function providerCompareVardefs()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'type' => 'number',
                    'len' => '38',
                ],
                [
                    'name' => 'foo',
                    'type' => 'int',
                    'len' => '38,2',
                ],
                true,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'number',
                    'len' => '30',
                ],
                [
                    'name' => 'foo',
                    'type' => 'int',
                    'len' => '31',
                ],
                false,
            ],
        ];
    }

    /**
     * Test for number fields compare in oracle
     * @param array $fieldDef1 Field from database
     * @param array $fieldDef2 Field from vardefs
     * @param bool $expectedResult If fields the same or not
     *
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1, $fieldDef2, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->db->compareVarDefs($fieldDef1, $fieldDef2));
    }

    /**
     * Test that the updated length for columns and indexes works on the Oracle version.
     */
    public function testUpdatedLength()
    {
        $this->assertEquals(
            'idx_test_123_456_foo_bar_hello_world_sugarcrm_id',
            getValidDBName('idx_test_123_456_foo_bar_hello_world_sugarcrm_id', false)
        );

        $this->assertNotEquals(
            'idx_test_123_456_foo_bar_hello_world_sugarcrm_test_test_test_sequence_autoincrement_test_hello_world_test_id_sequence_foo_bar_123_id',
            getValidDBName(
                'idx_test_123_456_foo_bar_hello_world_sugarcrm_test_test_test_sequence_autoincrement_test_hello_world_test_id_sequence_foo_bar_123_id',
                false
            )
        );
    }

    /**
     * @covers ::getMaxFieldSize
     * @dataProvider getMaxFieldSizeProvider
     */
    public function testGetMaxFieldSize($def, $expected)
    {
        $this->assertEquals($expected, $this->db->getMaxFieldSize($def));
    }

    public function getMaxFieldSizeProvider()
    {
        return [
            [['type' => 'html'], -1],
            [['type' => 'text'], -1],
            [['type' => 'bool'], -1],
            [['type' => 'unknown'], -1],
            [['unknown' => 'unknown'], -1],
        ];
    }
}
