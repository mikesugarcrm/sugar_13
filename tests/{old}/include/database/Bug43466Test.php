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

class Bug43466 extends TestCase
{
    /**
     * @var DBManager
     */
    private $db;

    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();
    }

    /**
     * @dataProvider matchingIndexProvider
     */
    public function testMatchingIndexDoesNotGenerateSql($indices)
    {
        $sql = $this->db->repairTableParams('calls', [
            'name' => [],
        ], $indices, false);

        $this->assertEquals('', $sql);
    }

    public static function matchingIndexProvider()
    {
        return [
            [
                [
                    [
                        'name' => 'idx_call_name',
                        'type' => 'index',
                        'fields' => [
                            'deleted',
                            'name',
                            'date_modified',
                        ],
                    ],
                    [
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => ['status'],
                    ],
                ],
            ],
            [
                [
                    [
                        'name' => 'idx_call_name2',
                        'type' => 'index',
                        'fields' => [
                            'deleted',
                            'name',
                            'date_modified',
                        ],
                    ],
                    [
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => ['status'],
                    ],
                ],
            ],
            [
                [
                    [
                        'name' => 'iDX_cAll_NAMe',
                        'type' => 'index',
                        'fields' => [
                            'deleted',
                            'name',
                            'date_modified',
                        ],
                    ],
                    [
                        'name' => 'idx_STAtus',
                        'type' => 'index',
                        'fields' => ['status'],
                    ],
                ],
            ],
            [
                [
                    [
                        'name' => 'idx_call_name',
                        'type' => 'index',
                        'fields' => [
                            'deleted',
                            'name',
                            'date_modified',
                        ],
                    ],
                    [
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => ['status'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider nonMatchingIndexProvider
     */
    public function testNonMatchingIndexGeneratesSql($indices)
    {
        $sql = $this->db->repairTableParams('calls', [
            'name' => [],
        ], $indices, false);

        $this->assertNotEquals('', $sql);
    }

    public static function nonMatchingIndexProvider()
    {
        return [
            [
                [
                    [
                        'name' => 'idx_call_name2',
                        'type' => 'index',
                        'fields' => ['name', 'status'],
                    ],
                    [
                        'name' => 'idx_status',
                        'type' => 'index',
                        'fields' => ['status'],
                    ],
                    [
                        'name' => 'idx_calls_date_start',
                        'type' => 'index',
                        'fields' => ['date_start'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider matchingVarDefProvider
     */
    public function testMatchingVarDefs(array $a, array $b)
    {
        $this->assertTrue($this->db->compareVarDefs($a, $b));
    }

    public static function matchingVarDefProvider()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                [
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                ],
            ],
        ];
    }

    /**
     * @dataProvider nonMatchingVarDefProvider
     */
    public function testNonMatchingVarDefs(array $a, array $b)
    {
        $this->assertFalse($this->db->compareVarDefs($a, $b));
    }

    public static function nonMatchingVarDefProvider()
    {
        return [
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                [
                    'name' => 'foo2',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '123',
                ],
                [
                    'name' => 'Foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
        ];
    }
}
