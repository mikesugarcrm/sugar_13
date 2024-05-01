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

namespace Sugarcrm\SugarcrmTestsUnit\modules\HealthCheck\Scanner;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\modules\HealthCheck\Scanner\Checks\Dbal as DbalCheck;
use Sugarcrm\Sugarcrm\modules\HealthCheck\Scanner\Checks\DbalUpgradeIssue;

require_once 'modules/HealthCheck/Scanner/Checks/Dbal.php';

/**
 * @coversDefaultClass \HealthCheckScanner
 */
class DbalUpgradeTest extends TestCase
{
    /**
     * @dataProvider issueProvider
     * @param string $code
     * @param string $expectedIssue
     * @param int $expectedLine
     * @return void
     */
    public function testIssue(string $code, array $expected): void
    {
        $check = new DbalCheck();
        $issues = $check->check($code);
        $this->assertSameSize($expected, $issues, 'Wrong issue number');
        foreach ($expected as [$expectedIssue, $expectedLine]) {
            $this->assertHasIssue($expectedIssue, $expectedLine, $issues);
        }
    }

    /**
     * @return [[$code, [[$regex, $line], ...]], ...]
     */
    public function issueProvider(): array
    {
        return [
            'removed class' => [
                <<<'EOT'
                <?php
                use Doctrine\DBAL\DBALException;
                try {
                    DBManagerFactory::getConnection()->executeStatement('select 1;');
                } catch (DBALException $e) {}
                EOT,
                [
                    ['/Removed class usage/i', 2],
                    ['/Removed class usage/i', 5],
                ],
            ],
            'removed constant' => [
                <<<'EOT'
                <?php
                $mode = \Doctrine\DBAL\Connection::TRANSACTION_READ_UNCOMMITTED;
                EOT,
                [
                    ['/Removed constant usage/i', 2],
                ],
            ],
            'one-based array' => [
                <<<'EOT'
                <?php
                $params = [123];
                DBManagerFactory::getConnection()->executeQuery('...', $params);
                $params = [1 => 123];
                $notParams = [1 => 123];
                DBManagerFactory::getConnection()->executeQuery('...', $params);
                EOT,
                [
                    ['/One-based numeric array of params/i', 6],
                ],
            ],
            'param name with a leading colon' => [
                <<<'EOT'
                <?php
                $params = ['a' => 123];
                DBManagerFactory::getConnection()->executeQuery('...', $params);
                $params = ['b' => 0, ':c' => 'aaa'];
                DBManagerFactory::getConnection()->executeQuery('...', $params);
                EOT,
                [
                    ['/Leading colon in query param name/i', 5],
                ],
            ],
            'removed methods' => [
                <<<'EOT'
                <?php
                $conn = DBManagerFactory::getConnection();
                $conn->setFetchMode(2);
                $xl = \FakeExcelClient::loadDocument('somefile.xlsx');
                $xlRows = $xl->fetchColumn(); // test false positive
                $stmt = $conn->executeQuery('...');
                $rows = $stmt->fetchColumn();
                $rows2 = $conn->executeQuery('...')->fetchArray();
                $stmt->closeCursor();
                EOT,
                [
                    ['/Removed method usage/i', 3],
                    ['/Removed method usage/i', 7],
                    ['/Removed method usage/i', 8],
                    ['/Removed method usage/i', 9],
                ],
            ],
            'iteration over result' => [
                <<<'EOT'
                <?php
                $manager = DBManagerFactory::getInstance();
                $db = $manager->getConnection();
                $xs = $db->executeQuery('...')->fetchAllAssociative();
                foreach ($xs as $x) {}
                $xs = $db->query('...');
                foreach ($xs->fetchAllAssociative() as $x) {}
                $xs = $db->executeQuery('...');
                foreach ($xs as $x) {}
                $xs = (new DummyRestClient)->query('...');
                foreach ($xs as $x) {}
                EOT,
                [
                    ['/Iteration over statement instead of result/i', 9],
                ],
            ],
            'query builder' => [
                <<<'EOT'
                <?php
                $conn = \DBManagerFactory::getConnection();
                $qb = $conn->createQueryBuilder();
                $qb->select("a.id");
                $qb->from("accounts", "a");
                $qb->join("a", "email_addr_bean_rel", "eabr", "a.id = eabr.bean_id AND eabr.bean_module = 'Accounts' AND eabr.deleted = 0");
                $qb->join("eabr", "email_addresses", "e", "eabr.email_address_id = e.id AND e.deleted = 0 AND e.email_address_caps LIKE " . $qb->createPositionalParameter(strtoupper("%$domain")));
                $qb->where($qb->expr()->eq('a.deleted', 0))
                    ->andWhere($qb->expr()->eq("eabr.primary_address", 1));
                $qb->setMaxResults(1);
                $id = $qb->execute()->fetchColumn();

                $res0 = $conn->createQueryBuilder()->executeQuery()->fetchArray();
                $res1 = $conn->createQueryBuilder()->select()->where()->execute()->fetchAssoc();
                EOT,
                [
                    ['/Removed method usage/i', 11],
                    ['/Removed method usage/i', 13],
                    ['/Removed method usage/i', 14],
                ],
            ],
            'failure on variable variable' => [
                <<<'EOT'
                <?php
                $a = 1;
                $b = 'a';
                ${$b} = 2;
                echo ${$b};
                EOT,
                [],
            ],
        ];
    }

    /**
     * @param string $messageRegex
     * @param int $line
     * @param DbalUpgradeIssue[] $issues
     * @return bool
     */
    private function assertHasIssue(string $messageRegex, int $line, array $issues): void
    {
        foreach ($issues as $issue) {
            if ($issue->getLine() === $line && preg_match($messageRegex, $issue->getMessage())) {
                return;
            }
        }
        $this->fail("expected issue '$messageRegex' not found in line $line");
    }
}
