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

namespace Sugarcrm\SugarcrmTestsUnit\SearchEngine;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
 */
class MetaDataHelperTest extends TestCase
{
    /**
     * @covers ::getFtsFields
     * @dataProvider providerGetFtsFields
     *
     *
     * @param string $module
     * @param array $vardef
     * @param boolean $override
     * @param array $result
     */
    public function testGetFtsFields($module, array $vardef, $override, array $result)
    {
        $helper = $this->getMetaDataHelperMock(['getModuleVardefs']);
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getModuleVardefs')
            ->will($this->returnValue($vardef));

        $fields = $helper->getFtsFields($module, $override);
        $this->assertEquals($result, $fields);
    }

    public function providerGetFtsFields()
    {
        return [
            [
                'Tasks',
                [
                    'fields' => [
                        'name' => [
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => ['enabled' => true, 'searchable' => true],
                        ],
                        'description' => [
                            'name' => 'description',
                            'type' => 'text',
                        ],
                        'work_log' => [
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => ['enabled' => false],
                        ],
                        'date_modified' => [
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => ['enabled' => true, 'searchable' => false, 'type' => 'varchar'],
                        ],
                    ],
                    'indices' => [],
                    'relationship' => [],
                ],
                true,
                [
                    'name' => [
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => ['enabled' => true, 'searchable' => true],
                    ],
                    'date_modified' => [
                        'name' => 'date_modified',
                        'type' => 'varchar',
                        'full_text_search' => ['enabled' => true, 'searchable' => false, 'type' => 'varchar'],
                    ],
                ],
            ],
            // No type override
            [
                'Tasks',
                [
                    'fields' => [
                        'name' => [
                            'name' => 'name',
                            'type' => 'name',
                            'full_text_search' => ['enabled' => true, 'searchable' => true],
                        ],
                        'description' => [
                            'name' => 'description',
                            'type' => 'text',
                        ],
                        'work_log' => [
                            'name' => 'work_log',
                            'type' => 'text',
                            'full_text_search' => ['enabled' => false],
                        ],
                        'date_modified' => [
                            'name' => 'date_modified',
                            'type' => 'datetime',
                            'full_text_search' => ['enabled' => true, 'searchable' => false, 'type' => 'varchar'],
                        ],
                    ],
                    'indices' => [],
                    'relationship' => [],
                ],
                false,
                [
                    'name' => [
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => ['enabled' => true, 'searchable' => true],
                    ],
                    'date_modified' => [
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => ['enabled' => true, 'searchable' => false, 'type' => 'varchar'],
                    ],
                ],
            ],
        ];
    }


    /**
     * @covers ::getAllAggDefsModule
     * @dataProvider providerGetModuleAggregations
     *
     *
     * @param string $module
     * @param array $vardef
     * @param array $result
     */
    public function testGetAllAggDefsModule($module, array $vardef, array $result)
    {
        $helper = $this->getMetaDataHelperMock(
            ['getFtsFields']
        );
        $helper->disableCache(true);

        $helper->expects($this->any())
            ->method('getFtsFields')
            ->will($this->returnValue($vardef));

        $fields = TestReflection::callProtectedMethod($helper, 'getAllAggDefsModule', [$module]);
        $this->assertEquals($result, $fields);
    }

