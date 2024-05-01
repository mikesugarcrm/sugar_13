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

namespace Sugarcrm\SugarcrmTests\Elasticsearch\QueueManager;

use DBManagerFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch;
use Sugarcrm\Sugarcrm\Elasticsearch\Queue;
use SugarTestAccountUtilities;

class QueueManagerTest extends TestCase
{
    public $accounts = [];

    public function setUp(): void
    {
        $db = DBManagerFactory::getInstance();
        $this->accounts['account0'] = SugarTestAccountUtilities::createAccount();
        $this->accounts['account1'] = SugarTestAccountUtilities::createAccount();
        $truncate = $db->truncateTableSQL('fts_queue');
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncate);
    }

    public function tearDown(): void
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        $db = DBManagerFactory::getInstance();
        $truncate = $db->truncateTableSQL('fts_queue');
        $db->commit(); //truncate should be the first query in transaction on DB2
        $db->query($truncate);
    }

    /**
     * @dataProvider ftsQueueProvider
     */
    public function testConsumeModuleFromQueue(array $accounts, bool $successExpected, int $processedExpected, int $leftExpected)
    {
        $db = DBManagerFactory::getInstance();
        $container = Elasticsearch\Container::getInstance();
        $queue = new Queue\QueueManager($container->getConfig('global'), $container);
        $queue->queueBeans([
            $this->accounts[$accounts[0]],
            $this->accounts[$accounts[1]],
        ]);
        [$success, $processed] = $queue->consumeModuleFromQueue('Accounts');
        $left = $db->getConnection()
            ->executeQuery('SELECT count(1) FROM fts_queue')
            ->fetchOne();
        $this->assertEquals($successExpected, $success);
        $this->assertEquals($processedExpected, $processed);
        $this->assertEquals($leftExpected, $left);
    }

    public function ftsQueueProvider()
    {
        yield [
            'accounts' => ['account0', 'account0',],
            'success' => true,
            'processed' => 1,
            'left' => 0,
        ];
        yield [
            'accounts' => ['account0', 'account1',],
            'success' => true,
            'processed' => 2,
            'left' => 0,
        ];
    }
}
