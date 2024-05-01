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
use Sugarcrm\Sugarcrm\Elasticsearch\Adapter\Client;
use Sugarcrm\Sugarcrm\Elasticsearch\Container;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\OneModulePerIndexStrategy;
use Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
 */
class IndexPoolTest extends TestCase
{
    /**
     * @covers ::registerStrategies
     * @covers ::addStrategy
     */
    public function testRegisterStrategies()
    {
        $indexPool = $this->getIndexPoolMock();
        TestReflection::callProtectedMethod($indexPool, 'registerStrategies');
        $strategies = TestReflection::getProtectedValue($indexPool, 'strategies');

        $this->assertEquals(2, safeCount($strategies));
        $this->assertEquals($strategies[IndexPool::DEFAULT_STRATEGY], StaticStrategy::class);
        $this->assertEquals($strategies[IndexPool::SINGLE_MODULE_STRATEGY], OneModulePerIndexStrategy::class);
    }

    /**
     * @covers ::normalizeIndexName
     * @dataProvider providerTestNormalizeIndexName
     */
    public function testNormalizeIndexName($prefix, $name, $output)
    {
        $indexPool = $this->getIndexPoolMock();
        TestReflection::setProtectedValue($indexPool, 'prefix', $prefix);
        $res = $indexPool->normalizeIndexName($name);
        $this->assertEquals($res, $output);
    }

    public function providerTestNormalizeIndexName()
    {
        return [
            ['foo', 'bar', 'foo_bar'],
            ['', 'bar', 'bar'],
            ['', 'Bar', 'bar'],
            ['Foo', 'Bar', 'foo_bar'],
        ];
    }

    /**
     * @covers ::getStrategy
     * @dataProvider providerTestGetStrategy
     */
    public function testTestGetStrategy($version, $module, $config, $loadedId, $oneIndexConfig, $expected)
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['getElasticServerVersion'])
            ->getMock();

        $client->expects($this->any())
            ->method('getElasticServerVersion')
            ->willReturn($version);

        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        TestReflection::setProtectedValue($container, 'client', $client);

        $indexPool = $this->getIndexPoolMock(['getIndexStrategyFromConfig']);
        $indexPool->expects($this->any())
            ->method('getIndexStrategyFromConfig')
            ->willReturn($oneIndexConfig);

        TestReflection::setProtectedValue($indexPool, 'container', $container);
        TestReflection::setProtectedValue($indexPool, 'config', $config);

        $staticStrategy = $this->getStaticStrategyMock();
        $staticStrategy->setIdentifier($loadedId);

        $loaded = [$loadedId => $staticStrategy];
        TestReflection::setProtectedValue($indexPool, 'loaded', $loaded);
        TestReflection::callProtectedMethod($indexPool, 'registerStrategies');
        $class = $indexPool->getStrategy($module);
        $this->assertEquals($class->getIdentifier(), $expected);
    }

    public function providerTestGetStrategy()
    {
        return [
            'ES 5.x, takes config' => [
                '5.4',
                'module_has_config_ES5x',
                ['module_has_config_ES5x' => ['strategy' => 'archive']],
                'archive',
                IndexPool::DEFAULT_STRATEGY,
                'archive',
            ],
            'ES 5.x, has no config, $oneIndexConfig has no impact' => [
                '5.6',
                'module_has_no_config_ES5x',
                [],
                '',
                IndexPool::SINGLE_MODULE_STRATEGY,
                IndexPool::DEFAULT_STRATEGY,
            ],
            'ES 6.x and up, has config, $oneIndexConfig has no impact' => [
                '6.2.3',
                'module_has_config_Es6x',
                ['module_has_config_Es6x' => ['strategy' => 'archive']],
                'archive',
                IndexPool::DEFAULT_STRATEGY,
                'archive',
            ],
            'ES 7.x, has no config, $oneIndexConfig has impact' => [
                '7.7.0',
                'module_has_no_config_for_Es7x',
                [],
                '',
                IndexPool::DEFAULT_STRATEGY,
                IndexPool::DEFAULT_STRATEGY,
            ],
            'ES 7.x, has no config, $oneIndexConfig has impact' => [
                '7.7.0',
                'module_has_no_config_for_Es7x',
                [],
                '',
                IndexPool::SINGLE_MODULE_STRATEGY,
                IndexPool::SINGLE_MODULE_STRATEGY,
            ],
        ];
    }

    /**
     * Get IndexPoolTest Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\IndexPool
     */
    protected function getIndexPoolMock(array $methods = null)
    {
        return $this->getMockBuilder(IndexPool::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * Get StaticStrategy Mock
     * @param array $methods
     * @return \Sugarcrm\Sugarcrm\Elasticsearch\Index\Strategy\StaticStrategy
     */
    protected function getStaticStrategyMock(array $methods = null)
    {
        return $this->getMockBuilder(StaticStrategy::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();
    }
}
