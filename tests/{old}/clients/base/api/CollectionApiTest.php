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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers CollectionApi
 */
class CollectionApiTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = $this->getMockForAbstractClass('CollectionApi');
    }

    /**
     * @dataProvider buildResponseProvider
     */
    public function testBuildResponse(array $records, array $offsets, array $errors, array $response)
    {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'buildResponse',
            [$records, $offsets, $errors]
        );

        $this->assertEquals($response, $actual);
    }

    public function buildResponseProvider()
    {
        $exception = new SugarApiExceptionNotAuthorized('SUGAR_API_EXCEPTION_RECORD_NOT_AUTHORIZED', ['view']);

        return [
            'no_errors' => [
                [
                    [
                        'a' => 'x',
                        '_link' => 'l1',
                    ],
                    [
                        'a' => 'y',
                        '_link' => 'l2',
                    ],
                ],
                [
                    'l1' => 1,
                    'l2' => -1,
                ],
                [],
                [
                    'records' => [
                        [
                            'a' => 'x',
                            '_link' => 'l1',
                        ],
                        [
                            'a' => 'y',
                            '_link' => 'l2',
                        ],
                    ],
                    'next_offset' => [
                        'l1' => 1,
                        'l2' => -1,
                    ],
                ],
            ],
            'all_errors' => [
                [],
                [
                    'l1' => -1,
                    'l2' => -1,
                ],
                [
                    'l1' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                    'l2' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                ],
                [
                    'records' => [],
                    'next_offset' => [
                        'l1' => -1,
                        'l2' => -1,
                    ],
                    'errors' => [
                        'l1' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                        'l2' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                    ],
                ],
            ],
            'some_errors' => [
                [
                    [
                        'a' => 'x',
                        '_link' => 'l1',
                    ],
                    [
                        'a' => 'y',
                        '_link' => 'l2',
                    ],
                ],
                [
                    'l1' => 1,
                    'l2' => -1,
                ],
                [
                    'l2' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                ],
                [
                    'records' => [
                        [
                            'a' => 'x',
                            '_link' => 'l1',
                        ],
                        [
                            'a' => 'y',
                            '_link' => 'l2',
                        ],
                    ],
                    'next_offset' => [
                        'l1' => 1,
                        'l2' => -1,
                    ],
                    'errors' => [
                        'l2' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testGetData()
    {
        /** @var CollectionApi|MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(['getSourceArguments', 'getSourceData'])
            ->getMockForAbstractClass();
        $api->expects($this->exactly(2))
            ->method('getSourceArguments')
            ->will($this->returnCallback(function () {
                return [];
            }));
        $api->expects($this->exactly(2))
            ->method('getSourceData')
            ->will($this->onConsecutiveCalls([
                'records' => [
                    ['name' => 'a'],
                ],
            ], [
                'records' => [
                    ['name' => 'c1'],
                    ['name' => 'c2'],
                ],
            ]));

        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('getSources')
            ->willReturn(['a', 'b', 'c']);

        $service = SugarTestRestUtilities::getRestServiceMock();

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'getData',
            [
                $service,
                [
                    'offset' => [
                        'a' => 0,
                        'b' => -1,
                        'c' => 1,
                    ],
                ],
                $definition,
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                ],
            ]
        );

        $this->assertEquals([
            'a' => [
                'records' => [
                    ['name' => 'a'],
                ],
            ],
            'c' => [
                'records' => [
                    ['name' => 'c1'],
                    ['name' => 'c2'],
                ],
            ],
        ], $actual);
    }

    public function testGetData_AllSubRequestsThrowExceptions()
    {
        $exception = new SugarApiExceptionNotAuthorized('SUGAR_API_EXCEPTION_RECORD_NOT_AUTHORIZED', ['view']);
        $error = [
            'next_offset' => -1,
            'records' => [],
            'error' => [
                'code' => $exception->getHttpCode(),
                'error' => $exception->getErrorLabel(),
                'error_message' => $exception->getMessage(),
            ],
        ];

        /** @var CollectionApi|MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(['getSourceArguments'])
            ->getMockForAbstractClass();
        $api->expects($this->any())
            ->method('getSourceArguments')
            ->will($this->returnValue([]));
        $api->expects($this->any())
            ->method('getSourceData')
            ->will($this->throwException($exception));

        $service = SugarTestRestUtilities::getRestServiceMock();

        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('getSources')
            ->willReturn(['a', 'b', 'c']);

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'getData',
            [
                $service,
                [
                    'offset' => [
                        'a' => 0,
                        'b' => 0,
                        'c' => 0,
                    ],
                ],
                $definition,
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                ],
            ]
        );

        $this->assertEquals(
            [
                'a' => $error,
                'b' => $error,
                'c' => $error,
            ],
            $actual
        );
    }

    public function testGetData_SomeSubRequestsThrowExceptions()
    {
        $exception = new SugarApiExceptionNotAuthorized('SUGAR_API_EXCEPTION_RECORD_NOT_AUTHORIZED', ['view']);
        $error = [
            'next_offset' => -1,
            'records' => [],
            'error' => [
                'code' => $exception->getHttpCode(),
                'error' => $exception->getErrorLabel(),
                'error_message' => $exception->getMessage(),
            ],
        ];
        $records = [
            'next_offset' => -1,
            'records' => [
                ['name' => 'a'],
                ['name' => 'b'],
                ['name' => 'c'],
            ],
        ];

        /** @var CollectionApi|MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(['getSourceArguments', 'getSourceData'])
            ->getMockForAbstractClass();
        $api->expects($this->any())
            ->method('getSourceArguments')
            ->will($this->returnValue([]));
        $api->method('getSourceData')
            ->will($this->onConsecutiveCalls(
                $this->throwException($exception),
                $this->returnValue($records),
                $this->throwException($exception)
            ));

        $service = SugarTestRestUtilities::getRestServiceMock();

        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('getSources')
            ->willReturn(['a', 'b', 'c']);

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'getData',
            [
                $service,
                [
                    'offset' => [
                        'a' => 0,
                        'b' => 0,
                        'c' => 0,
                    ],
                ],
                $definition,
                [
                    'a' => [],
                    'b' => [],
                    'c' => [],
                ],
            ]
        );

        $this->assertEquals(
            [
                'a' => $error,
                'b' => $records,
                'c' => $error,
            ],
            $actual
        );
    }

    /**
     * @dataProvider getSourceArgumentsProvider
     */
    public function testGetSourceArguments(array $args, $source, $sortFields, array $expected)
    {
        $service = SugarTestRestUtilities::getRestServiceMock();

        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->any())
            ->method('hasFieldMap')
            ->willReturn(true);
        $definition->expects($this->any())
            ->method('getFieldMap')
            ->willReturn([
                'alias' => 'field',
            ]);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getSourceArguments',
            [$service, $args, $definition, $source, $sortFields]
        );

        $this->assertEquals($expected, $actual);
    }

    public static function getSourceArgumentsProvider()
    {
        return [
            [
                [
                    'fields' => ['alias', 'another_field'],
                    'filter' => [
                        '$or' => [
                            'alias' => 'a',
                            'another_field' => 'b',
                        ],
                    ],
                    'order_by' => [
                        'alias' => true,
                        'another_field' => false,
                    ],
                    'offset' => [
                        'test_source' => 10,
                    ],
                    'max_num' => 20,
                    'stored_filter' => [],
                ],
                'test_source',
                ['sort_field'],
                [
                    'fields' => ['field', 'another_field', 'sort_field'],
                    'filter' => [
                        '$or' => [
                            'field' => 'a',
                            'another_field' => 'b',
                        ],
                    ],
                    'order_by' => 'field,another_field:desc',
                    'offset' => 10,
                    'max_num' => 20,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getSourceFilterProvider
     */
    public function testGetSourceFilter(array $sourceFilter, array $storedFilter, array $apiFilter, array $expected)
    {
        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('hasSourceFilter')
            ->with('test-source')
            ->willReturn(true);
        $definition->expects($this->once())
            ->method('getSourceFilter')
            ->with('test-source')
            ->willReturn($sourceFilter);
        $definition->expects($this->any())
            ->method('getStoredFilter')
            ->will(
                call_user_func_array([$this, 'onConsecutiveCalls'], $storedFilter)
            );
        $definition->expects($this->once())
            ->method('hasFieldMap')
            ->with('test-source')
            ->willReturn(true);
        $definition->expects($this->once())
            ->method('getFieldMap')
            ->with('test-source')
            ->willReturn([
                'api-alias1' => 'api-filter1',
                'api-alias2' => 'api-filter2',
            ]);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getSourceFilter',
            [
                [
                    'filter' => $apiFilter,
                    'stored_filter' => array_keys($storedFilter),
                ],
                $definition,
                'test-source',
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    public static function getSourceFilterProvider()
    {
        return [
            'empty' => [
                [],
                [],
                [],
                [],
            ],
            'combo' => [
                [
                    [
                        'source-filter1' => 'source-value1',
                        'source-filter2' => 'source-value2',
                    ],
                ],
                [
                    'sf1' => [
                        [
                            'stored-filter11' => 'stored-value11',
                            'stored-filter12' => 'stored-value12',
                        ],
                    ],
                    'sf2' => [
                        [
                            'stored-filter21' => 'stored-value21',
                            'stored-filter22' => 'stored-value22',
                        ],
                    ],
                ],
                [
                    [
                        'api-alias1' => 'api-value1',
                        'api-alias2' => 'api-value2',
                    ],
                ],
                [
                    [
                        'source-filter1' => 'source-value1',
                        'source-filter2' => 'source-value2',
                    ],
                    [
                        'stored-filter11' => 'stored-value11',
                        'stored-filter12' => 'stored-value12',
                    ],
                    [
                        'stored-filter21' => 'stored-value21',
                        'stored-filter22' => 'stored-value22',
                    ],
                    [
                        'api-filter1' => 'api-value1',
                        'api-filter2' => 'api-value2',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider normalizeArgumentsProvider
     */
    public function testNormalizeArguments(array $args, $orderBy, $expected)
    {
        /** @var CollectionApi|MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->setMethods(['normalizeOffset', 'normalizeStoredFilter', 'getDefaultLimit', 'getDefaultOrderBy'])
            ->getMockForAbstractClass();
        $api->expects($this->any())
            ->method('normalizeOffset')
            ->will($this->returnCallback(function () {
                return 'from-normalize-offset';
            }));
        $api->expects($this->any())
            ->method('normalizeStoredFilter')
            ->will($this->returnCallback(function () {
                return 'from-normalize-stored-filter';
            }));
        $api->expects($this->any())
            ->method('getDefaultLimit')
            ->will($this->returnCallback(function () {
                return 'from-default-limit';
            }));
        $api->expects($this->any())
            ->method('getDefaultOrderBy')
            ->will($this->returnCallback(function () {
                return 'from-default-order-by';
            }));

        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->any())
            ->method('getOrderBy')
            ->willReturn($orderBy);

        $actual = SugarTestReflection::callProtectedMethod(
            $api,
            'normalizeArguments',
            [$args, $definition]
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeArgumentsProvider()
    {
        return [
            'defaults' => [
                [],
                null,
                [
                    'offset' => 'from-normalize-offset',
                    'stored_filter' => 'from-normalize-stored-filter',
                    'max_num' => 'from-default-limit',
                    'order_by' => 'from-default-order-by',
                ],
            ],
            'from-arguments' => [
                [
                    'order_by' => 'order,by',
                    'max_num' => 25,
                ],
                null,
                [
                    'order_by' => [
                        'order' => true,
                        'by' => true,
                    ],
                    'max_num' => 25,
                    'offset' => 'from-normalize-offset',
                    'stored_filter' => 'from-normalize-stored-filter',
                ],
            ],
            'from-link-definition' => [
                [],
                'defined,in:desc,link',
                [
                    'offset' => 'from-normalize-offset',
                    'stored_filter' => 'from-normalize-stored-filter',
                    'max_num' => 'from-default-limit',
                    'order_by' => [
                        'defined' => true,
                        'in' => false,
                        'link' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider normalizeOffsetSuccess
     */
    public function testNormalizeOffsetSuccess($offset, array $expected)
    {
        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('getSources')
            ->willReturn(['a']);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeOffset',
            [
                ['offset' => $offset],
                $definition,
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeOffsetSuccess()
    {
        return [
            'default' => [
                null,
                [
                    'a' => 0,
                ],
            ],
            'integer' => [
                ['a' => 1],
                ['a' => 1],
            ],
            'numeric-string' => [
                ['a' => '-1'],
                ['a' => -1],
            ],
            'non-numeric-string' => [
                ['a' => 'non-numeric-string'],
                ['a' => 0],
            ],
            'negative' => [
                ['a' => -2],
                ['a' => -1],
            ],
            'irrelevant' => [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                ['a' => 1],
            ],
        ];
    }

    /**
     * @dataProvider normalizeOffsetFailure
     */
    public function testNormalizeOffsetFailure(array $offset)
    {
        $definition = $this->createMock('CollectionDefinitionInterface');

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeOffset',
            [$offset, $definition]
        );
    }

    public static function normalizeOffsetFailure()
    {
        return [
            'non-array' => [
                [
                    'offset' => 'a',
                ],
            ],
        ];
    }

    /**
     * @dataProvider normalizeStoredFilterSuccessProvider
     */
    public function testNormalizeStoredFilterSuccess(array $args, array $expected)
    {
        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->any())
            ->method('hasStoredFilter')
            ->willReturn(true);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeStoredFilter',
            [$args, $definition]
        );

        $this->assertEquals($expected, $actual);
    }

    public static function normalizeStoredFilterSuccessProvider()
    {
        return [
            'not-set' => [
                [],
                [],
            ],
            'string' => [
                [
                    'stored_filter' => 'test',
                ],
                ['test'],
            ],
            'array' => [
                [
                    'stored_filter' => ['test1', 'test2'],
                ],
                ['test1', 'test2'],
            ],
        ];
    }

    public function testNormalizeStoredFilterFailure()
    {
        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->any())
            ->method('hasStoredFilter')
            ->willReturn(false);

        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod(
            $this->api,
            'normalizeStoredFilter',
            [
                [
                    'stored_filter' => 'test',
                ],
                $definition,
            ]
        );
    }

    /**
     * @dataProvider extractErrorsProvider
     */
    public function testExtractErrors(array $data, array $expectedData, array $expectedErrors)
    {
        $errors = SugarTestReflection::callProtectedMethod($this->api, 'extractErrors', [&$data]);

        $this->assertEquals($expectedData, $data);
        $this->assertEquals($expectedErrors, $errors);
    }

    public function extractErrorsProvider()
    {
        $exception = new SugarApiExceptionNotAuthorized('SUGAR_API_EXCEPTION_RECORD_NOT_AUTHORIZED', ['view']);

        return [
            'no_errors' => [
                [
                    'l1' => [
                        'records' => [
                            [
                                'a' => 'x',
                            ],
                            [
                                'a' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    'l2' => [
                        'records' => [
                            [
                                'a' => 'y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    'l1' => [
                        'records' => [
                            [
                                'a' => 'x',
                            ],
                            [
                                'a' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    'l2' => [
                        'records' => [
                            [
                                'a' => 'y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [],
            ],
            'one_success_and_one_error' => [
                [
                    'l1' => [
                        'records' => [
                            [
                                'a' => 'x',
                            ],
                            [
                                'a' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    'l2' => [
                        'records' => [],
                        'next_offset' => -1,
                        'error' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                    ],
                ],
                [
                    'l1' => [
                        'records' => [
                            [
                                'a' => 'x',
                            ],
                            [
                                'a' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    'l2' => [
                        'records' => [],
                        'next_offset' => -1,
                    ],
                ],
                [
                    'l2' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                ],
            ],
            'all_errors' => [
                [
                    'l1' => [
                        'records' => [],
                        'next_offset' => -1,
                        'error' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                    ],
                    'l2' => [
                        'records' => [],
                        'next_offset' => -1,
                        'error' => [
                            'code' => $exception->getHttpCode(),
                            'error' => $exception->getErrorLabel(),
                            'error_message' => $exception->getMessage(),
                        ],
                    ],
                ],
                [
                    'l1' => [
                        'records' => [],
                        'next_offset' => -1,
                    ],
                    'l2' => [
                        'records' => [],
                        'next_offset' => -1,
                    ],
                ],
                [
                    'l1' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                    'l2' => [
                        'code' => $exception->getHttpCode(),
                        'error' => $exception->getErrorLabel(),
                        'error_message' => $exception->getMessage(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider sortDataProvider
     */
    public function testSortData(
        array $data,
        array $spec,
        $limit,
        array $offset,
        array $expectedRecords,
        array $expectedNextOffset
    ) {

        $this->assertNotEquals($expectedNextOffset, $offset);

        $records = SugarTestReflection::callProtectedMethod(
            $this->api,
            'sortData',
            [$data, $spec, $offset, $limit, &$nextOffset]
        );

        // remove the "_source" key from the records since it's a desired side effect of sorting but not what we
        // want to test here (the order of records and limit). also its value is undetermined in case of duplicates
        $records = array_map(function ($record) {
            unset($record['_source']);
            return $record;
        }, $records);

        $this->assertEquals($expectedRecords, $records);
        $this->assertEquals($expectedNextOffset, $nextOffset);
    }

    public function sortDataProvider()
    {
        return [
            'strings' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-x',
                                'a' => 'x',
                            ],
                            [
                                '_module' => 'm1',
                                'id' => 'm1-z',
                                'a' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-y',
                                'a' => 'Y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                3,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-x',
                        'a' => 'x',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-y',
                        'a' => 'Y',
                    ],
                    [
                        '_module' => 'm1',
                        'id' => 'm1-z',
                        'a' => 'z',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                ],
            ],
            'numbers' => [
                [
                    's1' => [
                        'records' => [
                            [
                                'a' => '10',
                                '_module' => 'm1',
                                'id' => 'r-10',
                            ],
                            [
                                'a' => '100',
                                '_module' => 'm1',
                                'id' => 'r-100',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                'a' => '11',
                                'id' => 'r-11',
                                '_module' => 'm2',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                        ],
                        'is_numeric' => true,
                        'direction' => true,
                    ],
                ],
                3,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        'a' => '10',
                        '_module' => 'm1',
                        'id' => 'r-10',
                    ],
                    [
                        'a' => '11',
                        '_module' => 'm2',
                        'id' => 'r-11',
                    ],
                    [
                        'a' => '100',
                        '_module' => 'm1',
                        'id' => 'r-100',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                ],
            ],
            'reverse' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-z',
                                'a' => 'z',
                            ],
                            [
                                '_module' => 'm1',
                                'id' => 'm1-x',
                                'a' => 'x',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-x',
                                'a' => 'Y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => false,
                    ],
                ],
                3,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-z',
                        'a' => 'z',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-x',
                        'a' => 'Y',
                    ],
                    [
                        '_module' => 'm1',
                        'id' => 'm1-x',
                        'a' => 'x',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                ],
            ],
            'multiple-sources-and-aliasing' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-x',
                                'a' => 'x',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-z',
                                'b' => 'z',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's3' => [
                        'records' => [
                            [
                                '_module' => 'm3',
                                'id' => 'm3-y',
                                'c' => 'y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['b'],
                            's3' => ['c'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                3,
                [
                    's1' => 0,
                    's2' => 0,
                    's3' => 0,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-x',
                        'a' => 'x',
                    ],
                    [
                        '_module' => 'm3',
                        'id' => 'm3-y',
                        'c' => 'y',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-z',
                        'b' => 'z',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                    's3' => -1,
                ],
            ],
            'multiple-columns' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-xx',
                                'a' => 'x',
                                'b' => 'x',
                            ],
                            [
                                '_module' => 'm1',
                                'id' => 'm1-yy',
                                'a' => 'y',
                                'b' => 'y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-xy',
                                'a' => 'x',
                                'b' => 'y',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                    [
                        'map' => [
                            's1' => ['b'],
                            's2' => ['b'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                3,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-xx',
                        'a' => 'x',
                        'b' => 'x',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-xy',
                        'a' => 'x',
                        'b' => 'y',
                    ],
                    [
                        '_module' => 'm1',
                        'id' => 'm1-yy',
                        'a' => 'y',
                        'b' => 'y',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                ],
            ],
            'multiple-fields-in-sort-on' => [
                [
                    'accounts' => [
                        'records' => [
                            [
                                '_module' => 'accounts',
                                'id' => 'alpha-bank',
                                'name' => 'Alpha Bank',
                            ],
                            [
                                '_module' => 'accounts',
                                'id' => 'general-electric',
                                'name' => 'General Electric',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    'contacts' => [
                        'records' => [
                            [
                                '_module' => 'contacts',
                                'id' => 'john-doe',
                                'first_name' => 'John',
                                'last_name' => 'Doe',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            'accounts' => ['name'],
                            'contacts' => ['last_name', 'first_name'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                3,
                [
                    'accounts' => 0,
                    'contacts' => 0,
                ],
                [
                    [
                        '_module' => 'accounts',
                        'id' => 'alpha-bank',
                        'name' => 'Alpha Bank',
                    ],
                    [
                        '_module' => 'contacts',
                        'id' => 'john-doe',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                    [
                        '_module' => 'accounts',
                        'id' => 'general-electric',
                        'name' => 'General Electric',
                    ],
                ],
                [
                    'accounts' => -1,
                    'contacts' => -1,
                ],
            ],
            'limit-and-offset' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-a',
                                'a' => 'a',
                            ],
                            [
                                '_module' => 'm1',
                                'id' => 'm1-c',
                                'a' => 'c',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-b',
                                'a' => 'b',
                            ],
                            [
                                '_module' => 'm2',
                                'id' => 'm2-d',
                                'a' => 'd',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's3' => [
                        'records' => [
                            [
                                '_module' => 'm3',
                                'id' => 'm3-e',
                                'a' => 'e',
                            ],
                            [
                                '_module' => 'm3',
                                'id' => 'm3-f',
                                'a' => 'f',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                            's3' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                2,
                [
                    's1' => 1,
                    's2' => 2,
                    's3' => 0,
                    's4' => -1,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-a',
                        'a' => 'a',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-b',
                        'a' => 'b',
                    ],
                ],
                [
                    's1' => 2,
                    's2' => 3,
                    's3' => 0,
                    's4' => -1,
                ],
            ],
            'negative-limit' => [
                [
                    's' => [
                        'records' => [
                            [
                                '_module' => 'm',
                                'id' => 'm-a',
                                'a' => 'a',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                -1,
                [
                    's' => 0,
                ],
                [
                    [
                        '_module' => 'm',
                        'id' => 'm-a',
                        'a' => 'a',
                    ],
                ],
                [
                    's' => -1,
                ],
            ],
            'database-order-preserved' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm1',
                                'id' => 'm1-a-uml',
                                'a' => 'ä',
                            ],
                            [
                                '_module' => 'm1',
                                'id' => 'm1-a',
                                'a' => 'a',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm2',
                                'id' => 'm2-u-uml',
                                'a' => 'ü',
                            ],
                            [
                                '_module' => 'm2',
                                'id' => 'm2-u',
                                'a' => 'u',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['a'],
                            's2' => ['a'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                4,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        '_module' => 'm1',
                        'id' => 'm1-a-uml',
                        'a' => 'ä',
                    ],
                    [
                        '_module' => 'm1',
                        'id' => 'm1-a',
                        'a' => 'a',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-u-uml',
                        'a' => 'ü',
                    ],
                    [
                        '_module' => 'm2',
                        'id' => 'm2-u',
                        'a' => 'u',
                    ],
                ],
                [
                    's1' => -1,
                    's2' => -1,
                ],
            ],
            'duplicates-removed' => [
                [
                    's1' => [
                        'records' => [
                            [
                                '_module' => 'm',
                                'id' => 'm-r1',
                            ],
                            [
                                '_module' => 'm',
                                'id' => 'm-r2',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                    's2' => [
                        'records' => [
                            [
                                '_module' => 'm',
                                'id' => 'm-r1',
                            ],
                            [
                                '_module' => 'm',
                                'id' => 'm-r2',
                            ],
                        ],
                        'next_offset' => -1,
                    ],
                ],
                [
                    [
                        'map' => [
                            's1' => ['id'],
                            's2' => ['id'],
                        ],
                        'is_numeric' => false,
                        'direction' => true,
                    ],
                ],
                1,
                [
                    's1' => 0,
                    's2' => 0,
                ],
                [
                    [
                        '_module' => 'm',
                        'id' => 'm-r1',
                    ],
                ],
                [
                    's1' => 1,
                    's2' => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider mapFilterSuccessProvider
     */
    public function testMapFilterSuccess(array $filter, array $fieldMap, array $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapFilter', [$filter, $fieldMap]);
        $this->assertEquals($expected, $actual);
    }

    public static function mapFilterSuccessProvider()
    {
        return [
            'simple' => [
                [
                    'a-alias' => 1,
                ],
                [
                    'a-alias' => 'a',
                ],
                [
                    'a' => 1,
                ],
            ],
            'cyclic' => [
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                    'd' => 4,
                ],
                [
                    'b' => 'a',
                    'c' => 'b',
                    'a' => 'c',
                    'd' => 'e',
                ],
                [
                    'c' => 1,
                    'a' => 2,
                    'b' => 3,
                    'e' => 4,
                ],
            ],
            'recursive' => [
                [
                    'q' => 1,
                    '$or' => [
                        'r' => [
                            '$and' => [
                                's' => 2,
                                't' => 3,
                            ],
                        ],
                        'u' => 4,
                    ],
                ],
                [
                    'q' => 'a',
                    'r' => 'b',
                    's' => 'c',
                    't' => 'd',
                    'u' => 'e',
                ],
                [
                    'a' => 1,
                    '$or' => [
                        'b' => [
                            '$and' => [
                                'c' => 2,
                                'd' => 3,
                            ],
                        ],
                        'e' => 4,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider mapFilterFailureProvider
     */
    public function testMapFilterFailure(array $filter, array $fieldMap)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod($this->api, 'mapFilter', [$filter, $fieldMap]);
    }

    public static function mapFilterFailureProvider()
    {
        return [
            'alias-conflict' => [
                [
                    'a' => 1,
                    'b' => 1,
                ],
                [
                    'a' => 'c',
                    'b' => 'c',
                ],
            ],
        ];
    }

    /**
     * @dataProvider mapOrderBySuccessProvider
     */
    public function testMapOrderBySuccess(array $orderBy, array $fieldMap, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapOrderBy', [$orderBy, $fieldMap]);
        $this->assertEquals($expected, $actual);
    }

    public static function mapOrderBySuccessProvider()
    {
        return [
            [
                [
                    'a' => true,
                    'b' => false,
                    'c' => true,
                ],
                [
                    'b' => 'a',
                    'a' => 'b',
                ],
                [
                    'b' => true,
                    'a' => false,
                    'c' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider mapOrderByFailureProvider
     */
    public function testMapOrderByFailure(array $orderBy, array $fieldMap)
    {
        $this->expectException(SugarApiExceptionInvalidParameter::class);
        SugarTestReflection::callProtectedMethod($this->api, 'mapOrderBy', [$orderBy, $fieldMap]);
    }

    public static function mapOrderByFailureProvider()
    {
        return [
            'alias-conflict' => [
                [
                    'a' => true,
                    'b' => false,
                ],
                [
                    'a' => 'c',
                    'b' => 'c',
                ],
            ],
        ];
    }

    /**
     * @dataProvider mapFieldsProvider
     */
    public function testMapFields(array $fields, array $fieldMap, array $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'mapFields', [$fields, $fieldMap]);
        $this->assertEquals($expected, $actual);
    }

    public static function mapFieldsProvider()
    {
        return [
            [
                ['a-alias', 'b-alias', 'c'],
                [
                    'a-alias' => 'a',
                    'b-alias' => 'b',
                ],
                ['a', 'b', 'c'],
            ],
        ];
    }

    /**
     * @dataProvider formatOrderByProvider
     */
    public function testFormatOrderBy($string, array $array)
    {
        $actual = SugarTestReflection::callProtectedMethod($this->api, 'formatOrderBy', [$array]);
        $this->assertEquals($string, $actual);
    }

    public static function formatOrderByProvider()
    {
        return [
            [
                'a,b:desc',
                [
                    'a' => true,
                    'b' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getAdditionalSortFieldsProvider
     */
    public function testGetAdditionalSortFields(array $args, array $sources, array $sortSpec, array $expected)
    {
        $definition = $this->createMock('CollectionDefinitionInterface');
        $definition->expects($this->once())
            ->method('getSources')
            ->willReturn($sources);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getAdditionalSortFields',
            [$args, $definition, $sortSpec]
        );

        $this->assertEquals($expected, $actual, 'Incorrect additional sort fields generated');
    }

    public static function getAdditionalSortFieldsProvider()
    {
        return [
            [
                [
                    'fields' => ['id', 'name', 'date_entered'],
                ],
                [
                    'accounts',
                    'contacts',
                ],
                [
                    [
                        'map' => [
                            'accounts' => [],
                            'contacts' => [
                                'last_name',
                            ],
                        ],
                    ],
                    [
                        'map' => [
                            'accounts' => [
                                'date_entered',
                            ],
                            'contacts' => [
                                'date_entered',
                            ],
                        ],
                    ],
                ],
                [
                    'accounts' => [],
                    'contacts' => [
                        'last_name',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider cleanDataProvider
     */
    public function testCleanData($records, $sortFields, $expected)
    {
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'cleanData',
            [$records, $sortFields]
        );

        $this->assertEquals($expected, $actual, 'Unrequested fields not removed from return data.');
    }

    public static function cleanDataProvider()
    {
        return [
            [
                [
                    [
                        'id' => 123,
                        'title' => 'Sales Executive',
                        'name' => 'John Smith',
                        'last_name' => 'Smith',
                        '_source' => 'contacts',
                    ],
                    [
                        'id' => 456,
                        'title' => 'Sgr Manager',
                        'name' => 'Peter Hanks',
                        '_source' => 'users',
                    ],
                ],
                [
                    'contacts' => ['last_name'],
                    'users' => [],
                ],
                [
                    [
                        'id' => 123,
                        'title' => 'Sales Executive',
                        'name' => 'John Smith',
                        '_source' => 'contacts',
                    ],
                    [
                        'id' => 456,
                        'title' => 'Sgr Manager',
                        'name' => 'Peter Hanks',
                        '_source' => 'users',
                    ],
                ],
            ],
        ];
    }

    public function columnComparatorProvider(): iterable
    {
        // value A, value B, Expected exception
        yield [1, 2, -1, false];
        // yield [1, 'str', true]; - this behavior was before the fix, now it is:
        yield [1, 'str', -1, false];
        yield ['str', 1, 1, false];
    }

    /**
     * @param mixed $a Value A
     * @param mixed $b Value B
     * @param int $expectedResult 0, 1 or -1
     * @param bool $expectedException Exception is expected
     * @dataProvider columnComparatorProvider
     */
    public function testColumnComparator($a, $b, int $expectedResult, bool $expectedException)
    {
        /** @var CollectionApi|MockObject $api */
        $api = $this->getMockBuilder('CollectionApi')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $comparator = SugarTestReflection::callProtectedMethod(
            $api,
            'getColumnComparator',
            [['a' => ['value'], 'b' => ['value']], true, true]
        );

        if ($expectedException) {
            $this->expectExceptionMessage('Unsupported operand types: int - string');
        }
        $result = $comparator(['_source' => 'a', 'value' => $a], ['_source' => 'b', 'value' => $b]);
        $this->assertEquals($expectedResult, $result);
    }
}
