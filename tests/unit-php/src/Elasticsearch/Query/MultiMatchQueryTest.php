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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Query;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\SearchFields;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
 */
class MultiMatchQueryTest extends TestCase
{
    /**
     * @covers ::setOperator
     * @param $operaor
     * @param $expectecd
     *
     * @dataProvider providerSetOperatorTest
     */
    public function testSetOperator($operaor, $expectecd)
    {
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setOperator($operaor);
        $this->assertSame($expectecd, TestReflection::getProtectedValue($multiMatchQueryMock, 'defaultOperator'));
    }

    public function providerSetOperatorTest()
    {
        return [
            ['AND', 'AND'],
            ['OR', 'OR'],
            ['NOT', 'NOT'],
            ['|', 'OR'],
            ['&', 'AND'],
            ['-', 'NOT'],
            // empty string
            ['', false],
            // operator is case-sensitive
            ['and', false],
            ['or', false],
            ['not', false],
        ];
    }

    /**
     * @covers ::setTerms
     * @param string $terms
     * @param $expectecd
     *
     * @dataProvider providerSetTermsTest
     */
    public function testSetTerms($terms, $expectecd)
    {
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setTerms($terms);
        $this->assertSame($expectecd, TestReflection::getProtectedValue($multiMatchQueryMock, 'terms'));
    }

    public function providerSetTermsTest()
    {
        return [
            ['abc AND def', 'abc AND def'],
            ['abc OR def', 'abc OR def'],
            ['abc NOT def', 'abc NOT def'],
            ['abc | def', 'abc | def'],
            ['abc & def', 'abc & def'],
            ['abc -def', 'abc -def'],
            ['abc def', 'abc def'],
        ];
    }

    /**
     * @covers ::setSearchFields
     */
    public function testSetSearchFields()
    {
        $searchFields = new SearchFields();
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setSearchFields($searchFields);
        $this->assertSame($searchFields, TestReflection::getProtectedValue($multiMatchQueryMock, 'searchFields'));
    }

    /**
     * @covers ::setUser
     */
    public function testSetUser()
    {
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $multiMatchQueryMock = $this->getMultiMatchQueryMock();
        $multiMatchQueryMock->setUser($userMock);
        $this->assertSame($userMock, TestReflection::getProtectedValue($multiMatchQueryMock, 'user'));
    }

    /**
     * @covers ::build
     * @covers ::buildBoolQuery
     * @covers ::createMultiMatchQuery
     * @covers ::buildMultiMatchQuery
     *
     *
     * @dataProvider providerTestBuild
     */
    public function testBuild(string $terms, string $operator, bool $useShortcut, array $searchFields, array $expected)
    {
        $multimatchQueryMock = $this->getMultiMatchQueryMock([
            'getReadAccessibleSearchFields',
            'getReadOwnerSearchFields',
        ]);

        $multimatchQueryMock->expects($this->any())
            ->method('getReadAccessibleSearchFields')
            ->will($this->returnValue($searchFields));

        $multimatchQueryMock->expects($this->any())
            ->method('getReadOwnerSearchFields')
            ->will($this->returnValue([]));

        $multimatchQueryMock->setOperator($operator);
        $multimatchQueryMock->setUseShortcutOperator($useShortcut);
        $userMock = TestMockHelper::getObjectMock($this, '\User');
        $userMock->id = '100';
        $multimatchQueryMock->setUser($userMock);
        $multimatchQueryMock->setTerms($terms);

        $query = $multimatchQueryMock->build();
        $this->assertSame($expected, $query->toArray());
    }

    public function providerTestBuild()
    {
        return [
            // using operator shortcut
            // Single term, default space operator is AND, use operator shortcut
            [
                'abcdef',
                '&',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abcdef',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // AND Operator, default space operator is AND
            [
                'abc AND def',
                'AND',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // No operator provided, default space operator is AND
            [
                'abc def',
                'AND',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // OR operator, default space operator is AND
            [
                'abc | def',
                '&',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // NOT operator
            [
                'abc -def',
                '&',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'bool' => [
                                                'should' => [
                                                    [
                                                        'multi_match' => [
                                                            'type' => 'cross_fields',
                                                            'query' => 'abc',
                                                            'fields' => [
                                                                0 => 'id',
                                                                1 => 'name',
                                                            ],
                                                            'tie_breaker' => 1.0,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'must_not' => [
                                        [
                                            'bool' => [
                                                'should' => [
                                                    [
                                                        'multi_match' => [
                                                            'type' => 'cross_fields',
                                                            'query' => 'def',
                                                            'fields' => [
                                                                0 => 'id',
                                                                1 => 'name',
                                                            ],
                                                            'tie_breaker' => 1.0,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Single term, default space operator is 'OR'
            [
                'abcdef',
                'OR',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abcdef',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // AND Operator, default space operator is 'OR'
            [
                'abc AND def',
                '|',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // No operator provided, default space operator is 'OR'
            [
                'abc def',
                'OR',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // OR operator, default space operator is 'OR'
            [
                'abc | def',
                'OZR',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // NOT operator, default operator is 'OR'
            [
                'abc -def',
                'OR',
                true,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'must' => [
                                        [
                                            'bool' => [
                                                'should' => [
                                                    [
                                                        'multi_match' => [
                                                            'type' => 'cross_fields',
                                                            'query' => 'abc',
                                                            'fields' => [
                                                                0 => 'id',
                                                                1 => 'name',
                                                            ],
                                                            'tie_breaker' => 1.0,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'must_not' => [
                                        [
                                            'bool' => [
                                                'should' => [
                                                    [
                                                        'multi_match' => [
                                                            'type' => 'cross_fields',
                                                            'query' => 'def',
                                                            'fields' => [
                                                                0 => 'id',
                                                                1 => 'name',
                                                            ],
                                                            'tie_breaker' => 1.0,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // don't use operator shortcut
            // OR operator, default space operator is AND
            [
                'abc | def',
                '&',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // NOT operator
            [
                'abc -def',
                '&',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => '-def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Single term, default space operator is 'OR'
            [
                'abcdef',
                'OR',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abcdef',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // AND Operator, default space operator is 'OR'
            [
                'abc AND def',
                '|',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'must' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // No operator provided, default space operator is 'OR'
            [
                'abc def',
                'OR',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // OR operator, default space operator is 'OR'
            [
                'abc | def',
                'OR',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // NOT operator, default operator is 'OR'
            [
                'abc -def',
                'OR',
                false,
                ['id', 'name'],
                [
                    'bool' => [
                        'should' => [
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'multi_match' => [
                                                'type' => 'cross_fields',
                                                'query' => 'abc -def',
                                                'fields' => [
                                                    0 => 'id',
                                                    1 => 'name',
                                                ],
                                                'tie_breaker' => 1.0,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery
     */
    protected function getMultiMatchQueryMock(array $methods = null)
    {
        return TestMockHelper::getObjectMock($this, \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery::class, $methods);
    }
}
