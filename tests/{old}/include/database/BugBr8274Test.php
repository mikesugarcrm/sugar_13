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

class BugBr8274Test extends TestCase
{
    public const TABLE_NAME = 'test_br_8274';

    /** @var DBManager */
    private $db;

    protected function setUp(): void
    {
        $db = DBManagerFactory::getInstance();
        if (!$db instanceof IBMDB2Manager) {
            $this->markTestSkipped('DB2-only test');
        }
        $this->db = $db;
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            $this->db->dropTableName(self::TABLE_NAME);
            unset($this->db);
        }
    }

    public function testVarcharDefaultEmptyStringIsNoDefault()
    {
        if ($this->db->tableExists(self::TABLE_NAME)) {
            $this->db->dropTableName(self::TABLE_NAME);
        }
        $fieldDefs = [
            'nodefault_c' => [
                'name' => 'nodefault_c',
                'type' => 'varchar',
                'len' => '36',
                'default' => '',
            ],
        ];
        $this->db->createTableParams(self::TABLE_NAME, $fieldDefs, []);

        $columns = $this->db->get_columns(self::TABLE_NAME);
        $this->assertArrayHasKey('type', $columns['nodefault_c']);
        $this->assertArrayNotHasKey('default', $columns['nodefault_c'], 'Column has default value');
    }
}
