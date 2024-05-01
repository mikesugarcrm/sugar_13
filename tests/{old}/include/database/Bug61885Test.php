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
 * Unit test for Bug 61885
 */
class Bug61885 extends TestCase
{
    private $db;
    private $created;

    /*
     */
    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();
    }

    /*
     */
    protected function tearDown(): void
    {
        foreach ($this->created as $table => $dummy) {
            $this->dropTableName($table);
        }
        unset($this->db);
        unset($this->created);
    }

    /*
     * @group bug61885
     */
    public function testDefect61885()
    {
        $tableName = 'test1_' . random_int(0, mt_getrandmax());
        $params = [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '36',
            ],
            'bar' => [
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '36',
            ],
        ];

        $index = [
            'name' => 'test_index',
            'type' => 'index',
            'fields' => ['foo', 'bar'],
        ];

        $indexT1 = [
            'name' => 'test_index',
            'type' => 'index',
            'fields' => ['FOO', 'BAR'],
        ];
        $indexT2 = [
            'name' => 'TEST_INDEX',
            'type' => 'index',
            'fields' => ['foo', 'bar'],
        ];

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, $index);

        $repair = $this->db->repairTableParams($tableName, $params, [$indexT1], false);

        $this->assertEmpty($repair, 'Failed on uppercase field names');

        $repair = $this->db->repairTableParams($tableName, $params, [$indexT2], false);

        $this->assertEmpty($repair, 'Failed on uppercase index name');
    }

    /**
     * @param string $tableName
     * @param array $fieldDefs - Field definitions, in vardef format
     * @param array $indices - Indices definitions, in vardef format
     *
     * @return mixed
     */
    protected function createTableParams($tableName, $fieldDefs, $indices)
    {
        $this->created[$tableName] = true;
        return $this->db->createTableParams($tableName, $fieldDefs, $indices);
    }

    /**
     * @param string $tableName
     *
     * @return mixed
     */
    protected function dropTableName($tableName)
    {
        $indicies = $this->db->get_indices($tableName);
        foreach ($indicies as $k => $index) {
            $this->db->add_drop_constraint($tableName, $index, true);
        }
        unset($this->created[$tableName]);
        return $this->db->dropTableName($tableName);
    }


    /*
     * Tests the $sugar_config['dbconfigoption'[['skip_index_rebuild'] config flag is working
     * @group bug61885
     * @covers DBManager::repairTableParams
     */
    public function testSkipIndexRebuildConfig()
    {
        $tableName = 'test1_' . random_int(0, mt_getrandmax());
        $params = [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '36',
            ],
            'bar' => [
                'name' => 'bar',
                'type' => 'varchar',
                'len' => '36',
            ],
            'mota' => [
                'name' => 'mota',
                'type' => 'varchar',
                'len' => '43',
            ],
        ];

        $index = [
            'name' => 'test_index',
            'type' => 'index',
            'fields' => ['foo', 'bar'],
        ];

        $indexChange = [
            'name' => 'test_index',
            'type' => 'index',
            'fields' => ['foo', 'mota'],
        ];

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }

        $this->createTableParams($tableName, $params, $index);

        // Config flag on
        $dbOptions = $this->db->getOptions();
        $dbOptions['skip_index_rebuild'] = true;
        $this->db->setOptions($dbOptions);

        $repair = $this->db->repairTableParams($tableName, $params, [$indexChange], false);
        $this->assertEmpty($repair, 'Failed on skip_index_rebuild config flag turned on');


        // Config flag off
        $dbOptions = $this->db->getOptions();
        $dbOptions['skip_index_rebuild'] = false;
        $this->db->setOptions($dbOptions);

        $repair = $this->db->repairTableParams($tableName, $params, [$indexChange], false);
        $this->assertNotEmpty($repair, 'Failed on skip_index_rebuild config flag turned off');


        // Config flag not present
        $dbOptions = $this->db->getOptions();
        unset($dbOptions['skip_index_rebuild']);
        $this->db->setOptions($dbOptions);

        $repair = $this->db->repairTableParams($tableName, $params, [$indexChange], false);
        $this->assertNotEmpty($repair, 'Failed on skip_index_rebuild config flag not present');
    }
}
