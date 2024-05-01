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

class DBManagerTest extends TestCase
{
    /**
     * @var DBManager
     */
    private $db;
    protected $created = [];

    protected $backupGlobals = false;

    /**
     * @var bool Backup for DBManager::encode.
     */
    protected $dbEncode;

    public static function setUpBeforeClass(): void
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['db']->query('DELETE FROM forecast_tree');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    protected function setUp(): void
    {
        if (empty($this->db)) {
            $this->db = DBManagerFactory::getInstance();
            $this->dbEncode = $this->db->getEncode();
        }
        $this->created = [];
    }

    protected function tearDown(): void
    {
        $this->db->setEncode($this->dbEncode);
        foreach ($this->created as $table => $dummy) {
            $this->db->dropTableName($table);
        }
    }

    protected function createTableParams($tablename, $fieldDefs, $indices)
    {
        $this->created[$tablename] = true;
        return $this->db->createTableParams($tablename, $fieldDefs, $indices);
    }

    protected function dropTableName($tablename)
    {
        unset($this->created[$tablename]);
        return $this->db->dropTableName($tablename);
    }

    private function createRecords(
        $num
    ) {

        $beanIds = [];
        for ($i = 0; $i < $num; $i++) {
            $bean = new Contact();
            $bean->id = create_guid();
            $bean->last_name = 'foobar';
            $this->db->insert($bean);
            $beanIds[] = $bean->id;
        }

        return $beanIds;
    }

    private function removeRecords(
        array $ids
    ) {

        foreach ($ids as $id) {
            $this->db->query("DELETE From contacts where id = '{$id}'");
        }
    }

    public function testGetDatabase()
    {
        if ($this->db instanceof MysqliManager) {
            $this->assertInstanceOf('Mysqli', $this->db->getDatabase());
        } else {
            $this->assertTrue(is_resource($this->db->getDatabase()));
        }
    }

    public function testCheckError()
    {
        $this->assertFalse($this->db->checkError('testCheckError'));
        $this->assertFalse($this->db->lastError());
    }

    public function testCheckErrorNoConnection()
    {
        $this->db->disconnect();
        $this->assertTrue($this->db->checkError('testCheckErrorNoConnection'));
        $this->db = DBManagerFactory::getInstance();
    }

    public function testGetQueryTime()
    {
        // BR-3387.  MSSQL caches the result, the second run will cost 'NO TIME'
        // using a random query.
        $randVal = random_int(0, 10000);
        $sql = 'SELECT accounts.* FROM accounts WHERE DELETED = 0';
        $this->db->limitQuery($sql, 0, 1 + $randVal, true);
        $this->assertTrue($this->db->getQueryTime() > 0);
    }

    public function testCheckConnection()
    {
        $this->db->checkConnection();
        if ($this->db instanceof MysqliManager) {
            $this->assertInstanceOf('Mysqli', $this->db->getDatabase());
        } else {
            $this->assertTrue(is_resource($this->db->getDatabase()));
        }
    }

    public function testInsert()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . random_int(0, mt_getrandmax());
        $bean->id = create_guid();

        $this->assertTrue($this->db->insert($bean));

        $result = $this->db->query("select id, last_name from contacts where id = '{$bean->id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'], $bean->last_name);
        $this->assertEquals($row['id'], $bean->id);

        $this->db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testUpdate()
    {
        [$id] = $this->createRecords(1);

        $bean = new Contact();
        $bean->id = $id;
        $bean->last_name = 'newfoobar' . random_int(0, mt_getrandmax());
        $this->assertTrue($this->db->update($bean));

        $result = $this->db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'], $bean->last_name);
        $this->assertEquals($row['id'], $id);

        $this->db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testEmptyUpdate()
    {
        [$id] = $this->createRecords(1);

        $bean = new Contact();
        $bean->last_name = 'newfoobar' . random_int(0, mt_getrandmax());
        $this->assertFalse($this->db->update($bean)); //$bean has an empty id, update will fail

        $result = $this->db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertNotEquals($row['last_name'], $bean->last_name); //Update failed, record was not updated in the DB

        $bean->id = $id;
        $this->assertTrue($this->db->update($bean)); //$bean has id now, update will work correctly

        $result = $this->db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'], $bean->last_name); // last_name was successfully saved in the DB

        $this->db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testCreateTableParams()
    {
        $tablename = 'test' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            [
                [
                    'name' => 'idx_' . $tablename,
                    'type' => 'index',
                    'fields' => ['foo'],
                ],
            ]
        );
        $this->assertTrue(in_array($tablename, $this->db->getTablesArray()));
    }

    public function testRepairTableNoChanges()
    {
        $tableName = 'testRTNC_' . random_int(0, mt_getrandmax());
        $params = [
            'id' => [
                'name' => 'id',
                'vname' => 'LBL_ID',
                'required' => true,
                'type' => 'id',
                'reportable' => false,
                'comment' => 'Unique identifier',
            ],
            'date_entered' => [
                'name' => 'date_entered',
                'vname' => 'LBL_DATE_ENTERED',
                'type' => 'datetime',
                'required' => true,
                'comment' => 'Date record created',
            ],
            'date_modified' => [
                'name' => 'date_modified',
                'vname' => 'LBL_DATE_MODIFIED',
                'type' => 'datetime',
                'required' => true,
                'comment' => 'Date record last modified',
            ],
            'modified_user_id' => [
                'name' => 'modified_user_id',
                'rname' => 'user_name',
                'id_name' => 'modified_user_id',
                'vname' => 'LBL_MODIFIED',
                'type' => 'assigned_user_name',
                'table' => 'modified_user_id_users',
                'isnull' => 'false',
                'dbType' => 'id',
                'required' => false,
                'len' => 36,
                'reportable' => true,
                'comment' => 'User who last modified record',
            ],
            'created_by' => [
                'name' => 'created_by',
                'rname' => 'user_name',
                'id_name' => 'created_by',
                'vname' => 'LBL_CREATED',
                'type' => 'assigned_user_name',
                'table' => 'created_by_users',
                'isnull' => 'false',
                'dbType' => 'id',
                'len' => 36,
                'comment' => 'User ID who created record',
            ],
            'name' => [
                'name' => 'name',
                'type' => 'varchar',
                'vname' => 'LBL_NAME',
                'len' => 150,
                'comment' => 'Name of the allowable action (view, list, delete, edit)',
            ],
            'category' => [
                'name' => 'category',
                'vname' => 'LBL_CATEGORY',
                'type' => 'varchar',
                'len' => 100,
                'reportable' => true,
                'required' => true,
                'isnull' => false,
                'comment' => 'Category of the allowable action (usually the name of a module)',
            ],
            'acltype' => [
                'name' => 'acltype',
                'vname' => 'LBL_TYPE',
                'type' => 'varchar',
                'len' => 100,
                'reportable' => true,
                'comment' => 'Specifier for Category, usually "module"',
            ],
            'aclaccess' => [
                'name' => 'aclaccess',
                'vname' => 'LBL_ACCESS',
                'type' => 'int',
                'len' => 3,
                'reportable' => true,
                'comment' => 'Number specifying access priority; highest access "wins"',
            ],
            'deleted' => [
                'name' => 'deleted',
                'vname' => 'LBL_DELETED',
                'type' => 'bool',
                'reportable' => false,
                'comment' => 'Record deletion indicator',
            ],
            'roles' => [
                'name' => 'roles',
                'type' => 'link',
                'relationship' => 'acl_roles_actions',
                'source' => 'non-db',
                'vname' => 'LBL_USERS',
            ],
            'reverse' => [
                'name' => 'reverse',
                'vname' => 'LBL_REVERSE',
                'type' => 'bool',
                'default' => 0,
            ],
            'deleted2' => [
                'name' => 'deleted2',
                'vname' => 'LBL_DELETED2',
                'type' => 'bool',
                'reportable' => false,
                'default' => '0',
            ],
            'primary_address_country' => [
                'name' => 'primary_address_country',
                'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                'type' => 'varchar',
                'group' => 'primary_address',
                'comment' => 'Country for primary address',
                'merge_filter' => 'enabled',
            ],
            'budget' => [
                'name' => 'budget',
                'vname' => 'LBL_CAMPAIGN_BUDGET',
                'type' => 'currency',
                'dbType' => 'double',
                'comment' => 'Budgeted amount for the campaign',
            ],
            'time_from' => [
                'name' => 'time_from',
                'vname' => 'LBL_TIME_FROM',
                'type' => 'time',
                'required' => false,
                'reportable' => false,
            ],
            'description' => [
                'name' => 'description',
                'vname' => 'LBL_DESCRIPTION',
                'type' => 'text',
                'comment' => 'Full text of the note',
                'rows' => 6,
                'cols' => 80,
            ],
            'cur_plain' => [
                'name' => 'cur_plain',
                'vname' => 'LBL_curPlain',
                'type' => 'currency',
            ],
            'cur_len_prec' => [
                'name' => 'cur_len_prec',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26,6',
            ],
            'cur_len' => [
                'name' => 'cur_len',
                'vname' => 'LBL_curLen',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
            ],
            'cur_len_prec2' => [
                'name' => 'cur_len_prec2',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
                'precision' => '6',
            ],
            'token_ts' => [
                'name' => 'token_ts',
                'type' => 'long',
                'required' => true,
                'comment' => 'Token timestamp',
                'function' => ['name' => 'displayDateFromTs', 'returns' => 'html', 'onListView' => true],
            ],
            'conskey' => [
                'name' => 'conskey',
                'type' => 'varchar',
                'len' => 32,
                'required' => true,
                'isnull' => false,
            ],
        ];
        $indexes = [
            [
                'name' => "idx_{$tableName}",
                'type' => 'primary',
                'fields' => [
                    'id',
                    'category',
                ],
            ],
        ];

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }

        $this->createTableParams($tableName, $params, $indexes);

        $repair = $this->db->repairTableParams($tableName, $params, $indexes, true);
        $this->assertEmpty($repair, 'Unexpected repairs: ' . $repair);
    }

    /**
     * Test creation of primary key on existing index.
     */
    public function testPrimaryKeyOnExistingIndex()
    {
        $tableName = 'testRTNC2_' . random_int(0, mt_getrandmax());
        $fields = [
            'list_id' => [
                'name' => 'list_id',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
            ],
            'bean_id' => [
                'name' => 'bean_id',
                'type' => 'id',
                'required' => true,
                'reportable' => false,
            ],
        ];
        $indices = [
            [
                'name' => 'testRTNC2_list_id_idx',
                'type' => 'index',
                'fields' => [
                    'list_id',
                ],
            ],
            [
                'name' => 'testRTNC2_list_id_bean_idx',
                'type' => 'index',
                'fields' => [
                    'list_id',
                    'bean_id',
                ],
            ],
        ];
        $this->createTableParams($tableName, $fields, $indices);
        $indices = [
            [
                'name' => 'testRTNC2_list_id_idx',
                'type' => 'index',
                'fields' => [
                    'list_id',
                ],
            ],
            [
                'name' => 'idx_testRTNC2_pk',
                'type' => 'primary',
                'fields' => [
                    'list_id',
                    'bean_id',
                ],
            ],
        ];
        $repair = $this->db->repairTableParams($tableName, $fields, $indices, true);
        $this->assertNotEmpty($repair, "Shouldn't be empty repair");

        $dbIndexes = $this->db->get_indices($tableName);

        //Should replaced with assertArraySubset in future
        $pk = false;
        foreach ($dbIndexes as $ind) {
            if ($ind['type'] == 'primary') {
                $pk = true;
                break;
            }
        }
        $this->assertTrue($pk, 'There should be primary index for table');
    }

    public function testRepairTableParamsAddData()
    {
        $tableName = 'test1_' . random_int(0, mt_getrandmax());
        $params = [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ],
        ];

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, []);

