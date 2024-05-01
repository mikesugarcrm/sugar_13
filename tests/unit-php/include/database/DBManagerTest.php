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

namespace Sugarcrm\SugarcrmTestsUnit\inc\database;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class OracleManagerTest
 *
 * @coversDefaultClass \DBManager
 */
class DBManagerTest extends TestCase
{
    /**
     * @covers ::isMysql
     * @param \DBManager|null $db
     * @param bool $expected
     *
     * @dataProvider isMysqlProvider
     */
    public function testIsMysql(?\DBManager $db, bool $expected): void
    {
        $this->assertSame($expected, \DBManager::isMysql($db));
    }

    public function isMysqlProvider()
    {
        return [
            'null' => [
                null,
                false,
            ],
            'mysql' => [
                $this->createMock(\MysqlManager::class),
                true,
            ],
            'oracle' => [
                $this->createMock(\OracleManager::class),
                false,
            ],
            'db2' => [
                $this->createMock(\IBMDB2Manager::class),
                false,
            ],
            'sqlsrv' => [
                $this->createMock(\SqlsrvManager::class),
                false,
            ],
        ];
    }

    /**
     * @covers ::isOracle
     * @param \DBManager|null $db
     * @param bool $expected
     *
     * @dataProvider isOracleProvider
     */
    public function testIsOracle(?\DBManager $db, bool $expected)
    {
        $this->assertSame($expected, \DBManager::isOracle($db));
    }

    public function isOracleProvider()
    {
        return [
            'null' => [
                null,
                false,
            ],
            'mysql' => [
                $this->createMock(\MysqlManager::class),
                false,
            ],
            'oracle' => [
                $this->createMock(\OracleManager::class),
                true,
            ],
            'db2' => [
                $this->createMock(\IBMDB2Manager::class),
                false,
            ],
            'sqlsrv' => [
                $this->createMock(\SqlsrvManager::class),
                false,
            ],
        ];
    }

    /**
     * @covers ::isDb2
     * @param \DBManager $db
     * @param bool $expected
     *
     * @dataProvider isDb2Provider
     */
    public function testIsDb2(?\DBManager $db, bool $expected)
    {
        $this->assertSame($expected, \DBManager::isDb2($db));
    }

    public function isDb2Provider()
    {
        return [
            'null' => [
                null,
                false,
            ],
            'mysql' => [
                $this->createMock(\MysqlManager::class),
                false,
            ],
            'oracle' => [
                $this->createMock(\OracleManager::class),
                false,
            ],
            'db2' => [
                $this->createMock(\IBMDB2Manager::class),
                true,
            ],
            'sqlsrv' => [
                $this->createMock(\SqlsrvManager::class),
                false,
            ],
        ];
    }

    /**
     * @covers ::isSqlServer
     * @param \DBManager $db
     * @param bool $expected
     *
     * @dataProvider isSqlServerProvider
     */
    public function testIsSqlServer(?\DBManager $db, bool $expected)
    {
        $this->assertSame($expected, \DBManager::isSqlServer($db));
    }

    public function isSqlServerProvider()
    {
        return [
            'null' => [
                null,
                false,
            ],
            'mysql' => [
                $this->createMock(\MysqlManager::class),
                false,
            ],
            'oracle' => [
                $this->createMock(\OracleManager::class),
                false,
            ],
            'db2' => [
                $this->createMock(\IBMDB2Manager::class),
                false,
            ],
            'sqlsrv' => [
                $this->createMock(\SqlsrvManager::class),
                true,
            ],
        ];
    }
}
