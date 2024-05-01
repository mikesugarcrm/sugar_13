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

class DBManagerFactoryTest extends TestCase
{
    private $oldSugarConfig;
    private $expectedInstance;
    private $oldInstance;

    protected function setUp(): void
    {
        $this->oldSugarConfig = $GLOBALS['sugar_config'];
        $this->expectedInstance = $GLOBALS['sugar_config']['dbconfig']['db_host_instance'];
        $this->oldInstance = DBManagerFactory::$instances[''];
        unset(DBManagerFactory::$instances['']);
        $GLOBALS['sugar_config']['db']['test'] = $GLOBALS['sugar_config']['dbconfig'];
        $GLOBALS['sugar_config']['db']['test']['db_host_instance'] = 'TEST';
    }

    protected function tearDown(): void
    {
        if (isset(DBManagerFactory::$instances['test'])) {
            DBManagerFactory::getInstance('test')->disconnect();
            unset(DBManagerFactory::$instances['test']);
        }
        unset($GLOBALS['sugar_config']['db']['test']);
        $GLOBALS['sugar_config'] = $this->oldSugarConfig;
        DBManagerFactory::$instances[''] = $this->oldInstance;
    }

    public function testGetInstance()
    {
        $db = DBManagerFactory::getInstance();

        $this->assertTrue($db instanceof DBManager, 'Should return a DBManger object');
    }

    public function testGetMultiInstance()
    {
        if ($GLOBALS['db']->dbType != 'mysql') {
            $this->markTestSkipped('Only applies to MySQL');
        }

        $this->assertEquals($this->expectedInstance, DBManagerFactory::getInstance()->connectOptions['db_host_instance']);
        $this->assertEquals('TEST', DBManagerFactory::getInstance('test')->connectOptions['db_host_instance']);
    }

    public function testGetInstanceCheckMysqlDriverChoosen()
    {
        if ($GLOBALS['db']->dbType != 'mysql') {
            $this->markTestSkipped('Only applies to MySql');
        }

        $this->assertInstanceOf('MysqlManager', DBManagerFactory::getInstance());
    }

    /**
     * @ticket 27781
     */
    public function testGetInstanceMssqlDefaultSelection()
    {
        if ($GLOBALS['db']->dbType != 'mssql') {
            $this->markTestSkipped('Only applies to SQL Server');
        }

        $this->assertInstanceOf('MssqlManager', DBManagerFactory::getInstance());
    }
}
