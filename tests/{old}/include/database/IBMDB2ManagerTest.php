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

class IBMDB2ManagerTest extends TestCase
{
    /** @var IBMDB2Manager */
    private $db;

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->db = new IBMDB2Manager();
    }

    /**
     * Testing that get_indices selects FTS indices too
     * @ticket CRYS-463
     */
    public function testGetIndices()
    {
        $db = $this->createPartialMock('IBMDB2Manager', [
            'populate_index_data',
        ]);

        $db->expects($this->once())
            ->method('populate_index_data');

        $db->get_indices('mytable');
    }

    /**
     * @ticket PAT-389
     */
    public function testAddColumnSQL()
    {
        $fieldDef = [
            'name' => 'testColumn',
            'required' => true,
            'type' => 'int',
            'isnull' => false,
        ];

        $this->assertStringNotContainsString(
            'NOT NULL',
            $this->db->addColumnSQL('testTable', $fieldDef)
        );

        $fieldDef['default'] = 0;
        $this->assertStringContainsString(
            'NOT NULL',
            $this->db->addColumnSQL('testTable', $fieldDef)
        );
    }

    public function providerConvert()
    {
        $returnArray = [
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
     * @dataProvider providerConvert
     */
    public function testConvert(array $parameters, $result)
    {
        $this->assertEquals($result, call_user_func_array([$this->db, 'convert'], $parameters));
    }

    /**
     * Test asserts that massageField generates correct default value for field if it's needed
     *
     * @dataProvider providerForMassageFieldDefDefault
     */
    public function testMassageFieldDefDefault(array $defs, $expected)
    {
        $this->db->massageFieldDef($defs);
        if (isset($expected)) {
            $this->assertArrayHasKey('default', $defs, 'Default value is not present');
            $this->assertEquals($expected, $defs['default'], 'Default value is incorrect');
        } else {
            $this->assertArrayNotHasKey('default', $defs, 'Default value is incorrect');
        }
    }

    public static function providerForMassageFieldDefDefault()
    {
        return [
            [
                [
                    'name' => 'test',
                    'type' => 'int',
                ],
                null,
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'int',
                    'default' => 5,
                ],
                5,
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'int',
                    'required' => true,
                ],
                0,
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'varchar',
                ],
                null,
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'varchar',
                    'default' => 'string',
                ],
                'string',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'varchar',
                    'required' => true,
                ],
                '',
            ],
        ];
    }

    /**
     * Test order_stability capability BR-2097
     */
    public function testOrderStability()
    {
        $msg = 'IBMDB2Manager should not have order_stability capability';
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
}
