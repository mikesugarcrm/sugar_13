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

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

class UniqueIndexTest extends TestCase
{
    /**
     * @var DBManager
     */
    private $db;
    protected $created = [];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_strings');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();
        $this->created = [];
    }

    protected function tearDown(): void
    {
        foreach ($this->created as $table => $_) {
            $this->db->dropTableName($table);
        }
    }

    public function testCreateMultipleUniqueIndex()
    {
        $def = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
            ],
            'unique_field_1' => [
                'name' => 'unique_field_1',
                'type' => 'varchar',
                'len' => '32',
            ],
            'unique_field_2' => [
                'name' => 'unique_field_2',
                'type' => 'varchar',
                'len' => '32',
            ],
            'unique_field_3' => [
                'name' => 'unique_field_3',
                'type' => 'varchar',
                'len' => '32',
            ],
        ];
        $tableName = 'test_tbl_with_unique_idx';
        $this->createTableParams(
            $tableName,
            $def,
            [
                [
                    'name' => 'idx_pk_' . $tableName,
                    'type' => 'primary',
                    'fields' => ['id'],
                ], [
                'name' => 'idx_uf3_' . $tableName,
                'type' => 'unique',
                'fields' => ['unique_field_3'],
                ],
            ]
        );
        $index = $this->db->get_index($tableName, 'idx_uf3_' . $tableName);
        $this->assertEquals('unique', $index['type']);

        $this->db->addIndexes(
            $tableName,
            [
                [
                    'name' => 'idx_uf1_' . $tableName,
                    'type' => 'unique',
                    'fields' => ['unique_field_1'],
                ],
            ]
        );
        $index = $this->db->get_index($tableName, 'idx_uf1_' . $tableName);
        $this->assertEquals('unique', $index['type']);

        $this->db->addIndexes(
            $tableName,
            [
                [
                    'name' => 'idx_uf2_' . $tableName,
                    'type' => 'unique',
                    'fields' => ['unique_field_2'],
                ],
            ]
        );
        $index = $this->db->get_index($tableName, 'idx_uf2_' . $tableName);
        $this->assertEquals('unique', $index['type']);

        $this->db->addIndexes(
            $tableName,
            [
                [
                    'name' => 'idx_uf12_' . $tableName,
                    'type' => 'unique',
                    'fields' => ['unique_field_1', 'unique_field_2'],
                ],
            ]
        );
        $index = $this->db->get_index($tableName, 'idx_uf12_' . $tableName);
        $this->assertEquals('unique', $index['type']);

        $this->db->addIndexes(
            $tableName,
            [
                [
                    'name' => 'idx_uf123_' . $tableName,
                    'type' => 'unique',
                    'fields' => ['unique_field_1', 'unique_field_2', 'unique_field_3'],
                ],
            ]
        );
        $index = $this->db->get_index($tableName, 'idx_uf123_' . $tableName);
        $this->assertEquals('unique', $index['type']);
    }

    public function testUniqueIndexPopulated()
    {
        $tableName = $this->createTestTable();

        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => 'UNIQUE-VALUE-1',
        ]);
        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => 'UNIQUE-VALUE-2',
        ]);
        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => null,
        ]);
        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => null,
        ]);

        $data = $this->db->getConnection()->fetchAllAssociative('SELECT unique_field FROM ' . $tableName);
        $this->assertIsArray($data);
        $values = array_column($data, 'unique_field');
        sort($values);
        $expected = [
            null,
            null,
            'UNIQUE-VALUE-1',
            'UNIQUE-VALUE-2',
        ];
        $this->assertEquals($expected, $values);
    }

    public function testUniqueIndexInsertRefused()
    {
        $this->expectException(UniqueConstraintViolationException::class);

        $tableName = $this->createTestTable();

        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => 'NON-UNIQUE-VALUE',
        ]);
        $this->db->getConnection()->insert($tableName, [
            'id' => $this->generateId(),
            'unique_field' => 'NON-UNIQUE-VALUE',
        ]);

        // check that the only row inserted, not more
        $data = $this->db->getConnection()->fetchAllAssociative('SELECT unique_field FROM ' . $tableName);
        $this->assertIsArray($data);
        $expected = [
            ['unique_field' => 'NON-UNIQUE-VALUE'],
        ];
        $this->assertEquals($expected, $data);
    }

    private function generateId(): string
    {
        return Uuid::uuid4();
    }

    private function createTestTable(): string
    {
        $tableName = 'test_tbl_with_unique_idx';
        $def = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
            ],
            'unique_field' => [
                'name' => 'unique_field',
                'type' => 'varchar',
                'len' => '32',
            ],
        ];
        $this->createTableParams(
            $tableName,
            $def,
            [
                [
                    'name' => 'idx_pk_' . $tableName,
                    'type' => 'primary',
                    'fields' => ['id'],
                ], [
                'name' => 'idx_unique_field_' . $tableName,
                'type' => 'unique',
                'fields' => ['unique_field'],
                ],
            ]
        );

        return $tableName;
    }

    private function createTableParams($tableName, $fieldDefs, $indices)
    {
        $this->created[$tableName] = true;

        return $this->db->createTableParams($tableName, $fieldDefs, $indices);
    }
}
