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
class SugarQuery_Compiler_DoctrineTest extends TestCase
{
    /**
     * @var Account
     */
    private $account;

    protected function setUp(): void
    {
        $this->account = BeanFactory::newBean('Accounts');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDownCustomFields();
    }

    public function testSelectFieldsByDefault()
    {
        $query = $this->getQuery();
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');
        $this->assertNotEmpty($select);
    }

    public function testSelect()
    {
        $query = new SugarQuery();
        $query->select([
            ['name', 'account_name'],
            'parent_name',
        ]);
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');

        $this->assertContains('accounts.name account_name', $select);
        $this->assertNotContains('accounts.parent_name', $select);
    }

    public function testCountQuery()
    {
        $query = new SugarQuery();
        $query->select('name')->setCountQuery();
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');

        $lastColumn = array_pop($select);
        $this->assertStringStartsWith('COUNT(', $lastColumn);

        $groupBy = $builder->getQueryPart('groupBy');
        $this->assertContains('accounts.name', $groupBy);
    }

    public function testDistinctQuery()
    {
        $query = new SugarQuery();
        $query->distinct(true)->select('name');
        $query->from($this->account);
        $builder = $query->compile();

        $select = $builder->getQueryPart('select');
        $this->assertContains('DISTINCT accounts.name', $select);
    }

    /**
     * @param array $options FROM options
     * @param array $expected Expected FROM part
     *
     * @dataProvider compileFromProvider
     */
    public function testCompileFrom(array $options, array $expected)
    {
        $query = $this->getQuery($options);
        $builder = $query->compile();

        $this->assertContains($expected, $builder->getQueryPart('from'));
    }

    /**
     * @return array
     */
    public static function compileFromProvider()
    {
        return [
            'without-alias' => [
                [],
                [
                    'table' => 'accounts',
                    'alias' => null,
                ],
            ],
            'with-alias' => [
                [
                    'alias' => 'a',
                ],
                [
                    'table' => 'accounts',
                    'alias' => 'a',
                ],
            ],
        ];
    }

    public function testCompileFromNoBean()
    {
        $query = new SugarQuery();

        $this->expectException(SugarQueryException::class);
        $query->compile();
    }

    public function testCompileJoin()
    {
        $query = $this->getQuery();
        $query->joinTable('opportunities', [
            'joinType' => 'left',
        ])->on()->equalsField('opportunities.account_id', 'accounts.id');
        $builder = $query->compile();
        $join = $builder->getQueryPart('join');

        $this->assertSame([
            [
                'joinType' => 'left',
                'joinTable' => 'opportunities',
                'joinAlias' => 'opportunities',
                'joinCondition' => 'opportunities.account_id = accounts.id',
            ],
        ], $join['accounts']);
    }

    public function testCompileJoinSubQuery()
    {
        $subQuery = new SugarQuery();

        /** @var SugarQuery_Compiler_Doctrine|MockObject $compiler */
        $compiler = $this->getMockBuilder('SugarQuery_Compiler_Doctrine')
            ->setMethods(['compileSubQuery'])
            ->setConstructorArgs([$this->account->db])
            ->getMock();
        $compiler->expects($this->once())
            ->method('compileSubQuery')
            ->with($this->anything(), $subQuery)
            ->willReturn('SELECT 1 FROM DUAL');

        $query = new SugarQuery();
        $query->from($this->account, [
            'team_security' => false,
        ]);
        $query->joinTable($subQuery, [
            'alias' => 'q',
        ]);
        $builder = $compiler->compile($query);
        $join = $builder->getQueryPart('join');

        $this->assertSame([
            [
                'joinType' => 'inner',
                'joinTable' => '(SELECT 1 FROM DUAL)',
                'joinAlias' => 'q',
                'joinCondition' => null,
            ],
        ], $join['accounts']);
    }

    /**
     * @param boolean $addDeleted The value of the 'add_deleted' option
     * @param string $expectedWhere Expected WHERE expression
     * @param array $expectedParams Expected statement parameters
     * @param array $expectedTypes Expected types
     * @dataProvider compileWhereProvider
     */
    public function testCompileWhere($addDeleted, $expectedWhere, $expectedParams, $expectedTypes)
    {
        $options = [];
        $options['team_security'] = false;

        $query = new SugarQuery();
        $query->from($this->account, [
            'add_deleted' => $addDeleted,
            'team_security' => false,
        ]);
        $query->where()
            ->equals('industry', 'Apparel');
        $builder = $query->compile();

        $this->assertEquals($expectedWhere, $builder->getQueryPart('where'));
        $this->assertSame($expectedParams, $builder->getParameters());
        $this->assertSame($expectedTypes, $builder->getParameterTypes());
    }

