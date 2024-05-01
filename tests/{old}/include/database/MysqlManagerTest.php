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

abstract class MysqlManagerTest extends TestCase
{
    /**
     * @var MysqlManager
     */
    protected $db;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
    }

    protected function setUp(): void
    {
        if ($GLOBALS['db']->dbType != 'mysql') {
            $this->markTestSkipped('The instance needs to be configured to use MySQL');
        }
        SugarConfig::getInstance()->clearCache();
    }

    public static function tearDownAfterClass(): void
    {
        SugarConfig::getInstance()->clearCache();
        unset($GLOBALS['sugar_config']['disable_optimize_table']);
        parent::tearDownAfterClass();
    }

    public function testQuote()
    {
        $string = "'dog eat ";
        $this->assertEquals($this->db->quote($string), "\\'dog eat ");
    }

    public function testArrayQuote()
    {
        $string = ["'dog eat "];
        $this->db->arrayQuote($string);
        $this->assertEquals($string, ["\\'dog eat "]);
    }

    public function providerConvert()
    {
        $returnArray = [
            [
                ['foo', 'nothing'],
                'foo',
            ],
            [
                ['foo', 'today'],
                'CURDATE()',
            ],
            [
                ['foo', 'left'],
                'LEFT(foo)',
            ],
            [
                ['foo', 'left', ['1', '2', '3']],
                'LEFT(foo,1,2,3)',
            ],
            [
                ['foo', 'date_format'],
                'DATE_FORMAT(foo,\'%Y-%m-%d\')',
            ],
            [
                ['foo', 'date_format', ['1', '2', '3']],
                'DATE_FORMAT(foo,\'1\')',
            ],
            [
                ['foo', 'date_format', ["'1'", "'2'", "'3'"]],
                'DATE_FORMAT(foo,\'1\')',
            ],
            [
                ['foo', 'datetime', ["'%Y-%m'"]],
                'foo',
            ],
            [
                ['foo', 'IFNULL'],
                'IFNULL(foo,\'\')',
            ],
            [
                ['foo', 'IFNULL', ['1', '2', '3']],
                'IFNULL(foo,1,2,3)',
            ],
            [
                ['foo', 'CONCAT', ['1', '2', '3']],
                'CONCAT(foo,1,2,3)',
            ],
            [
                [['1', '2', '3'], 'CONCAT'],
                'CONCAT(1,2,3)',
            ],
            [
                [['1', '2', '3'], 'CONCAT', ['foo', 'bar']],
                'CONCAT(1,2,3,foo,bar)',
            ],
            [
                ['foo', 'text2char'],
                'foo',
            ],
            [
                ['foo', 'length'],
                'LENGTH(foo)',
            ],
            [
                ['foo', 'month'],
                'MONTH(foo)',
            ],
            [
                ['foo', 'quarter'],
                'QUARTER(foo)',
            ],
            [
                ['foo', 'add_date', [1, 'day']],
                'DATE_ADD(foo, INTERVAL 1 day)',
            ],
            [
                ['foo', 'add_date', [2, 'week']],
                'DATE_ADD(foo, INTERVAL 2 week)',
            ],
            [
                ['foo', 'add_date', [3, 'month']],
                'DATE_ADD(foo, INTERVAL 3 month)',
            ],
            [
                ['foo', 'add_date', [4, 'quarter']],
                'DATE_ADD(foo, INTERVAL 4 quarter)',
            ],
            [
                ['foo', 'add_date', [5, 'year']],
                'DATE_ADD(foo, INTERVAL 5 year)',
            ],
            [
                ['1.23', 'round', [6]],
                'round(1.23, 6)',
            ],
            [
                ['date_created', 'date_format', ['%v']],
                "DATE_FORMAT(date_created,'%v')",
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
        $this->assertEquals("LTRIM(RTRIM(CONCAT(IFNULL(foo.col1,''),' ',IFNULL(foo.col2,''),' ',IFNULL(foo.col3,''))))", $ret);
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
                '2009-01-01 12:00:00',
            ],
            [
                ['2009-01-01 12:00:00', 'time'],
                '2009-01-01 12:00:00',
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
            $this->db->fromConvert($parameters[0], $parameters[1]),
            $result
        );
    }

    public function providerEmptyValues()
    {
        $returnArray = [
            [
                ['1970-01-01', 'date'], true,
            ],
            [
                ['1970-01-01 00:00:00', 'datetime'], true,
            ],
            [
                ['0000-00-00 00:00:00', 'datetime'], true,
            ],
            [
                ['0000-00-00', 'date'], true,
            ],
            [
                ['2013-01-01', 'date'], false,
            ],
            [
                ['2013-01-01 09:04:32', 'datetime'], false,
            ],
            [
                ['00:00:00', 'time'], true,
            ],
            [
                ['12:32:30', 'time'], false,
            ],
        ];

        return $returnArray;
    }


    /**
     * @ticket BR-238
     * @dataProvider providerEmptyValues
     */
    public function testEmptyValues($parameters, $expected)
    {
        $emptyValue = SugarTestReflection::callProtectedMethod($this->db, '_emptyValue', $parameters);
        $this->assertEquals($expected, $emptyValue);
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'MysqlManager should not have order_stability capability';
        $this->assertFalse($this->db->supports('order_stability'), $msg);
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
            [['type' => 'html'], 65535],
            [['type' => 'text'], 65535],
            [['type' => 'bool'], -1],
            [['type' => 'unknown'], -1],
            [['unknown' => 'unknown'], -1],
        ];
    }

    public function testOptimizeTable()
    {
        $db = $this->createPartialMock('MysqliManager', [
            'query',
        ]);

        $db->expects($this->once())
            ->method('query');

        $db->optimizeTable('mytable');
    }

    public function testOptimizeTableDisabled()
    {
        $GLOBALS['sugar_config']['disable_optimize_table'] = true;
        $db = $this->createPartialMock('MysqliManager', [
            'query',
        ]);

        $db->expects($this->never())
            ->method('query');

        $db->optimizeTable('mytable');
    }
}
