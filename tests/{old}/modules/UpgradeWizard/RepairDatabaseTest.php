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

class RepairDatabaseTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();
        if ($this->db->dbType == 'mysql') {
            $sql = 'ALTER TABLE meetings MODIFY COLUMN status varchar(100) NULL DEFAULT \'Test\'';
            $sql2 = 'ALTER TABLE calls MODIFY COLUMN status varchar(100) NULL DEFAULT \'Test\'';
            $sql3 = 'ALTER TABLE tasks MODIFY COLUMN status varchar(100) NULL DEFAULT \'Test\'';
            $sql4 = 'ALTER TABLE email_addr_bean_rel DROP INDEX idx_email_address_bean';

            //Run the SQL
            $this->db->query($sql);
            $this->db->query($sql2);
            $this->db->query($sql3);
            $this->db->query($sql4);
            $this->db->commit();
        }
    }

    protected function tearDown(): void
    {
        if ($this->db->dbType == 'mysql') {
            $sql = "ALTER TABLE meetings MODIFY COLUMN status varchar(100) NULL DEFAULT 'Planned'";
            $sql2 = "ALTER TABLE calls MODIFY COLUMN status varchar(100) NULL DEFAULT 'Planned'";
            $sql3 = "ALTER TABLE tasks MODIFY COLUMN status varchar(100) NULL DEFAULT 'Not Started'";
            $sql4 = 'ALTER TABLE email_addr_bean_rel ADD INDEX idx_email_address_bean (email_address_id, bean_module, bean_id)';
            //Run the SQL
            $this->db->query($sql);
            $this->db->query($sql2);
            $this->db->query($sql3);
            $this->db->query($sql4);
            $this->db->commit();
        }
    }

    public function testRepairTableParams()
    {
        $defs = [];
        if ($this->db->dbType != 'mysql') {
            $this->markTestSkipped('Skip if not mysql db');
            return;
        }

        $bean = new Meeting();
        $result = $this->getRepairTableParamsResult($bean);
        $this->assertMatchesRegularExpression(
            '/ALTER TABLE meetings\s+?modify column status varchar\(100\)  DEFAULT \'Planned\' NULL/i',
            $result
        );

        $bean = new Call();
        $result = $this->getRepairTableParamsResult($bean);
        $this->assertTrue(!empty($result));
        $this->assertMatchesRegularExpression(
            '/ALTER TABLE calls\s+?modify column status varchar\(100\)  DEFAULT \'Planned\' NULL/i',
            $result
        );

        $bean = new Task();
        $result = $this->getRepairTableParamsResult($bean);
        $this->assertTrue(!empty($result));
        $this->assertMatchesRegularExpression(
            '/ALTER TABLE tasks\s+?modify column status varchar\(100\)  DEFAULT \'Not Started\' NULL/i',
            $result
        );

        $def = $GLOBALS['dictionary']['email_addr_bean_rel'];
        $result = $this->db->repairTableParams($def['table'], $def['fields'], $def['indices'], false, $defs['engine'] ?? null);
        $this->assertMatchesRegularExpression('/ALTER TABLE email_addr_bean_rel\s+ADD INDEX idx_email_address_bean \(email_address_id,bean_module,bean_id\)/i', $result);
    }

    private function getRepairTableParamsResult($bean)
    {
        $indices = $bean->getIndices();
        $fielddefs = $bean->getFieldDefinitions();
        $tablename = $bean->getTableName();

        //Clean the indicies to prevent duplicate definitions
        $new_indices = [];
        foreach ($indices as $ind_def) {
            $new_indices[$ind_def['name']] = $ind_def;
        }

        global $dictionary;
        $engine = null;
        if (isset($dictionary[$bean->getObjectName()]['engine']) && !empty($dictionary[$bean->getObjectName()]['engine'])) {
            $engine = $dictionary[$bean->getObjectName()]['engine'];
        }


        $result = $this->db->repairTableParams($tablename, $fielddefs, $new_indices, false, $engine);
        return $result;
    }

    /**
     * @dataProvider typeProvider()
     */
    public function testRepairCustomTable(string $type, int $length): void
    {
        global $db;

        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'test_c',
            'type' => $type,
            'len' => $length,
        ]);

        $this->assertArrayHasKey('test_c', $db->get_columns('accounts_cstm'));

        $db->query(
            $db->dropColumnSQL('accounts_cstm', [
                'name' => 'test_c',
                'type' => 'varchar',
            ])
        );

        $this->assertArrayNotHasKey('test_c', $db->get_columns('accounts_cstm'));

        $df = new DynamicField('Accounts');
        $df->setup(BeanFactory::newBean('Accounts'));
        $df->repairCustomFields();

        $columns = $db->get_columns('accounts_cstm');
        $this->assertArrayHasKey('test_c', $columns);
        $this->assertEquals($length, $columns['test_c']['len']);
    }

    /**
     * @return mixed[][]
     */
    public static function typeProvider(): iterable
    {
        return [
            ['enum', 32],
            ['image', 50],
        ];
    }
}
