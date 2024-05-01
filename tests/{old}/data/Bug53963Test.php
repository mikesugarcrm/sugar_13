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
 * Bug #53963
 * Primary keys/indexes missing for *_audit tables (DB2 only)
 *
 * @author fsteegmans@sugarcrm.com
 * @ticked 53963
 */
class Bug53963Test extends TestCase
{
    private $db;
    private $bean;
    private $audit_table_name;

    protected function setUp(): void
    {
        $this->db = $GLOBALS['db'];
        $this->bean = new SugarBean();
        $this->bean->table_name = 'Bug53963Test';
        $this->audit_table_name = $this->bean->get_audit_table_name();
        $this->cleanUpAuditTable();
    }

    protected function tearDown(): void
    {
        $this->cleanUpAuditTable();
    }

    public function testAuditTablePrimaryKeyCreation()
    {
        function findPK($previous, $index)
        {
            return $previous || ($index['type'] == 'primary');
        }

        $this->bean->create_audit_table();
        $indices = $this->db->get_indices($this->audit_table_name);
        $this->assertNotEmpty($indices, 'Audit table indices are missing!');
        $this->assertTrue(array_reduce($indices, 'findPK'), 'Audit table is missing a primary key index');
    }

    private function cleanUpAuditTable()
    {
        if ($this->db->tableExists($this->audit_table_name)) {
            $this->db->dropTableName($this->audit_table_name);
        }
    }
}
