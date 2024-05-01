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

namespace Sugarcrm\SugarcrmTests\Dbal\Query;

use PHPUnit\Framework\TestCase;
use SugarTestHelper;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Dbal\Query\QueryBuilder
 */
class QueryBuilderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    public function testFromSubQuery()
    {
        global $current_user;

        $conn = \DBManagerFactory::getConnection();
        $q1 = $conn->createQueryBuilder();
        $q1->select('id', 'last_name')
            ->from('users')
            ->where('id = ' . $q1->createPositionalParameter($current_user->id));

        $q2 = $conn->createQueryBuilder();
        $q2->select('*')
            ->from('(' . $q2->importSubQuery($q1) . ')', 'q1');

        $row = $q2->execute()->fetchAssociative();

        $this->assertIsArray($row);
        $this->assertEquals($current_user->id, $row['id']);
        $this->assertEquals($current_user->last_name, $row['last_name']);
    }

    public function testJoinSubQuery()
    {
        global $current_user;

        $conn = \DBManagerFactory::getConnection();
        $q1 = $conn->createQueryBuilder();
        $q1->select('id', 'last_name')
            ->from('users')
            ->where('id = ' . $q1->createPositionalParameter($current_user->id));

        $q2 = $conn->createQueryBuilder();
        $q2->select(['q2.id, q1.last_name'])
            ->from('users', 'q2')
            ->join('q2', '(' . $q2->importSubQuery($q1) . ')', 'q1', 'q2.id = q1.id');

        $row = $q2->execute()->fetchAssociative();

        $this->assertIsArray($row);
        $this->assertEquals($current_user->id, $row['id']);
        $this->assertEquals($current_user->last_name, $row['last_name']);
    }

    public function testInSubQuery()
    {
        global $current_user;

        $conn = \DBManagerFactory::getConnection();
        $q1 = $conn->createQueryBuilder();
        $q1->select('id')
            ->from('users')
            ->where('id = ' . $q1->createPositionalParameter($current_user->id));

        $q2 = $conn->createQueryBuilder();
        $q2->select(['id, last_name'])
            ->from('users')
            ->where('id IN(' . $q2->importSubQuery($q1) . ')');

        $row = $q2->execute()->fetchAssociative();

        $this->assertIsArray($row);
        $this->assertEquals($current_user->id, $row['id']);
        $this->assertEquals($current_user->last_name, $row['last_name']);
    }
}
