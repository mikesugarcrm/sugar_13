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

namespace Sugarcrm\SugarcrmTestsUnit\Elasticsearch\Index;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Mapping;
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\MappingWithType;
use Sugarcrm\Sugarcrm\Elasticsearch\Analysis\AnalysisBuilder;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
 */
class IndexManagerTest extends TestCase
{
    /**
     * @covers ::getIndexSettingsFromConfig
     * @dataProvider providerTestGetIndexSettingsFromConfig
     */
    public function testGetIndexSettingsFromConfig($indexName, $version, $config, $output)
    {
        $index = $this->getIndexMock($indexName);
        $indexManager = $this->getIndexManagerMock(['getServerVersion']);
        $indexManager->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        TestReflection::setProtectedValue($indexManager, 'config', $config);
        TestReflection::setProtectedValue($indexManager, 'defaultSettings', ['setting_Z' => 'core']);
        $settings = TestReflection::callProtectedMethod($indexManager, 'getIndexSettingsFromConfig', [$index]);
        $this->assertEquals($settings, $output);
    }

    public function providerTestGetIndexSettingsFromConfig()
    {
        return [
            // explicit index config + default config + default core
            [
                'index_foo',
                '5.6',
                [
                    'index_foo' => [
                        'setting_A' => 'foo',
                        'setting_B' => 'fox',
                    ],
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY => [
                        'setting_A' => 'bar',
                        'setting_C' => 'foo',
                    ],
                    'index_bar' => [],
                ],
                [
                    'setting_Z' => 'core',
                    'setting_C' => 'foo',
                    'setting_A' => 'foo',
                    'setting_B' => 'fox',
                ],
            ],
            // default config + default core
            [
                'index_foo',
                '6.6',
                [
                    IndexManager::DEFAULT_INDEX_SETTINGS_KEY => [
                        'setting_A' => 'bar',
                        'setting_C' => 'foo',
                        'setting_Z' => 'nocore',
                    ],
                    'index_bar' => [],
                ],
                [
                    'setting_Z' => 'nocore',
                    'setting_A' => 'bar',
                    'setting_C' => 'foo',
                ],
            ],
            // explicit config with analysis settings (the latter is stripped) and in ES 7.9
            [
                'index_foo',
                '7.9',
                [
                    'index_foo' => [
                        'setting_A' => 'bar',
                        'setting_B' => 'fox',
                        AnalysisBuilder::ANALYSIS => 'quick',
                    ],
                ],
                [
                    'setting_Z' => 'core',
                    'index.max_ngram_diff' => 50,
                    'setting_A' => 'bar',
                    'setting_B' => 'fox',
                ],
            ],
        ];
    }

    /**
     * @covers ::getMapping
     * @param string $version
     * @param $expected
     *
     * @dataProvider getMappingProvider
     */
    public function testGetMapping(string $version, $expected)
    {
        $mock = $this->getIndexManagerMock(['getServerVersion']);
        $mock->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        $result = TestReflection::callProtectedMethod($mock, 'getMapping', []);
        $this->assertTrue($result instanceof $expected);
    }

    public function getMappingProvider()
    {
        return [
            'typed version 5.6' => [
                '5.6',
                MappingWithType::class,
            ],
            'typed version 6.8' => [
                '6.8',
                MappingWithType::class,
            ],
            'typeless version 7.9' => [
                '7.9',
                Mapping::class,
            ],
        ];
    }

    /**
     * @covers ::getDefaultSettings
     * @param string $version
     * @param $expected
     *
     * @dataProvider getDefaultSettingsProvider
     */
    public function testGetDefaultSettings(string $version, $key, $expected)
    {
        $mock = $this->getIndexManagerMock(['getServerVersion']);
        $mock->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        $result = TestReflection::callProtectedMethod($mock, 'getDefaultSettings', []);
        $this->assertSame($expected, key_exists($key, $result));
    }

    public function getDefaultSettingsProvider()
    {
        return [
            'typed version 5.6' => [
                '5.6',
                'index.mapping.ignore_malformed',
                true,
            ],
            'typed version 6.8' => [
                '6.8',
                'index.number_of_replicas',
                true,
            ],
            'typed version 6.8 no ngram_diff key' => [
                '6.8',
                'index.max_ngram_diff',
                false,
            ],
            'typeless version 7.9' => [
                '7.9',
                'index.max_ngram_diff',
                true,
            ],
        ];
    }

    /**
     * @covers ::getDefaultMapping
     * @param string $version
     * @param $expected
     *
     * @dataProvider getDefaultMappingProvider
     */
    public function testGetDefaultMapping(string $version, $key, $expected)
    {
        $mock = $this->getIndexManagerMock(['getServerVersion']);
        $mock->expects($this->any())
            ->method('getServerVersion')
            ->willReturn($version);

        $result = TestReflection::callProtectedMethod($mock, 'getDefaultMapping', []);
        $this->assertSame($expected, key_exists($key, $result));
    }

    public function getDefaultMappingProvider()
    {
        return [
            'typed version 5.6 contains _all' => [
                '5.6',
                '_all',
                true,
            ],
            'typed version 6.8 contains dynamic' => [
                '6.8',
                'dynamic',
                true,
            ],
            'typed version 7.9 has no _all' => [
                '7.9',
                '_all',
                false,
            ],
            'typeless version 7.9' => [
                '7.9',
                'dynamic',
                true,
            ],
        ];
    }

    /**
     * Get IndexManagerTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager
     */
    protected function getIndexManagerMock(array $methods = null)
    {
        return $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexManager::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get Index mock
     * @param string $name
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index
     */
    protected function getIndexMock($name)
    {
        $index = $this->getMockBuilder(\Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Index::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $index->setBaseName($name);
        return $index;
    }
}