        $params['bar'] = [
            'name' => 'bar',
            'type' => 'int',
        ];
        $cols = $this->db->get_columns($tableName);
        $this->assertArrayNotHasKey('bar', $cols);

        $repair = $this->db->repairTableParams($tableName, $params, [], false);
        $this->assertMatchesRegularExpression('#MISSING IN DATABASE.*bar#i', $repair);
        $repair = $this->db->repairTableParams($tableName, $params, [], true);
        $cols = $this->db->get_columns($tableName);
        $this->assertArrayHasKey('bar', $cols);
        $this->assertEquals('bar', $cols['bar']['name']);
        $this->assertEquals($this->db->getColumnType('int'), $cols['bar']['type']);
    }

    public function testRepairTableParamsAddIndex()
    {
        $tableName = 'test1_' . random_int(0, mt_getrandmax());
        $params = [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
                'isnull' => false,
                'required' => true,
            ],
            'bar' => [
                'name' => 'bar',
                'type' => 'int',
            ],
        ];
        $primaryKey = $tableName . '_pk';
        $indices = [
            [
                'name' => $primaryKey,
                'type' => 'primary',
                'fields' => ['foo'],
            ],
            [
                'name' => 'test_index',
                'type' => 'index',
                'fields' => ['foo', 'bar', 'bazz'],
            ],
        ];
        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, []);
        $params['bazz'] = [
            'name' => 'bazz',
            'type' => 'int',
        ];

        $repair = $this->db->repairTableParams($tableName, $params, $indices, false);
        $this->assertMatchesRegularExpression('#MISSING IN DATABASE.*bazz#i', $repair);
        $this->assertMatchesRegularExpression('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $this->assertMatchesRegularExpression('#MISSING INDEX IN DATABASE.*primary#i', $repair);
        $this->db->repairTableParams($tableName, $params, $indices, true);

        $idx = $this->db->get_indices($tableName);
        foreach ($idx as $index) {
            if ($index['type'] == 'primary') {
                $idx['primary'] = $index;
                break;
            }
        }
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bazz', $idx['test_index']['fields']);
        $this->assertArrayHasKey('primary', $idx);
        $this->assertContains('foo', $idx['primary']['fields']);

        $cols = $this->db->get_columns($tableName);
        $this->assertArrayHasKey('bazz', $cols);
        $this->assertEquals('bazz', $cols['bazz']['name']);
        $this->assertEquals($this->db->getColumnType('int'), $cols['bazz']['type']);
    }

    public function testRepairTableParamsAddIndexAndData()
    {
        $tableName = 'test1_' . random_int(0, mt_getrandmax());
        $params = [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => '255',
            ],
            'bar' => [
                'name' => 'bar',
                'type' => 'int',
            ],
        ];
        $index = [
            'name' => 'test_index',
            'type' => 'index',
            'fields' => ['foo', 'bar'],
        ];
        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, []);

        $repair = $this->db->repairTableParams($tableName, $params, [$index], false);
        $this->assertMatchesRegularExpression('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->db->repairTableParams($tableName, $params, [$index], true);
        $idx = $this->db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bar', $idx['test_index']['fields']);
    }

    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename1,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $tablename2 = 'test2_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename2,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $res = $this->db->compareFieldInTables(
            'foo',
            $tablename1,
            $tablename2
        );

        $this->assertEquals($res['msg'], 'match');
    }

    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename1,
            [
                'foobar' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $tablename2 = 'test4_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename2,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $res = $this->db->compareFieldInTables(
            'foo',
            $tablename1,
            $tablename2
        );
        $this->assertEquals($res['msg'], 'not_exists_table1');
    }

    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename1,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $tablename2 = 'test6_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename2,
            [
                'foobar' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $res = $this->db->compareFieldInTables(
            'foo',
            $tablename1,
            $tablename2
        );

        $this->assertEquals($res['msg'], 'not_exists_table2');
    }

    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename1,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $tablename2 = 'test8_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename2,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'int',
                ],
            ],
            []
        );

        $res = $this->db->compareFieldInTables(
            'foo',
            $tablename1,
            $tablename2
        );

        $this->assertEquals($res['msg'], 'no_match');
    }

    public function providerRepairIndexes()
    {
        return [
            // create PK
            [
                [],
                [['name' => 'pkey', 'type' => 'primary', 'fields' => ['id']]],
                'ADD {"name":"pkey","type":"primary","fields":["id"]}',
            ],
            // PK name change
            [
                [['name' => 'pkey', 'type' => 'primary', 'fields' => ['id']]],
                [['name' => 'pkey2', 'type' => 'primary', 'fields' => ['id']]],
                '',
            ],
            // PK removal
            [
                [['name' => 'pkey', 'type' => 'primary', 'fields' => ['id']]],
                [],
                '',
            ],
            // Index add
            [
                [],
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                'ADD {"name":"mykey","type":"index","fields":["foo","bar"]}',
            ],
            // Index remove
            [
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                [],
                '',
            ],
            // Index change
            [
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo']]],
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                'ADD {"name":"mykey","type":"index","fields":["foo","bar"]}',
            ],
            // Index change 2
            [
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo']]],
                [['name' => 'mykeynew', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                'ADD {"name":"mykeynew","type":"index","fields":["foo","bar"]}',
            ],
            // Index rename
            [
                [['name' => 'mykey', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                [['name' => 'mykeynew', 'type' => 'index', 'fields' => ['foo', 'bar']]],
                '',
            ],
        ];
    }

    /**
     * @dataProvider providerRepairIndexes
     * @param array $current
     * @param array $new
     * @param string $query
     */
    public function testRepairIndexes($current, $new, $query)
    {
        $tablename1 = 'test23_' . random_int(0, mt_getrandmax());
        $dbmock = $this->getMockBuilder(get_class($this->db))
            ->setMethods(['get_columns', 'get_indices', 'add_drop_constraint'])
            ->getMock();

        $db_columns = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
            ],
            'foo' => [
                'name' => 'foo',
                'type' => 'int',
            ],
            'bar' => [
                'name' => 'bar',
                'type' => 'short',
            ],
            'foobar' => [
                'name' => 'foobar',
                'type' => 'float',
            ],
        ];


        $dbmock->expects($this->any())
            ->method('get_columns')
            ->will($this->returnValue($db_columns));

        $dbmock->expects($this->any())
            ->method('get_indices')
            ->will($this->returnValue($current));

        $dbmock->expects($this->any())
            ->method('add_drop_constraint')
            ->will($this->returnCallback(
                function ($table, $definition, $drop = false) {
                    if ($definition['type'] == 'clustered') {
                        $definition['type'] = 'index'; // SQL Server uses clustered index, we need switch that back
                    }
                    return "ALTER TABLE $table " . ($drop ? 'DROP' : 'ADD') . ' ' . json_encode($definition);
                }
            ));

        $sql = SugarTestReflection::callProtectedMethod(
            $dbmock,
            'repairTableIndices',
            [$tablename1, $db_columns, $new, false]
        );

        if (!empty($query)) {
            $this->assertStringContainsString('ALTER TABLE ' . $tablename1, $sql);
            $this->assertStringContainsString($query, $sql);
        } else {
            $this->assertStringNotContainsString('ALTER TABLE ' . $tablename1, $sql);
        }
    }

    public function testAddColumn()
    {
        $tablename1 = 'test23_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename1,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                'foobar' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $tablename2 = 'test24_' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename2,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $res = $this->db->compareFieldInTables(
            'foobar',
            $tablename1,
            $tablename2
        );

        $this->assertEquals($res['msg'], 'not_exists_table2');

        $this->db->addColumn(
            $tablename2,
            [
                'foobar' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ]
        );

        $res = $this->db->compareFieldInTables(
            'foobar',
            $tablename1,
            $tablename2
        );

        $this->assertEquals($res['msg'], 'match');
    }

    public static function alterColumnDataProvider()
    {
        yield 'type conversion' => [
            'target' => [
                'name' => 'foobar',
                'type' => 'varchar',
                'len' => 255,
                'required' => true,
                'default' => 'sugar',
            ],
            'temp' => [
                'name' => 'foobar',
                'type' => 'int',
            ],
        ];

        yield 'modify default' => [
            'target' => [
                'name' => 'foobar',
                'type' => 'varchar',
                'len' => 255,
                'default' => 'kilroy',
            ],
            'temp' => [
                'name' => 'foobar',
                'type' => 'double',
                'default' => '99999',
            ],
        ];

        yield 'drop default' => [
            'target' => [
                'name' => 'foobar',
                'type' => 'varchar',
                'len' => 255,
            ],
            'temp' => [
                'name' => 'foobar',
                'type' => 'double',
                'default' => '99999',
            ],
        ];

        // cannot reliably shrink columns in Oracle
        if (!DBManagerFactory::getInstance() instanceof OracleManager) {
            yield 'varchar shortening' => [
                'target' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => 255,
                    'required' => true,
                    'default' => 'sweet',
                ],
                'temp' => [
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => 1500,
                ],
            ];
            yield 'clob(65K) to clob(2M) conversion' => [
                'target' => [
                    'name' => 'foobar',
                    'type' => 'longtext',
                    'required' => true,
                ],
                'temp' => [
                    'name' => 'foobar',
                    'type' => 'text',
                    'default' => 'dextrose',
                ],
            ];
        }

        yield 'int-to-double' => [
            'target' => [
                'name' => 'foobar',
                'type' => 'double',
                'required' => true,
            ],
            'temp' => [
                'name' => 'foobar',
                'type' => 'int',
                'default' => 0,
            ],
        ];
    }

    /**
     * @dataProvider alterColumnDataProvider
     * @param mixed[] $target
     * @param mixed[] $temp
     */
    public function testAlterColumn(array $target, array $temp)
    {
        $foo_col = ['name' => 'foo', 'type' => 'varchar', 'len' => '255']; // Common column between tables

        $tablebase = 'alter_column_';

        $t1 = $tablebase . 'A';
        $t2 = $tablebase . 'B';
        $this->createTableParams(
            $t1,
            ['foo' => $foo_col, 'foobar' => $target],
            []
        );
        $this->createTableParams(
            $t2,
            ['foo' => $foo_col, 'foobar' => $temp],
            []
        );

        $res = $this->db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals(
            'no_match',
            $res['msg'],
            "testAlterColumn table columns match while they shouldn't for table $t1 and $t2: "
            . print_r($res, true)
        );

        $this->db->alterColumn($t2, ['foobar' => $target]);

        $res = $this->db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals(
            'match',
            $res['msg'],
            "testAlterColumn table columns don't match while they should for table $t1 and $t2: "
            . print_r($res, true)
        );
    }

    public function testAlterColumnWithIndex()
    {
        $this->createTableParams('alter_column_with_index', [
            'name' => [
                'name' => 'name',
                'type' => 'varchar',
                'len' => 16,
            ],
        ], [
            [
                'type' => 'index',
                'name' => 'idx_alt_col_idx_name',
                'fields' => ['name'],
            ],
        ]);

        $columns = $this->db->get_columns('alter_column_with_index');
        $this->assertEquals(16, $columns['name']['len']);

        $this->db->alterColumn('alter_column_with_index', [
            'name' => 'name',
            'type' => 'varchar',
            'len' => 20,
        ]);

        $columns = $this->db->get_columns('alter_column_with_index');
        $this->assertEquals(20, $columns['name']['len']);
    }

    public function testOracleAlterVarchar2ToNumber()
    {
        $insertValue = '100';
        if (!($this->db instanceof OracleManager)) {
            $this->markTestSkipped('This test can run only on Oracle instance');
        }
        $params = [
            'foo' => [
                'name' => 'foo',
                'vname' => 'LBL_FOO',
                'type' => 'enum',
                'dbType' => 'varchar',
            ],
        ];
        $tableName = 'testVarchar2ToNumber' . random_int(0, mt_getrandmax());

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, []);

        $this->db->insertParams($tableName, $params, ['foo' => $insertValue]);

        $params = [
            'foo' => [
                'name' => 'foo',
                'vname' => 'LBL_FOO',
                'type' => 'enum',
                'dbType' => 'int',
            ],
        ];

        $this->db->repairTableParams($tableName, $params, [], true);

        $columns = $this->db->get_columns($tableName);
        $this->assertEquals('number', $columns['foo']['type']);

        $checkResult = $this->db->fetchOne('SELECT foo FROM ' . $tableName);
        $this->assertEquals($insertValue, $checkResult['foo']);
    }

    public function testDropTableName()
    {
        $tablename = 'test' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );
        $this->assertTrue(in_array($tablename, $this->db->getTablesArray()));

        $this->dropTableName($tablename);

        $this->assertFalse(in_array($tablename, $this->db->getTablesArray()));
    }

    public function testDisconnectAll()
    {
        DBManagerFactory::disconnectAll();
        $this->assertTrue($this->db->checkError('testDisconnectAll'));
        $this->db = DBManagerFactory::getInstance();
    }

    public function testQuery()
    {
        $beanIds = $this->createRecords(5);

        $result = $this->db->query("SELECT id From contacts where last_name = 'foobar'");
        if ($this->db instanceof MysqliManager) {
            $this->assertInstanceOf('Mysqli_result', $result);
        } else {
            $this->assertTrue(is_resource($result));
        }

        while ($row = $this->db->fetchByAssoc($result)) {
            $this->assertTrue(in_array($row['id'], $beanIds), "Id not found '{$row['id']}'");
        }

        $this->removeRecords($beanIds);
    }

    public function disabledLimitQuery()
    {
        $beanIds = $this->createRecords(5);
        $_REQUEST['module'] = 'contacts';
        $result = $this->db->limitQuery("SELECT id From contacts where last_name = 'foobar'", 1, 3);
        if ($this->db instanceof MysqliManager) {
            $this->assertInstanceOf('Mysqli_result', $result);
        } else {
            $this->assertTrue(is_resource($result));
        }

        while ($row = $this->db->fetchByAssoc($result)) {
            if ($row['id'][0] > 3 || $row['id'][0] < 0) {
                $this->assertFalse(in_array($row['id'], $beanIds), "Found {$row['id']} in error");
            } else {
                $this->assertTrue(in_array($row['id'], $beanIds), "Didn't find {$row['id']}");
            }
        }
        unset($_REQUEST['module']);
        $this->removeRecords($beanIds);
    }

    public function testLimitQueryOrderedByAlias()
    {
        $sql = <<<SQL
SELECT
    id,
    name name_alias
FROM
    accounts
WHERE
    deleted = 0
ORDER BY
    name_alias ASC
SQL;
        $result = $this->db->limitQuery($sql, 0, 1);
        $this->assertNotEmpty($result, $this->db->lastDbError());
    }

    public function testGetOne()
    {
        $beanIds = $this->createRecords(1);

        $id = $this->db->getOne("SELECT id From contacts where last_name = 'foobar'");
        $this->assertEquals($id, $beanIds[0]);

        // bug 38994
        if ($this->db->dbType == 'mysql') {
            $id = $this->db->getOne($this->db->limitQuerySql("SELECT id From contacts where last_name = 'foobar'", 0, 1));
            $this->assertEquals($id, $beanIds[0]);
        }

        $this->removeRecords($beanIds);
    }

    public function testGetFieldsArray()
    {
        $beanIds = $this->createRecords(1);

        $result = $this->db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        $fields = $this->db->getFieldsArray($result, true);

        $this->assertEquals(['id'], $fields);

        $this->removeRecords($beanIds);
    }

    public function testGetAffectedRowCount()
    {
        $beanIds = $this->createRecords(1);
        // need to keep $result
        $result = $this->db->query("DELETE From contacts where id = '{$beanIds[0]}'", false, '', false, true);
        $this->assertEquals(1, $this->db->getAffectedRowCount($result));
    }

    public function testFetchByAssoc()
    {
        $beanIds = $this->createRecords(1);

        $result = $this->db->query("SELECT id From contacts where id = '{$beanIds[0]}'");

        $row = $this->db->fetchByAssoc($result);

        $this->assertTrue(is_array($row));
        $this->assertEquals($row['id'], $beanIds[0]);

        $this->removeRecords($beanIds);
    }

    public function testDisconnect()
    {
        $this->db->disconnect();
        $this->assertTrue($this->db->checkError('testDisconnect'));
        $this->db = DBManagerFactory::getInstance();
    }

    public function testGetTablesArray()
    {
        $tablename = 'test' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $this->assertTrue($this->db->tableExists($tablename));
    }

    public function testVersion()
    {
        $ver = $this->db->version();

        $this->assertTrue(is_string($ver));
    }

    public function testTableExists()
    {
        $tablename = 'test' . random_int(0, mt_getrandmax());
        $this->createTableParams(
            $tablename,
            [
                'foo' => [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
            ],
            []
        );

        $this->assertTrue(in_array($tablename, $this->db->getTablesArray()));
    }

    public function providerCompareVardefs()
    {
        $returnArray = [
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
                true,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                ],
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                false,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                ],
                [
                    'name' => 'foo',
                    'len' => '255',
                ],
                false,
            ],
            [
                [
                    'name' => 'foo',
                    'len' => '255',
                ],
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                true,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                [
                    'name' => 'FOO',
                    'type' => 'varchar',
                    'len' => '255',
                ],
                true,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'decimal',
                    'len' => '16,6',
                    'default' => '0.000000',
                ],
                [
                    'name' => 'foo',
                    'type' => 'decimal',
                    'default' => '',
                    'no_default' => '',
                    'len' => '16,6',
                    'size' => '20',
                    'precision' => '6',
                ],
                true,
            ],
            [
                [
                    'name' => 'foo',
                    'type' => 'decimal',
                    'len' => '16,6',
                    'default' => '0.000000',
                ],
                [
                    'name' => 'foo',
                    'type' => 'decimal',
                    'default' => '0',
                    'no_default' => '',
                    'len' => '16,6',
                    'size' => '20',
                    'precision' => '6',
                ],
                true,
            ],
            [
                [
                    'name' => 'solution_number',
                    'type' => 'int',
                    'len' => '11',
                    'auto_increment' => '1',
                    'required' => 'true',
                ],
                [
                    'name' => 'solution_number',
                    'type' => 'int',
                    'len' => '11',
                    'auto_increment' => 'true',
                    'required' => 'true',
                    'autoinc_next' => '51',
                    'dbType' => 'int',
                ],
                true,
            ],
        ];

        return $returnArray;
    }

    /**
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1, $fieldDef2, $expectedResult)
    {
        if ($expectedResult) {
            $this->assertTrue($this->db->compareVarDefs($fieldDef1, $fieldDef2));
        } else {
            $this->assertFalse($this->db->compareVarDefs($fieldDef1, $fieldDef2));
        }
    }

    /**
     * @ticket 34892
     */
    public function test_Bug34892_MssqlNotClearingErrorResults()
    {
        // execute a bad query
        $this->db->query('select dsdsdsdsdsdsdsdsdsd', false, 'test_Bug34892_MssqlNotClearingErrorResults', true);
        // assert it found an error
        $this->assertNotEmpty($this->db->lastError(), 'lastError should return true as a result of the previous illegal query');
        // now, execute a good query
        $this->db->query('select * from config');
        // and make no error messages are asserted
        $this->assertEmpty($this->db->lastError(), 'lastError should have cleared the previous error and return false of the last legal query');
    }

    public function vardefProvider()
    {
        $GLOBALS['log']->info('DBManagerTest.vardefProvider: _db = ' . print_r($this->db));
        $this->setUp(); // Just in case the DB driver is not created yet.
        $emptydate = $this->db->emptyValue('date');
        $emptytime = $this->db->emptyValue('time');
        $emptydatetime = $this->db->emptyValue('datetime');

        return [
            [
                'testid',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'varchar',
                        'required' => true,
                    ],
                ],
                ['id' => 'test123'],
                ['id' => "'test123'"],
            ],
            [
                'testtext',
                [
                    'text1' => [
                        'name' => 'text1',
                        'type' => 'varchar',
                        'required' => true,
                    ],
                    'text2' => [
                        'name' => 'text2',
                        'type' => 'varchar',
                    ],
                ],
                [],
                [
                    'text1' => "''",
                    'text2' => 'NULL',
                ],
                [],
            ],
            [
                'testtext2',
                [
                    'text1' => [
                        'name' => 'text1',
                        'type' => 'varchar',
                        'required' => true,
                    ],
                    'text2' => [
                        'name' => 'text2',
                        'type' => 'varchar',
                    ],
                ],
                ['text1' => 'foo', 'text2' => 'bar'],
                ['text1' => "'foo'", 'text2' => "'bar'"],
            ],
            [
                'testreq',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'varchar',
                        'required' => true,
                    ],
                    'intval' => [
                        'name' => 'intval',
                        'type' => 'int',
                        'required' => true,
                    ],
                    'floatval' => [
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required' => true,
                    ],
                    'money' => [
                        'name' => 'money',
                        'type' => 'currency',
                        'required' => true,
                    ],
                    'test_dtm' => [
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required' => true,
                    ],
                    'test_dtm2' => [
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required' => true,
                    ],
                    'test_dt' => [
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required' => true,
                    ],
                    'test_tm' => [
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required' => true,
                    ],
                ],
                [
                    'id' => 'test123',
                    'intval' => 42,
                    'floatval' => 42.24,
                    'money' => 56.78,
                    'test_dtm' => '2002-01-02 12:34:56',
                    'test_dtm2' => '2011-10-08 01:02:03',
                    'test_dt' => '1998-10-04', 'test_tm' => '03:04:05',
                ],
                [
                    'id' => "'test123'",
                    'intval' => 42,
                    'floatval' => 42.24,
                    'money' => 56.78,
                    'test_dtm' => $this->db->convert('\'2002-01-02 12:34:56\'', 'datetime'),
                    'test_dtm2' => $this->db->convert('\'2011-10-08 01:02:03\'', 'datetime'),
                    'test_dt' => $this->db->convert('\'1998-10-04\'', 'date'),
                    'test_tm' => $this->db->convert('\'03:04:05\'', 'time'),
                ],
            ],
            [
                'testreqnull',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'varchar',
                        'required' => true,
                    ],
                    'intval' => [
                        'name' => 'intval',
                        'type' => 'int',
                        'required' => true,
                    ],
                    'floatval' => [
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required' => true,
                    ],
                    'money' => [
                        'name' => 'money',
                        'type' => 'currency',
                        'required' => true,
                    ],
                    'test_dtm' => [
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required' => true,
                    ],
                    'test_dtm2' => [
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required' => true,
                    ],
                    'test_dt' => [
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required' => true,
                    ],
                    'test_tm' => [
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required' => true,
                    ],
                ],
                [],
                [
                    'id' => "''",
                    'intval' => 0,
                    'floatval' => 0,
                    'money' => 0,
                    'test_dtm' => "$emptydatetime",
                    'test_dtm2' => "$emptydatetime",
                    'test_dt' => "$emptydate",
                    'test_tm' => "$emptytime",
                ],
                [],
            ],
            [
                'testnull',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'varchar',
                    ],
                    'intval' => [
                        'name' => 'intval',
                        'type' => 'int',
                    ],
                    'floatval' => [
                        'name' => 'floatval',
                        'type' => 'decimal',
                    ],
                    'money' => [
                        'name' => 'money',
                        'type' => 'currency',
                    ],
                    'test_dtm' => [
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                    ],
                    'test_dtm2' => [
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                    ],
                    'test_dt' => [
                        'name' => 'test_dt',
                        'type' => 'date',
                    ],
                    'test_tm' => [
                        'name' => 'test_tm',
                        'type' => 'time',
                    ],
                ],
                [
                    'id' => 123,
                ],
                [
                    'id' => "'123'",
                    'intval' => 'NULL',
                    'floatval' => 'NULL',
                    'money' => 'NULL',
                    'test_dtm' => 'NULL',
                    'test_dtm2' => 'NULL',
                    'test_dt' => 'NULL',
                    'test_tm' => 'NULL',
                ],
                [],
            ],
            [
                'testempty',
                [
                    'id' => [
                        'name' => 'id',
                        'type' => 'varchar',
                    ],
                    'intval' => [
                        'name' => 'intval',
                        'type' => 'int',
                    ],
                    'floatval' => [
                        'name' => 'floatval',
                        'type' => 'decimal',
                    ],
                    'money' => [
                        'name' => 'money',
                        'type' => 'currency',
                    ],
                    'test_dtm' => [
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                    ],
                    'test_dtm2' => [
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                    ],
                    'test_dt' => [
                        'name' => 'test_dt',
                        'type' => 'date',
                    ],
                    'test_tm' => [
                        'name' => 'test_tm',
                        'type' => 'time',
                    ],
                    'text_txt' => [
                        'name' => 'test_txt',
                        'type' => 'varchar',
                    ],
                ],
                [
                    'id' => '',
                    'intval' => '',
                    'floatval' => '',
                    'money' => '',
                    'test_dtm' => '',
                    'test_dtm2' => '',
                    'test_dt' => '',
                    'test_tm' => '',
                    'text_txt' => null,
                ],
                [
                    'id' => "''",
                    'intval' => 'NULL',
                    'floatval' => 'NULL',
                    'money' => 'NULL',
                    'test_dtm' => 'NULL',
                    'test_dtm2' => 'NULL',
                    'test_dt' => 'NULL',
                    'test_tm' => 'NULL',
                    'test_txt' => 'NULL',
                ],
                [
                    'intval' => 'NULL',
                    'floatval' => 'NULL',
                    'money' => 'NULL',
                    'test_dtm' => 'NULL',
                    'test_dtm2' => 'NULL',
                    'test_dt' => 'NULL',
                    'test_tm' => 'NULL',
                ],
            ],
        ];
    }

    /**
     * Test the canInstall
     * @return void
     */
    public function testCanInstall()
    {
        $DBManagerClass = get_class($this->db);
        if (!method_exists($this->db, 'version') || !method_exists($this->db, 'canInstall')) {
            $this->markTestSkipped(
                "Class {$DBManagerClass} doesn't implement canInstall or version methods"
            );
        }

        $method = new ReflectionMethod($DBManagerClass, 'canInstall');
        if ($method->class == 'DBManager') {
            $this->markTestSkipped(
                "Class {$DBManagerClass} or one of it's ancestors doesn't override DBManager's canInstall"
            );
        }

        // First assuming that we are only running unit tests against a supported database :)
        $this->assertTrue($this->db->canInstall(), 'Apparently we are not running this unit test against a supported database!!!');

        $DBstub = $this->getMockBuilder($DBManagerClass)->setMethods(['version'])->getMock();
        $DBstub->expects($this->any())
            ->method('version')
            ->will($this->returnValue('0.0.0')); // Expect that any supported version is higher than 0.0.0

        $this->assertTrue(is_array($DBstub->canInstall()), 'Apparently we do support version 0.0.0 in ' . $DBManagerClass);
    }

    public function providerValidateQuery()
    {
        return [
            [true, 'SELECT * FROM accounts'],
            [false, 'SELECT * FROM blablabla123'],
        ];
    }

    /**
     * Test query validation
     * @dataProvider providerValidateQuery
     * @param $good
     * @param $sql
     * @return void
     */
    public function testValidateQuery($good, $sql)
    {
        $check = $this->db->validateQuery($sql);
        $this->assertEquals($good, $check);
    }

    public function testTextSizeHandling()
    {
        $tablename = 'testTextSize';
        $fielddefs = [
            'id' => [
                'name' => 'id',
                'required' => true,
                'type' => 'id',
            ],
            'test' => [
                'name' => 'test',
                'type' => 'longtext',
            ],
            'dummy' => [
                'name' => 'dummy',
                'type' => 'longtext',
            ],
        ];

        $this->createTableParams($tablename, $fielddefs, []);

        $str = str_repeat('x', 131072);

        $size = strlen($str);
        $this->db->insertParams($tablename, $fielddefs, ['id' => $size, 'test' => $str, 'dummy' => $str]);

        $select = "SELECT test FROM $tablename WHERE id = '{$size}'";
        $strresult = $this->db->getOne($select);

        $this->assertEquals(0, mb_strpos($str, $strresult), 'String returned from temp table did not match data just written');
    }

    public function testGetIndicesContainsPrimary()
    {
        $indices = $this->db->get_indices('accounts');

        // find if any are primary
        $found = false;

        foreach ($indices as $index) {
            if ($index['type'] == 'primary') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Module');
    }

    /*
     * testDBGuidGeneration
     * Tests that the first 1000 DB generated GUIDs are unique
     */
    public function testDBGuidGeneration()
    {
        $guids = [];
        $sql = "SELECT {$this->db->getGuidSQL()} {$this->db->getFromDummyTable()}";
        for ($i = 0; $i < 1000; $i++) {
            $newguid = $this->db->getOne($sql);
            $this->assertFalse(in_array($newguid, $guids), "'$newguid' already existed in the array of GUIDs!");
            $guids [] = $newguid;
        }
    }

    public function testAddPrimaryKey()
    {
        $tablename = 'testConstraints';
        $fielddefs = [
            'id' => [
                'name' => 'id',
                'required' => true,
                'type' => 'id',
            ],
            'test' => [
                'name' => 'test',
                'type' => 'longtext',
            ],
        ];

        $this->createTableParams($tablename, $fielddefs, []);
        unset($this->created[$tablename]); // that table is required by testRemovePrimaryKey test

        $sql = $this->db->add_drop_constraint(
            $tablename,
            [
                'name' => 'testConstraints_pk',
                'type' => 'primary',
                'fields' => ['id'],
            ],
            false
        );

        $result = $this->db->query($sql);

        $indices = $this->db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach ($indices as $index) {
            if ($index['type'] == 'primary') {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Table');
    }

    /**
     * @depends testAddPrimaryKey
     */
    public function testRemovePrimaryKey()
    {
        $tablename = 'testConstraints';
        $this->created[$tablename] = true;

        $sql = $this->db->add_drop_constraint(
            $tablename,
            [
                'name' => 'testConstraints_pk',
                'type' => 'primary',
                'fields' => ['id'],
            ],
            true
        );

        $result = $this->db->query($sql);

        $indices = $this->db->get_indices($tablename);

        // find if any are primary
        $found = false;

        foreach ($indices as $index) {
            if ($index['type'] == 'primary') {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found, 'Primary Key Found On Table');
    }


    private function addChildren($tableName, $parent_id, $parent_name, $number, $level, $stoplevel)
    {
        if ($level >= $stoplevel) {
            return;
        }
        for ($sibling = 0; $sibling < $number; $sibling++) {
            $id = create_guid();
            $name = "{$parent_name}_{$sibling}";
            $this->addRecord($tableName, $id, $parent_id, $name, $level);
            $this->addChildren($tableName, $id, $name, $number, $level + 1, $stoplevel);
        }
    }

    private function addRecord($tableName, $id, $parent_id, $name, $level)
    {
        $this->db->query(sprintf(
            'INSERT INTO %s (id, parent_id, name, db_level) VALUES (%s, %s, %s, %s)',
            $tableName,
            $this->db->quoted($id),
            ($parent_id !== null ? $this->db->quoted($parent_id) : 'NULL'),
            $this->db->quoted($name),
            $this->db->quoted($level)
        ));
    }

    private function setupRecursiveStructure($tableName)
    {
        $params = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
                'required' => true,
            ],
            'parent_id' => [
                'name' => 'parent_id',
                'type' => 'id',
            ],
            'name' => [
                'name' => 'name',
                'type' => 'varchar',
                'len' => '20',
            ],
            'db_level' => [  // For verification purpose
                'name' => 'db_level',
                'type' => 'int',
            ],
        ];
        $indexes = [
            [
                'name' => 'idx_' . $tableName . '_id',
                'type' => 'primary',
                'fields' => ['id'],
            ],
            [
                'name' => 'idx_' . $tableName . 'parent_id',
                'type' => 'index',
                'fields' => ['parent_id'],
            ],
        ];

        $this->createTableParams($tableName, $params, $indexes);

        // Load data
        $id = create_guid();
        $name = '1';
        $this->addRecord($tableName, $id, null, $name, 0);
        $this->addChildren($tableName, $id, $name, 2, 1, 10);
    }

    public function providerRecursiveQuery()
    {
        return [
            ['1_0_0_0_0_0_0_0_0_0', '9', 1],
            ['1_1_1_1_1_1_1_1_1_1', '9', 1],
            ['1_0_0_0_0_0_0_0_0', '8', 3],
            ['1_1_1_1_1_1_1_1', '7', 7],
            ['1_0_0_0_0_0_0', '6', 15],
            ['1_1_1_1_1_1', '5', 31],
            ['1_0_0_0_0', '4', 63],
            ['1_1_1_1', '3', 127],
            ['1_0_0', '2', 255],
            ['1_1', '1', 511],
            ['1', '0', 1023],
        ];
    }

    /**
     * @dataProvider providerRecursiveQuery
     * @group hierarchy
     * @param $startName
     * @param $startDbLevel
     * @param $nrchildren
     */
    public function testRecursiveQuery($startName, $startDbLevel, $nrchildren)
    {
        // Skip this test for MySQL8+ as 8+ doesn't support inline variables used in getRecursiveSelectSQL
        if ($this->isMysqlVersionAtLeast8()) {
            $this->markTestSkipped('skipped for MySQL8+');
        }

        $this->db->preInstall();

        // setup test table and fill it with data if it doesn't already exist
        $table = 'test_recursive';
        if (!$this->db->tableExists($table)) {
            $this->setupRecursiveStructure($table);
        }

        $startId = $currentId = $this->db->getOne('SELECT id FROM ' . $table . ' WHERE name = '
            . $this->db->quoted($startName));
        $levels = $startDbLevel;

        // Testing lineage
        $lineageSQL = $this->db->getRecursiveSelectSQL(
            $table,
            'id',
            'parent_id',
            'id, parent_id, name, db_level',
            true,
            'id = ' . $this->db->quoted($startId)
        );

        $result = $this->db->query($lineageSQL);

        $currentName = null;
        while ($row = $this->db->fetchByAssoc($result)) {
            $currentName = $row['name'];
            $this->assertEquals($currentId, $row['id'], 'Incorrect ID found');
            if (!empty($row['parent_id'])) {
                $currentId = $row['parent_id'];
            }
            $this->assertEquals($levels--, $row['db_level'], 'Incorrect level found');
        }
        $this->assertEquals('1', $currentName, 'Incorrect top node name');
        $this->assertEquals(0, $levels + 1, 'Incorrect end level'); //Compensate for extra -1 after last node level assert

        // Testing children
        $childcount = 0;
        $childrenSQL = $this->db->getRecursiveSelectSQL(
            $table,
            'id',
            'parent_id',
            // select ID even if we don't need it because the MSSQL implementation will use if (or parent ID)
            // internally depending on the value of $lineage (probably should be fixed)
            'id, parent_id, name',
            false,
            'id = ' . $this->db->quoted($startId)
        );

        $result = $this->db->query($childrenSQL);

        while (($row = $this->db->fetchByAssoc($result)) != null) {
            $this->assertStringStartsWith(
                $startName,
                $row['name'],
                'Row id doesn\'t start with starting name as expected'
            );
            $childcount++;
        }
        $this->assertEquals($nrchildren, $childcount, 'Number of found descendants does not match expectations');
    }

    // Inserts a 2D array of data into the specified table
    // First row of array must be the column headers, first column must be PK column name
    public function insertTableArray($tableName, $tableDataArray)
    {
        $sqlPrefix = "INSERT INTO $tableName ( {$tableDataArray[0][0]} ";  // has to be at least one column
        for ($col = 1; $col < safeCount($tableDataArray[0]); $col++) {
            $sqlPrefix .= ", {$tableDataArray[0][$col]}";
        }
        $sqlPrefix .= ') VALUES (';
        $sqlPostfix = ')';
        $sqlBody = '';

        // do the inserts for each row of data
        for ($row = 1; $row < safeCount($tableDataArray); $row++) {
            $rowData = $tableDataArray[$row];
            $sqlBody = "'{$rowData[0]}'";
            for ($col = 1; $col < safeCount($rowData); $col++) {
                $sqlBody .= ",'{$rowData[$col]}'";
            }

            // Insert the data
            $sql = $sqlPrefix . $sqlBody . $sqlPostfix;
            $result = $this->db->query($sql);
        }
    }

    // Deletes a 2D array of data from the specified table
    // First row of array must be column headers, first column must be PK column name
    public function deleteTableArray($tableName, $tableDataArray)
    {
        $sql = "DELETE FROM $tableName WHERE {$tableDataArray[0][0]} IN ( '{$tableDataArray[1][0]}'";  // has to be at least one column
        for ($row = 2; $row < safeCount($tableDataArray); $row++) {
            $sql .= ",'{$tableDataArray[$row][0]}'";
        }
        $sql .= ')';

        // Delete the data
        $result = $this->db->query($sql);
    }

    public function testRecursiveQueryMultiHierarchy()
    {
        // Skip this test for MySQL8+ as 8+ doesn't support inline variables used in getRecursiveSelectSQL
        if ($this->isMysqlVersionAtLeast8()) {
            $this->markTestSkipped('skipped for MySQL8+');
        }

        if (!$this->db->supports('recursive_query')) {
            $this->markTestSkipped('DBManager does not support recursive query');
        }

        $this->db->preInstall();

        // Setup test data
        $tableName = 'forecast_tree';
        $this->assertTrue($this->db->tableExists($tableName), "Table $tableName does not exist");
        $tableDataArray = [['id', 'parent_id', 'name', 'hierarchy_type', 'user_id']
            , ['sales_test_1', null, 'sales1', 'sales_test', 'user1']
            , ['sales_test_11', 'sales_test_1', 'sales11', 'sales_test', 'user11']
            , ['sales_test_12', 'sales_test_1', 'sales12', 'sales_test', 'user12']
            , ['sales_test_13', 'sales_test_1', 'sales13', 'sales_test', 'user13']
            , ['sales_test_121', 'sales_test_12', 'sales121', 'sales_test', 'user121']
            , ['sales_test_122', 'sales_test_12', 'sales122', 'sales_test', 'user122']
            , ['sales_test_131', 'sales_test_13', 'sales131', 'sales_test', 'user131']
            , ['sales_test_132', 'sales_test_13', 'sales132', 'sales_test', 'user132']
            , ['sales_test_133', 'sales_test_13', 'sales133', 'sales_test', 'user133']
            , ['prod_test_1', null, 'prod1', 'prod_test', 'user1']
            , ['prod_test_11', 'prod_test_1', 'prod11', 'prod_test', 'user11']
            , ['prod_test_12', 'prod_test_1', 'prod12', 'prod_test', 'user12']
            , ['prod_test_13', 'prod_test_1', 'prod13', 'prod_test', 'user13']
            , ['prod_test_121', 'prod_test_12', 'prod121', 'prod_test', 'user121']
            , ['prod_test_122', 'prod_test_12', 'prod122', 'prod_test', 'user122']
            , ['prod_test_131', 'prod_test_13', 'prod131', 'prod_test', 'user131']
            , ['prod_test_132', 'prod_test_13', 'prod132', 'prod_test', 'user132']
            , ['prod_test_133', 'prod_test_13', 'prod133', 'prod_test', 'user133']
            , ['prod_test_1321', 'prod_test_132', 'prod1321', 'prod_test', 'user1321'],
        ];

        $this->insertTableArray($tableName, $tableDataArray);

        // idStarting, Up/Down, Forecast_Tree Type, expected result count
        $resultsDataArray = [['sales_test_1', false, 'sales_test', 9]
            , ['sales_test_13', false, 'sales_test', 4]
            , ['sales_test_131', true, 'sales_test', 3]
            , ['sales_test_13', true, 'sales_test', 2]
            , ['sales_test_1', true, 'sales_test', 1]
            , ['prod_test_1', false, 'prod_test', 10]
            , ['prod_test_13', false, 'prod_test', 5]
            , ['prod_test_1321', true, 'prod_test', 4]
            , ['prod_test_133', true, 'prod_test', 3]
            , ['prod_test_1', true, 'prod_test', 1],
        ];

        // Loop through each test
        foreach ($resultsDataArray as $resultsRow) {
            // Get where clause
            $whereClause = "hierarchy_type='$resultsRow[2]'";

            // Get hierarchical result set
            $key = 'id';
            $parent_key = 'parent_id';
            $fields = 'id, parent_id';
            $lineage = $resultsRow[1];
            $startWith = "id = '{$resultsRow[0]}'";
            $level = null;

            $hierarchicalSQL = $this->db->getRecursiveSelectSQL($tableName, $key, $parent_key, $fields, $lineage, $startWith, $level, $whereClause);
            $result = $this->db->query($hierarchicalSQL);
            $resultsCnt = 0;

            while (($row = $this->db->fetchByAssoc($result)) != null) {
                $resultsCnt++;
            }

            $this->assertEquals($resultsCnt, $resultsRow[3], "Incorrect number or records. Found: $resultsCnt Expected: $resultsRow[3] for ID: $resultsRow[0]");
        }

        // remove data from table
        $result = $this->deleteTableArray($tableName, $tableDataArray);
    }

    /**
     * @group dbmanager
     * @group db
     *
     * @dataProvider providerTestMassageValueEmptyValue
     */
    public function testMassageValueEmptyValue($fieldDef, $value, $expected)
    {
        $return = $this->db->massageValue($value, $fieldDef);
        $this->assertEquals($expected, $return);
    }

    public function providerTestMassageValueEmptyValue()
    {
        return [
            [
                [
                    'name' => 'float with empty value and required is true',
                    'type' => 'float',
                    'required' => true,
                ],
                '',
                'NULL',
            ],
            [
                [
                    'name' => 'float with empty value and required is true and isnull is false',
                    'type' => 'float',
                    'required' => true,
                    'isnull' => false,
                ],
                '',
                0,
            ],
            [
                [
                    'name' => 'float with empty value and required is false',
                    'type' => 'float',
                ],
                '',
                'NULL',
            ],
            [
                [
                    'name' => 'enum type with empty value and required is true',
                    'type' => 'enum',
                    'required' => true,
                ],
                null,
                'NULL',
            ],
            [
                [
                    'name' => 'enum type with empty value and required is false',
                    'type' => 'enum',
                    'required' => false,
                ],
                null,
                'NULL',
            ],
            [
                [
                    'name' => 'date type with empty value and required is true',
                    'type' => 'date',
                    'required' => true,
                ],
                '',
                'NULL',
            ],
            [
                [
                    'name' => 'date type with empty value and required is false',
                    'type' => 'date',
                    'required' => false,
                ],
                '',
                'NULL',
            ],
        ];
    }

    public function searchStringProvider()
    {
        return [
            [
                'wildcard' => '%',
                'inFront' => false,
                'search' => 'test*test2',
                'expected' => 'test*test2%',
            ],
            [
                'wildcard' => '*',
                'inFront' => false,
                'search' => 'test*test2',
                'expected' => 'test%test2%',
            ],
            [
                'wildcard' => '%',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test*test2%',
            ],
            [
                'wildcard' => '*',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test%test2%',
            ],
            [
                'wildcard' => '',
                'inFront' => true,
                'search' => 'test*test2',
                'expected' => '%test*test2%',
            ],
        ];
    }

    /**
     * Unit test for DBManager::sqlLikeString().
     *
     * @dataProvider searchStringProvider
     * @param $wildcard string The sugar config wildcard character.
     * @param $inFront bool The sugar config to prepend the search string with a wildcard.
     * @param $search string The search query string.
     * @param $expected string The expected return value upon calling DBManager::sqlLikeString().
     */
    public function testSqlLikeString($wildcard, $inFront, $search, $expected)
    {
        $defaultConfigWildcard = $GLOBALS['sugar_config']['search_wildcard_char'];
        $defaultWildcardInFront = $GLOBALS['sugar_config']['search_wildcard_infront'];

        $GLOBALS['sugar_config']['search_wildcard_char'] = $wildcard;
        $GLOBALS['sugar_config']['search_wildcard_infront'] = $inFront;

        $str = $this->db->sqlLikeString($search);
        $this->assertEquals($expected, $str);

        $GLOBALS['sugar_config']['search_wildcard_char'] = $defaultConfigWildcard;
        $GLOBALS['sugar_config']['search_wildcard_infront'] = $defaultWildcardInFront;
    }

    /**
     * Returns def and its expectation
     *
     * @return array
     */
    public static function getTypeForOneColumnSQLRep()
    {
        return [
            [
                [
                    'name' => 'test',
                    'type' => 'date',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'float',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => '',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'date',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'float',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => 'not-empty',
                ],
                'once',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'date',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'datetime',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'encrypt',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'dropdown',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'decimal',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'float',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
            [
                [
                    'name' => 'test',
                    'type' => 'integer',
                    'default' => 'not-empty',
                    'no_default' => true,
                ],
                'never',
            ],
        ];
    }

    /**
     * Testing that massageValue is called only when we need that
     *
     * @dataProvider getTypeForOneColumnSQLRep
     */
    public function testOneColumnSQLRep($fieldDef, $expected)
    {
        $db = $this->getMockBuilder(get_class($this->db))->setMethods(['massageValue'])->getMock();
        $method = $db->expects($this->$expected())->method('massageValue');
        if ($expected != 'never') {
            $method->with($this->equalTo($fieldDef['default']), $this->equalTo($fieldDef))->will($this->returnValue('correct'));
        }

        $result = SugarTestReflection::callProtectedMethod($db, 'oneColumnSQLRep', [$fieldDef]);
        if ($expected == 'once') {
            $this->assertStringContainsString('correct', $result);
        } elseif ($expected == 'never') {
            $this->assertStringNotContainsString('correct', $result);
        }
    }

    public function lengthTestProvider()
    {
        $data = [
            [
                ['len' => '5'],
                ['len' => '4', 'precision' => '2'],
                '7,2',
            ],
            [
                ['len' => '4'],
                ['len' => '12', 'precision' => '2'],
                '12,2',
            ],
            [
                ['type' => 'decimal', 'len' => '10', 'precision' => '6'],
                ['len' => '12', 'precision' => '2'],
                '12,6',
            ],
            [
                ['type' => 'decimal', 'len' => '12', 'precision' => '6'],
                ['len' => '14', 'precision' => '6'],
                '14,6',
            ],
            [
                ['len' => '4,2', 'precision' => '2', 'type' => 'decimal'],
                ['len' => '26,2', 'precision' => '2'],
                '26,2',
            ],
        ];

        $result = [];
        foreach ([
                     'MysqliManager',
                     'SqlsrvManager',
                     'IBMDB2Manager',
                 ] as $driver) {
            foreach ($data as $item) {
                $item[] = $driver;
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @ticket BR-1787
     * @group unit
     * @dataProvider lengthTestProvider
     */
    public function testChangeFieldLength($dbcol, $vardefcol, $result, $driver)
    {
        DBManagerFactory::getDbDrivers(); // load the drivers
        $DBManagerClass = get_class($this->db);
        $db_columns = [
            'id' => ['name' => 'id', 'type' => 'char', 'len' => '36'],
            'quantity' => ['name' => 'quantity', 'type' => 'int', 'len' => '5'],
        ];
        $db_columns['quantity'] = array_merge($db_columns['quantity'], $dbcol);

        $vardefs = [
            'id' => ['name' => 'id', 'type' => 'id', 'len' => '36'],
            'quantity' => ['name' => 'quantity', 'type' => 'decimal', 'len' => '4', 'precision' => '2'],
        ];
        $vardefs['quantity'] = array_merge($vardefs['quantity'], $vardefcol);

        // Oracle currently forces decimals to be 20,2 - can't test here
        $dbmock = $this->getMockBuilder($driver)
            ->setMethods(['get_columns', 'get_field_default_constraint_name', 'get_indices', 'checkIdentity'])
            ->getMock();
        if (!($dbmock instanceof DBManager)) {
            // Failed to instantiate the driver, skip it
            $this->markTestSkipped("Could not load driver for $driver");
        }
        $dbmock->expects($this->any())
            ->method('get_columns')
            ->will($this->returnValue($db_columns));
        $dbmock->expects($this->any())
            ->method('get_field_default_constraint_name')
            ->will($this->returnValue([]));
        $dbmock->expects($this->any())
            ->method('get_indices')
            ->will($this->returnValue([]));
        $dbmock->expects($this->any())
            ->method('checkIdentity')
            ->will($this->returnValue(false));

        $sql = SugarTestReflection::callProtectedMethod($dbmock, 'repairTableColumns', ['faketable', $vardefs, false]);
        $this->assertMatchesRegularExpression("#quantity.*?decimal\\($result\\)#i", $sql);
    }

    public function isNullableProvider()
    {
        return [
            [
                [
                    'name' => 'id',
                    'vname' => 'LBL_TAG_NAME',
                    'type' => 'id',
                    'len' => '36',
                    'required' => true,
                    'reportable' => false,
                ],
                false,
            ],
            [
                [
                    'name' => 'parent_tag_id',
                    'vname' => 'LBL_PARENT_TAG_ID',
                    'type' => 'id',
                    'len' => '36',
                    'required' => false,
                    'reportable' => false,
                ],
                true,
            ],
            [
                [
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'dbType' => 'id',
                    'len' => '36',
                    'required' => false,
                    'reportable' => false,
                ],
                true,
            ],
            [
                [
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'type' => 'id',
                    'len' => '36',
                    'reportable' => false,
                ],
                true,
            ],
            [
                [
                    'name' => 'any_id',
                    'vname' => 'LBL_ANY_ID',
                    'dbType' => 'id',
                    'len' => '36',
                    'reportable' => false,
                ],
                true,
            ],
        ];
    }

    /**
     * @ticket PAT-579
     * @dataProvider isNullableProvider
     */
    public function testIsNullable($vardef, $isNullable)
    {
        $this->assertEquals($isNullable, SugarTestReflection::callProtectedMethod($this->db, 'isNullable', [$vardef]));
    }

    private function setupInsertStructure()
    {
        // create test table for operational testing
        $tableName = 'test_insert';
        $params = [
            'id' => [
                'name' => 'id',
                'type' => 'id',
                'required' => true,
            ],
            'col1' => [
                'name' => 'col1',
                'type' => 'varchar',
                'len' => '100',
            ],
            'col2' => [
                'name' => 'col2',
                'type' => 'text',
                'len' => '200000',
            ],
            'col3' => [
                'name' => 'col3',
                'type' => 'date',
            ],
        ];
        $indexes = [
            [
                'name' => 'idx_' . $tableName . '_id',
                'type' => 'primary',
                'fields' => ['id'],
            ],
        ];
        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, $indexes);

        return [
            'tableName' => $tableName,
            'params' => $params,
        ];
    }

    public function providerInsert()
    {
        return [
            [
                [
                    'id' => 1,
                    'col1' => 'col1 data',
                    'col2' => 'col2 data',
                    'col3' => '2012-12-31',
                ],
            ],
            [
                [
                    'id' => 2,
                    'col1' => '2',
                    'col2' => 'col2 data',
                    'col3' => '2012-12-31',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerInsert
     * @param $data
     */
    public function testInsertParams($data)
    {
        $dataStructure = $this->setupInsertStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];
        $this->assertTrue($this->db->insertParams($tableName, $params, $data));
        $resultsCntExpected = 1;

        $result = $this->db->query("SELECT * FROM $tableName");
        $resultsCnt = 0;
        while (($row = $this->db->fetchByAssoc($result)) != null) {
            $resultsCnt++;
        }
        $this->assertEquals($resultsCnt, $resultsCntExpected, "Incorrect number or records. Found: $resultsCnt Expected: $resultsCntExpected");
    }

    public function testInsertBlob()
    {
        $dataStructure = $this->setupInsertStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];
        $this->db->query("DELETE FROM $tableName");

        $blobData = '0123456789abcdefghijklmnopqrstuvwxyz';
        while (strlen($blobData) < 100000) {
            $blobData .= $blobData;
        }

        $data = ['id' => '1', 'col1' => '10', 'col2' => $blobData];
        $this->assertTrue($this->db->insertParams($tableName, $params, $data));

        $result = $this->db->query("SELECT * FROM $tableName");
        $row = $this->db->fetchByAssoc($result);
        $foundLen = strlen($row['col2']);
        $expectedLen = strlen($blobData);
        $this->assertEquals($row['col2'], $blobData, "Failed test writing blob data. Found: $foundLen chars, Expected: $expectedLen");
    }

    public function testInsertUpdateBean()
    {
        // insert test
        $bean = new Contact();
        $bean->last_name = 'foobar' . random_int(0, mt_getrandmax());
        $bean->id = create_guid();
        $bean->description = 'description' . random_int(0, mt_getrandmax());
        $bean->new_with_id = true;
        $this->db->insert($bean);

        $result = $this->db->query("select id, last_name, description from contacts where id = '{$bean->id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals($bean->last_name, $row['last_name'], 'last_name failed');
        $this->assertEquals($bean->description, $row['description'], 'description failed');
        $this->assertEquals($bean->id, $row['id'], 'id failed');

        // update test
        $bean->last_name = 'newfoobar' . random_int(0, mt_getrandmax());   // change their lastname field
        $bean->description = 'newdescription' . random_int(0, mt_getrandmax());
        $this->db->update($bean);
        $result = $this->db->query("select id, last_name, description from contacts where id = '{$bean->id}'");
        $row = $this->db->fetchByAssoc($result);
        $this->assertEquals($bean->last_name, $row['last_name'], 'last_name failed');
        $this->assertEquals($bean->description, $row['description'], 'description failed');
        $this->assertEquals($bean->id, $row['id'], 'id failed');
    }

    private function setupDataTypesStructure()
    {
        // create test table for data type testing
        $tableName = 'test_types';
        $params = [
            'id' => ['name' => 'id', 'type' => 'id', 'required' => true],
            'int_param' => ['name' => 'int_param', 'type' => 'int', 'default' => 1],
            'double_param' => ['name' => 'double_param', 'type' => 'double', 'default' => 1],
            'float_param' => ['name' => 'float_param', 'type' => 'float', 'default' => 1],
            'uint_param' => ['name' => 'uint_param', 'type' => 'uint', 'default' => 1],
            'ulong_param' => ['name' => 'ulong_param', 'type' => 'ulong', 'default' => 1],
            'long_param' => ['name' => 'long_param', 'type' => 'long', 'default' => 1],
            'short_param' => ['name' => 'short_param', 'type' => 'short', 'default' => 1],
            'varchar_param' => ['name' => 'varchar_param', 'type' => 'varchar', 'default' => 'test'],
            'text_param' => ['name' => 'text_param', 'type' => 'text', 'default' => 'test'],
            'longtext_param' => ['name' => 'longtext_param', 'type' => 'longtext', 'default' => 'test'],
            'date_param' => ['name' => 'date_param', 'type' => 'date'],
            'enum_param' => ['name' => 'enum_param', 'type' => 'enum', 'default' => 'test'],
            'relate_param' => ['name' => 'relate_param', 'type' => 'relate', 'default' => 'test'],
            'multienum_param' => ['name' => 'multienum_param', 'type' => 'multienum', 'default' => 'test'],
            'html_param' => ['name' => 'html_param', 'type' => 'html', 'default' => 'test'],
            'longhtml_param' => ['name' => 'longhtml_param', 'type' => 'longhtml', 'default' => 'test'],
            'datetime_param' => ['name' => 'datetime_param', 'type' => 'datetime'],
            'datetimecombo_param' => ['name' => 'datetimecombo_param', 'type' => 'datetimecombo'],
            'time_param' => ['name' => 'time_param', 'type' => 'time'],
            'bool_param' => ['name' => 'bool_param', 'type' => 'bool'],
            'tinyint_param' => ['name' => 'tinyint_param', 'type' => 'tinyint'],
            'char_param' => ['name' => 'char_param', 'type' => 'char', 'default' => 'test'],
            'id_param' => ['name' => 'id_param', 'type' => 'id', 'default' => 'test'],
            'blob_param' => ['name' => 'blob_param', 'type' => 'blob', 'default' => 'test'],
            'longblob_param' => ['name' => 'longblob_param', 'type' => 'longblob', 'default' => 'test'],
            'currency_param' => ['name' => 'currency_param', 'type' => 'currency', 'default' => 1.11],
            'decimal_param' => ['name' => 'decimal_param', 'type' => 'decimal', 'len' => 10, 'precision' => 4, 'default' => 1.11],
            'decimal2_param' => ['name' => 'decimal2_param', 'type' => 'decimal2', 'len' => 10, 'precision' => 4, 'default' => 1.11],
            'url_param' => ['name' => 'url_param', 'type' => 'url', 'default' => 'test'],
            'encrypt_param' => ['name' => 'encrypt_param', 'type' => 'encrypt', 'default' => 'test'],
            'file_param' => ['name' => 'file_param', 'type' => 'file', 'default' => 'test'],
        ];

        $indexes = [
            [
                'name' => 'idx_' . $tableName . '_id',
                'type' => 'primary',
                'fields' => ['id'],
            ],
        ];
        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, $indexes);

        return ['tableName' => $tableName,
            'params' => $params,
        ];
    }


    /**
     * Each row is inserted and then read back and checked, including defaults.
     */
    private function setupDataTypesData()
    {
        return [
            [
                'id' => create_guid(),
                'int_param' => 1,
                'double_param' => 1,
                'float_param' => 1.1,
                'uint_param' => 1,
                'ulong_param' => 1,
                'long_param' => 1,
                'short_param' => 1,
                'varchar_param' => 'varchar',
                'text_param' => 'text',
                'longtext_param' => 'longtext',
                'date_param' => '2012-12-31',
                'enum_param' => 'enum',
                'relate_param' => 'relate',
                'multienum_param' => 'multienum',
                'html_param' => 'html',
                'longhtml_param' => 'longhtml',
                'datetime_param' => '2012-12-31 01:01:01',
                'datetimecombo_param' => '2012-12-31 01:01:01',
                'time_param' => '01:01:01',
                'bool_param' => '1',
                'tinyint_param' => 1,
                'char_param' => 'char',
                'id_param' => 'id',
                'blob_param' => 'blob',
                'longblob_param' => 'longblob',
                'currency_param' => 123.456,
                'decimal_param' => 12.34,
                'decimal2_param' => 12.34,
                'url_param' => 'utl',
                'encrypt_param' => 'encrypt',
                'file_param' => 'file',
            ],
            [
                'id' => create_guid(),
                'int_param' => 2,
                'double_param' => 2,
                'float_param' => 2.2,
                'uint_param' => 2,
                'ulong_param' => 2,
                'long_param' => 2,
                'short_param' => 2,
                'varchar_param' => 'varchar',
                'text_param' => 'text',
                'longtext_param' => 'longtext',
                'date_param' => '2012-12-31',
                'enum_param' => 'enum',
                'relate_param' => 'relate',
                'multienum_param' => 'multienum',
                'html_param' => 'html',
                'longhtml_param' => 'longhtml',
                'datetime_param' => '2012-12-31 01:01:01',
                'datetimecombo_param' => '2012-12-31 01:01:01',
                'time_param' => '01:01:01',
                'bool_param' => '1',
                'tinyint_param' => 1,
                'char_param' => 'char',
                'id_param' => 'id',
                'blob_param' => 'blob',
                'longblob_param' => 'longblob',
                'currency_param' => 123.456,
                'decimal_param' => 12.34,
                'decimal2_param' => 12.34,
                'url_param' => 'utl',
                'encrypt_param' => 'encrypt',
                'file_param' => 'file',
            ],
            [
                'id' => create_guid(),
                'int_param' => 3,
                'double_param' => 3,
            ],
        ];
    }

    public function testDataTypes()
    {
        // create data table
        $dataStructure = $this->setupDataTypesStructure();
        $params = $dataStructure['params'];
        $tableName = $dataStructure['tableName'];

        // load and test each data record
        $dataArray = $this->setupDataTypesData();

        foreach ($dataArray as $data) {  // insert a single row of data and check it column by column
            $this->db->insertParams($tableName, $params, $data);
            $id = $data['id'];
            $result = $this->db->query("SELECT * FROM $tableName WHERE id = " . $this->db->quoted($id));
            while (($row = $this->db->fetchByAssoc($result)) != null) {
                foreach ($data as $colKey => $col) {
                    $found = $this->db->fromConvert($row[$colKey], $params[$colKey]['type']);
                    $expected = $data[$colKey];
                    if (empty($expected)) { // if null then compare to the table defined default
                        $expected = $params[$colKey]['default'];
                    }
                    $this->assertEquals($expected, $found, "Failed prepared statement data compare for column $colKey. Found: $found  Expected: $expected");
                }
            }
        }
    }

    /**
     * This test is checking conversion blob field to clob
     * @param string $data Data for insert into table
     *
     * @dataProvider providerBlobToClob
     */
    public function testAlterTableBlobToClob($data)
    {
        if (!($this->db instanceof IBMDB2Manager)) {
            $this->markTestSkipped('This test can run only on DB2 instance');
        }
        $params = [
            'logmeta' => [
                'name' => 'logmeta',
                'vname' => 'LBL_LOGMETA',
                'type' => 'json',
                'dbType' => 'longblob',
            ],
        ];
        $tableName = 'testAlterTableBlobToClob' . random_int(0, mt_getrandmax());

        if ($this->db->tableExists($tableName)) {
            $this->db->dropTableName($tableName);
        }
        $this->createTableParams($tableName, $params, []);

        $this->db->insertParams($tableName, $params, ['logmeta' => $data]);

        $params = [
            'logmeta' => [
                'name' => 'logmeta',
                'vname' => 'LBL_LOGMETA',
                'type' => 'json',
                'dbType' => 'longtext',
            ],
        ];

        $this->db->repairTableParams($tableName, $params, [], true);

        $columns = $this->db->get_columns($tableName);
        $this->assertEquals('clob', $columns['logmeta']['type']);

        $checkResult = $this->db->fetchOne('SELECT logmeta FROM ' . $tableName);
        $this->assertEquals($data, $checkResult['logmeta']);
    }

    public function testCreateTableSQL()
    {
        $sql = $this->db->createTableSQL(new Contact());

        $this->assertMatchesRegularExpression('/create\s*table\s*contacts/i', $sql);
    }

    public function testCreateTableSQLParams()
    {
        $bean = BeanFactory::newBean('Contacts');

        $sql = $this->db->createTableSQLParams(
            $bean->getTableName(),
            $bean->getFieldDefinitions(),
            $bean->getIndices()
        );

        $this->assertMatchesRegularExpression('/create\s*table\s*contacts/i', $sql);
    }

    public function testCreateIndexSQL()
    {
        $sql = $this->db->createIndexSQL(
            new Contact(),
            ['id' => ['name' => 'id']],
            'idx_id'
        );

        $this->assertMatchesRegularExpression(
            '/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',
            $sql
        );

        $sql = $this->db->createIndexSQL(
            new Contact(),
            ['id' => ['name' => 'id']],
            'idx_id',
            false
        );

        $this->assertMatchesRegularExpression(
            '/create\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*\)/i',
            $sql
        );

        $sql = $this->db->createIndexSQL(
            new Contact(),
            ['id' => ['name' => 'id'], 'deleted' => ['name' => 'deleted']],
            'idx_id'
        );

        $this->assertMatchesRegularExpression(
            '/create\s*unique\s*index\s*idx_id\s*on\s*contacts\s*\(\s*id\s*,\s*deleted\s*\)/i',
            $sql
        );
    }

    public function testGetFieldType()
    {
        $fieldDef = [
            'dbType' => 'varchar',
            'dbtype' => 'int',
            'type' => 'char',
            'Type' => 'bool',
            'data_type' => 'email',
        ];

        $this->assertEquals($this->db->getFieldType($fieldDef), 'varchar');
        unset($fieldDef['dbType']);
        $this->assertEquals($this->db->getFieldType($fieldDef), 'int');
        unset($fieldDef['dbtype']);
        $this->assertEquals($this->db->getFieldType($fieldDef), 'char');
        unset($fieldDef['type']);
        $this->assertEquals($this->db->getFieldType($fieldDef), 'bool');
        unset($fieldDef['Type']);
        $this->assertEquals($this->db->getFieldType($fieldDef), 'email');
    }

    public function testGetAutoIncrement()
    {
        $case = BeanFactory::newBean('Cases');
        $case->name = 'foo';
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $helperResult = $this->db->getAutoIncrement('cases', 'case_number');

        $this->assertEquals($lastAuto + 1, $helperResult);
    }

    public function testGetAutoIncrementSQL()
    {
        if ($this->db->dbType != 'oci8') {
            $this->markTestSkipped('Only applies to Oracle');
        }

        $sql = $this->db->getAutoIncrementSQL('cases', 'case_number');
        $this->assertMatchesRegularExpression('/cases_case_number_seq\.nextval/i', $sql);
    }

    public function testSetAutoIncrementStart()
    {
        $case = BeanFactory::newBean('Cases');
        $case->name = 'foo';
        $case->save();
        $case->retrieve($case->id);
        $lastAuto = $case->case_number;
        $case->deleted = true;
        $case->save();
        $newAuto = $lastAuto + 5;
        $this->db->setAutoIncrementStart('cases', 'case_number', $newAuto);
        $case2 = BeanFactory::newBean('Cases');
        $case2->name = 'foo2';
        $case2->save();
        $case2->retrieve($case2->id);
        $case_number = $case2->case_number;

        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case->id}'");
        $GLOBALS['db']->query("DELETE FROM cases WHERE id= '{$case2->id}'");

        $this->assertEquals($newAuto, $case_number);
    }

    public function testAddColumnSQL()
    {
        $sql = $this->db->addColumnSQL(
            'contacts',
            ['foo' => ['name' => 'foo', 'type' => 'varchar']]
        );

        $this->assertMatchesRegularExpression('/alter\s*table\s*contacts/i', $sql);
    }

    public function testAlterColumnSQL()
    {
        $sql = $this->db->alterColumnSQL(
            'contacts',
            ['foo' => ['name' => 'foo', 'type' => 'varchar']]
        );

        // Generated SQL may be a sequence of statements
        switch (gettype($sql)) {
            case 'array':
                $sql = $sql[0];
                // fall-through
                // no break
            case 'string':
                $this->assertMatchesRegularExpression('/alter\s*table\s*contacts/i', $sql);
                break;
        }
    }

    public function testDropTableSQL()
    {
        $sql = $this->db->dropTableSQL(new Contact());

        $this->assertMatchesRegularExpression('/drop\s*table.*contacts/i', $sql);
    }

    public function testDropTableNameSQL()
    {
        $sql = $this->db->dropTableNameSQL('contacts');

        $this->assertMatchesRegularExpression('/drop\s*table.*contacts/i', $sql);
    }

    public function testDeleteColumnSQL()
    {
        $sql = $this->db->deleteColumnSQL(
            new Contact(),
            ['foo' => ['name' => 'foo', 'type' => 'varchar']]
        );

        if ($this->db->dbType == 'oci8') {
            $this->assertMatchesRegularExpression('/alter\s*table\s*contacts\s*drop\s*\(\s*foo\s*\)/i', $sql);
            return;
        }

        $this->assertMatchesRegularExpression('/alter\s*table\s*contacts\s*drop\s*column\s*foo/i', $sql);
    }

    public function testDropColumnSQL()
    {
        $tableName = 'drop_columns_sql_test';

        $id = [
            'name' => 'id',
            'type' => 'id',
        ];

        $field1 = [
            'name' => 'test1',
            'type' => 'int',
        ];

        $field2 = [
            'name' => 'test2',
            'type' => 'int',
        ];

        $this->createTableParams(
            $tableName,
            [
                'id' => $id,
                'test1' => $field1,
                'test2' => $field2,
            ],
            []
        );

        $this->assertNotFalse(
            $this->db->query(
                $this->db->dropColumnSQL($tableName, $field1)
            )
        );

        $this->assertNotFalse(
            $this->db->query(
                $this->db->dropColumnSQL($tableName, [$field2])
            )
        );

        $this->db->addColumn($tableName, [$field1, $field2]);

        $this->assertNotFalse(
            $this->db->query(
                $this->db->dropColumnSQL($tableName, [$field1, $field2])
            )
        );
    }

    public function testMassageValue()
    {
        $this->assertEquals(
            123,
            $this->db->massageValue(123, ['name' => 'foo', 'type' => 'int'])
        );

        switch ($this->db->dbType) {
            case 'mssql':
            case 'oci8':
            case 'ibm_db2':
                $expected = "'''dog'''";
                break;
            default:
                $expected = "'\\'dog\\''";
                break;
        }

        $this->assertEquals(
            $expected,
            $this->db->massageValue("'dog'", ['name' => 'foo', 'type' => 'varchar'])
        );
    }

    public function testGetColumnType()
    {
        switch ($this->db->dbType) {
            case 'oci8':
                $expected_type = 'number';
                break;
            case 'ibm_db2':
                $expected_type = 'integer';
                break;
            default:
                $expected_type = 'int';
        }

        $this->assertEquals($expected_type, $this->db->getColumnType('int'));
    }

    public function testIsFieldArray()
    {
        $this->assertTrue(
            $this->db->isFieldArray(['name' => 'foo', 'type' => ['int']])
        );

        $this->assertFalse(
            $this->db->isFieldArray(['name' => 'foo', 'type' => 'int'])
        );

        $this->assertTrue(
            $this->db->isFieldArray(['name' => 'foo'])
        );

        $this->assertFalse(
            $this->db->isFieldArray(1)
        );
    }

    public function testQuoted()
    {
        $this->assertEquals(
            "'" . $this->db->quote('foobar') . "'",
            $this->db->quoted('foobar')
        );
    }

    public function testGetIndices()
    {
        $indices = $this->db->get_indices('contacts');

        foreach ($indices as $index) {
            $this->assertTrue(!empty($index['name']));
            $this->assertTrue(!empty($index['type']));
            $this->assertTrue(!empty($index['fields']));
        }
    }

    public function testAddDropConstraint()
    {
        $tablename = 'test' . date('YmdHis');
        $sql = $this->db->add_drop_constraint(
            $tablename,
            [
                'name' => 'idx_foo',
                'type' => 'index',
                'fields' => ['foo'],
            ],
            false
        );

        $this->assertMatchesRegularExpression('/idx_foo/i', $sql);
        $this->assertMatchesRegularExpression('/foo/i', $sql);

        $tablename = 'test' . date('YmdHis');
        $sql = $this->db->add_drop_constraint(
            $tablename,
            [
                'name' => 'idx_foo',
                'type' => 'index',
                'fields' => ['foo'],
            ],
            true
        );

        $this->assertMatchesRegularExpression('/idx_foo/i', $sql);
        $this->assertMatchesRegularExpression('/foo/i', $sql);
        $this->assertMatchesRegularExpression('/drop/i', $sql);
    }

    public function testNumberOfColumns()
    {
        $tablename = 'test' . date('YmdHis');
        $this->createTableParams($tablename, [
            'foo' => [
                'name' => 'foo',
                'type' => 'varchar',
                'len' => 255,
            ],
        ], []);

        $this->assertEquals($this->db->number_of_columns($tablename), 1);
    }

    public function testGetColumns()
    {
        $vardefs = $this->db->get_columns('contacts');

        $this->assertTrue(isset($vardefs['id']));
        $this->assertTrue(isset($vardefs['id']['name']));
        $this->assertTrue(isset($vardefs['id']['type']));
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecision()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->db->alterColumnSQL(
            'contacts',
            [
                'compensation_min' => [
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => '',
                ],
            ]
        );

        $this->assertDoesNotMatchRegularExpression('/float\s*\(18,\s*\)/i', $sql);
        $this->assertMatchesRegularExpression('/float\s*\(18\)/i', $sql);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecision()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->db->alterColumnSQL(
            'contacts',
            [
                'compensation_min' => [
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => ' ',
                ],
            ]
        );

        $this->assertDoesNotMatchRegularExpression('/float\s*\(18,\s*\)/i', $sql);
        $this->assertMatchesRegularExpression('/float\s*\(18\)/i', $sql);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecision()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->db->alterColumnSQL(
            'contacts',
            [
                'compensation_min' => [
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18',
                    'precision' => '2',
                ],
            ]
        );

        if ($this->db->dbType == 'mssql') {
            $this->assertMatchesRegularExpression('/float\s*\(18\)/i', $sql);
        } else {
            $this->assertMatchesRegularExpression('/float\s*\(18,2\)/i', $sql);
        }
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLen()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }

        $sql = $this->db->alterColumnSQL(
            'contacts',
            [
                'compensation_min' => [
                    'required' => false,
                    'name' => 'compensation_min',
                    'vname' => 'LBL_COMPENSATION_MIN',
                    'type' => 'float',
                    'massupdate' => 0,
                    'comments' => '',
                    'help' => '',
                    'importable' => 'true',
                    'duplicate_merge' => 'disabled',
                    'duplicate_merge_dom_value' => 0,
                    'audited' => 0,
                    'reportable' => 1,
                    'len' => '18,2',
                ],
            ]
        );

        if ($this->db->dbType == 'mssql') {
            $this->assertMatchesRegularExpression('/float\s*\(18\)/i', $sql);
        } else {
            $this->assertMatchesRegularExpression('/float\s*\(18,2\)/i', $sql);
        }
    }

    /**
     * @ticket 22921
     */
    public function testEmptyPrecisionMassageFieldDef()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = [
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => '',
        ];
        $this->db->massageFieldDef($fielddef);

        $this->assertEquals('18', $fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testBlankSpacePrecisionMassageFieldDef()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }

        $fielddef = [
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => ' ',
        ];
        $this->db->massageFieldDef($fielddef);

        $this->assertEquals('18', $fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionMassageFieldDef()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = [
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18',
            'precision' => '2',
        ];
        $this->db->massageFieldDef($fielddef);

        $this->assertEquals('18,2', $fielddef['len']);
    }

    /**
     * @ticket 22921
     */
    public function testSetPrecisionInLenMassageFieldDef()
    {
        if (in_array($this->db->dbType, ['oci8', 'ibm_db2'])) {
            $this->markTestSkipped("Skipping on {$this->db->dbType}, as it doesn't apply to this backend");
        }
        $fielddef = [
            'required' => false,
            'name' => 'compensation_min',
            'vname' => 'LBL_COMPENSATION_MIN',
            'type' => 'float',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => 0,
            'audited' => 0,
            'reportable' => 1,
            'len' => '18,2',
        ];
        $this->db->massageFieldDef($fielddef);

        $this->assertEquals('18,2', $fielddef['len']);
    }

    public function testGetSelectFieldsFromQuery()
    {
        $i = 0;
        foreach (['', 'DISTINCT '] as $distinct) {
            $fields = [];
            $expected = [];
            foreach (['field', "''", "'data'", 'sometable.field'] as $data) {
                if ($data[0] != "'") {
                    $data .= $i++;
                    $fields[] = "{$distinct}$data";
                    $dotfields = explode('.', $data);
                    $expected[] = $dotfields[count($dotfields) - 1];
                }
                $as = 'otherfield' . ($i++);
                $fields[] = "{$distinct}$data $as";
                $expected[] = $as;
                $as = 'otherfield' . ($i++);
                $fields[] = "{$distinct}$data as $as";
                $expected[] = $as;
            }
            $query = 'SELECT ' . join(', ', $fields);
            $result = $this->db->getSelectFieldsFromQuery($query);
            foreach ($expected as $expect) {
                $this->assertContains($expect, array_keys($result), "Result should include $expect");
            }
        }
    }

    public function providerBlobToClob()
    {
        return [
            ['testAlterTableBlobToClob'],
            ['縢嫆璻侁刵 榎 偢偣唲鷩'],
            [serialize(range(1, 262144))],
        ];
    }

    /**
     * specific test cases based around db type are to be done by functional tests elsewhere.
     * @dataProvider orderByEnumProvider
     * @param $order_by order by column
     * @param $values value array
     * @param $order_dir order by direction
     * @param $result expected result
     */
    public function testOrderByEnum($order_by, $values, $order_dir, $expected)
    {
        $result = $this->db->orderByEnum($order_by, $values, $order_dir);
        $this->assertEquals($expected, $result);
    }

    public function orderByEnumProvider()
    {
        return [
            ['', [], '', ''],
        ];
    }

    public function testLimitSubQuery()
    {
        if (!$this->db->supports('limit_subquery')) {
            $this->markTestSkipped('Backend does not support LIMIT clauses in subqueries');
        }

        $subQuery = $this->db->limitQuery('SELECT id FROM users WHERE 1=1 ORDER BY id', 0, 1, false, '', false);
        $query = 'SELECT id FROM users WHERE id IN (' . $subQuery . ')';
        $row = $this->db->fetchOne($query);
        $this->assertIsArray($row);
    }

    public function testLimitSubQueryWithUnionAndComment()
    {
        $dummy = 'SELECT \'x\' id ' . $this->db->getFromDummyTable();

        $query = <<<SQL
SELECT
  accounts.id
FROM accounts
JOIN (
  /* this comments makes a fool of SQL Server's query parser,
     it thinks that UNION is in the top level query */
  $dummy
  UNION
  $dummy
) x ON x.id = accounts.id
WHERE 1 = 1
ORDER BY accounts.id
SQL;

        // the LIMIT needs to be greater than 1
        $result = $this->db->limitQuery($query, 0, 2);
        $this->assertNotEmpty($result);
    }

    public function testLimitUnionQueryWithoutOrderBy()
    {
        $dummy = 'SELECT 1 id ' . $this->db->getFromDummyTable();

        $query = $dummy . ' UNION ALL ' . $dummy;

        $result = $this->db->limitQuery($query, 0, 1);
        $row = $this->db->fetchRow($result);

        $this->assertEquals('1', $row['id']);
    }

    // Returns true in case we use MySQL version >= 8
    private function isMysqlVersionAtLeast8(): bool
    {
        if ($this->db instanceof MysqlManager) {
            if (version_compare($this->db->version(), '8.0.0') >= 0) {
                return true;
            }
        }

        return false;
    }

    public function testDelete_BeanDeleted()
    {
        [$id] = $this->createRecords(1);

        $bean = BeanFactory::newBean('Contacts');
        $bean->id = $id;
        $this->assertTrue($this->db->delete($bean), 'bean not deleted');

        $bean = BeanFactory::retrieveBean('Contacts', $id, ['use_cache' => false]);
        $this->assertNull($bean, 'found bean');
    }

    public function testDelete_BeanNotFound()
    {
        $bean = BeanFactory::newBean('Contacts');
        $bean->id = Uuid::uuid1();

        $this->assertFalse($this->db->delete($bean));
    }
}