    /**
     * @return array
     */
    public static function compileWhereProvider()
    {
        return [
            'consider-deleted-flag' => [
                true,
                // we don't enforce parentheses around simple expressions, but Doctrine CompositeExpression adds them
                '(accounts.industry = ?) AND (accounts.deleted = ?)',
                ['Apparel', 0],
                [ParameterType::STRING, ParameterType::BOOLEAN],
            ],
            'ignore-deleted-flag' => [
                false,
                'accounts.industry = ?',
                ['Apparel'],
                [ParameterType::STRING],
            ],
        ];
    }

    public function testCompileHaving()
    {
        $query = new SugarQuery();
        $query->from($this->account);
        $query->havingRaw('COUNT(id) > 3');
        $builder = $query->compile();

        $this->assertEquals('COUNT(id) > 3', $builder->getQueryPart('having'));
    }

    /**
     * @param string $orderBy ORDER BY columns from query
     * @param array $expected Expected ORDER BY part
     * @dataProvider compileOrderByWithJoinProvider
     */
    public function testCompileOrderByWithJoin(string $orderBy, array $expected)
    {
        $query = new SugarQuery();
        $query->from($this->account);
        $query->joinTable('opportunities', [
            'joinType' => 'left',
        ])->on()->equalsField('opportunities.account_id', 'accounts.id');

        $query->orderBy($orderBy, 'DESC');

        $builder = $query->compile();
        $actual = $builder->getQueryPart('orderBy');
        $this->assertEquals($expected, $actual, 'Expected order by not found for join.');
    }

    /**
     * @return array
     */
    public function compileOrderByWithJoinProvider()
    {
        return [
            'sort using column from first table in join' => [
                'accounts.name',
                [
                    'accounts.name DESC',
                    'accounts.id DESC',
                ],
            ],
            'sort using column from second table in join' => [
                'opportunities.name',
                [
                    'opportunities.name DESC',
                    'opportunities.id DESC',
                ],
            ],
        ];
    }

    public function testCompileOrderByCustomField()
    {
        SugarTestHelper::setUpCustomField('Accounts', [
            'name' => 'test',
            'type' => 'text',
        ]);
        $query = new SugarQuery();
        $query->from(BeanFactory::newBean('Accounts'));
        $query->joinTable('account_cstm', [
            'joinType' => 'left',
        ])->on()->equalsField('account_cstm.id_c', 'accounts.id');
        $query->orderBy('test_c', 'DESC');

        $builder = $query->compile();
        $actual = $builder->getQueryPart('orderBy');
        $this->assertEquals(
            ['accounts_cstm.test_c DESC', 'accounts_cstm.id_c DESC'],
            $actual,
            'Expected order by not found for custom field.'
        );
    }

    /**
     * @param array $orderBy ORDER BY columns from query
     * @param array $expected Expected ORDER BY part
     * @dataProvider compileOrderByProvider
     */
    public function testCompileOrderBy(array $orderBy, array $expected)
    {
        $query = new SugarQuery();
        $query->from($this->account);

        foreach ($orderBy as $column) {
            call_user_func_array([$query, 'orderBy'], $column);
        }

        $builder = $query->compile();

        // this is not us enforcing the DESC order by default, this is how Sugar works now
        $this->assertEquals($expected, $builder->getQueryPart('orderBy'));
    }

    /**
     * @return array
     */
    public static function compileOrderByProvider()
    {
        return [
            'id-is-added' => [
                [
                    ['name', 'DESC'],
                ],
                [
                    'accounts.name DESC',
                    'accounts.id DESC',
                ],
            ],
            'id-is-not-duplicated' => [
                [
                    ['id', 'DESC'],
                ],
                [
                    'accounts.id DESC',
                ],
            ],
            'direction-is-preserved' => [
                [
                    ['name', 'ASC'],
                ],
                [
                    'accounts.name ASC',
                    'accounts.id ASC',
                ],
            ],
            'empty-order-is-preserved' => [
                [],
                [],
            ],
            'non-db-columns-are-ignored' => [
                [
                    ['members'],
                ],
                [
                    'accounts.id DESC',
                ],
            ],
        ];
    }

    /**
     * @param callable $where
     * @dataProvider compileConditionProvider
     */
    public function testCompileCondition(callable $where, $expectedWhere, $expectedParams = [])
    {
        $query = $this->getQuery();
        $where($query->where());

        $compiler = $this->getCompilerWithCollationCaseSensitivity(false);
        $builder = $compiler->compile($query);

        $this->assertEquals($expectedWhere, (string)$builder->getQueryPart('where'));
        $this->assertSame($expectedParams, $builder->getParameters());
    }

