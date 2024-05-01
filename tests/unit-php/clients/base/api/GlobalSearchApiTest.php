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

namespace Sugarcrm\SugarcrmTestsUnit\clients\base\api;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\ResultSet;
use Sugarcrm\Sugarcrm\Elasticsearch\Mapping\Mapping;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \GlobalSearchApi
 */
class GlobalSearchApiTest extends TestCase
{
    /**
     * @covers ::parseArguments
     * @dataProvider providerTestParseArguments
     */
    public function testParseArguments(array $args, $moduleList, $q, $limit, $offset)
    {
        $sut = $this->getGlobalSearchApiMock();
        TestReflection::callProtectedMethod($sut, 'parseArguments', [$args]);

        $this->assertSame($moduleList, TestReflection::getProtectedValue($sut, 'moduleList'));
        $this->assertSame($q, TestReflection::getProtectedValue($sut, 'term'));
        $this->assertSame($limit, TestReflection::getProtectedValue($sut, 'limit'));
        $this->assertSame($offset, TestReflection::getProtectedValue($sut, 'offset'));
    }

    public function providerTestParseArguments()
    {
        return [

            // defaults
            [
                [],
                [],
                '',
                20,
                0,
            ],

            // valid settings
            [
                [
                    'module_list' => 'Accounts,Contacts',
                    'q' => 'swaffelen',
                    'max_num' => 50,
                    'offset' => 100,
                ],
                ['Accounts', 'Contacts'],
                'swaffelen',
                50,
                100,
            ],

            // cast integers
            [
                [
                    'module_list' => 'Leads',
                    'q' => 'more stuff',
                    'max_num' => 'invalid',
                    'offset' => 5.30,
                ],
                ['Leads'],
                'more stuff',
                0,
                5,
            ],
        ];
    }

    /**
     * @covers ::executeGlobalSearch
     */
    public function testExecuteGlobalSearch()
    {
        $engine = $this->createMock(\Sugarcrm\Sugarcrm\SearchEngine\Capability\GlobalSearch\GlobalSearchCapable::class);

        $expectedCalls = [
            'from',
            'getTags',
            'setTagLimit',
            'setFilters',
            'term',
            'limit',
            'offset',
            'fieldBoost',
            'highlighter',
        ];

        foreach ($expectedCalls as $callMe) {
            $engine->expects($this->once())
                ->method($callMe)
                ->will($this->returnValue($engine));
        }

        $sut = $this->getGlobalSearchApiMock();
        TestReflection::callProtectedMethod($sut, 'executeGlobalSearch', [$engine]);
    }

    /**
     * @covers ::formatResults
     * @dataProvider providerTestFormatResults
     */
    public function testFormatResults(array $hits, array $expected)
    {
        $api = $this->getRestServiceMock([]);
        $resultSet = $this->getFormatResultsFixture($hits);

        $sut = $this->getGlobalSearchApiMock(['formatBeanFromResult']);
        $sut->expects($this->exactly(count($hits)))
            ->method('formatBeanFromResult')
            ->will($this->returnCallback([$this, 'formatBeanFromResult']));

        $actual = TestReflection::callProtectedMethod($sut, 'formatResults', [$api, [], $resultSet]);
        $this->assertEquals($expected, $actual);
    }

    public function providerTestFormatResults()
    {
        return [

            // no score or highlights available
            [
                [
                    [
                        '_id' => '123',
                        Mapping::MODULE_NAME_FIELD => 'Accounts',
                        '_source' => [
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ],
                    ],
                    [
                        '_id' => '456',
                        Mapping::MODULE_NAME_FIELD => 'Contacts',
                        '_source' => [
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ],
                    ],
                ],
                [
                    [
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                    ],
                    [
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                    ],
                ],
            ],

            // score and highlights on one entry
            [
                [
                    [
                        '_id' => '123',
                        Mapping::MODULE_NAME_FIELD => 'Accounts',
                        '_source' => [
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ],
                        '_score' => 1.80,
                        'highlight' => [
                            'name' => ['hl1', 'hl2'],
                        ],
                    ],
                    [
                        '_id' => '456',
                        Mapping::MODULE_NAME_FIELD => 'Contacts',
                        '_source' => [
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ],
                    ],
                ],
                [
                    [
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                        '_score' => 1.80,
                        '_highlights' => [
                            'name' => ['hl1', 'hl2'],
                        ],
                    ],
                    [
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                    ],
                ],
            ],

            // score and highlights mixed
            [
                [
                    [
                        '_id' => '123',
                        Mapping::MODULE_NAME_FIELD => 'Accounts',
                        '_source' => [
                            'id' => '123',
                            'name' => 'SugarCRM',
                        ],
                        'highlight' => [
                            'name' => ['hl1', 'hl2'],
                        ],
                    ],
                    [
                        '_id' => '456',
                        Mapping::MODULE_NAME_FIELD => 'Contacts',
                        '_source' => [
                            'id' => '456',
                            'first_name' => 'skymeyer',
                        ],
                        '_score' => 1.50,
                    ],
                ],
                [
                    [
                        'id' => '123',
                        'name' => 'SugarCRM',
                        '_module' => 'Accounts',
                        '_highlights' => [
                            'name' => ['hl1', 'hl2'],
                        ],
                    ],
                    [
                        'id' => '456',
                        'first_name' => 'skymeyer',
                        '_module' => 'Contacts',
                        '_score' => 1.50,
                    ],
                ],
            ],
        ];
    }

    /**
     * Callback for testFormatResults
     */
    public function formatBeanFromResult()
    {
        $args = func_get_args();
        $result = $args[2];
        $beanData = $result->getData();
        $beanData['_module'] = $result->getType();
        return $beanData;
    }

    /**
     * @param null|array $methods
     * @return \GlobalSearchApi
     */
    protected function getGlobalSearchApiMock($methods = null)
    {
        return $this->getMockBuilder('GlobalSearchApi')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return \RestService
     */
    protected function getRestServiceMock($methods = null)
    {
        return $this->getMockBuilder('RestService')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Fixture helper for testFormatResults
     * @param array $hits
     * @return ResultSet
     */
    protected function getFormatResultsFixture(array $hits)
    {
        $elasticaResults = [];

        foreach ($hits as $hit) {
            $elasticaResults[] = new \Elastica\Result($hit);
        }

        $response = $this->createMock('\\' . \Elastica\Response::class);
        $query = $this->createMock('\\' . \Elastica\Query::class);
        $elasticaResultSet = $this->getMockBuilder('\\' . \Elastica\ResultSet::class)
            ->setConstructorArgs([$response, $query, $elasticaResults])->setMethods(null)->getMock();

        return new ResultSet($elasticaResultSet);
    }
}
