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

namespace Sugarcrm\SugarcrmTests\Dbal;

use Doctrine\DBAL\Schema\Table;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * Covers our modifications to the DBAL connection class hierarchy
 */
class StatementTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $table = new Table('type_conversion');
        $table->addColumn('id', 'string', [
            'length' => 36,
        ]);
        $table->addColumn('name', 'string');
        $table->addColumn('deptno', 'integer', [
            'notnull' => false,
        ]);

        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        $sm->createTable($table);
    }

    public static function tearDownAfterClass(): void
    {
        $conn = \DBManagerFactory::getConnection();
        $sm = $conn->getSchemaManager();
        $sm->dropTable('type_conversion');
    }

    public function testStringAsInteger()
    {
        $conn = \DBManagerFactory::getConnection();

        $conn->insert('type_conversion', [
            'id' => Uuid::uuid1(),
            'name' => 'Alice',
        ]);
        $conn->insert('type_conversion', [
            'id' => 1,
            'name' => 'Bob',
        ]);

        $stmt = $conn->executeQuery('SELECT name FROM type_conversion WHERE id = ?', [1]);

        $this->assertEquals(['Bob'], $stmt->fetchFirstColumn());
    }

    public function testIntegerAsString()
    {
        $conn = \DBManagerFactory::getConnection();

        $conn->insert('type_conversion', [
            'id' => Uuid::uuid1(),
            'name' => 'Alice',
            'deptno' => '7',
        ]);
        $conn->insert('type_conversion', [
            'id' => Uuid::uuid1(),
            'name' => 'Bob',
            'deptno' => 12,
        ]);

        $stmt = $conn->executeQuery('SELECT name FROM type_conversion WHERE deptno = ?', ['7']);

        $this->assertEquals(['Alice'], $stmt->fetchFirstColumn());
    }
}