    /**
     * @return array
     */
    public static function compileConditionProvider()
    {
        return [
            'is-null' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->isNull('industry');
                },
                'accounts.industry IS NULL',
            ],
            'is-not-null' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->notNull('industry');
                },
                'accounts.industry IS NOT NULL',
            ],
            'in' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', ['Apparel', 'Banking']);
                },
                'accounts.industry IN (?,?)',
                ['Apparel', 'Banking'],
            ],
            'in-empty-set' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', []);
                },
                'accounts.industry IN (NULL)',
            ],
            'in-sub-query' => [
                function (SugarQuery_Builder_Where $where) {
                    $subQuery = new SugarQuery();
                    $subQuery->from(BeanFactory::newBean('Accounts'), [
                        'add_deleted' => false,
                        'team_security' => false,
                    ]);
                    $subQuery->select('id');
                    $subQuery->where()
                        ->equals('industry', 'Apparel');
                    $where->in('id', $subQuery);
                },
                'accounts.id IN (SELECT accounts.id FROM accounts WHERE accounts.industry = ?)',
                ['Apparel'],
            ],
            'not-in' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->notIn('industry', ['Retail', 'Shipping']);
                },
                'accounts.industry IS NULL OR accounts.industry NOT IN (?,?)',
                ['Retail', 'Shipping'],
            ],
            'equal-field' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->equalsField('industry', 'account_type');
                },
                'accounts.industry = accounts.account_type',
            ],
            'not-equal-field' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->notEqualsField('industry', 'account_type');
                },
                'accounts.industry != accounts.account_type',
            ],
            'compare-field' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->gt('industry', ['$field' => 'account_type']);
                },
                'accounts.industry > accounts.account_type',
            ],
            'between' => [
                function (SugarQuery_Builder_Where $where) {
                    // it's a bad example, but Accounts doesn't have numeric fields,
                    // while the SQL for DATE fields will depend on the current DB platform
                    $where->between('rating', 'good', 'bad');
                },
                'accounts.rating BETWEEN ? AND ?',
                ['good', 'bad'],
            ],
            'starts-with' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->starts('name', 'A');
                },
                'accounts.name LIKE ?',
                ['A%'],
            ],
            /* temporarily disable this for BR-4919
            'ends-with-escaping' => array(
                function (SugarQuery_Builder_Where $where) {
                    $where->ends('name', '%_!');
                },
                'accounts.name LIKE ? ESCAPE \'!\'',
                array(
                    0 => '%!%!_!!',
                ),
            ),
            */
            'does-not-contain-array' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->notContains('name', ['X', 'Y']);
                },
                'accounts.name IS NULL OR (accounts.name NOT LIKE ? AND accounts.name NOT LIKE ?)',
                ['%X%', '%Y%'],
            ],
            'like' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->like('name', '%X%Y%Z%');
                },
                'accounts.name LIKE ?',
                ['%X%Y%Z%'],
            ],
            'in-set-with-int-0' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', [0]);
                },
                'accounts.industry IN (?)',
                [0],
            ],
            'in-set-with-empty-string' => [
                function (SugarQuery_Builder_Where $where) {
                    $where->in('industry', ['']);
                },
                '(accounts.industry IS NULL) OR (accounts.industry IN (?))',
                [''],
            ],
        ];
    }

    public function testCompileConditionCaseSensitive()
    {
        $query = $this->getQuery();
        $query->where()
            ->notContains('name', ['x', 'y']);

        $compiler = $this->getCompilerWithCollationCaseSensitivity(true);
        $builder = $compiler->compile($query);

        $this->assertEquals(
            'accounts.name IS NULL OR (UPPER(accounts.name) NOT LIKE ? AND UPPER(accounts.name) NOT LIKE ?)',
            $builder->getQueryPart('where')
        );
        $this->assertSame(['%X%', '%Y%'], $builder->getParameters());
    }

    private function getQuery(array $options = [])
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
    private function getCompilerWithCollationCaseSensitivity($value)
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

    public function testCompileCompareWithSubQuery()
    {
        $subQuery = $this->getQuery();
        $subQuery->select('industry');
        $subQuery->where()
            ->equals('id', 'ACCOUNT_ID');

        $query = $this->getQuery([
            'add_deleted' => true,
        ]);
        $query->select('name');
        $query->where()
            ->equals('industry', $subQuery);

        $builder = $query->compile();

        $this->assertEquals(
            'SELECT accounts.name FROM accounts WHERE (accounts.industry = ('
            . 'SELECT accounts.industry FROM accounts WHERE accounts.id = ?'
            . ')) AND (accounts.deleted = ?)',
            $builder->getSQL()
        );
        $this->assertSame(['ACCOUNT_ID', 0], $builder->getParameters());
        $this->assertSame([ParameterType::STRING, ParameterType::BOOLEAN], $builder->getParameterTypes());
    }

    public function testCompileUnion()
    {
        $query1 = $this->getQuery();
        $query1->select('name');
        $query1->where()
            ->equals('industry', 'Apparel');

        $query2 = $this->getQuery();
        $query2->select('name');
        $query2->where()
            ->equals('account_type', 'Analyst');

        $query = new SugarQuery();
        $query->union($query1);
        $query->union($query2);
        $query->orderBy('name', 'ASC');

        $builder = $query->compile();

        $this->assertEquals(
            '(SELECT accounts.name FROM accounts WHERE accounts.industry = ?)'
            . ' UNION ALL'
            . ' (SELECT accounts.name FROM accounts WHERE accounts.account_type = ?)'
            . ' ORDER BY name ASC',
            $builder->getSQL()
        );
        $this->assertSame(['Apparel', 'Analyst'], $builder->getParameters());
        $this->assertSame([ParameterType::STRING, ParameterType::STRING], $builder->getParameterTypes());

        $result = $builder->execute();
        $this->assertInstanceOf(Doctrine\DBAL\Result::class, $result);
    }

    /**
     * test joinErasedFields
     * @dataProvider providerTestJoinErasedFields
     */
    public function testJoinErasedFields($messsge, $isPiiFieldsSelected, $expected)
    {
        /** @var SugarQuery_Compiler_Doctrine|MockObject $compiler */
        $compiler = $this->getMockBuilder(SugarQuery_Compiler_Doctrine::class)
            ->setMethods(['isPiiFieldsSelected'])
            ->setConstructorArgs([$this->account->db])
            ->getMock();

        $compiler->expects($this->any())
            ->method('isPiiFieldsSelected')
            ->willReturn($isPiiFieldsSelected);

        $query = $this->getQuery();
        $query->select('name');
        $query->where()->equals('industry', 'Apparel');
        $builder = $query->compile();

        $bean = $this->getMockBuilder(SugarBean::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $bean->db = $this->account->db;

        SugarTestReflection::callProtectedMethod($compiler, 'joinErasedFields', [$builder, $query, $bean, 'acct_alias', '']);

        $this->assertSame($expected, $builder->getSQL(), 'test failed: ' . $messsge);
    }

    public function providerTestJoinErasedFields()
    {
        return [
            ['no pii field is selected', false, 'SELECT accounts.name FROM accounts WHERE accounts.industry = ?'],
            // @codingStandardsIgnoreStart
            ['pii fields are selected', true, 'SELECT accounts.name, acct_alias_erased.data  FROM accounts LEFT JOIN erased_fields acct_alias_erased ON acct_alias_erased.bean_id = acct_alias.id AND acct_alias_erased.table_name = ? WHERE accounts.industry = ?'],
            // @codingStandardsIgnoreEnd
        ];
    }

    /**
     * @dataProvider providerTestIsPiiFieldsSelected
     */
    public function testIsPiiFieldsSelected($message, $hasPiiFields, $selectedFields, $selectedPiiFields, $expected)
    {
        $bean = $this->getMockBuilder(SugarBean::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasPiiFields', 'getFieldDefinitions'])
            ->getMock();

        $bean->db = $this->account->db;

        $bean->expects($this->any())
            ->method('hasPiiFields')
            ->willReturn($hasPiiFields);

        $bean->expects($this->any())
            ->method('getFieldDefinitions')
            ->willReturn($selectedPiiFields);

        /** @var SugarQuery_Compiler_Doctrine|MockObject $compiler */
        $compiler = $this->getMockBuilder(SugarQuery_Compiler_Doctrine::class)
            ->setConstructorArgs([$this->account->db])
            ->setMethods(['getSelectFieldsByTable'])
            ->getMock();

        $compiler->expects($this->any())
            ->method('getSelectFieldsByTable')
            ->willReturn($selectedFields);

        $query = $this->getMockBuilder(SugarQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = SugarTestReflection::callProtectedMethod($compiler, 'isPiiFieldsSelected', [$bean, $query, 'acct']);

        $this->assertSame($expected, $result, 'failed at test: ' . $message);
    }

    public function providerTestIsPiiFieldsSelected()
    {
        return [
            ['has no pii field', false, ['name', 'title'], ['name' => 1], false],
            ['has pii field and selected pii fields', true, ['name', 'title'], ['name' => 1], true],
            ['has pii field and selected pii fields custom field', true, ['name_c', 'title'], ['name_c' => 1], true],
            ['has pii field and no selected fields', true, [], ['name' => 1], false],
            ['has pii field and no pii field is selected', true, ['name', 'title'], [], false],
            ['has pii field and selected fields do not interect pii fields', true, ['name', 'title'], ['any_name' => 1], false],
        ];
    }
}
