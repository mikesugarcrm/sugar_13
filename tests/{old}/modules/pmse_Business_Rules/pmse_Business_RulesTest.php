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
 * pmse_Business_RulesTest: Tests the size of the field rst_source_definition in the pmse_business_rules table.
 * The size has to allow more than 65535 bytes.
 */
class pmse_Business_RulesTest extends TestCase
{
    /**
     * @var SugarBean
     */
    private $bean;

    /**
     * @var DBManager
     */
    private $db;

    protected function setUp(): void
    {
        $this->db = DBManagerFactory::getInstance();

        $bean = BeanFactory::newBean('pmse_Business_Rules');
        $bean->name = 'pmseBusinessRulesTest';
        $bean->rst_source_definition = file_get_contents(__DIR__ . '/long-text-br-test.txt');
        $bean->save();

        $this->bean = $bean;
    }

    protected function tearDown(): void
    {
        $this->db->query(sprintf(
            'DELETE FROM %s WHERE id = %s',
            $this->bean->getTableName(),
            $this->db->quoted($this->bean->id)
        ));
    }

    public function testAssertSizeOfBR()
    {
        $query = sprintf(
            'SELECT rst_source_definition FROM %s WHERE id = %s',
            $this->bean->getTableName(),
            $this->db->quoted($this->bean->id)
        );
        $result = $this->db->fetchOne($query);

        $this->assertNotEquals(false, $result);
        $this->assertArrayHasKey('rst_source_definition', $result);
        $this->assertEquals($result['rst_source_definition'], $this->bean->rst_source_definition);
    }
}
