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

use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass SugarQuery_Compiler_Doctrine
 */
class BugBr8762Test extends TestCase
{
    /**
     * @var Account
     */
    private $account;

    /**
     * @return array
     */
    public static function compileConditionProvider()
    {
        return [
            'in-2500-values' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->in('id', range(1, 2500));
                },
                '/^accounts\.id IN \((\?,){999}\?\) OR accounts\.id IN \((\?,){999}\?\) OR accounts\.id IN \((\?,){499}\?\)$/',
                range(1, 2500),
            ],
            'not-in-2500-values' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->notIn('id', range(1, 2500));
                },
                '/^accounts\.id IS NULL OR accounts\.id NOT IN \((\?,){999}\?\) AND accounts\.id NOT IN \((\?,){999}\?\) AND accounts\.id NOT IN \((\?,){499}\?\)$/',
                range(1, 2500),
            ],
        ];
    }

    /**
     * @param callable $where
     * @dataProvider compileConditionProvider
     */
    public function testCompileCondition(callable $where, $regexWhere, $expectedParams = []): void
    {
        if (!($this->account->db instanceof OracleManager)) {
            $this->markTestSkipped('Oracle-only test');
        }

        $query = $this->getQuery();
        $where($query->where());

        $compiler = $this->getCompilerWithCollationCaseSensitivity(false);
        $builder = $compiler->compile($query);

        $this->assertMatchesRegularExpression($regexWhere, (string)$builder->getQueryPart('where'));
        $this->assertSame($expectedParams, $builder->getParameters());
    }

    private function getQuery(array $options = []): SugarQuery
    {
        $query = new SugarQuery();
        $query->from($this->account, array_merge([
            'add_deleted' => false,
            'team_security' => false,
        ], $options));

        return $query;
    }

    /**
     * Returns compiled with mocked case sensitivity of the underlying database collation
     *
     * @param boolean $value Whether the locale is case sensitive
     * @return MockObject|SugarQuery\Compiler\Doctrine
     */
    private function getCompilerWithCollationCaseSensitivity($value): SugarQuery_Compiler_Doctrine
    {
        /** @var SugarQuery_Compiler_Doctrine|MockObject $compiler */
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_Doctrine')
            ->setMethods(['isCollationCaseSensitive'])
            ->setConstructorArgs([$this->account->db])
            ->getMock();
        $compiler->expects($this->any())
            ->method('isCollationCaseSensitive')
            ->willReturn($value);

        return $compiler;
    }

    protected function setUp(): void
    {
        $this->account = BeanFactory::newBean('Accounts');
    }
}
