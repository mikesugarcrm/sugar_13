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

use Elastica\Exception\ResponseException;
use Elastica\Request;
use Elastica\Response;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Exception\QueryBuilderException;
use Sugarcrm\Sugarcrm\Elasticsearch\Provider\GlobalSearch\Highlighter;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\MatchAllQuery;
use Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Query\QueryBuilder
 */
class QueryBuilderTest extends TestCase
{
    /**
     * @covers ::buildPostFilters
     * @dataProvider providerBuildPostFilters
     *
     * @param array $filterParams : a list of post filters' parameters
     * @param array $outputArray : the expected value of the output filter in array format
     */
    public function testBuildPostFilters($filterParams, $outputArray)
    {
        $builder = $this->getQueryBuilderMock();

        $postFilters = [];
        foreach ($filterParams as $key => $value) {
            $termFilter = new \Elastica\Query\Term();
            $termFilter->setTerm($key, $value);
            $postFilters[] = $termFilter;
        }

        $result = TestReflection::callProtectedMethod($builder, 'buildPostFilters', [$postFilters]);

        $this->assertEquals($result->toArray(), $outputArray);
    }

    public function providerBuildPostFilters()
    {
        return [
            [
                ['_type' => 'Accounts', 'assigned_user_id' => 'seed_max_id'],
                [
                    'bool' => [
                        'must' => [
                            '0' => ['term' => ['_type' => ['value' => 'Accounts', 'boost' => 1.0]]],
                            '1' => ['term' => ['assigned_user_id' => ['value' => 'seed_max_id', 'boost' => 1.0]]],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::__construct
     */
    public function testConstructor()
    {
        $containerMock = TestMockHelper::getObjectMock($this, \Sugarcrm\Sugarcrm\Elasticsearch\Container::class);
        $queryBuilderMock = new QueryBuilder($containerMock);

        $this->assertInstanceOf(
            '\\' . \Sugarcrm\Sugarcrm\Elasticsearch\Container::class,
            TestReflection::getProtectedValue($queryBuilderMock, 'container')
        );
    }

    /**
     * @covers ::getUser
     * @covers ::setUser
     */
    public function testSetUser()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $userMock = TestMockHelper::getObjectMock($this, \User::class);
        $queryBuilderMock->setUser($userMock);
        $this->assertSame($userMock, $queryBuilderMock->getUser());
    }

    /**
     * @covers ::disableVisibility
     */
    public function testDisableVisibility()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->disableVisibility();
        $this->assertFalse(TestReflection::getProtectedValue($queryBuilderMock, 'applyVisibility'));
    }

    /**
     * @covers ::setQuery
     */
    public function testSetQuery()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $matchAllQuery = new MatchAllQuery();
        $queryBuilderMock->setQuery($matchAllQuery);
        $this->assertSame($matchAllQuery, TestReflection::getProtectedValue($queryBuilderMock, 'query'));
    }

    /**
     * @covers ::getModules
     * @covers ::setModules
     *
     * @dataProvider providerTestSetModules
     */
    public function testSetModules($disableVisibility, array $modules, array $allowedModules, $expectedModules)
    {
        $queryBuilderMock = $this->getQueryBuilderMock(['getAllowedModules']);
        $queryBuilderMock->expects($this->any())
            ->method('getAllowedModules')
            ->will($this->returnValue($allowedModules));

        if ($disableVisibility) {
            $queryBuilderMock->disableVisibility();
        }

        $queryBuilderMock->setModules($modules);
        $this->assertSame($expectedModules, $queryBuilderMock->getModules());
    }

    public function providerTestSetModules()
    {
        return [
            [
                false,
                ['Accounts', 'Contacts'],
                ['Accounts', 'Emails'],
                ['Accounts', 'Emails'],
            ],
            [
                true,
                ['Accounts', 'Contacts'],
                ['Accounts', 'Emails'],
                ['Accounts', 'Contacts'],
            ],
        ];
    }

    /**
     * @covers ::setLimit
     * @covers ::setOffset
     * @covers ::setSort
     * @covers ::setExplain
     *
     * @dataProvider providerTestSets
     */
    public function testSets($limit, $offset, $sort, $explain, $expected)
    {
        $queryBuilderMock = $this->getQueryBuilderMock();

        if (!empty($limit)) {
            $queryBuilderMock->setLimit($limit);
        }

        if (!empty($offset)) {
            $queryBuilderMock->setOffset($offset);
        }

        if (!empty($sort)) {
            $queryBuilderMock->setSort($sort);
        }

        $queryBuilderMock->setExplain($explain);

        $properties = ['limit', 'offset', 'sort', 'explain'];
        foreach ($properties as $property) {
            $this->assertSame($expected[$property], TestReflection::getProtectedValue($queryBuilderMock, $property));
        }
    }

    public function providerTestSets()
    {
        return [
            [
                10,
                20,
                ['id', 'name'],
                true,
                [
                    'limit' => 10,
                    'offset' => 20,
                    'sort' => ['id', 'name'],
                    'explain' => true,
                ],
            ],
            [
                null,
                20,
                ['id', 'name'],
                false,
                [
                    'limit' => null,
                    'offset' => 20,
                    'sort' => ['id', 'name'],
                    'explain' => false,
                ],
            ],
            [
                null,
                20,
                [],
                true,
                [
                    'limit' => null,
                    'offset' => 20,
                    'sort' => ['_score'], //default sort
                    'explain' => true,
                ],
            ],
        ];
    }

    /**
     * @covers ::setHighLighter
     */
    public function testSetHighLighter()
    {
        $highlighter = new Highlighter();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->setHighlighter($highlighter);
        $this->assertSame($highlighter, TestReflection::getProtectedValue($queryBuilderMock, 'highlighter'));
    }

    /**
     * @covers ::build
     * @covers ::buildQuery
     * @covers ::addSettingsAfterBuild
     * @covers ::addFilter
     * @covers ::buildFilters
     */
    public function testBuild()
    {
        // create MultiMatchQuery mock
        $query = TestMockHelper::getObjectMock(
            $this,
            \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery::class,
            [
                'getReadAccessibleSearchFields',
                'getReadOwnerSearchFields',
            ]
        );
        $query->setTerms('abc');
        $query->expects($this->any())
            ->method('getReadAccessibleSearchFields')
            ->will($this->returnValue(['id']));
        $query->expects($this->any())
            ->method('getReadOwnerSearchFields')
            ->will($this->returnValue([]));

        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryBuilderMock->disableVisibility();
        $queryBuilderMock->setQuery($query);
        $queryBuilderMock->setLimit(100);
        $queryBuilderMock->setOffset(10);

        $returnQuery = $queryBuilderMock->build();
        $resultArray = $returnQuery->toArray();
        $this->assertSame(100, $resultArray['size']);
        $this->assertSame(10, $resultArray['from']);
        $this->assertSame(['_score'], $resultArray['sort']);
        $expecteQuery = [
            [
                'bool' => [
                    'should' => [
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'multi_match' => [
                                            'type' => 'cross_fields',
                                            'query' => 'abc',
                                            'fields' => [
                                                0 => 'id',
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
        ];

        $this->assertSame($expecteQuery, $resultArray['query']['bool']['must']);
    }

    /**
     * @covers ::executeSearch
     * @covers ::createResultSet
     */
    public function testExecuteSearch()
    {
        // create MultiMatchQuery mock
        $query = $this->getQueryMock();

        // ResultSet Mock
        $responseMock = TestMockHelper::getObjectMock($this, Response::class);
        $resultSetMock = TestMockHelper::getObjectMock($this, \Elastica\ResultSet::class, ['getResponse']);
        $resultSetMock->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        // Search Mock
        $searchMock = TestMockHelper::getObjectMock($this, \Elastica\Search::class, ['search']);
        $searchMock->expects($this->any())
            ->method('search')
            ->will($this->returnValue($resultSetMock));

        // QueryBuilder Mock
        $queryBuilderMock = $this->getQueryBuilderWithMethodsMock($searchMock);

        $userMock = TestMockHelper::getObjectMock($this, \User::class);
        $queryBuilderMock->setUser($userMock);
        $queryBuilderMock->setModules(['Accounts']);
        $queryBuilderMock->setQuery($query);
        $queryBuilderMock->disableVisibility();

        // call executeSearch
        $searchResult = $queryBuilderMock->executeSearch();
        $this->assertNotEmpty($searchResult->getResponse());
    }

    /**
     * @covers ::executeSearch
     */
    public function testExecuteSearchWithResponseException()
    {
        // create MultiMatchQuery mock
        $queryMock = $this->getQueryMock();

        // Search Mock
        $requestMock = TestMockHelper::getObjectMock($this, Request::class);
        $responseMock = TestMockHelper::getObjectMock($this, Response::class);

        $searchMock = TestMockHelper::getObjectMock($this, \Elastica\Search::class, ['search']);
        $searchMock->expects($this->any())
            ->method('search')
            ->will($this->throwException(new ResponseException($requestMock, $responseMock)));

        // QueryBuilder Mock
        $queryBuilderMock = $this->getQueryBuilderWithMethodsMock($searchMock);

        $userMock = TestMockHelper::getObjectMock($this, \User::class);
        $queryBuilderMock->setUser($userMock);
        $queryBuilderMock->setModules(['Accounts']);
        $queryBuilderMock->setQuery($queryMock);
        $queryBuilderMock->disableVisibility();

        // call executeSearch
        $searchResult = $queryBuilderMock->executeSearch();
        $this->assertNotEmpty($searchResult->getResponse());
    }

    /**
     * @covers ::executeSearch
     *
     */
    public function testExecuteSearchWithNonResponseException()
    {
        $this->expectException(\Exception::class);

        // Search Mock
        $searchMock = TestMockHelper::getObjectMock($this, \Elastica\Search::class, ['search']);
        $searchMock->expects($this->any())
            ->method('search')
            ->will($this->throwException(new \Exception()));

        // QueryBuilder Mock
        $queryBuilderMock = $this->getQueryBuilderWithMethodsMock($searchMock);

        $userMock = TestMockHelper::getObjectMock($this, \User::class);
        $queryMock = $this->getQueryMock();

        $queryBuilderMock->setUser($userMock);
        $queryBuilderMock->setModules(['Accounts']);
        $queryBuilderMock->setQuery($queryMock);
        $queryBuilderMock->disableVisibility();

        // call executeSearch
        $searchResult = $queryBuilderMock->executeSearch();
    }

    /**
     * @covers ::executeSearch
     */
    public function testExcuteSearchNoUserException()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $this->expectException(QueryBuilderException::class);
        $queryBuilderMock->executeSearch();
    }

    /**
     * @covers ::executeSearch
     */
    public function testExcuteSearchNoModuleException()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $userMock = TestMockHelper::getObjectMock($this, \User::class);
        $queryBuilderMock->setUser($userMock);

        $this->expectException(QueryBuilderException::class);
        $queryBuilderMock->executeSearch();
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilderMock(array $methods = null)
    {
        return TestMockHelper::getObjectMock($this, QueryBuilder::class, $methods);
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilderWithMethodsMock($searchMock)
    {
        $methods = [
            'newSearchObject',
            'getAllowedModules',
            'getReadIndices',
        ];
        $queryBuilderMock = TestMockHelper::getObjectMock($this, QueryBuilder::class, $methods);
        if (!empty($searchMock)) {
            $queryBuilderMock->expects($this->any())
                ->method('newSearchObject')
                ->will($this->returnValue($searchMock));
        }

        $queryBuilderMock->expects($this->any())
            ->method('getAllowedModules')
            ->will($this->returnValue(['Accounts']));

        $queryBuilderMock->expects($this->any())
            ->method('getReadIndices')
            ->will($this->returnValue(['Accounts']));

        return $queryBuilderMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getQueryMock()
    {
        $queryMock = TestMockHelper::getObjectMock(
            $this,
            \Sugarcrm\Sugarcrm\Elasticsearch\Query\MultiMatchQuery::class,
            ['getReadAccessibleSearchFields', 'getReadOwnerSearchFields']
        );
        $queryMock->setTerms('abc');
        $queryMock->expects($this->any())
            ->method('getReadAccessibleSearchFields')
            ->will($this->returnValue(['id']));
        $queryMock->expects($this->any())
            ->method('getReadOwnerSearchFields')
            ->will($this->returnValue([]));

        return $queryMock;
    }
}