    public function providerGetModuleAggregations()
    {
        return [
            [
                'Tasks',
                [
                    'name' => [
                        'name' => 'name',
                        'type' => 'name',
                        'full_text_search' => ['enabled' => true, 'searchable' => true],
                    ],
                    // module specific aggregation, no options
                    'description' => [
                        'name' => 'description',
                        'type' => 'text',
                        'full_text_search' => [
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => [
                                'agg1' => [
                                    'type' => 'term',
                                ],
                            ],
                        ],
                    ],
                    // module specific aggregation, with options
                    'work_log' => [
                        'name' => 'work_log',
                        'type' => 'text',
                        'full_text_search' => [
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => [
                                'agg2' => [
                                    'type' => 'term',
                                    'options' => ['size' => 21, 'order' => 'desc'],
                                ],
                            ],
                        ],
                    ],
                    // cross module aggregation, no options
                    'date_entered' => [
                        'name' => 'date_entered',
                        'type' => 'datetime',
                        'full_text_search' => [
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => [
                                'date_entered' => [
                                    'type' => 'date_range',
                                ],
                            ],
                        ],
                    ],
                    // cross module aggregation, with options
                    'date_modified' => [
                        'name' => 'date_modified',
                        'type' => 'datetime',
                        'full_text_search' => [
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => [
                                'date_modified' => [
                                    'type' => 'date_range',
                                    'options' => ['from' => 'foo', 'to' => 'bar'],
                                ],
                            ],
                        ],
                    ],
                    // mix of cross and module specific aggregations
                    'status' => [
                        'name' => 'status',
                        'type' => 'enum',
                        'full_text_search' => [
                            'enabled' => true,
                            'searchable' => true,
                            'aggregations' => [
                                'status_types' => [
                                    'type' => 'term',
                                    'options' => ['foo' => 'bar1'],
                                ],
                                'status' => [
                                    'type' => 'dropdown',
                                    'options' => ['foo' => 'bar2'],
                                ],
                                'status_something' => [
                                    'type' => 'myStatus',
                                    'options' => ['foo' => 'bar3'],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'cross' => [
                        'date_entered' => [
                            'type' => 'date_range',
                            'options' => [],
                        ],
                        'date_modified' => [
                            'type' => 'date_range',
                            'options' => ['from' => 'foo', 'to' => 'bar'],
                        ],
                        'status' => [
                            'type' => 'dropdown',
                            'options' => ['foo' => 'bar2'],
                        ],
                    ],
                    'module' => [
                        'description.agg1' => [
                            'type' => 'term',
                            'options' => [],
                        ],
                        'work_log.agg2' => [
                            'type' => 'term',
                            'options' => ['size' => 21, 'order' => 'desc'],
                        ],
                        'status.status_types' => [
                            'type' => 'term',
                            'options' => ['foo' => 'bar1'],
                        ],
                        'status.status_something' => [
                            'type' => 'myStatus',
                            'options' => ['foo' => 'bar3'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers ::isFieldSearchable
     * @dataProvider dataProviderIsFieldSearchable
     *
     * @param array $defs
     * @param boolean $isSearchable
     */
    public function testIsFieldSearchable(array $defs, $isSearchable)
    {
        $sut = $this->getMetaDataHelperMock();
        $this->assertSame($isSearchable, $sut->isFieldSearchable($defs));
    }

    public function dataProviderIsFieldSearchable()
    {
        return [
            [
                [
                    'name' => 'foo1',
                    'full_text_search' => ['enabled' => true, 'searchable' => false],
                ],
                false,
            ],
            [
                [
                    'name' => 'foo2',
                    'full_text_search' => ['enabled' => true, 'searchable' => true],
                ],
                true,
            ],
            [
                [
                    'name' => 'foo3',
                    'full_text_search' => ['enabled' => true, 'boost' => 1],
                ],
                true,
            ],
            [
                [
                    'name' => 'foo4',
                    'full_text_search' => ['enabled' => true, 'boost' => 3, 'searchable' => true],
                ],
                true,
            ],
            [
                [
                    'name' => 'foo5',
                    'full_text_search' => ['enabled' => true],
                ],
                false,
            ],
        ];
    }


    /**
     * @covers ::getCache
     * @covers ::setCache
     * @dataProvider providerTestInMemoryCache
     */
    public function testInMemoryCache($key, $value, $isCacheDisabled, $isRealCacheDisabled, $expected)
    {

        $helper = $this->getMetaDataHelperMock(
            ['getRealCacheKey', 'isRealCacheDisabled', 'getRealCache', 'setRealCache']
        );
        $helper->expects($this->any())
            ->method('getRealCacheKey')
            ->will($this->returnValue('mdmhelper_' . $key));
        $helper->expects($this->any())
            ->method('isRealCacheDisabled')
            ->will($this->returnValue($isRealCacheDisabled));
        $helper->expects($this->any())
            ->method('getRealCache')
            ->will($this->returnValue($value));
        $helper->expects($this->any())
            ->method('setRealCache')
            ->will($this->returnValue($value));


        TestReflection::setProtectedValue($helper, 'disableCache', $isCacheDisabled);
        TestReflection::setProtectedValue($helper, 'inMemoryCache', []);

        TestReflection::callProtectedMethod($helper, 'setCache', [$key, $value]);
        $result = TestReflection::callProtectedMethod($helper, 'getCache', [$key]);

        $this->assertSame($result, $expected);
    }

    public function providerTestInMemoryCache()
    {
        return [
            [
                'enabled_modules',
                ['Accounts', 'Contacts'],
                false,
                false,
                ['Accounts', 'Contacts'],
            ],
            [
                'enabled_modules',
                ['Accounts', 'Contacts'],
                true,
                true,
                ['Accounts', 'Contacts'],
            ],
            [
                'enabled_modules',
                ['Accounts', 'Contacts'],
                true,
                false,
                null,
            ],
            [
                'enabled_modules',
                ['Accounts', 'Contacts'],
                false,
                true,
                ['Accounts', 'Contacts'],
            ],
        ];
    }

    /**
     * @covers ::getAllEnabledModules
     * @dataProvider getAllEnabledModulesProvider
     */
    public function testGetAllEnabledModules($modulesMap, $vardefsMap, $expected)
    {
        $helper = $this->getMetaDataHelperMock(
            ['isRealCacheDisabled', 'getModuleVardefs']
        );
        TestReflection::setProtectedValue($helper, 'disableCache', true);

        $helper->expects($this->any())
            ->method('isRealCacheDisabled')
            ->will($this->returnValue(true));

        $helper->expects($this->any())
            ->method('getModuleVardefs')
            ->will($this->returnValueMap($vardefsMap));

        $metadataManagerMock = $this->getMockBuilder(\MetaDataManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModuleList'])
            ->getMock();
        $metadataManagerMock->expects($this->any())
            ->method('getModuleList')
            ->will($this->returnValueMap($modulesMap));

        TestReflection::setProtectedValue($helper, 'mdm', $metadataManagerMock);

        $this->assertSame($expected, $helper->getAllEnabledModules());
    }

    public function getAllEnabledModulesProvider()
    {
        return [
            'all modules' => [
                [
                    [true, ['Accounts']],
                    [false, ['foo', 'Accounts']],
                ],
                [
                    ['foo', ['full_text_search' => true]],
                    ['Accounts', ['full_text_search' => true]],
                ],
                ['foo', 'Accounts'],
            ],
            'filtered modules' => [
                [
                    [true, ['Accounts']],
                    [false, ['foo', 'Accounts']],
                ],
                [
                    ['foo', ['full_text_search' => false]],
                    ['Accounts', ['full_text_search' => true]],
                ],
                ['Accounts'],
            ],
        ];
    }

    /**
     * Get MetaDataHelper mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper
     */
    protected function getMetaDataHelperMock(array $methods = null)
    {
        $mock = $this->getMockBuilder(\Sugarcrm\Sugarcrm\SearchEngine\MetaDataHelper::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        // stub out our logger
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        TestReflection::setProtectedValue($mock, 'logger', $logger);

        return $mock;
    }
}
