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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * The thing is when somebody disables module via module loader - it makes zip archive of module
 * and move it to separate directory. Attempt to remove tBA for disabled module is not successful
 * because we can't create bean without module's code or get any information about it.
 * The point of the fix - is to walk thew all tables and remove tBA flags.
 * */
class RS1661Test extends TestCase
{
    /**
     * @var TeamBasedACLConfigurator|MockObject
     */
    private $tbaConfig = null;

    /**
     * @var DBManager
     */
    private $db = null;

    /**
     * @var string
     */
    protected $tableName = 'rs1661test';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $db = DBManagerFactory::getInstance();
        $this->db = $db;
        $this->dropTestTableIfExists();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->dropTestTableIfExists();
        SugarTestHelper::tearDown();
    }

    /**
     * Drop test table if it exists in database.
     */
    private function dropTestTableIfExists()
    {
        if ($this->db->tableExists($this->tableName)) {
            $this->db->dropTableName($this->tableName);
        }
    }

    /**
     * Test testRemoveAllTBAValuesFromTables method.
     *
     * On this step we just take all available database tables
     * and check that all acl_team_set_id records are empty
     */
    public function testRemoveAllTBAValuesFromTables()
    {
        $this->createAndPopulateTable();

        $excludeTables = $this->getExcludedTables();

        /** @var TeamBasedACLConfigurator|MockObject $lvMock */
        $this->tbaConfig = $this->getMockBuilder('TeamBasedACLConfigurator')
            ->setMethods(['removeAllTBAValuesFromTable'])
            ->getMock();
        // Check that removeAllTBAValuesFromTable called only once for out test table
        $this->tbaConfig
            ->expects($this->once())
            ->method('removeAllTBAValuesFromTable')
            ->with($this->tableName);
        $this->tbaConfig->removeTBAValuesFromAllTables($excludeTables);

        // Check that out test record will be updated to null
        $this->tbaConfig = $this->getMockBuilder('TeamBasedACLConfigurator')->setMethods(null)->getMock();
        $this->tbaConfig->removeTBAValuesFromAllTables($excludeTables);
        $row = $this->db->fetchOne("SELECT * FROM {$this->tableName}");
        $this->assertNull($row['acl_team_set_id']);
    }

    /**
     * Create test table and populate with some data
     */
    private function createAndPopulateTable()
    {
        $fieldParams = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
            ],
            'acl_team_set_id' => [
                'name' => 'acl_team_set_id',
                'type' => 'varchar',
                'len' => 36,
            ],
        ];

        // Create test table with params
        $this->db->createTableParams($this->tableName, $fieldParams, []);

        // Insert some data to test table
        $this->db->insertParams($this->tableName, $fieldParams, [
            'id' => Uuid::uuid1(),
            'acl_team_set_id' => Uuid::uuid1(),
        ]);
    }

    /**
     * Get list of tables which should be excluded from test - all except our test table
     *
     * @return array
     */
    protected function getExcludedTables()
    {
        // Get list of tables from current database
        $allTables = $this->db->getTablesArray();
        $excludeTables = [];
        foreach ($allTables as $tableName) {
            // Exclude everything except our test table
            if ($tableName != $this->tableName) {
                $excludeTables[] = $tableName;
            }
        }

        return $excludeTables;
    }
}
